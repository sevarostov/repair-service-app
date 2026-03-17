<?php

namespace App\Enums;

/**
 * Ошибки, которые могут возникнуть при работе с API
 */
enum ApiError: int
{
	/**
	 * Отсутствие ошибки
	 */
	case NoError = 200;

	/**
	 * Неизвестная ошибка
	 */
	case RuntimeError = 400;

	/**
	 * Ошибка валидации данных
	 */
	case ValidationError = 422;

	case Forbidden = 403;
	case Conflict = 409;

	/**
	 * Получить описание ошибки
	 *
	 * @return string
	 */
	public function getDescription(): string
	{
		return match ($this) {
			self::NoError => "",
			self::RuntimeError => "Произошла ошибка. Пожалуйста, попробуйте позже или свяжитесь с Службой поддержки",
			self::Forbidden => "Недостаточно прав для совершения действия",
			self::Conflict => "Заявка уже взята в работу",
		};
	}
}
