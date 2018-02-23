<?php

namespace Sta\DeepSocialPhpApiClient;

class ConfigProvider
{
    public function __invoke()
    {
        return $this->getConfig();
    }

    public function getConfig()
    {
        $config = [
            'dependencies' => $this->getDependencyConfig(),
        ];

        return $config;
    }

    /**
     * Provide default container dependency configuration.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'factories' => [
                DeepSocialClient::class => DeepSocialClientFactory::class,
            ],
            'aliases' => [
                Deep
            ]
        ];
    }
}
