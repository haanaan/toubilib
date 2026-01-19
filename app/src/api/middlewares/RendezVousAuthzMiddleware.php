<?php
declare(strict_types=1);

namespace toubilib\api\middlewares;

use toubilib\api\dto\UserProfileDTO;
use toubilib\core\application\ports\api\RendezVousAuthzServiceInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Routing\RouteContext;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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
        // Extraire le token JWT de l'en-tête Authorization
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (empty($authHeader)) {
            return $this->jsonError($handler, $request, 401, 'Missing Authorization header');
        }

        // Vérifier le format Bearer
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $this->jsonError($handler, $request, 401, 'Invalid Authorization header format');
        }

        $token = $matches[1];

        // Décoder le token JWT (pas besoin de valider car fait par la gateway)
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            
            // Créer le profil utilisateur depuis le token
            $profile = new UserProfileDTO(
                id: (string) $decoded->sub,
                email: $decoded->email ?? '',
                role: $decoded->role ?? ''
            );

        } catch (\Exception $e) {
            return $this->jsonError($handler, $request, 401, 'Invalid token: ' . $e->getMessage());
        }

        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        if ($route === null) {
            return $this->jsonError($handler, $request, 400, 'Route not found');
        }

        $pattern = $route->getPattern();
        $method = $request->getMethod();
        $args = $route->getArguments();

        $authorized = false;

        if ($pattern === '/rendezvous/{id}' && $method === 'GET') {
            $rdvId = $args['id'] ?? null;
            if ($rdvId === null) {
                return $this->jsonError($handler, $request, 400, 'Missing rendezvous id');
            }
            $authorized = $this->authzService->canAccessRendezVous($profile, (string) $rdvId);
        }

        if ($pattern === '/praticiens/{id}/agenda' && $method === 'GET') {
            $praticienId = $args['id'] ?? null;
            if ($praticienId === null) {
                return $this->jsonError($handler, $request, 400, 'Missing praticien id');
            }
            $authorized = $this->authzService->canAccessAgenda($profile, (string) $praticienId);
        }

        if ($pattern === '/rendezvous' && $method === 'POST') {
            // Pour la création de RDV, utiliser le corps parsé
            $body = $request->getParsedBody();
            
            if (!is_array($body)) {
                return $this->jsonError($handler, $request, 400, 'Invalid request body');
            }
            
            // Vérifier que l'utilisateur authentifié est soit le patient soit le praticien concerné
            $patientId = $body['patient_id'] ?? null;
            $praticienId = $body['praticien_id'] ?? null;
            
            if ($profile->role === 'patient' && $patientId === $profile->id) {
                $authorized = true;
            } elseif ($profile->role === 'praticien' && $praticienId === $profile->id) {
                $authorized = true;
            }
        }

        if (!$authorized) {
            return $this->jsonError($handler, $request, 403, 'Forbidden');
        }

        return $handler->handle($request);
    }

    private function jsonError(RequestHandler $handler, Request $request, int $status, string $message): Response
    {
        $response = $handler->handle($request);
        $response->getBody()->rewind();
        $response->getBody()->write(json_encode(['error' => $message]));
        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }
}
