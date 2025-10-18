<?php
declare(strict_types=1);

namespace toubilib\api\actions;

use toubilib\core\application\usecases\AuthenticationProvider;
use toubilib\core\domain\entities\exceptions\AuthenticationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SigninAction
{
    private AuthenticationProvider $authProvider;

    public function __construct(AuthenticationProvider $authProvider)
    {
        $this->authProvider = $authProvider;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $params = (array) $request->getParsedBody();

        if (
            empty($params['email']) || !is_string($params['email']) ||
            empty($params['password']) || !is_string($params['password'])
        ) {
            $error = ['error' => 'Email et mot de passe doivent être fournis et valides.'];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $tokens = $this->authProvider->signin($params['email'], $params['password']);
            $response->getBody()->write(json_encode($tokens));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (AuthenticationException $ex) {
            $error = ['error' => $ex->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    }
}
