<?php

namespace toubilib\api\middlewares;

use toubilib\api\dto\InputRendezVousDTO;
class InputRendezVousMiddleware
{
    public function handle(array $requestData, callable $next)
    {
        if (empty($requestData['praticienId'])) {
            throw new \InvalidArgumentException('praticienId obligatoire');
        }
        if (empty($requestData['debut']) || empty($requestData['fin'])) {
            throw new \InvalidArgumentException('Créneau obligatoire');
        }

        $debut = \DateTimeImmutable::createFromFormat(DATE_ATOM, $requestData['debut']);
        $fin = \DateTimeImmutable::createFromFormat(DATE_ATOM, $requestData['fin']);
        if (!$debut || !$fin) {
            throw new \InvalidArgumentException('Format de date invalide');
        }

        $dto = new InputRendezVousDTO(
            id: $requestData['id'] ?? null,
            praticienId: (string) $requestData['praticienId'],
            debut: $debut->format(DATE_ATOM),
            fin: $fin->format(DATE_ATOM),
            motif: $requestData['motif'] ?? null,
            patientId: $requestData['patientId'] ?? null,
            patientEmail: $requestData['patientEmail'] ?? null,
        );

        return $next($dto);
    }
}
