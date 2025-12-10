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

class RendezVousAuthzMiddleware implements MiddlewareInterface
{
    public function __construct(private RendezVousAuthzServiceInterface $authzService)
    {
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        /** @var UserProfileDTO|null $profile */
        $profile = $request->getAttribute('userProfile');
        if ($profile === null) {
            return $this->jsonError($handler, $request, 401, 'Missing authenticated user profile');
        }

        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        if ($route === null) {
            return $this->jsonError($handler, $request, 400, 'Route not found');
        }

        $pattern = $route->getPattern();          // ex: '/rendezvous/{id}'
        $method = $request->getMethod();         // 'GET' ou 'DELETE'
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
