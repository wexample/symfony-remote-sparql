## Installation

```php
// config/bundles.php
Wexample\SymfonyRemote\WexampleSymfonyRemoteBundle::class => ['all' => true],
Wexample\SymfonyRemoteSparql\WexampleSymfonyRemoteSparqlBundle::class => ['all' => true],
```

With no configuration, Wikidata's query service is the one endpoint. Name the app in `user_agent`: Wikidata turns away clients that do not say who they are.

```yaml
# config/packages/wexample_symfony_remote_sparql.yaml
wexample_symfony_remote_sparql:
  user_agent: 'my-app/1.0'
```
