<?php
/**
 * Project ${PROJECT_URL}
 *
 * @link      ${GITHUB_URL} Source code
 */

namespace Sta\DeepSocialPhpApiClient\Entity;

class AudienceInterestsItem
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var float
     */
    protected $percentage;

    /**
     * AudienceInterestsItem constructor.
     *
     * @param string $name
     * @param float $percentage
     */
    public function __construct(string $name, float $percentage)
    {
        $this->name       = $name;
        $this->percentage = $percentage;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(?string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return float
     */
    public function getPercentage(): ?float
    {
        return $this->percentage;
    }

    /**
     * @param float $percentage
     *
     * @return $this
     */
    public function setPercentage(?float $percentage)
    {
        $this->percentage = $percentage;

        return $this;
    }

}
