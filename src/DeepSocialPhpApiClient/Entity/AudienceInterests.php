<?php
/**
 * Project ${PROJECT_URL}
 *
 * @link      ${GITHUB_URL} Source code
 */

namespace Sta\DeepSocialPhpApiClient\Entity;

/**
 * @method \Sta\DeepSocialPhpApiClient\Entity\AudienceInterestsItem get($key, $default = null)
 */
class AudienceInterests extends ConcreteCollection
{
    public function __construct(array $data)
    {
        $finalList = [];
        foreach ($data as $key => $value) {
            if (!($value instanceof AudienceInterestsItem)) {
                $finalList[] = new AudienceInterestsItem($key, $value);
            }
        }

        parent::__construct($finalList);
    }
}
