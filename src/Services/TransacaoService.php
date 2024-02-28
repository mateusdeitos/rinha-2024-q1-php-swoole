<?php

namespace App\Services;

use App\Database\Pool;
use App\DTO\ExtratoDTO;
use App\DTO\TransacaoBodyDTO;
use App\Enum\TipoTransacaoEnum;
use Exception;
use PDO;
use stdClass;
use Swoole\Coroutine as Co;
use Swoole\Database\PDOProxy;

class TransacaoService
{
    public function __construct(private Pool $pool) {}

    public function createTransacao(TransacaoBodyDTO $transacaoBodyDTO): string|null
    {
		
		return $this->pool->runCallback(function (PDOProxy|PDO $connection) use ($transacaoBodyDTO) {
			$valor = $transacaoBodyDTO->getValor();
			$clienteId = $transacaoBodyDTO->getClienteId();
			$tipo = $transacaoBodyDTO->getTipo() === TipoTransacaoEnum::CREDITO ? "c" : "d";
			$descricao = $transacaoBodyDTO->getDescricao();

			$fn = $transacaoBodyDTO->getTipo() === TipoTransacaoEnum::CREDITO ? "creditar" : "debitar";
			
			$statement = $connection->prepare("SELECT $fn(:cliente_id, :valor, :descricao) as result");
			$statement->bindParam(":cliente_id", $clienteId, \PDO::PARAM_INT);
			$statement->bindParam(":valor", $valor, \PDO::PARAM_INT);
			$statement->bindParam(":descricao", $descricao, \PDO::PARAM_STR);

			$statement->execute();

			$response = $statement->fetchObject();

			if ($tipo === "c" || $response->result != "NULL") {
				return $response->result;
			}

			return null;
		});
    }

}
