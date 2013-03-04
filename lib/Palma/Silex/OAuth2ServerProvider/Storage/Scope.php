<?php
namespace Palma\Silex\OAuth2ServerProvider\Storage;

use OAuth2\Storage\ScopeInterface;


class Scope extends AbstractStorage implements ScopeInterface
{
	/**
	 * Response:
     *
     * <code>
     * Array
     * (
     *     [id] => (int) The scope's ID
     *     [scope] => (string) The scope itself
     *     [name] => (string) The scope's name
     *     [description] => (string) The scope's description
     * )
     * </code>
     *
     * @param  string     $scope The scope
     * @return bool|array If the scope doesn't exist return false 
	 */
	public function getScope($scope)
	{
		$qb = $this->connection->createQueryBuilder();
		$qb
			->select('scope.id as id, scope.scope as scope, scope.name as name, scope.description as description')
			->from('oauth_scopes', 'scope')
			->where('scope.scope = :scope')
			->setParameter('scope', $scope);

		return $qb->execute()->fetch();
	}
}