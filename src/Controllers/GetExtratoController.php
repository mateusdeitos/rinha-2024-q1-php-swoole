<?php

namespace App\Controllers;

use App\Database\Db;
use App\Services\ExtratoService;
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

	public function run() {
		$extratoService = new ExtratoService(new Db());

		$extrato = $extratoService->getExtrato($this->clienteId);
		if (!$extrato) {
			$this->response->status(404);
			return;
		}

		$this->response->header("Content-Type", "application/json");
		$this->response->end(json_encode($extrato));
	}

	public static function match(string $uri, &$matches = []): bool {
		return preg_match("/clientes\/(\d+)\/extrato/", $uri, $matches);
	}
}
