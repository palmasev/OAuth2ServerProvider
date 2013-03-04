<?php
namespace Palma\Silex\OAuth2ServerProvider\ServiceProvider;

use Silex\ServiceProviderInterface;
use Silex\Application;
use Palma\Silex\OAuth2ServerProvider\Storage\Client;
use Palma\Silex\OAuth2ServerProvider\Storage\Scope;
use Palma\Silex\OAuth2ServerProvider\Storage\Session;
use OAuth2\AuthServer;
use OAuth2\ResourceServer;

class OAuth2ServerProvider implements ServiceProviderInterface
{
	public function register (Application $app)
	{
		$app['oauth2.storage.client'] = $app->share(function (Application $app) {
			return new Client($app['db']);
		});

		$app['oauth2.storage.scope'] = $app->share(function (Application $app) {
			return new Scope($app['db']);
		});

		$app['oauth2.storage.session'] = $app->share(function (Application $app) {
			return new Session($app['db']);
		});

		$app['oauth2.authserver'] = $app->share(function (Application $app) {
			return new AuthServer(
				$app['oauth2.storage.client'],
				$app['oauth2.storage.session'],
				$app['oauth2.storage.scope']
			);
		});

		$app['oauth2.resourceserver'] = $app->share(function (Application $app) {
			return new ResourceServer(
				$app['oauth2.storage.session'],
				$app['oauth2.storage.scope']
			);
		});
	}

	public function boot (Application $app) 
	{
	}
}