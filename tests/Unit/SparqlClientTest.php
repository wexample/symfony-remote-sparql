<?php

namespace Wexample\SymfonyRemoteSparql\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Wexample\PhpRemote\Enum\RemoteState;
use Wexample\PhpRemote\Exception\RemoteException;
use Wexample\SymfonyRemoteSparql\Helper\SparqlValueHelper;
use Wexample\SymfonyRemoteSparql\Service\SparqlClient;

class SparqlClientTest extends TestCase
{
    private function client(MockHttpClient $httpClient): SparqlClient
    {
        return new SparqlClient($httpClient, 'sparql_wikidata', 'Wikidata', 'https://query.example/sparql', 'tests/1.0', 'http://www.wikidata.org/entity/', 5, 3, 0);
    }

    private function answer(): MockResponse
    {
        return new MockResponse(json_encode(['head' => ['vars' => ['person', 'label', 'start']], 'results' => ['bindings' => [
            [
                'person' => ['type' => 'uri', 'value' => 'http://www.wikidata.org/entity/Q3052772'],
                'label' => ['type' => 'literal', 'value' => 'Emmanuel Macron', 'xml:lang' => 'fr'],
                'start' => ['type' => 'literal', 'value' => '2017-05-14T00:00:00Z'],
            ],
            ['person' => ['type' => 'uri', 'value' => 'http://example.org/other/1']],
        ]]]));
    }

    public function testRowsAreReadPlainTheEntitiesShort(): void
    {
        $requests = [];
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$requests): MockResponse {
            $requests[] = [$url, $options['normalized_headers']['user-agent'][0] ?? null];

            return $this->answer();
        });

        $rows = $this->client($httpClient)->select('SELECT ?person WHERE {}');

        $this->assertSame([
            ['person' => 'Q3052772', 'label' => 'Emmanuel Macron', 'start' => '2017-05-14T00:00:00Z'],
            ['person' => 'http://example.org/other/1'],
        ], $rows);
        $this->assertStringStartsWith('https://query.example/sparql?query=SELECT', $requests[0][0]);
        $this->assertSame('User-Agent: tests/1.0', $requests[0][1]);
    }

    public function testAFailureIsTriedAgain(): void
    {
        $httpClient = new MockHttpClient([new MockResponse('', ['http_code' => 502]), $this->answer()]);

        $this->assertCount(2, $this->client($httpClient)->select('SELECT ?person WHERE {}'));
    }

    public function testTheLastFailureIsARemoteException(): void
    {
        $httpClient = new MockHttpClient(array_fill(0, 3, new MockResponse('', ['http_code' => 503])));

        try {
            $this->client($httpClient)->select('SELECT ?person WHERE {}');
            $this->fail('No exception.');
        } catch (RemoteException $exception) {
            $this->assertSame('sparql_wikidata', $exception->remoteKey);
        }
    }

    public function testAnAnswerOfAnotherFormatIsARemoteException(): void
    {
        $this->expectException(RemoteException::class);

        $this->client(new MockHttpClient(array_fill(0, 3, new MockResponse('{"error": "maintenance"}'))))->select('SELECT ?person WHERE {}');
    }

    public function testTheStatusAsksOnce(): void
    {
        $this->assertSame(RemoteState::Up, $this->client(new MockHttpClient(new MockResponse('{"head": {}, "boolean": true}')))->checkStatus()->state);
    }

    public function testDatesAreDays(): void
    {
        $this->assertSame('2017-05-14', SparqlValueHelper::toDate('2017-05-14T00:00:00Z')?->format('Y-m-d'));
        $this->assertNull(SparqlValueHelper::toDate('t1234567'));
        $this->assertNull(SparqlValueHelper::toDate(null));
    }
}
