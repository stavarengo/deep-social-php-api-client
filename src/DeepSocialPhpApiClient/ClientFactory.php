<?php
/**
 * Project ${PROJECT_URL}
 *
 * @link      ${GITHUB_URL} Source code
 */

namespace Sta\DeepSocialPhpApiClient;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Sta\DeepSocialPhpApiClient\Exception\MissingDeepSocialConfiguration;

class ClientFactory
{

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Client
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws MissingDeepSocialConfiguration
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $appConfig = [];
        if (isset($options['config'])) {
            $appConfig = $options['config'];
        } else if ($container->has('config')) {
            $appConfig = $container->get('config');
        }

        if (!isset($appConfig['deepSocialPhpApiClient']['deepSocial']['apiToken'])) {
            throw new MissingDeepSocialConfiguration('Missing configuration entry "deepSocialPhpApiClient.deepSocial.apiToken".');
        }

        $config = $appConfig['deepSocialPhpApiClient'];

        $cachePool = null;
        if (isset($options['cachePool'])) {
            $cachePool = $options['cachePool'];
        } else if (isset($config['cachePoolFactoryName']) && $config['cachePoolFactoryName']) {
            $cachePool = $container->get($config['cachePoolFactoryName']);
        } else if ($container->has(CacheItemPoolInterface::class)) {
            $cachePool = $container->get(CacheItemPoolInterface::class);
        }

        return new Client($config['deepSocial']['apiToken'], $cachePool);
    }
}
