<?php
declare(strict_types=1);

namespace toubilib\api\actions;

use toubilib\api\provider\AuthnProvider;
use toubilib\core\application\exceptions\AuthenticationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RefreshAction
{
    private AuthnProvider $authProvider;

    public function __construct(AuthnProvider $authProvider)
    {
        $this->authProvider = $authProvider;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $params = (array) $request->getParsedBody();

        if (empty($params['refreshToken']) || !is_string($params['refreshToken'])) {
            $error = ['error' => 'Le refresh token doit être fourni.'];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $tokens = $this->authProvider->refresh($params['refreshToken']);
            $response->getBody()->write(json_encode($tokens));
            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (AuthenticationException $ex) {
            $error = ['error' => $ex->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    }
}
