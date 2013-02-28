<?php
use OAuth2\Storage\ClientInterface;

class Client implements ClientInterface
{
	protected $connection;

	public function __construct($connection)
	{
		$this->connection = $connection;
	}

	/**
	 * @param  string     $clientId     The client's ID
	 * @param  string     $clientSecret The client's secret (default = "null")
	 * @param  string     $redirectUri  The client's redirect URI (default = "null")
	 * @return bool|array               Returns false if the validation fails, array on success
	 */
	public function getClient($clientId = null, $clientSecret = null, $redirectUri = null)
	{
		$qb = $this->connection->createQueryBuilder();
		$qb
			->select('client.id, client.secret, endpoint.redirect_uri, client.name')
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

		$query = $qb->getSql();
		return $qb->execute()->fetchArray();
	}
}