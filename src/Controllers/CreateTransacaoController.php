<?php

namespace App\Controllers;

use App\Database\Pool;
use App\DTO\TransacaoBodyDTO;
use App\Enum\TipoTransacaoEnum;
use App\Services\ExtratoService;
use App\Services\TransacaoService;
use DateTime;
use Swoole\Http\Request;
use Swoole\Http\Response;

class CreateTransacaoController
{
    private int $clienteId;

    public function __construct(
        private Request $request,
        private Response $response
    ) {
        $matches = [];
        self::match($request->server["request_uri"], $matches);
        $this->clienteId = intval($matches[1]);
    }

    public function run(Pool $pool)
    {
        $transacaoService = new TransacaoService($pool);

        if (!$transacaoDTO = $this->getBody()) {
            throw new \Exception("Corpo da requisição inválido", 422);
        }

		$extrato = $transacaoService->createTransacao($transacaoDTO);
		if (!$extrato) {
			throw new \Exception("Erro ao criar transação", 422);
		}

        $this->response->status(200);
        $this->response->write($extrato);
    }

    private function getBody(): TransacaoBodyDTO|false
    {
        $body = json_decode($this->request->rawContent(), true);
        if (!is_array($body)) {
            throw new \Exception("Corpo da requisição inválido", 422);
        }

        if (!is_int($body["valor"])) {
            throw new \Exception("valor inválido", 422);
        }

        if (!TipoTransacaoEnum::tryFrom($body["tipo"])) {
            throw new \Exception("Tipo de transação inválido", 422);
        }

        if (!is_string($body["descricao"])) {
            throw new \Exception("Descrição inválida", 422);
        }
		
		$descricao = preg_replace("/[^a-zA-Z0-9]/", "", $body["descricao"]);
		if (!is_string($descricao)) {
			throw new \Exception("Descrição inválida", 422);
		}

		if (strlen($descricao) > 10 || strlen($descricao) < 1) {
			throw new \Exception("Descrição inválida", 422);
		}

        return new TransacaoBodyDTO(
            $this->clienteId,
            intval($body["valor"]),
            strval($body["descricao"]),
            TipoTransacaoEnum::from($body["tipo"])
        );
    }

    public static function match(string $uri, &$matches = []): bool
    {
        return preg_match("/clientes\/(\d+)\/transacoes/", $uri, $matches);
    }
}
