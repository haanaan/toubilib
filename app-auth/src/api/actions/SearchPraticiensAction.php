<?php
declare(strict_types=1);

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\core\application\ports\api\PraticienServiceInterface;

class SearchPraticiensAction
{
    private PraticienServiceInterface $service;

    public function __construct(PraticienServiceInterface $service)
    {
        $this->service = $service;
    }

    public function __invoke($request, $response, $args)
    {
        $ville = $request->getQueryParams()['ville'] ?? null;
        $specialite = $request->getQueryParams()['specialite'] ?? null;

        $result = $this->service->search($ville, $specialite);

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
