<?php
declare(strict_types=1);

namespace toubilib\api\middlewares;

use toubilib\api\dto\UserProfileDTO;
use toubilib\api\provider\jwt\JwtService;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;

class AuthnMiddleware implements MiddlewareInterface
{
    public function __construct(private JwtService $jwtService)
    {
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader)) {
            return $this->jsonError($handler, $request, 401, 'Missing Authorization header');
        }

        if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return $this->jsonError($handler, $request, 400, 'Invalid Authorization header format');
        }

        $jwt = $matches[1];

        try {
            $payload = $this->jwtService->verify($jwt);
        } catch (ExpiredException) {
            return $this->jsonError($handler, $request, 401, 'Token expired');
        } catch (SignatureInvalidException | BeforeValidException) {
            return $this->jsonError($handler, $request, 401, 'Invalid token');
        } catch (\Exception) {
            return $this->jsonError($handler, $request, 401, 'Invalid token');
        }

        if (!isset($payload->sub, $payload->email, $payload->role)) {
            return $this->jsonError($handler, $request, 401, 'Invalid token payload');
        }

        $profile = new UserProfileDTO(
            id: (string) $payload->sub,
            email: (string) $payload->email,
            role: (string) $payload->role
        );

        $request = $request->withAttribute('userProfile', $profile);

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
