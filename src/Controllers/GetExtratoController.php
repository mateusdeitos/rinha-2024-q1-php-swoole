<?php

namespace App\Controllers;

use App\Database\Pool;
use App\Services\ExtratoService;
use DateTime;
use Swoole\Http\Request;
use Swoole\Http\Response;

class GetExtratoController {

	private int $clienteId;

	public function __construct(
		private Request $request,
		private Response $response
	) {
		$matches = [];
		self::match($request->server["request_uri"], $matches);
		$this->clienteId = intval($matches[1]);
	}

	public function run(Pool $pool) {
		$extratoService = new ExtratoService($pool);

		$extrato = $extratoService->getExtrato($this->clienteId);
		if (!$extrato) {
			throw new \Exception("Cliente não encontrado", 404);
		}

        $this->response->write(json_encode([
			"saldo" => [
				"total" => $extrato->saldo,
				"data_extrato" => (new DateTime())->format(DateTime::ATOM),
				"limite" => $extrato->limite,
			],
			"ultimas_transacoes" => $extrato->ultimas_transacoes
		]));
	}

	public static function match(string $uri, &$matches = []): bool {
		return preg_match("/clientes\/(\d+)\/extrato/", $uri, $matches);
	}
}
