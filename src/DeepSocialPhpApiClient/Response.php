<?php
/**
 * Project ${PROJECT_URL}
 *
 * @link      ${GITHUB_URL} Source code
 */

namespace Sta\DeepSocialPhpApiClient;


use Psr\Http\Message\ResponseInterface;
use Sta\DeepSocialPhpApiClient\Entity\AbstractEntity;
use Sta\DeepSocialPhpApiClient\Entity\Error;
use Sta\DeepSocialPhpApiClient\Exception\EntityClassMustBeAnSubClassOfAbstractEntity;
use Sta\DeepSocialPhpApiClient\Exception\InvalidJsonString;

class Response implements \JsonSerializable
{
    /**
     * @var ResponseInterface
     */
    protected $httpResponse;
    /**
     * @var \Sta\DeepSocialPhpApiClient\Entity\AbstractEntity
     */
    protected $entity = false;
    /**
     * @var bool
     */
    protected $_hasError = null;
    /**
     * @var \Sta\DeepSocialPhpApiClient\Entity\Error
     */
    protected $errorEntity = false;
    /**
     * @var string
     */
    protected $_bodyContents = false;
    /**
     * @var string
     */
    private $entityClass;

    /**
     * Response constructor.
     * @param ResponseInterface $httpResponse
     * @param $entityClass
     * @throws EntityClassMustBeAnSubClassOfAbstractEntity
     */
    public function __construct(\Psr\Http\Message\ResponseInterface $httpResponse, $entityClass)
    {
        $this->httpResponse = $httpResponse;
        $this->entityClass = $entityClass;

        if (!is_subclass_of($entityClass, AbstractEntity::class)) {
            throw new EntityClassMustBeAnSubClassOfAbstractEntity(
                sprintf(
                    'Invalid entity class "%s". Please provide a entity class name that inherits from "%s".',
                    $entityClass,
                    AbstractEntity::class
                )
            );
        }
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'httpResponse' => [
                'code' => $this->httpResponse->getStatusCode(),
                'body' => $this->getBodyContents(),
            ],
            'hasError' => $this->hasError(),
            'entity' => $this->getEntity(),
            'errorEntity' => $this->getErrorEntity(),
        ];
    }

    /**
     * Returns the current response body content, if there is a current response.
     * This method exists just to ensure we will not waste time reading the whole stream every time.
     *
     * @return string
     */
    protected function getBodyContents(): ?string
    {
        if ($this->_bodyContents === false) {
            $this->httpResponse->getBody()->rewind();
            $this->_bodyContents = $this->httpResponse->getBody()->getContents();
        }

        return $this->_bodyContents;
    }

    /**
     * Returns true if the response contains an error.
     *
     * @return bool
     */
    public function hasError()
    {
        if ($this->_hasError === null) {
            $statusCode = $this->httpResponse->getStatusCode();
            $isItAnErrorStatusCode = $statusCode > 399 && $statusCode < 600;

            $abstractEntity = null;
            if (!$isItAnErrorStatusCode) {
                try {
                    $abstractEntity = $this->_convertBodyResponseToEntity($this->entityClass, $this->getBodyContents());
                    $this->entity = $abstractEntity;
                } catch (\Exception $e) {
                }
            }
            $this->_hasError = $isItAnErrorStatusCode || !$abstractEntity;
        }

        return $this->_hasError;
    }

    /**
     * Convert the response string we got from DeepSocial to an PHP object, so we can work with the DeepSocial data
     * using type hint from ours IDE and others stuffs.
     *
     * @param string $entityClass
     * @param string $bodyAsJsonString
     * @return AbstractEntity
     */
    protected function _convertBodyResponseToEntity(string $entityClass, string $bodyAsJsonString): AbstractEntity
    {
        // Clear json_last_error()
        json_encode(null);

        $jsonDecodeResult = json_decode($bodyAsJsonString, true);
        if ($jsonDecodeResult === false || !is_array($jsonDecodeResult)) {
            throw new InvalidJsonString(
                sprintf(
                    'Could not interpret the string as an JSON. JSON error: "%s - %s". String received: %s',
                    json_last_error(),
                    json_last_error_msg(),
                    $bodyAsJsonString
                )
            );
        }

        return new $entityClass($jsonDecodeResult);
    }

    /**
     * Get the Entity representing the response from DeepSocial.
     * Hands up! If the response is an error, this method will return null.
     *
     * @return AbstractEntity
     *
     * @see \Sta\DeepSocialPhpApiClient\Response::getErrorEntity()
     */
    public function getEntity(): AbstractEntity
    {
        if ($this->entity === false && !$this->hasError()) {
            $this->entity = $this->_convertBodyResponseToEntity($this->entityClass, $this->getBodyContents());
        }

        return $this->entity ?: null;
    }

    /**
     * The error we got from DeepSocial request, if any error occurred.
     * It will return null if no error occurred.
     *
     * @return \Sta\DeepSocialPhpApiClient\Entity\Error
     * @see \Sta\DeepSocialPhpApiClient\Response::getEntity()
     */
    public function getErrorEntity(): ?Error
    {
        if ($this->errorEntity === false) {
            $this->errorEntity = null;
            if ($this->hasError()) {
                $this->errorEntity = $this->_convertBodyResponseToEntity(Error::class, $this->getBodyContents());
            }
        }

        return $this->errorEntity;
    }

    /**
     * The real response object that is being wrapped.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getHttpResponse(): \Psr\Http\Message\ResponseInterface
    {
        return $this->httpResponse;
    }

    /**
     * Necessary because this object will be serialized when cached.
     *
     * @return array
     */
    public function __sleep()
    {
        $this->hasError();
        $this->getErrorEntity();

        return [
            '_hasError',
            'entity',
            'errorEntity',
        ];
    }

}
