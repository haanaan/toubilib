<?php
declare(strict_types=1);

namespace AppRdv\api\middlewares;

use AppRdv\api\dto\UserProfileDTO;
use AppRdv\core\application\ports\api\RendezVousAuthzServiceInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Routing\RouteContext;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Exception\HttpForbiddenException;

class RendezVousAuthzMiddleware implements MiddlewareInterface
{
    private string $jwtSecret;

    public function __construct(
        private RendezVousAuthzServiceInterface $authzService,
        string $jwtSecret
    ) {
        $this->jwtSecret = $jwtSecret;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        // Extraire le token de l'en-tête Authorization
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (empty($authHeader)) {
            throw new HttpUnauthorizedException($request, 'Missing Authorization header');
        }

        // Vérifier le format Bearer
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            throw new HttpUnauthorizedException($request, 'Invalid Authorization header format');
        }

        $token = $matches[1];

        // Décoder le token JWT (pas besoin de valider, c'est fait par la gateway)
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            
            // Créer le profil utilisateur depuis le token
            $profile = new UserProfileDTO(
                id: (string) $decoded->sub,
                email: $decoded->email ?? '',
                role: $decoded->role ?? ''
            );

        } catch (\Exception $e) {
            throw new HttpUnauthorizedException($request, 'Invalid token: ' . $e->getMessage());
        }

        // Récupérer le contexte de la route
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        if ($route === null) {
            throw new HttpUnauthorizedException($request, 'Route not found');
        }

        $pattern = $route->getPattern();
        $method = $request->getMethod();
        $args = $route->getArguments();

        $authorized = false;

        // Vérifier les autorisations selon la route
        if ($pattern === '/rendezvous/{id}' && $method === 'GET') {
            $rdvId = $args['id'] ?? null;
            if ($rdvId === null) {
                throw new HttpUnauthorizedException($request, 'Missing rendezvous id');
            }
            $authorized = $this->authzService->canAccessRendezVous($profile, (string) $rdvId);
        }

        if ($pattern === '/praticiens/{id}/agenda' && $method === 'GET') {
            $praticienId = $args['id'] ?? null;
            if ($praticienId === null) {
                throw new HttpUnauthorizedException($request, 'Missing praticien id');
            }
            $authorized = $this->authzService->canAccessAgenda($profile, (string) $praticienId);
        }

        if ($pattern === '/rendezvous' && $method === 'POST') {
            $body = json_decode((string) $request->getBody(), true);
            $authorized = $this->authzService->canCreateRendezVous($profile, $body ?? []);
        }

        if (!$authorized) {
            throw new HttpForbiddenException($request, 'Forbidden: insufficient permissions');
        }

        // Ajouter le profil utilisateur à la requête pour les actions suivantes
        $request = $request->withAttribute('userProfile', $profile);

        return $handler->handle($request);
    }
}
