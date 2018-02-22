<?php
/**
 * Project ${PROJECT_URL}
 *
 * @link      ${GITHUB_URL} Source code
 */

namespace Sta\DeepSocialPhpApiClient\Entity;


/**
 * @method int getStatusCode()
 * @method string getError()
 * @method string getErrormsg()
 * @method string getMessage()
 * @method string getEntityId()
 */
class Error extends AbstractEntity
{
    public const ERROR_NO_QUOTA_REMAINING = 'no_quota_remaining';
    public const ERROR_INVALID_API_TOKEN = 'invalid_api_token';
    public const ERROR_BAD_REQUEST = 'bad_request';
    public const ERROR_ACCOUNT_IS_PRIVATE = 'account_is_private';
    public const ERROR_RATELIMIT_EXCEEDED = 'Ratelimit_exceeded';
    public const ERROR_EMPTY_WALL = 'empty_wall';
    public const ERROR_ACCOUNT_REMOVED = 'account_removed';
    public const ERROR_ACCOUNT_NOT_FOUND = 'account_not_found';
}
