<?php
declare(strict_types=1);

namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\core\application\usecases\RegisterPatientService;
use toubilib\core\application\exceptions\AuthenticationException;

class RegisterPatientAction
{
    public function __construct(private RegisterPatientService $service)
    {
    }

    public function __invoke(Request $request, Response $response, array $args = []): Response
    {
        $params = (array) $request->getParsedBody();

        if (
            empty($params['email']) || !is_string($params['email']) ||
            empty($params['password']) || !is_string($params['password'])
        ) {
            return $this->json($response, ['error' => 'Email et mot de passe doivent être fournis.'], 400);
        }

        $email = trim($params['email']);
        $password = $params['password'];

        try {
            $id = $this->service->register($email, $password);

            $response->getBody()->write(json_encode([
                'id' => $id,
                'email' => $email,
                'role' => 'patient',
            ]));

            return $response
                ->withStatus(201)
                ->withHeader('Content-Type', 'application/json');
        } catch (AuthenticationException $e) {
            return $this->json($response, ['error' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            return $this->json($response, ['error' => 'Erreur serveur'], 500);
        }
    }

    private function json(Response $response, array $data, int $status): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }
}
