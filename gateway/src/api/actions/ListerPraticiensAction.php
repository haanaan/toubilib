<?php
declare(strict_types=1);

namespace gateway\api\actions;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ListerPraticiensAction
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $apiResponse = $this->client->get('/praticiens');

        $bodyContent = (string) $apiResponse->getBody();

        $response->getBody()->write($bodyContent);

        return $response
            ->withStatus($apiResponse->getStatusCode())
            ->withHeader('Content-Type', $apiResponse->getHeaderLine('Content-Type'));
    }

}
