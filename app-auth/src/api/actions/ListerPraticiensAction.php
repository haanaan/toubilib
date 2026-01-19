<?php
declare(strict_types=1);

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\ports\api\PraticienServiceInterface;
use toubilib\core\application\ports\api\AgendaPraticienServiceInterface;

class ListerPraticiensAction
{
    public function __construct(
        private PraticienServiceInterface $service,
        private AgendaPraticienServiceInterface $agenda
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $q = $request->getQueryParams();
        $includeNext = isset($q['include']) && strtolower((string) $q['include']) === 'next';

        $from = null;
        $to = null;
        if ($includeNext) {
            $from = $this->parseDate($q['from'] ?? 'now');
            $to = $this->parseDate($q['to'] ?? '+30 days');
            if (!$from || !$to || $from >= $to) {
                $response->getBody()->write(json_encode(['error' => 'Invalid or missing from/to'], JSON_UNESCAPED_UNICODE));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        $dtos = $this->service->listerPraticiens();
        $payload = [];

        foreach ($dtos as $dto) {
            $row = $dto->toArray();
            if ($includeNext) {
                $next = $this->agenda->getProchainRdv($dto->id, $from, $to);
                $row['nextRdv'] = $next ? $next->toArray() : null;
            }
            $payload[] = $row;
        }

        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function parseDate(?string $s): ?\DateTimeImmutable
    {
        if (!$s)
            return null;
        try {
            return new \DateTimeImmutable($s);
        } catch (\Throwable) {
            return null;
        }
    }
}
