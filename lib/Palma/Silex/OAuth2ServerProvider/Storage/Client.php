<?php
namespace Palma\Silex\OAuth2ServerProvider\Storage;

use OAuth2\Storage\ClientInterface;

class Client extends AbstractStorage implements ClientInterface
{
	/**
	 * <code>
	 * Array
	 * (
	 *     [client_id] => (string) The client ID
	 *     [client secret] => (string) The client secret
	 *     [redirect_uri] => (string) The redirect URI used in this request
	 *     [name] => (string) The name of the client
	 * )
	 * </code>
	 * 
	 * @param  string     $clientId     The client's ID
	 * @param  string     $clientSecret The client's secret (default = "null")
	 * @param  string     $redirectUri  The client's redirect URI (default = "null")
	 * @return bool|array               Returns false if the validation fails, array on success
	 */
	public function getClient($clientId = null, $clientSecret = null, $redirectUri = null)
	{
		$qb = $this->connection->createQueryBuilder();
		$qb
			->select('client.id as client_id, client.secret as client_secret, endpoint.redirect_uri as redirect_uri, client.name as name')
			->from('oauth_clients', 'client')
			->leftJoin('client', 'oauth_client_endpoints', 'endpoint', 'endpoint.client_id=client.id')
			->where('client.id = :client_id')
			->setParameter('client_id', $clientId);

		if ($redirectUri!=null) {
			$qb
				->andWhere('endpoint.redirect_uri = :redirect_uri')
				->setParameter('redirect_uri', $redirect_uri);
		}
		if ($clientSecret!=null) {
			$qb
				->andWhere('client.secret = :secret')
				->setParameter('secret', $clientSecret);
		}
		
		if ($clientId==null || ($redirectUri==null && $clientSecret==null)) {
			throw new \Exception("Error Processing Request", 1);
		}

		return $qb->execute()->fetchArray();
	}
}