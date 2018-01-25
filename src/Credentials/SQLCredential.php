<?php
namespace UCDavis\Credentials;

/**
 * Credential for SQL database
 */
class SQLCredential implements ICredential
{
	const USERNAME = '';
	const PASSWORD = '';
	const HOST   = '';

	public function getUserName()
	{
		return self::USERNAME;
	}

	public function getPassword()
	{
		return self::PASSWORD;
	}

	public function getHost()
	{
		return self::HOST;
	}
}
?>
