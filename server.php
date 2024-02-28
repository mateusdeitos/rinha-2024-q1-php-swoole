<?php

require_once 'vendor/autoload.php';

use App\Controllers\CreateTransacaoController;
use App\Controllers\GetExtratoController;
use App\Database\Pool;
use Swoole\Database\PDOProxy;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

$port = intval(getenv("PORT"));
$server = new Server("localhost", $port);

\Swoole\Runtime::enableCoroutine();

$server->on("start", function (Server $server) {
    echo "Swoole http server is started at http://localhost:{$server->port}\n";
});

$server->on("request", function (Request $request, Response $response) {
	$pool = new Pool();
	$response->setHeader("Content-Type", "application/json");
	$uri = $request->server["request_uri"];
	$matches = [];

	if (preg_match("/clientes\/(\d+)\/extrato/", $uri, $matches)) {
		$clienteId = intval($matches[1]);
		$extrato = getExtrato($clienteId, $pool);
		$response->status(200);
		$response->end($extrato);
		return;
	}

	if (preg_match("/clientes\/(\d+)\/transacoes/", $uri, $matches)) {
		try {
			$clienteId = intval($matches[1]);
			$extrato = createTransacao($clienteId, $request, $pool);
			$response->status(200);
			$response->end($extrato);
		} catch (\Throwable $th) {
			$response->status($th->getCode());
			$response->end();
		}
		return;
	}

	$response->status(404);
	$response->end();
});

function getExtrato(int $clienteId, Pool $pool) {
	return $pool->runCallback(function (PDOProxy|PDO $connection) use ($clienteId) {
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

function createTransacao(int $clienteId, Request $request, Pool $pool) {
	return $pool->runCallback(function (PDOProxy|PDO $connection) use ($clienteId, $request) {
		$body = json_decode($request->rawContent());
        if (!is_object($body)) {
            throw new \Exception("Corpo da requisição inválido", 422);
        }

        if (!is_int($body->valor)) {
            throw new \Exception("valor inválido", 422);
        }

        if (!in_array($body->tipo, ["c", "d"])) {
            throw new \Exception("Tipo de transação inválido", 422);
        }

        if (!is_string($body->descricao)) {
            throw new \Exception("Descrição inválida", 422);
        }
		
		$descricao = preg_replace("/[^a-zA-Z0-9]/", "", $body->descricao);
		if (!is_string($descricao)) {
			throw new \Exception("Descrição inválida", 422);
		}

		if (strlen($descricao) > 10 || strlen($descricao) < 1) {
			throw new \Exception("Descrição inválida", 422);
		}

		$fn = $body->tipo === "c" ? "creditar" : "debitar";
		
		$statement = $connection->prepare("SELECT $fn(:cliente_id, :valor, :descricao) as result");
		$statement->bindParam(":cliente_id", $clienteId, \PDO::PARAM_INT);
		$statement->bindParam(":valor", $body->valor, \PDO::PARAM_INT);
		$statement->bindParam(":descricao", $body->descricao, \PDO::PARAM_STR);

		$statement->execute();

		$response = $statement->fetchObject();

		if ($response->result === null) {
			throw new \Exception("Erro ao criar transação", 422);
		}

		return $response->result;
	});
}

$server->start();
