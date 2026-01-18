<?php
namespace gateway\api\actions;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetPraticienByIdAction
{
    private Client $client;
    private string $apiBaseUrl;

    public function __construct(Client $client, string $apiBaseUrl)
    {
        $this->client = $client;
        $this->apiBaseUrl = rtrim($apiBaseUrl, '/');
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? null;
        $apiResponse = $this->client->get($this->apiBaseUrl . '/praticiens/' . $id, [
            'http_errors' => false,
        ]);

        $body = (string) $apiResponse->getBody();
        $response->getBody()->write($body);

        return $response
            ->withStatus($apiResponse->getStatusCode())
            ->withHeader('Content-Type', $apiResponse->getHeaderLine('Content-Type'));
    }
}
