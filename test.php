<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once 'vendor/autoload.php';

$app = new Application();
$app['debug'] = true;
error_reporting(E_ALL);
	ini_set('display_errors', 'On');
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
	'db.options' => array(
		'driver' => 'pdo_mysql',
		'host' => 'localhost',
		'user' => 'root',
		'password' => 'mio',
		'charset' => 'utf8',
		'dbname' => 'oauth_test'
	)
));

$app->register(new Palma\Silex\OAuth2ServerProvider\ServiceProvider\OAuth2ServerProvider());

$app->register(new Silex\Provider\SessionServiceProvider());

$app['oauth2.authserver']->addGrantType(new \OAuth2\Grant\AuthCode());

$checkToken = function(Request $request, Application $app) 
{
	try {
		$app['oauth2.resourceserver']->isValid();
		return null;
	}
	catch(\Exception $e) {
		return new Response($e->getMessage());
	}
};

$app->get('/', function(Application $app) {
	$params = $app['oauth2.authserver']->checkAuthoriseParams();
	$app['session']->set('parametros', $params);
	return $app->redirect('/test.php/signin');
});

$app->get('/signin', function(Application $app) {
	// Check the authorization params are set
	if ( !($app['session']->has('parametros')))
	{
		throw new Exception('Missing auth parameters');
	}
	$params = $app['oauth2.authserver']->checkAuthoriseParams();
	//return $app->redirect('/test.php/signin');
});

$app->get('/protegido', function(Application $app, Request $request) use ($checkToken) {
	//$checkToken();
	return "Protegido";
})->before($checkToken);

$app->run();