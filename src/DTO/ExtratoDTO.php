<?php

namespace App\DTO;

use DateTime;
use JsonSerializable;

class ExtratoDTO implements JsonSerializable {

	public int $saldo;
	public int $limite;
	private DateTime $data_extrato;

	/**
	 * @var array<TransacaoDTO>
	 */
	private array $ultimas_transacoes = [];

	public function __construct() {
		$this->data_extrato = new DateTime();
	}

	public function addTransacao(TransacaoDTO $transacaoDTO): void {
		$this->ultimas_transacoes[] = $transacaoDTO;
	}

	public function jsonSerialize(): array {
		return [
			'saldo' => $this->saldo,
			'limite' => $this->limite,
			'data_extrato' => $this->data_extrato->format(DateTime::ATOM),
			'ultimas_transacoes' => $this->ultimas_transacoes
		];
	}

}
