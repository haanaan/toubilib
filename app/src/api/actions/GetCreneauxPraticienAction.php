<?php
declare(strict_types=1);

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\ports\api\AgendaPraticienServiceInterface;
class GetCreneauxPraticienAction
{
    public function __construct(private AgendaPraticienServiceInterface $agenda)
    {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $profile = $request->getAttribute('userProfile');

        $pid = (string) $args['id'];
        $q = $request->getQueryParams();
        $from = $this->parse($q['from'] ?? null);
        $to = $this->parse($q['to'] ?? null);

        if (!$from || !$to || $from >= $to) {
            $response->getBody()->write(json_encode(['error' => 'Invalid or missing from/to'], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $dtos = $this->agenda->getCreneauxOccupes($pid, $from, $to);
        $payload = array_map(fn($d) => $d->toArray(), $dtos);

        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function parse(?string $s): ?\DateTimeImmutable
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
