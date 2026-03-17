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
	case StatusInvalid = 1;
	case AssignmentMismatch = 2;

	/**
	 * Получить описание ошибки
	 *
	 * @return string
	 */
	public function getDescription(): string
	{
		return match ($this) {
			self::NoError => "Успешно",
			self::RuntimeError => "Произошла ошибка. Пожалуйста, попробуйте позже или свяжитесь с Службой поддержки",
			self::Forbidden => "Только пользователь с ролью 'мастер' может взять заявку в работу",
			self::Conflict => "Заявка уже взята в работу",
			self::StatusInvalid => 'Заявка должна иметь статус "Назначен мастер" для взятия в работу',
			self::AssignmentMismatch => 'Можно взять в работу только назначенную вам заявку',
		};
	}
}
