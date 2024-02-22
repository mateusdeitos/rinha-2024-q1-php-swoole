<?php

namespace App\Services;

use App\Database\Db;
use App\DTO\ExtratoDTO;
use App\DTO\TransacaoDTO;

class ExtratoService {

	private Db $db;

	public function __construct(Db $db) {
		$this->db = $db;
	}

	public function getExtrato(int $clienteId): ExtratoDTO|null {
		$statement = $this->db->getConnection()->prepare("SELECT saldo, limite FROM clientes WHERE id = :cliente_id");

		$statement->bindParam(":cliente_id", $clienteId, \PDO::PARAM_INT);
		$statement->execute();

		$extrato = $statement->fetchObject(ExtratoDTO::class);
		
		$statement = $this->db->getConnection()->prepare("SELECT id, cliente_id, valor, descricao, tipo, realizada_em FROM transacoes WHERE cliente_id = :cliente_id ORDER BY id DESC");

		$statement->bindParam(":cliente_id", $clienteId, \PDO::PARAM_INT);
		$statement->execute();

		while ($transacao = $statement->fetchObject(TransacaoDTO::class)) {
			$extrato->addTransacao($transacao);
		}

		return $extrato;
	}
}
