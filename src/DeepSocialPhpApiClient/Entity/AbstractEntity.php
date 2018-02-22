<?php
/**
 * Project ${PROJECT_URL}
 *
 * @link      ${GITHUB_URL} Source code
 */

namespace Sta\DeepSocialPhpApiClient\Entity;

use Sta\DeepSocialPhpApiClient\Entity\Exception\AllObjectAttributesOfAnEntityMustAlsoBeAnInstanceOfAnEntity;

abstract class AbstractEntity
{
    /**
     * @var \ReflectionClass[]
     */
    protected static $classReflection = [];
    /**
     * @var array
     */
    protected $data = [];
    /**
     * @var array
     */
    protected $cache = [];

    /**
     * AbstractEntity constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getRawData()
    {
        return $this->data;
    }

    public function __call($methodName, $arguments)
    {
        $attributeName = preg_replace('/^get/', '', $methodName);

        return $this->get($attributeName);
    }

    /**
     * Returns the value of an attribute.
     *
     * @param $attributeName
     *      The name of the attribute, write in either camel case, or snake case (eg: content_geo_statistic or
     *      ContentGeoStatistic).
     *
     * @return mixed
     *
     * @throws \Sta\DeepSocialPhpApiClient\Entity\Exception\AllObjectAttributesOfAnEntityMustAlsoBeAnInstanceOfAnEntity
     * @throws \Sta\DeepSocialPhpApiClient\Entity\Exception\AttributeNotFound
     */
    public function get($attributeName)
    {
        $result = null;
        $patternToMatchAgeAttribute = '/^[aA]ge(\d\d)_(\d\d)$/';
        $patternToMatchAge65OrMoreAttribute = '/^[aA]ge65OrMore$/';

        $normalizeAttributeName = preg_replace($patternToMatchAge65OrMoreAttribute, '65+', $attributeName);
        $normalizeAttributeName = preg_replace($patternToMatchAgeAttribute, '$1-$2', $normalizeAttributeName);

        $nameAsSnakeCaseWithFirstLower = $this->_camelCaseToSnakeCase($attributeName, 'lower');
        $cacheKey = sprintf('%s:%s', get_class($this), $nameAsSnakeCaseWithFirstLower);

        if (array_key_exists($cacheKey, $this->cache)) {
            $result = $this->cache[$cacheKey];
        } else {
            $rawData = null;
            $deepSocialAttrName = null;
            $nameAsCamelCaseFirstLower = $this->_snakeCaseToCamelCase($attributeName, 'lower');
            $nameAsSnakeCaseWithFirstUpper = ucfirst($nameAsSnakeCaseWithFirstLower);

            if (array_key_exists($nameAsSnakeCaseWithFirstLower, $this->data)) {
                $deepSocialAttrName = $nameAsSnakeCaseWithFirstLower;
            } else if (array_key_exists($nameAsSnakeCaseWithFirstUpper, $this->data)) {
                $deepSocialAttrName = $nameAsSnakeCaseWithFirstUpper;
            } else if (array_key_exists($nameAsCamelCaseFirstLower, $this->data)) {
                $deepSocialAttrName = $nameAsCamelCaseFirstLower;
            } else if (array_key_exists($normalizeAttributeName, $this->data)) {
                $deepSocialAttrName = $normalizeAttributeName;
            } else if ($this instanceof Genders) {
                if ($nameAsCamelCaseFirstLower == 'male') {
                    $deepSocialAttrName = 'MALE';
                } else if ($nameAsCamelCaseFirstLower == 'female') {
                    $deepSocialAttrName = 'FEMALE';
                }
            }

            $isAgeAttribute = !!preg_match($patternToMatchAgeAttribute, $attributeName);
            $isAgeAttribute = $isAgeAttribute || !!preg_match($patternToMatchAge65OrMoreAttribute, $attributeName);

//            if (!$deepSocialAttrName && !$isAgeAttribute) {
//                $validKeys = [];
//                foreach (array_keys($this->data) as $key) {
//                    $validKeys[] = "$key => {$this->_snakeCaseToCamelCase($key)}";
//                }
//                throw new AttributeNotFound(
//                    sprintf(
//                        'There is no such attribute called "%s" in the class "%s". For this class, these are ' .
//                        'the valid attributes: "%s".',
//                        $attributeName,
//                        get_class($this),
//                        implode('", "', $validKeys)
//                    )
//                );
//            }

            $rawData = null;
            if ($deepSocialAttrName) {
                if (array_key_exists($deepSocialAttrName, $this->data)) {
                    $rawData = $this->data[$deepSocialAttrName];
                } else {
                    if ($this instanceof Genders) {
                        $rawData = null;
                    }
                }
            }
            $nameAsCamelCaseFirstUpper = ucfirst($nameAsCamelCaseFirstLower);
            $responseClassName = __NAMESPACE__ . '\\' . $nameAsCamelCaseFirstUpper;
            if (is_array($rawData) && count($rawData) == 2
                && array_key_exists('FEMALE', $rawData) && array_key_exists('MALE', $rawData)
            ) {
                $responseClassName = Genders::class;
            }
            if ($rawData === null && $this instanceof GendersPerAge && $isAgeAttribute) {
                $rawData = [];
                $responseClassName = Genders::class;
            }

            if (is_array($rawData)
                && $this instanceof ContentGeoStatistic
                && preg_match('/\\Cities$/', $responseClassName)
            ) {
                $result = array_map(
                    function ($rawCityData) {
                        return new City($rawCityData);
                    },
                    $rawData
                );
            } else if (is_array($rawData) && class_exists($responseClassName)) {
                $classParents = class_parents($responseClassName);
                if (!isset($classParents[self::class]) && !isset($classParents[AbstractCollection::class])) {
                    throw new AllObjectAttributesOfAnEntityMustAlsoBeAnInstanceOfAnEntity(
                        sprintf(
                            'Error while trying to access the attribute "%s::%s". I do not know how to create ' .
                            'the an instance of the class "%s". If your entity has an attribute which is another ' .
                            'object, make sure all of those attributes also extends "%s" or "%s".',
                            get_class($this),
                            $nameAsCamelCaseFirstLower,
                            $responseClassName,
                            self::class,
                            AbstractCollection::class
                        )
                    );
                }
                $result = new $responseClassName($rawData === null ? [] : $rawData);
            } else {
                $result = $rawData;
            }

            $this->cache[$cacheKey] = $result;
        }

        return $result;
    }

    /**
     * @param string $string
     *
     * @param string $first
     *      Use 'lower' or 'upper'. Anything different of these values will be ignored.
     *
     * @return string
     */
    private function _camelCaseToSnakeCase($string, $first = 'lower')
    {
        $result = preg_replace_callback(
            '/([a-z])([A-Z])(?![A-Z])/',
            function ($matches) {
                return $matches[1] . '_' . mb_strtolower($matches[2], 'UTF-8');
            },
            $string
        );

        switch ($first) {
            case 'lower':
                $result = lcfirst($result);
                break;
            case 'upper':
                $result = ucfirst($result);
                break;
        }

        return $result;
    }

    /**
     * @param string $string
     *
     * @param string $first
     *      Use 'lower' or 'upper'. Anything different of these values will be ignored.
     *
     * @return string
     */
    private function _snakeCaseToCamelCase($string, $first = 'upper')
    {
        $result = preg_replace_callback(
            '/_(.)/',
            function ($matches) {
                return mb_strtoupper($matches[1], 'UTF-8');
            },
            $string
        );

        switch ($first) {
            case 'lower':
                $result = lcfirst($result);
                break;
            case 'upper':
                $result = ucfirst($result);
                break;
        }

        return $result;
    }
}
