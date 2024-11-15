<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro;

use GuzzleHttp\Client;
use Sumedia\WinestroApi\Winestro\Request\RequestInterface;
use Sumedia\WinestroApi\Winestro\Response\ResponseInterface;
use Symfony\Component\DependencyInjection\Container;

class Connection implements ConnectionInterface
{
    private array $parameters = [
        'output' => 'json'
    ];

    private string $url = '';

    private Client $client;

    private array $runtimeCache = [];

    public function __construct(
        private Container $container,
        private LogManager $logManager
    ) {
        $this->client = new Client();
    }

    public function setParameter(string $key, string $value): void
    {
        $this->parameters[$key] = $value;
    }

    public function getParameter(string $key): string
    {
        return $this->parameters[$key] ?? '';
    }

    public function hasParameter(string $key): bool
    {
        return isset($this->parameters[$key]);
    }

    public function removeParameter(string $key): void
    {
        unset($this->parameters[$key]);
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function executeRequest(RequestInterface $request): ResponseInterface
    {
        foreach ($request->getParameters() as $key => $value) {
            $this->setParameter($key, (string) $value);
        }

        $this->logRequest();

        $data = $this->post();

        $this->logResponse($data);

        return $this->createResponse($request->getResponseName(), $data);
    }

    private function post(): array
    {
        sort($this->parameters);
        $runtimeCacheId = md5($this->url . ':' . serialize($this->parameters));
        if (isset($this->runtimeCache[$runtimeCacheId])) {
            return $this->runtimeCache[$runtimeCacheId];
        }

        $response = $this->client->post($this->url, [
            'query' => $this->getParameters(),
            'allow_redirects' => [
                'strict' => true,
                'protocols' => ['https']
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Response status code: ' . $response->getStatusCode());
        }

        $jsonAsString = $response->getBody()->getContents();
        $responseAsArray = json_decode($jsonAsString, true);
        $this->runtimeCache[$runtimeCacheId] = $responseAsArray;
        return $responseAsArray;
    }

    private function createResponse(string $responseName, array $response): ResponseInterface
    {
        $responseClass = 'Sumedia\\WinestroApi\\Winestro\\Response\\' . $responseName;
        $responseInstance = $this->container->get($responseClass);
        $responseInstance->populate($response);
        return $responseInstance;
    }

    private function logRequest(): void
    {
        $exportData = $this->getParameters();
        if (isset($exportData['apiCODE'])) {
            $exportData['apiCODE'] = 'xxx';
        }
        if (isset($exportData['kto'])) {
            $exportData['kto'] = 'xxx';
        }
        if (isset($exportData['iban'])) {
            $exportData['iban'] = 'xxx';
        }

        $this->logManager->debug('Connector request:' . var_export($exportData, true));
        $this->logManager->debug('For WBO Contact: ' . $this->url . '?' . http_build_query($exportData, '', '&', PHP_QUERY_RFC3986));
    }

    private function logResponse(array $response): void
    {
        $this->logManager->debug('Connection response: ' . var_export($response, true));
    }
}
