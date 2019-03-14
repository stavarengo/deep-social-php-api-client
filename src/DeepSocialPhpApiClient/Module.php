<?php
/**
 * Created by PhpStorm.
 * User: stavarengo
 * Date: 14/03/19
 * Time: 17:07
 */

namespace Sta\DeepSocialPhpApiClient;


class Module
{
    public function getConfig()
    {
        $provider = new ConfigProvider();
        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }
}