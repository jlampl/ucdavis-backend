<?php
namespace UCDavis\Credentials;

/**
 * Credentials for access to MongoDB.
 */
class MongoCredential implements ICredential
{
	const HOST = '';

	public function getUserName()
	{
		// TODO: Setup user account
	}

	public function getPassword()
	{
		// TODO: Setup user account
	}

	public function getHost()
	{
		return self::HOST;
	}
}
?>
