<?php
/**
 * Project ${PROJECT_URL}
 *
 * @link      ${GITHUB_URL} Source code
 */

namespace Sta\DeepSocialPhpApiClient\Entity;

/**
 * @method \Sta\DeepSocialPhpApiClient\Entity\AbstractCollection get($key, $default = null)
 */
class AudienceBrandAffinity extends ConcreteCollection
{
    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (!($value instanceof AbstractCollection)) {
                $data[$key] = new ConcreteCollection($value);
            }
        }

        parent::__construct($data);
    }
}
