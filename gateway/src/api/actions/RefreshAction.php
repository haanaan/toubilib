<?php
declare(strict_types=1);

namespace gateway\api\actions;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RefreshAction
{
    private Client $client;
    private string $apiBaseUrl;

    public function __construct(Client $client, string $apiBaseUrl)
    {
        $this->client = $client;
        $this->apiBaseUrl = rtrim($apiBaseUrl, '/');
    }

    public function __invoke(Request $request, Response $response): Response
    {
        try {
            $body = $request->getBody()->getContents();
            
            // Transférer la requête vers l'application toubilib
            $apiResponse = $this->client->request('POST', $this->apiBaseUrl . '/auth/refresh', [
                'http_errors' => false,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $request->getHeaderLine('Authorization'),
                ],
                'body' => $body,
            ]);

            $bodyContent = (string) $apiResponse->getBody();
            $response->getBody()->write($bodyContent);

            return $response
                ->withStatus($apiResponse->getStatusCode())
                ->withHeader('Content-Type', 'application/json');

        } catch (GuzzleException $e) {
            $error = [
                'error' => true,
                'message' => 'Erreur lors de la connexion au service d\'authentification',
                'details' => $e->getMessage(),
            ];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(503)->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            $error = [
                'error' => true,
                'message' => 'Erreur serveur',
                'details' => $e->getMessage(),
            ];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}
