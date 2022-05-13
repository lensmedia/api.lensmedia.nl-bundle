<?php

namespace Lens\Bundle\LensApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('lens_lens_api');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('features')
                    ->children()
                        ->booleanNode('form_exclusion_extension')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('http_client_options')
                    ->children()
                        ->scalarNode('base_uri')
                            ->info('The URI to resolve relative URLs, following rules in RFC 3985, section 2.')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('auth_basic')
                            ->info('An HTTP Basic authentication "username:password".')
                        ->end()
                        ->scalarNode('auth_bearer')
                            ->info('A token enabling HTTP Bearer authorization.')
                        ->end()
                        ->scalarNode('auth_ntlm')
                            ->info('A "username:password" pair to use Microsoft NTLM authentication (requires the cURL extension).')
                        ->end()
                        ->arrayNode('query')
                            ->info('Associative array of query string values merged with the base URI.')
                            ->useAttributeAsKey('key')
                            ->beforeNormalization()
                                ->always(function ($config) {
                                    if (!\is_array($config)) {
                                        return [];
                                    }
                                    if (!isset($config['key'], $config['value']) || \count($config) > 2) {
                                        return $config;
                                    }

                                    return [$config['key'] => $config['value']];
                                })
                            ->end()
                            ->normalizeKeys(false)
                            ->scalarPrototype()->end()
                        ->end()
                        ->arrayNode('headers')
                            ->info('Associative array: header => value(s).')
                            ->useAttributeAsKey('name')
                            ->normalizeKeys(false)
                            ->variablePrototype()->end()
                        ->end()
                        ->integerNode('max_redirects')
                            ->info('The maximum number of redirects to follow.')
                        ->end()
                        ->scalarNode('http_version')
                            ->info('The default HTTP version, typically 1.1 or 2.0, leave to null for the best version.')
                        ->end()
                        ->arrayNode('resolve')
                            ->info('Associative array: domain => IP.')
                            ->useAttributeAsKey('host')
                            ->beforeNormalization()
                                ->always(function ($config) {
                                    if (!\is_array($config)) {
                                        return [];
                                    }
                                    if (!isset($config['host'], $config['value']) || \count($config) > 2) {
                                        return $config;
                                    }

                                    return [$config['host'] => $config['value']];
                                })
                            ->end()
                            ->normalizeKeys(false)
                            ->scalarPrototype()->end()
                        ->end()
                        ->scalarNode('proxy')
                            ->info('The URL of the proxy to pass requests through or null for automatic detection.')
                        ->end()
                        ->scalarNode('no_proxy')
                            ->info('A comma separated list of hosts that do not require a proxy to be reached.')
                        ->end()
                        ->floatNode('timeout')
                            ->info('The idle timeout, defaults to the "default_socket_timeout" ini parameter.')
                        ->end()
                        ->floatNode('max_duration')
                            ->info('The maximum execution time for the request+response as a whole.')
                        ->end()
                        ->scalarNode('bindto')
                            ->info('A network interface name, IP address, a host name or a UNIX socket to bind to.')
                        ->end()
                        ->booleanNode('verify_peer')
                            ->info('Indicates if the peer should be verified in an SSL/TLS context.')
                        ->end()
                        ->booleanNode('verify_host')
                            ->info('Indicates if the host should exist as a certificate common name.')
                        ->end()
                        ->scalarNode('cafile')
                            ->info('A certificate authority file.')
                        ->end()
                        ->scalarNode('capath')
                            ->info('A directory that contains multiple certificate authority files.')
                        ->end()
                        ->scalarNode('local_cert')
                            ->info('A PEM formatted certificate file.')
                        ->end()
                        ->scalarNode('local_pk')
                            ->info('A private key file.')
                        ->end()
                        ->scalarNode('passphrase')
                            ->info('The passphrase used to encrypt the "local_pk" file.')
                        ->end()
                        ->scalarNode('ciphers')
                            ->info('A list of SSL/TLS ciphers separated by colons, commas or spaces (e.g. "RC3-SHA:TLS13-AES-128-GCM-SHA256"...)')
                        ->end()
                        ->arrayNode('peer_fingerprint')
                            ->info('Associative array: hashing algorithm => hash(es).')
                            ->normalizeKeys(false)
                            ->children()
                                ->variableNode('sha1')->end()
                                ->variableNode('pin-sha256')->end()
                                ->variableNode('md5')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
