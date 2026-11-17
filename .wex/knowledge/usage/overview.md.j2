## Asking

```php
public function __construct(private SparqlClient $wikidata) {}

$rows = $this->wikidata->select('SELECT ?person ?personLabel WHERE {
  ?person wdt:P39 wd:Q191954 .
  SERVICE wikibase:label { bd:serviceParam wikibase:language "fr,mul,en". }
}');
// [['person' => 'Q3052772', 'personLabel' => 'Emmanuel Macron'], …]
```

`SparqlValueHelper::toDate()` reads an `xsd:dateTime` as a day, and gives null for Wikidata's unknown values, which come as blank nodes.

## Endpoints

```yaml
wexample_symfony_remote_sparql:
  user_agent: 'my-app/1.0'
  endpoints:
    wikidata:
      url: 'https://query.wikidata.org/sparql'
      entity_prefix: 'http://www.wikidata.org/entity/'
    dbpedia:
      url: 'https://dbpedia.org/sparql'
      timeout: 60        # seconds, per request
      attempts: 3        # the last failure raises RemoteException
      retry_delay: 10    # seconds, times the attempt number
```

Each endpoint gives the service `wexample_symfony_remote_sparql.endpoint.<name>`, autowired as `SparqlClient $<name>` — `SparqlClient` alone is the first one —, and the remote `sparql_<name>`, whose check asks `ASK {}`. Configuring `endpoints` replaces the default: list Wikidata too to keep it.

## When the endpoint fails

```php
$rows = $this->guard->attempt($this->wikidata, fn () => $this->wikidata->select($query), []);
```

php-remote's `RemoteGuard` logs the failure and returns the fallback: an import goes on without this answer.
