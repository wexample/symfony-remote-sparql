<?php

namespace Wexample\SymfonyRemoteSparql\Service;

use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Wexample\PhpRemote\Class\RemoteStatus;
use Wexample\PhpRemote\Exception\RemoteException;
use Wexample\PhpRemote\Interface\RemoteInterface;

/**
 * One SPARQL endpoint, asked over HTTP GET and read as plain rows: a value per
 * variable, the unbound ones left out, the entity URIs short (`Q42`) when the
 * endpoint names a prefix to cut.
 *
 * A failure — the endpoint unreachable, an error status, an answer that is no
 * SPARQL JSON — is tried again a few times, then raised as a RemoteException.
 */
class SparqlClient implements RemoteInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $key,
        private readonly string $label,
        private readonly string $url,
        private readonly string $userAgent,
        private readonly ?string $entityPrefix = null,
        private readonly float $timeout = 120,
        private readonly int $attempts = 3,
        private readonly int $retryDelay = 10,
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function checkStatus(): RemoteStatus
    {
        $this->query('ASK {}', 1);

        return RemoteStatus::up();
    }

    /**
     * @return list<array<string, string>> one row per result
     *
     * @throws RemoteException when the endpoint still fails after the last attempt
     */
    public function select(string $sparql): array
    {
        $rows = [];

        foreach ($this->query($sparql, $this->attempts)['results']['bindings'] ?? [] as $binding) {
            $row = [];

            foreach ($binding as $variable => $value) {
                $row[$variable] = 'uri' === $value['type'] && null !== $this->entityPrefix && str_starts_with($value['value'], $this->entityPrefix)
                    ? substr($value['value'], strlen($this->entityPrefix))
                    : $value['value'];
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @return array<string, mixed> the decoded SPARQL JSON
     */
    private function query(
        string $sparql,
        int $attempts
    ): array {
        // Public endpoints drop or cut a long answer now and then: asked again, they give it.
        for ($attempt = 1; ; ++$attempt) {
            try {
                $answer = $this->httpClient->request('GET', $this->url, [
                    'query' => ['query' => $sparql],
                    'headers' => [
                        'Accept' => 'application/sparql-results+json',
                        // Wikidata's service turns away requests that do not say who they are.
                        'User-Agent' => $this->userAgent,
                    ],
                    'timeout' => $this->timeout,
                ])->toArray();

                if (! isset($answer['results']['bindings']) && ! isset($answer['boolean'])) {
                    throw new RemoteException($this->key, 'The answer is no SPARQL JSON result.');
                }

                return $answer;
            } catch (ExceptionInterface|RemoteException $exception) {
                if ($attempt >= $attempts) {
                    throw $exception instanceof RemoteException ? $exception : new RemoteException($this->key, $exception->getMessage(), $exception);
                }

                sleep($attempt * $this->retryDelay);
            }
        }
    }
}
