<?php
declare(strict_types=1);

namespace toubilib\api\actions;

use toubilib\api\provider\jwt\JwtService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ValidateTokenAction
{
    private JwtService $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        // Récupérer le token depuis l'en-tête Authorization
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (empty($authHeader)) {
            $error = ['error' => 'Token manquant', 'message' => 'En-tête Authorization requis'];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        // Extraire le token (format: "Bearer <token>")
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $error = ['error' => 'Format de token invalide', 'message' => 'Format attendu: Bearer <token>'];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $token = $matches[1];

        try {
            // Vérifier le token
            $payload = $this->jwtService->verify($token);
            
            // Vérifier que c'est un access token
            if (!isset($payload->type) || $payload->type !== 'access') {
                $error = ['error' => 'Type de token invalide', 'message' => 'Seuls les access tokens sont acceptés'];
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            // Token valide - retourner les informations du payload
            $result = [
                'valid' => true,
                'user' => [
                    'id' => $payload->sub,
                    'email' => $payload->email ?? null,
                    'role' => $payload->role ?? null,
                ]
            ];
            
            $response->getBody()->write(json_encode($result));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\Firebase\JWT\ExpiredException $e) {
            $error = ['error' => 'Token expiré', 'message' => 'Le token JWT a expiré'];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            $error = ['error' => 'Signature invalide', 'message' => 'La signature du token est invalide'];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $error = ['error' => 'Token invalide', 'message' => $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    }
}
