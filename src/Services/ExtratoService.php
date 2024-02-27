<?php

namespace App\Services;

use App\Database\Pool;
use App\DTO\ExtratoDTO;
use App\DTO\TransacaoDTO;
use PDO;
use Swoole\Database\PDOProxy;

class ExtratoService {

	public function __construct(private Pool $pool) {}

	public function getExtrato(int $clienteId, bool $withTransacoes = true): object|null {
		return $this->pool->runCallback(function (PDOProxy|PDO $connection) use ($clienteId, $withTransacoes) {
			$statement = $connection->prepare("SELECT saldo, limite FROM clientes WHERE id = :cliente_id");
	
			$statement->bindParam(":cliente_id", $clienteId, \PDO::PARAM_INT);
			$statement->execute();
	
			$extrato = $statement->fetchObject();

			if (!$extrato) {
				return null;
			}

			if (!$withTransacoes) {
				return $extrato;
			}
			
			$statement = $connection->prepare((
				"SELECT *
				   FROM transacoes 
				 WHERE cliente_id = :cliente_id 
				 ORDER BY realizada_em DESC 
				 LIMIT 10"
			));
	
			$statement->bindParam(":cliente_id", $clienteId, \PDO::PARAM_INT);
			$statement->execute();

			$extrato->ultimas_transacoes = $statement->fetchAll(\PDO::FETCH_OBJ);

			// while ($transacao = $statement->fetchObject()) {
			// 	$extrato->addTransacao(TransacaoDTO::fromObject($transacao));
			// }
	
			return $extrato;
		});
	}
}
