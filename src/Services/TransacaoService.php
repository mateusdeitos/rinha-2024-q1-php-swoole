<?php

namespace App\Services;

use App\Database\Pool;
use App\DTO\TransacaoBodyDTO;
use App\Enum\TipoTransacaoEnum;
use Exception;
use PDO;
use Swoole\Coroutine as Co;
use Swoole\Database\PDOProxy;

class TransacaoService
{
    public function __construct(private Pool $pool) {}

    public function createTransacao(TransacaoBodyDTO $transacaoBodyDTO): void
    {
        if ($transacaoBodyDTO->getTipo() === TipoTransacaoEnum::CREDITO) {
            $this->createCredito($transacaoBodyDTO);
        } else {
            $this->createDebito($transacaoBodyDTO);
        }

    }

    private function createCredito(TransacaoBodyDTO $transacaoBodyDTO): void
    {
        $tasks = [
            fn() => $this->pool->runCallback(fn (PDOProxy|PDO $conn) => $this->insertTransacao($conn, $transacaoBodyDTO)),
            fn() => $this->pool->runCallback(fn (PDOProxy|PDO $conn) => $this->updateSaldo($conn, $transacaoBodyDTO))
        ];

        Co\batch($tasks);
    }

    private function createDebito(TransacaoBodyDTO $transacaoBodyDTO): void
    {
		$this->pool->runCallback(function (PDOProxy|PDO $connection) use ($transacaoBodyDTO) {
			$clienteId = $transacaoBodyDTO->getClienteId();
			$connection->beginTransaction();
	
			try {
				$connection->query("SELECT saldo FROM clientes WHERE id = $clienteId FOR UPDATE")->execute();
		
				$updatedSaldo = $this->updateSaldo($connection, $transacaoBodyDTO);
				$connection->commit();
				if ($updatedSaldo === 0) {
					throw new Exception("Saldo insuficiente", 422);
				}
	
				$this->insertTransacao($connection, $transacaoBodyDTO);
			} catch (\Throwable $th) {
				throw $th;
			}

		});

    }
	
    private function updateSaldo(PDOProxy|PDO $connection, TransacaoBodyDTO $transacaoBodyDTO): int
    {
        $statement = $connection->prepare("UPDATE clientes SET saldo = saldo + :valor WHERE id = :cliente_id AND ABS(saldo + :valor) <= limite");

		$clienteId = $transacaoBodyDTO->getClienteId();
		$valor = $transacaoBodyDTO->getValor();
        $statement->bindParam(":cliente_id", $clienteId, \PDO::PARAM_INT);
        $statement->bindParam(":valor", $valor, \PDO::PARAM_INT);
        $statement->execute();

		$rowCount = $statement->rowCount();

        return $rowCount;
    }

    private function insertTransacao(PDOProxy|PDO $connection, TransacaoBodyDTO $transacaoBodyDTO): void
    {
        $statement = $connection->prepare("INSERT INTO transacoes (cliente_id, valor, descricao, tipo, realizada_em) VALUES (?, ?, ?, ?, NOW())");

        $statement->execute(
            [
                $transacaoBodyDTO->getClienteId(),
				$transacaoBodyDTO->getValor(),
				$transacaoBodyDTO->getDescricao(),
				$transacaoBodyDTO->getTipo() === TipoTransacaoEnum::CREDITO ? "c" : "d",
            ]
        );

    }
}
