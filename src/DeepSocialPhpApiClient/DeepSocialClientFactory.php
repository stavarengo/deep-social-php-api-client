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

class DeepSocialClientFactory
{

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return DeepSocialClient
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws MissingDeepSocialConfiguration
     */
    public function __invoke(ContainerInterface $container, $requestedName = null, $options = null)
    {
        if (!is_array($options)) {
            $options = [];
        }

        $appConfig = [];
        if (isset($options['config'])) {
            $appConfig = $options['config'];
        } else if ($container->has('config')) {
            $appConfig = $container->get('config');
        }

        if (!isset($appConfig[ConfigProvider::class]['deepSocial']['apiToken'])) {
            throw new MissingDeepSocialConfiguration(
                sprintf('Missing configuration entry "%s.deepSocial.apiToken".', ConfigProvider::class)
            );
        }

        $config = $appConfig[ConfigProvider::class];

        $cachePool = null;
        if (isset($options['cachePool'])) {
            $cachePool = $options['cachePool'];
        } else if (isset($config['cachePoolFactoryName']) && $config['cachePoolFactoryName']) {
            $cachePool = $container->get($config['cachePoolFactoryName']);
        } else if ($container->has(CacheItemPoolInterface::class)) {
            $cachePool = $container->get(CacheItemPoolInterface::class);
        }

        return new DeepSocialClient($config['deepSocial']['apiToken'], $cachePool);
    }

    public function createService($serviceLocator)
    {
        return $this->__invoke($serviceLocator, self::class);
    }
}
