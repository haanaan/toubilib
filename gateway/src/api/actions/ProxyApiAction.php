<?php
namespace gateway\api\actions;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProxyApiAction
{
    private Client $client;
    private string $apiBaseUrl;
    private string $praticiensBaseUrl;
    private string $rdvBaseUrl;

    public function __construct(Client $client, string $apiBaseUrl, string $praticiensBaseUrl, string $rdvBaseUrl)
    {
        $this->client = $client;
        $this->apiBaseUrl = rtrim($apiBaseUrl, '/');
        $this->praticiensBaseUrl = rtrim($praticiensBaseUrl, '/');
        $this->rdvBaseUrl = rtrim($rdvBaseUrl, '/');
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $method = $request->getMethod();
            $uri = $request->getUri();
            $path = $uri->getPath();
            $query = $uri->getQuery();

            if (preg_match('#^/praticiens($|/)#', $path)) {
                $baseUrl = $this->praticiensBaseUrl;
            } elseif (preg_match('#^/rdv($|/)#', $path)) {
                $baseUrl = $this->rdvBaseUrl;
            } else {
                $baseUrl = $this->apiBaseUrl;
            }

            $targetUrl = $baseUrl . $path;
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
        } catch (\Throwable $e) {
            $error = [
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => explode("\n", $e->getTraceAsString()),
            ];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}
