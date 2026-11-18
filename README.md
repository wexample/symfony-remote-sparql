# symfony-remote-sparql

Version: 2.0.1

SPARQL endpoints (Wikidata's query service by default) as symfony-remotes: SELECT queries read as plain rows

## Table of Contents

- [Installation](#installation)
- [Introduction](#introduction)

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

`symfony-remote-sparql` asks SPARQL endpoints from a Symfony app — Wikidata's query service by default, any other one by configuration (DBpedia, the BnF, a triple store of one's own). A `SparqlClient` per endpoint runs `SELECT` queries and reads their answers as plain rows: a value per variable, the unbound ones left out, entity URIs cut to their id (`Q42`) when the endpoint names the prefix to cut.

Each endpoint is a `symfony-remote`, so `remote:status` checks it. A failure is tried again a few times, then raised as php-remote's `RemoteException`: run the call through `RemoteGuard` where the app must go on without the answer.
