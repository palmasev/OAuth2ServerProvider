<?php
namespace Palma\Silex\OAuth2ServerProvider\Storage;

use OAuth2\Storage\SessionInterface;

class Session extends AbstractStorage implements SessionInterface
{
	/**
     * @param  string $clientId          The client ID
     * @param  string $redirectUri       The redirect URI
     * @param  string $type              The session owner's type (default = "user")
     * @param  string $typeId            The session owner's ID (default = "null")
     * @param  string $authCode          The authorisation code (default = "null")
     * @param  string $accessToken       The access token (default = "null")
     * @param  string $refreshToken      The refresh token (default = "null")
     * @param  int    $accessTokenExpire The expiry time of an access token as a unix timestamp
     * @param  string $stage             The stage of the session (default ="request")
     * @return int                       The session ID
	 */
	public function createSession(
        $clientId,
        $redirectUri,
        $type = 'user',
        $typeId = null,
        $authCode = null,
        $accessToken = null,
        $refreshToken = null,
        $accessTokenExpire = null,
        $stage = 'requested'
    )
    {
    	$this->connection->insert(
    			'oauth_sessions', 
    			array(
    				'client_id'            => $clientId, 
    				'redirect_uri'         => $redirectUri,
    				'owner_type'           => $type,
    				'owner_id'             => $typeId,
    				'auth_code'            => $authCode,
    				'access_token'         => $accessToken,
    				'refresh_token'        => $refreshToken,
    				'access_token_expires' => $accessTokenExpire,
    				'stage'                => $stage,
    				'first_requested'      => time(),
    				'last_updated'         => time()
    				)
    		);
    	return $this->connection->lastInsertId();
    }

    /**
     * Update an OAuth session
     *
     * @param  string $sessionId         The session ID
     * @param  string $authCode          The authorisation code (default = "null")
     * @param  string $accessToken       The access token (default = "null")
     * @param  string $refreshToken      The refresh token (default = "null")
     * @param  int    $accessTokenExpire The expiry time of an access token as a unix timestamp
     * @param  string $stage             The stage of the session (default ="request")
     * @return  void
     */
    public function updateSession(
        $sessionId,
        $authCode = null,
        $accessToken = null,
        $refreshToken = null,
        $accessTokenExpire = null,
        $stage = 'requested'
    )
    {
    	$this->connection->update(
    			'oauth_sessions', 
    			array(
    				'auth_code'            => $authCode,
    				'access_token'         => $accessToken,
    				'refresh_token'        => $refreshToken,
    				'access_token_expires' => $accessTokenExpire,
    				'stage'                => $stage,
    				'last_updated'         => time()
    				),
    			array('id', $sessionId)
    		);
    }

    /**
     * Delete an OAuth session
     *
     * @param  string $clientId The client ID
     * @param  string $type     The session owner's type
     * @param  string $typeId   The session owner's ID
     * @return  void
     */
    public function deleteSession(
        $clientId,
        $type,
        $typeId
    )
    {
    	$this->connection->delete(
    			'oauth_sessions',
    			array(
    				'client_id'            => $clientId, 
    				'owner_type'           => $type,
    				'owner_id'             => $typeId,
    			)
    		);
    }

    /**
     * Validate that an authorisation code is valid
     *
     * @param  string     $clientId    The client ID
     * @param  string     $redirectUri The redirect URI
     * @param  string     $authCode    The authorisation code
     * @return  array|bool   Returns the session if the auth code
     *  is valid otherwise returns false
     */
    public function validateAuthCode(
        $clientId,
        $redirectUri,
        $authCode
    )
    {
    	$session = $this->connection->fetchAssoc(
	    		'select id, client_id, redirect_uri, owner_type, owner_id, auth_code, stage, first_requested, last_updated 
	    		 from oauth_sessions 
	    		 where client_id = ? and redirect_uri = ? and auth_code = ?',
	    		array($clientId, $redirectUri, $authCode)
    		);
    	if($session==null)
    	{
    		return false;
    	}

    	return $session;
    }

    /**
     * Validate an access token
     *
     *
     * @param  string $accessToken
     * @return boolean|array
     */
    public function validateAccessToken($accessToken)
    {
    	$session = $this->connection->fetchAssoc(
	    		'select id, owner_type, owner_id
	    		 from oauth_sessions 
	    		 where access_token = ?',
	    		array($accessToken)
    		);
    	if($session==null)
    	{
    		return false;
    	}

    	return $session;
    }

    /**
     * Return the access token for a given session
     *
     *
     * @param  int         $sessionId The OAuth session ID
     * @return string|null            Returns the access token as a string if
     *  found otherwise returns null
     */
    public function getAccessToken($sessionId)
    {
    	$session = $this->connection->fetchColumn(
	    		'select access_token
	    		 from oauth_sessions 
	    		 where id = ?',
	    		array($sessionId)
    		);

    	return $session;
    }

    /**
     * Validate a refresh token
     * @param  string $refreshToken The refresh token
     * @param  string $clientId     The client ID
     * @return int                  The session ID
     */
    public function validateRefreshToken($refreshToken, $clientId)
    {
    	$session = $this->connection->fetchColumn(
	    		'select id
	    		 from oauth_sessions 
	    		 where access_token = ? and client_id = ?',
	    		array($refreshToken, $clientId)
    		);

    	return $session;
    }

    /**
     * Update the refresh token
     *
     * @param  string $sessionId             The session ID
     * @param  string $newAccessToken        The new access token for this session
     * @param  string $newRefreshToken       The new refresh token for the session
     * @param  int    $accessTokenExpires    The UNIX timestamp of when the new token expires
     * @return void
     */
    public function updateRefreshToken($sessionId, $newAccessToken, $newRefreshToken, $accessTokenExpires)
    {
    	$this->connection->update(
    			'oauth_sessions', 
    			array(
    				'access_token'         => $newAccessToken,
    				'refresh_token'        => $newRefreshToken,
    				'access_token_expires' => $accessTokenExpire,
    				'last_updated'         => time()
    				),
    			array('id', $sessionId)
    		);
    }

    /**
     * Associates a session with a scope
     *
     * @param int    $sessionId The session ID
     * @param string $scopeId   The scope ID
     * @return void
     */
    public function associateScope($sessionId, $scopeId)
    {
    	$this->connection->insert('oauth_session_scopes', array('session_id' => $sessionId, 'scope_id' => $scopeId));
    }

    /**
     * Return the scopes associated with an access token
     * 
     * Response:
     *
     * <code>
     * Array
     * (
     *     [0] => (string) The scope
     *     [1] => (string) The scope
     *     [2] => (string) The scope
     *     ...
     *     ...
     * )
     * </code>
     *
     * @param  int   $sessionId The session ID
     * @return array
     */
    public function getScopes($sessionId)
    {
    	$scopes = $this->connection->fetchAll(
		    		'select oauth_scopes.scope 
		    		from oauth_session_scopes 
		    			join oauth_scopes on oauth_session_scopes.scope_id = oauth_scopes.id
		    		where session_id = ?',
		    		array($sessionId)
    	);

    	return array_values($scopes);
    }
}