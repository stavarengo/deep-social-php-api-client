# deep-social-php-api-client
PHP Client Library for http://deep.social API

If you are interested in a PHP API for [Deep.Social](http://deep.social), that's your library :)

## About It
 
- Depends only on PSRs.
- Optionally use cache to avoid spend your DeepSocial credits requesting data you already have.
- You can use it with any application, either if it uses or not a factories from PSR-11.
- It should be very easy to use, since I tried to keep all the source code well documented.
 
## Installation
Install via `composer`.

```
composer require stavarengo/deep-social-php-api-client:^0.0
```

## Basic Usage - More complete documentation yet to come

- Use it directly (without a factory).
  ```php
  $client = new \Sta\DeepSocialPhpApiClient\Client('YOUR_DEPPSOCIAL_API_TOKEN', null);// This 'null' means: "no cache"
  $response = $client->getAudienceData('@SOME_INSTAGRAM_USER_NAME');
  
  var_dump($response->hasError() ? $response->getErrorEntity() : $response->getEntity());
  ```

- Use our default factory (PSR-11).
  ```php
  $client = $container->get(\Sta\DeepSocialPhpApiClient\Client::class)
  
  var_dump($response->hasError() ? $response->getErrorEntity() : $response->getEntity());
  ```
