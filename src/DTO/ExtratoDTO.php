<?php

namespace App\DTO;

use DateTime;
use JsonSerializable;

class ExtratoDTO implements JsonSerializable
{
    public int $saldo;
    public int $limite;
    private DateTime $data_extrato;
    private bool $ignore_transacoes = false;
    private bool $ignore_data_extrato = false;

    /**
     * @var array<TransacaoDTO>
     */
    private array $ultimas_transacoes = [];

    public function __construct()
    {
        $this->data_extrato = new DateTime();
    }

    public function addTransacao(TransacaoDTO $transacaoDTO): void
    {
        $this->ultimas_transacoes[] = $transacaoDTO;
    }

    public function setIgnoreTransacoes(bool $ignore): void
    {
        $this->ignore_transacoes = $ignore;
    }

    public function setIgnoreDataExtrato(bool $ignore): void
    {
        $this->ignore_data_extrato = $ignore;
    }

    public function jsonSerialize(): array
    {
        $data = [
            'saldo' => $this->saldo,
            'limite' => $this->limite,
            'data_extrato' => $this->data_extrato->format(DateTime::ATOM),
            'ultimas_transacoes' => $this->ultimas_transacoes
        ];

        if ($this->ignore_transacoes) {
            unset($data['ultimas_transacoes']);
        }

        if ($this->ignore_data_extrato) {
            unset($data['data_extrato']);
        }

        return $data;
    }

}
