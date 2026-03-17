<?php

namespace App\Exceptions;

use App\Enums\ApiError;
use RuntimeException;
use Throwable;

/**
 * Исключение с установкой ошибки API
 */
class ApiException extends RuntimeException {
	/**
	 * Конструктор
	 *
	 * @param ApiError $error Ошибка API
	 * @param string $message Сообщение об ошибке
	 * @param int $code Код ошибки
	 * @param Throwable|null $previous Предыдущее исключение
	 */
	public function __construct(private readonly ApiError $error, string $message = "", int $code = 0, ?Throwable $previous = null) {
		parent::__construct($message ?: $error->getDescription(), $code, $previous);
	}

	/**
	 * Получить ошибку API
	 *
	 * @return ApiError
	 */
	public function getError(): ApiError {
		return $this->error;
	}
}
