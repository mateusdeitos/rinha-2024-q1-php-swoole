<?php

namespace App\DTO;

use JsonSerializable;

class CreateTransacaoResponseDTO implements JsonSerializable {

	public int $saldo;
	public int $limite;

	public function jsonSerialize(): array {
		return [
			'saldo' => $this->saldo,
			'limite' => $this->limite,
		];
	}

}
