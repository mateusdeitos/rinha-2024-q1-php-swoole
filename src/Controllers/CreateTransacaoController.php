<?php

namespace App\Controllers;

use App\Database\Pool;
use App\DTO\TransacaoBodyDTO;
use App\Enum\TipoTransacaoEnum;
use App\Services\ExtratoService;
use App\Services\TransacaoService;
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
        $extratoService = new ExtratoService($pool);

        if (!$transacaoDTO = $this->getBody()) {
            return;
        }

        try {
            $transacaoService->createTransacao($transacaoDTO);
            $extrato = $extratoService->getExtrato($this->clienteId, false);
            if (!$extrato) {
                $this->response->status(404);
                $this->response->end();
                return;
            }

            $extrato->setIgnoreTransacoes(true);
            $extrato->setIgnoreDataExtrato(true);

            $this->response->status(200);
            $this->response->header("Content-Type", "application/json");
            $this->response->end(json_encode($extrato));
        } catch (\Exception $e) {
            $this->response->status($e->getCode());
            $this->response->end($e->getMessage());
        }
    }

    private function getBody(): TransacaoBodyDTO|false
    {
        $body = json_decode($this->request->rawContent(), true);
        if (!is_array($body)) {
            $this->response->status(400);
            $this->response->end("Corpo da requisição inválido");
            return false;
        }

        if ($body["valor"] < 0) {
            $this->response->status(400);
            $this->response->end("valor inválido");
            return false;
        }

        if (!TipoTransacaoEnum::tryFrom($body["tipo"])) {
            $this->response->status(400);
            $this->response->end("Tipo de transação inválido");
            return false;
        }

        if (mb_strlen($body["descricao"]) > 10) {
            $this->response->status(400);
            $this->response->end("Descrição inválida");
            return false;
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
