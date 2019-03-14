<?php

namespace Sta\DeepSocialPhpApiClient;

class ConfigProvider
{
    public function __invoke()
    {
        return  [
            'dependencies' => $this->getDependencyConfig(),
        ];
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
            ]
        ];
    }
}
