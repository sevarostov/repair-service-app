<?php

namespace App\Exceptions;

use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Response;

/**
 * Исключение, выбрасываемое при возникновении ошибок, которые можно показывать клиенту
 */
class UserException extends ApiException implements Responsable {
	/**
	 * @inheritDoc
	 */
	public function toResponse($request): Response {
		return response()->json($this->getData());
	}

	/**
	 * Получить данные ответа
	 *
	 * @return array
	 */
	protected function getData(): array {
		return [
			"success" => false,
			"code" => $this->getError()->value,
			"error" => $this->getError()->getDescription(),
		];
	}
}
