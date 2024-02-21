<?php

namespace App\Database;

use Exception;

class Db {

	private \PDO $connection;
	
	public function __construct() {
		$envs = [
			"DB_DRIVER",
			"DB_HOSTNAME",
			"DB_PORT",	
			"DB_INITIAL_POOL_SIZE",
			"DB_MAX_POOL_SIZE",
			"DB_PASSWORD",
			"DB_USER",
			"DATABASE",
		];

		$envMap = [];

		foreach ($envs as $env) {
			$envValue = getenv($env);
			if (!$envValue) {
				throw new Exception("Environment variable $env not found");
			}

			$envMap[$env] = $envValue;
		}

		$dsn = sprintf(
			'%s:host=%s;port=%s;dbname=%s',
			$envMap["DB_DRIVER"],
			$envMap["DB_HOSTNAME"],
			$envMap["DB_PORT"],
			$envMap["DATABASE"]
		);

		$connection = new \PDO($dsn, $envMap["DB_USER"], $envMap["DB_PASSWORD"], [
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_PERSISTENT => true,
		]);

		$this->connection = $connection;
	}

	public function getConnection(): \PDO {
		return $this->connection;
	}

}
