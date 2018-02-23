<?php
/**
 * Project ${PROJECT_URL}
 *
 * @link      ${GITHUB_URL} Source code
 */

namespace Sta\DeepSocialPhpApiClient;

use GuzzleHttp\RequestOptions;
use Psr\Cache\CacheItemPoolInterface;
use Sta\DeepSocialPhpApiClient\Entity\AudienceData;

class DeepSocialClient
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;
    /**
     * @var string
     */
    protected $apiToken;
    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $cachePoll;

    public function __construct($apiToken, ?CacheItemPoolInterface $cachePoll, array $guzzleHttpClientConfig = [])
    {
        $this->apiToken = $apiToken;

        $defaultGuzzleHttpClientConfig = [
            'base_uri' => 'https://deepsocialapi.com/',
            RequestOptions::TIMEOUT => 120,
            RequestOptions::HTTP_ERRORS => false,
        ];

        $this->httpClient = new \GuzzleHttp\Client(
            array_merge(
                $defaultGuzzleHttpClientConfig,
                $guzzleHttpClientConfig
            )
        );

        $this->cachePoll = $cachePoll;
    }

    /**
     * @param $userNameOrUserId
     *      The Instagram username which you want the data.
     *
     * @param bool $returnOnlyIfCached
     *      When true, it will avoid consuming your DeepSocial credits by only returning data that exists in cache.
     *      If the data does not exists in cache, then it returns null.
     *
     * @return Response
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getAudienceData($userNameOrUserId, bool $returnOnlyIfCached = false): ?Response
    {
        $cacheKey = strtolower($userNameOrUserId);
        $cacheItem = null;
        if ($this->cachePoll) {
            $cacheItem = $this->cachePoll->getItem($cacheKey);
            if ($cacheItem->isHit()) {
                return $this->cachePoll->getItem($cacheKey)->get();
            }
        }
        if ($returnOnlyIfCached) {
            return null;
        }

        $httpResponse = $this->httpClient->get(
            '/v1/Sampling_request',
            [
                RequestOptions::QUERY => [
                    'api_token' => $this->apiToken,
                    'url' => $userNameOrUserId,
                ],
            ]
        );

        $response = new Response($httpResponse, AudienceData::class);

        if ($cacheItem) {
            if (!$response->hasError()) {
                $cacheItem->set($response);
                $this->cachePoll->save($cacheItem);
            }
        }

        return $response;
    }
}
