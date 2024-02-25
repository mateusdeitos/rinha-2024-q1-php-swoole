<?php

namespace App\Services;

use App\Database\Pool;
use App\DTO\ExtratoDTO;
use App\DTO\TransacaoDTO;
use PDO;
use Swoole\Database\PDOProxy;

class ExtratoService {

	public function __construct(private Pool $pool) {}

	public function getExtrato(int $clienteId, bool $withTransacoes = true): ExtratoDTO|null {
		return $this->pool->runCallback(function (PDOProxy|PDO $connection) use ($clienteId, $withTransacoes) {
			$statement = $connection->prepare("SELECT saldo, limite FROM clientes WHERE id = :cliente_id");
	
			$statement->bindParam(":cliente_id", $clienteId, \PDO::PARAM_INT);
			$statement->execute();
	
			/**
			 * @var ExtratoDTO
			 */
			$extrato = $statement->fetchObject(ExtratoDTO::class);
	
			if (!$withTransacoes) {
				return $extrato;
			}
			
			$statement = $connection->prepare((
				"SELECT id, cliente_id, valor, descricao, tipo, realizada_em 
				   FROM transacoes 
				 WHERE cliente_id = :cliente_id 
				 ORDER BY id DESC 
				 LIMIT 10"
			));
	
			$statement->bindParam(":cliente_id", $clienteId, \PDO::PARAM_INT);
			$statement->execute();
	
			while ($transacao = $statement->fetchObject()) {
				$extrato->addTransacao(TransacaoDTO::fromObject($transacao));
			}
	
			return $extrato;
		});
	}
}
