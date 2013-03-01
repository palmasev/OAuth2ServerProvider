<?php
namespace Palma\Silex\OAuth2ServerProvider\Generator;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer;

class DatabaseGenerator
{
	private $connectionParams;
	private $conn;
	private $schema;

	function __construct($connectionParams)
	{
		$this->connectionParams = $connectionParams;
		$this->conn = DriverManager::getConnection($connectionParams, new Configuration());
	}

	function preRun()
	{
		$this->schema = new Schema();

		$clientsTable = $this->schema->createTable("oauth_clients");
		$clientsTable->addColumn('id', 'string', array('length' => 40));
		$clientsTable->addColumn('secret', 'string', array('length' => 40));
  		$clientsTable->addColumn('name', 'string', array('length' => 255));
  		$clientsTable->addColumn('auto_approve', 'boolean', array('defaults'=>false));
  		$clientsTable->setPrimaryKey(array("id"));

  		$endpointsTable = $this->schema->createTable('oauth_client_endpoints');
  		$endpointsTable->addColumn('id', 'integer', array('autoincrement' => true, "unsigned" => true));
  		$endpointsTable->addColumn('client_id', 'string', array('length' => 40));
  		$endpointsTable->addColumn('redirect_uri', 'string', array('length' => 255, 'notnull' => false));
  		$endpointsTable->addForeignKeyConstraint($clientsTable,
  										array('client_id'),
  										array('id'),
  										array('onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE')
  										);
		$endpointsTable->setPrimaryKey(array("id"));

  		$sessionsTable = $this->schema->createTable("oauth_sessions");
  		$sessionsTable->addColumn('id', 'integer', array('autoincrement' => true, "unsigned" => true));
  		$sessionsTable->addColumn('client_id', 'string', array('length' => 40));
  		$sessionsTable->addColumn('redirect_uri', 'string', array('length' => 255, 'default' => ''));
  		$sessionsTable->addColumn('owner_type', 'string', array('length' => 6, 'default' => 'user'));
  		$sessionsTable->addColumn('owner_id', 'string', array('length' => 255, 'default' => ''));
  		$sessionsTable->addColumn('auth_code', 'string', array('length' => 40, 'default' => ''));
  		$sessionsTable->addColumn('access_token', 'string', array('length' => 40, 'default' => ''));
  		$sessionsTable->addColumn('refresh_token', 'string', array('length' => 40, 'default' => ''));
  		$sessionsTable->addColumn('access_token_expires', 'integer', array('length' => 10, 'notnull' => false));
  		$sessionsTable->addColumn('stage', 'string', array('length' => 9, 'default' => 'requested'));
  		$sessionsTable->addColumn('first_requested', 'integer', array('length' => 10, 'notnull' => false, "unsigned" => true));
  		$sessionsTable->addColumn('last_updated', 'integer', array('length' => 10, 'notnull' => false, "unsigned" => true));
  		$sessionsTable->addForeignKeyConstraint($clientsTable,
  										array('client_id'),
  										array('id'),
  										array('onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE')
  										);
  		$sessionsTable->setPrimaryKey(array("id"));

  		$scopesTable = $this->schema->createTable("oauth_scopes");
  		$scopesTable->addColumn('id', 'integer', array('autoincrement' => true, "unsigned" => true));
  		$scopesTable->addColumn('scope', 'string', array('length' => 255, 'default' => ''));
  		$scopesTable->addColumn('name', 'string', array('length' => 255, 'default' => ''));
  		$scopesTable->addColumn('description', 'string', array('length' => 255, 'default' => ''));
  		$scopesTable->addUniqueIndex(array('scope'));
  		$scopesTable->setPrimaryKey(array("id"));

  		$sessionScopesTable = $this->schema->createTable("oauth_session_scopes");
  		$sessionScopesTable->addColumn('id', 'integer', array('autoincrement' => true, "unsigned" => true));
  		$sessionScopesTable->addColumn('session_id', 'integer', array("unsigned" => true));
  		$sessionScopesTable->addColumn('scope_id', 'integer', array("unsigned" => true));
  		$sessionScopesTable->addForeignKeyConstraint($sessionsTable,
  										array('session_id'),
  										array('id'),
  										array('onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE')
  										);
  		$sessionScopesTable->addForeignKeyConstraint($scopesTable,
  										array('scope_id'),
  										array('id'),
  										array('onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE')
  										);
		  $sessionScopesTable->setPrimaryKey(array("id"));

  		$synchronizer = new SingleDatabaseSynchronizer($this->conn);
  		return $synchronizer->getUpdateSchema($this->schema, true);
	}

	function run()
	{
		$synchronizer = new SingleDatabaseSynchronizer($this->conn);
  	$synchronizer->updateSchema($this->schema, true);

	}
}