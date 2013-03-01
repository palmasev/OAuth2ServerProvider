<?php
namespace Palma\Silex\OAuth2ServerProvider\Storage;
use Doctrine\DBAL\Connection;

abstract class AbstractStorage
{
	protected $connection;

	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
	}
}