<?php

namespace App\DTO;

use App\Enum\TipoTransacaoEnum;

class TransacaoBodyDTO {

	public function __construct(
		private int $clienteId,
		private int $valor,
		private string $descricao,
		private TipoTransacaoEnum $tipo
	) {}

	public function getClienteId(): int {
		return $this->clienteId;
	}

	public function getValor(): int {
		return abs($this->valor);
	}

	public function getDescricao(): string {
		return $this->descricao;
	}

	public function getTipo(): TipoTransacaoEnum {
		return $this->tipo;
	}

}
