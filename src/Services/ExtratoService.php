<?php

namespace App\Services;

use App\Database\Pool;
use PDO;
use Swoole\Database\PDOProxy;

class ExtratoService {

	public function __construct(private Pool $pool) {}

	public function getExtrato(int $clienteId): string|null {
		return $this->pool->runCallback(function (PDOProxy|PDO $connection) use ($clienteId) {
			$statement = $connection->prepare("SELECT extrato(:cliente_id) as result");
	
			$statement->bindParam(":cliente_id", $clienteId, \PDO::PARAM_INT);
			$statement->execute();
	
			$response = $statement->fetchObject();

			if ($response->result === "NULL") {
				return null;
			}

			return $response->result;
		});
	}
}
