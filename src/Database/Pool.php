<?php

namespace App\Database;

class Pool {

	private static ?\Swoole\Database\PDOPool $pool = null;

	public static function getPool(): \Swoole\Database\PDOPool {
		if (!self::$pool) {
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
					throw new \Exception("Environment variable $env not found");
				}

				$envMap[$env] = $envValue;
			}

			$pdoConfig = new \Swoole\Database\PDOConfig();
			$pdoConfig
				->withDriver($envMap["DB_DRIVER"])
				->withHost($envMap["DB_HOSTNAME"])
				->withPort($envMap["DB_PORT"])
				->withDbname($envMap["DATABASE"])
				->withUsername($envMap["DB_USER"])
				->withPassword($envMap["DB_PASSWORD"]);

			self::$pool = new \Swoole\Database\PDOPool(
				$pdoConfig,
				(int) $envMap["DB_MAX_POOL_SIZE"],
			);

			if (intval($envMap["DB_INITIAL_POOL_SIZE"]) > 0) {
				self::$pool->fill((int) $envMap["DB_INITIAL_POOL_SIZE"]);
			}
		}

		return self::$pool;
	}

	public static function getConnection(): \PDO|\Swoole\Database\PDOProxy {
		return self::getPool()->get();
	}

	public static function releaseConnection(\PDO|\Swoole\Database\PDOProxy $connection): void {
		self::getPool()->put($connection);
	}

	/**
	 * @param callable $callback function(\PDO|\Swoole\Database\PDOProxy $connection): mixed
	 */
	public static function runCallback(callable $callback): mixed {
		$connection = self::getConnection();
		try {
			return $callback($connection);
		} finally {
			self::releaseConnection($connection);
		}
	}
}
