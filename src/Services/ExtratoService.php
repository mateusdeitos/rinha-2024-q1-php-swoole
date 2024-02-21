<?php

namespace App\Services;

use App\Database\Db;
use App\DTO\ExtratoDTO;

class ExtratoService {

	private Db $db;

	public function __construct(Db $db) {
		$this->db = $db;
	}

	public function getExtrato(int $clienteId): ExtratoDTO|null {
		$statement = $this->db->getConnection()->prepare("SELECT saldo, limite FROM clientes WHERE id = :cliente_id");

		$statement->bindParam(":cliente_id", $clienteId, \PDO::PARAM_INT);
		$statement->execute();

		return $statement->fetchObject(ExtratoDTO::class);
	}
}
