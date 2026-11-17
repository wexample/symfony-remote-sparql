<?php

namespace Wexample\SymfonyRemoteSparql\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Wexample\SymfonyHelpers\DependencyInjection\AbstractWexampleSymfonyExtension;
use Wexample\SymfonyRemoteSparql\Service\SparqlClient;

/**
 * A SparqlClient per configured endpoint, `wexample_symfony_remote_sparql.endpoint.<name>`,
 * a remote under the key `sparql_<name>`. `SparqlClient $<name>` autowires it;
 * `SparqlClient` alone, the first endpoint.
 */
class WexampleSymfonyRemoteSparqlExtension extends AbstractWexampleSymfonyExtension
{
    public function load(
        array $configs,
        ContainerBuilder $container
    ): void {
        $config = $this->processConfiguration(new Configuration(), $configs);

        foreach ($config['endpoints'] as $name => $endpoint) {
            $id = 'wexample_symfony_remote_sparql.endpoint.'.$name;

            $container->setDefinition($id, (new Definition(SparqlClient::class, [
                new Reference('http_client'),
                'sparql_'.$name,
                $endpoint['label'] ?? $name.' SPARQL endpoint',
                $endpoint['url'],
                $config['user_agent'],
                $endpoint['entity_prefix'],
                $endpoint['timeout'],
                $endpoint['attempts'],
                $endpoint['retry_delay'],
            ]))->setAutoconfigured(true));

            $container->registerAliasForArgument($id, SparqlClient::class, (string) $name);

            if (! $container->hasAlias(SparqlClient::class)) {
                $container->setAlias(SparqlClient::class, $id);
            }
        }
    }
}
