<?php

namespace Drupal\custom_configs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Flood\FloodInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CepLookupController extends ControllerBase {

    private ClientInterface $httpClient;
    private FloodInterface $flood;

    public function __construct(ClientInterface $http_client, FloodInterface $flood) {
        $this->httpClient = $http_client;
        $this->flood = $flood;
    }

    public static function create(ContainerInterface $container): self {
        return new static(
            $container->get('http_client'),
            $container->get('flood')
        );
    }

    public function lookup(string $cep, Request $request): JsonResponse {
        $ip = (string) ($request->getClientIp() ?: 'unknown');
        if (!$this->flood->isAllowed('custom_configs.cep_lookup', 30, 60, $ip)) {
            return new JsonResponse(['message' => 'Muitas consultas. Tente novamente em instantes.'], 429, [
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ]);
        }
        $this->flood->register('custom_configs.cep_lookup', 60, $ip);

        if (!preg_match('/^\d{8}$/', $cep)) {
            return new JsonResponse(['message' => 'CEP inválido.'], 400, [
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ]);
        }

        $baseUrl = getenv('CEP_API_BASE_URL') ?: 'https://viacep.com.br/ws';

        try {
            $response = $this->httpClient->request('GET', rtrim($baseUrl, '/') . '/' . $cep . '/json/', [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'timeout' => 6,
                'connect_timeout' => 4,
                'http_errors' => FALSE,
            ]);

            $status = $response->getStatusCode();
            if ($status !== 200) {
                return new JsonResponse(['message' => 'CEP não encontrado.'], 404, [
                    'Cache-Control' => 'no-store, no-cache, must-revalidate',
                ]);
            }

            $raw = json_decode((string) $response->getBody(), TRUE);
            if (!is_array($raw)) {
                return new JsonResponse(['message' => 'Resposta inválida do serviço de CEP.'], 502, [
                    'Cache-Control' => 'no-store, no-cache, must-revalidate',
                ]);
            }

            if (!empty($raw['erro'])) {
                return new JsonResponse(['message' => 'CEP não encontrado.'], 404, [
                    'Cache-Control' => 'no-store, no-cache, must-revalidate',
                ]);
            }

            $payload = [
                'cep' => (string) ($raw['cep'] ?? ''),
                'endereco' => (string) ($raw['logradouro'] ?? ''),
                'bairro' => (string) ($raw['bairro'] ?? ''),
                'cidade' => (string) ($raw['localidade'] ?? ''),
                'uf' => strtoupper((string) ($raw['uf'] ?? '')),
                'complemento' => (string) ($raw['complemento'] ?? ''),
            ];

            return new JsonResponse($payload, 200, [
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ]);
        }
        catch (\Throwable $exception) {
            return new JsonResponse(['message' => 'Falha ao consultar CEP.'], 502, [
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ]);
        }
    }

}
