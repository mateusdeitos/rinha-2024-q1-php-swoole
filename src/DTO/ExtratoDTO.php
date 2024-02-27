<?php

namespace App\DTO;

use DateTime;
use JsonSerializable;

class ExtratoDTO
{
    public int $saldo;
    public int $limite;

    /**
     * @var array<TransacaoDTO>
     */
    private array $ultimas_transacoes = [];

    public function addTransacao(TransacaoDTO $transacaoDTO): void
    {
        $this->ultimas_transacoes[] = $transacaoDTO;
    }

    public function getSaldo(): int
    {
        return $this->saldo;
    }

    public function getLimite(): int
    {
        return $this->limite;
    }

    public function getUltimasTransacoes(): array
    {
        return $this->ultimas_transacoes;
    }

}
