<?php

namespace App\DTO;

use App\Enum\TipoTransacaoEnum;
use JsonSerializable;

class TransacaoDTO implements JsonSerializable {

	public function __construct(
		private int $id,
		private int $clienteId,
		private float $valor,
		private string $descricao,
		private TipoTransacaoEnum $tipo,
		private string $realizada_em
	) {}

	public function getId(): int {
		return $this->id;
	}

	public function getClienteId(): int {
		return $this->clienteId;
	}

	public function getValor(): float {
		return $this->valor;
	}

	public function getDescricao(): string {
		return $this->descricao;
	}

	public function getTipo(): TipoTransacaoEnum {
		return $this->tipo;
	}

	public function getRealizadaEm(): string {
		return $this->realizada_em;
	}

	public static function fromObject(object $data): self {
		return new self(
			$data->id,
			$data->cliente_id,
			$data->valor,
			$data->descricao,
			TipoTransacaoEnum::from($data->tipo),
			$data->realizada_em
		);
	}

	public function jsonSerialize(): array {
		return [
			"id" => $this->id,
			"cliente_id" => $this->clienteId,
			"valor" => $this->valor,
			"descricao" => $this->descricao,
			"tipo" => $this->tipo,
			"realizada_em" => $this->realizada_em
		];
	}

}
