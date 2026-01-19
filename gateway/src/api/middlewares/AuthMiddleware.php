<?php
declare(strict_types=1);

namespace gateway\api\middlewares;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Slim\Exception\HttpUnauthorizedException;

class AuthMiddleware implements MiddlewareInterface
{
    private Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
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

        // Valider le token auprès du microservice d'authentification
        try {
            $response = $this->httpClient->post('/tokens/validate', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token
                ]
            ]);

            // Si la validation réussit, décoder la réponse et ajouter les infos utilisateur à la requête
            $userData = json_decode((string) $response->getBody(), true);
            
            if ($userData && isset($userData['user'])) {
                // Ajouter les informations utilisateur à la requête pour les middlewares suivants
                $request = $request->withAttribute('user', $userData['user']);
            }

        } catch (GuzzleException $e) {
            // En cas d'erreur (401, etc.), lever une exception
            throw new HttpUnauthorizedException($request, 'Invalid or expired token');
        }

        // Passer au middleware suivant
        return $handler->handle($request);
    }
}
