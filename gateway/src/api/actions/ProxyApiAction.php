<?php
namespace gateway\api\actions;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProxyApiAction
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
        $method = $request->getMethod();
        $uri = $request->getUri();
        $path = $uri->getPath();
        $query = $uri->getQuery();
        $targetUrl = $this->apiBaseUrl . $path;
        if (!empty($query)) {
            $targetUrl .= '?' . $query;
        }

        $options = [
            'http_errors' => false,
            'headers' => $request->getHeaders(),
        ];
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $options['body'] = $request->getBody()->getContents();
        }

        $apiResponse = $this->client->request($method, $targetUrl, $options);
        $body = (string) $apiResponse->getBody();
        $response->getBody()->write($body);

        return $response
            ->withStatus($apiResponse->getStatusCode())
            ->withHeader('Content-Type', $apiResponse->getHeaderLine('Content-Type'));
    }
}
