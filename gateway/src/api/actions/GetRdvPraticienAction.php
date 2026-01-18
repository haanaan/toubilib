<?php
declare(strict_types=1);

namespace gateway\api\actions;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetRdvPraticienAction
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
        $queryParams = $request->getQueryParams();
        $queryString = http_build_query($queryParams);
        $url = $this->apiBaseUrl . '/praticiens/' . $id . '/agenda';
        if (!empty($queryString)) {
            $url .= '?' . $queryString;
        }
        $apiResponse = $this->client->get($url, [
            'http_errors' => false,
        ]);

        $body = (string) $apiResponse->getBody();
        $response->getBody()->write($body);

        return $response
            ->withStatus($apiResponse->getStatusCode())
            ->withHeader('Content-Type', $apiResponse->getHeaderLine('Content-Type'));
    }
}
