<?php

namespace App\Models;

use Carbon\Carbon;
use EloquentTypeHinting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin EloquentTypeHinting
 *
 * @property int $id
 * @property string $client_name
 * @property string $phone
 * @property string $address
 * @property string $problem_text
 * @property string $status Статус  (enum: `new|assigned|in_progress|done|cancelled`)
 * @property int|null $assigned_to
 * @property Carbon|null $created_at Дата создания записи
 * @property Carbon|null $updated_at Дата последнего обновления записи
 *
 *
 *
 * Relations
 * @property-read User|null $assigned
 */
class Request extends Model
{
	use HasFactory;

	public const STATUS_NEW = 'new';
	public const STATUS_ASSIGNED = 'assigned';
	public const STATUS_IN_PROGRESS = 'in_progress';
	public const STATUS_DONE = 'done';
	public const STATUS_CANCELLED = 'cancelled';

	protected $fillable = [
		'client_name',
		'phone',
		'address',
		'problem_text',
		'status',
		'assigned_to',
	];

	/**
	 * Получить массив всех возможных статусов
	 *
	 * @return array<string, string>
	 */
	public static function getStatuses(): array
	{
		return [
			self::STATUS_NEW => 'Новый',
			self::STATUS_ASSIGNED => 'Назначен мастер',
			self::STATUS_IN_PROGRESS => 'В работе',
			self::STATUS_DONE => 'Выполнен',
			self::STATUS_CANCELLED => 'Отменена',
		];
	}

	/**
	 * Получить текстовое представление статуса
	 *
	 * @param string $status
	 * @return string
	 */
	public static function getStatusLabel(string $status): string
	{
		$statuses = self::getStatuses();
		return $statuses[$status] ?? 'Неизвестный статус';
	}

	/**
	 * @return BelongsTo
	 */
	public function assigned(): BelongsTo
	{
		return $this->belongsTo(User::class, 'assigned_to');
	}

	/**
	 * Получить CSS‑классы для бейджа статуса (для Bootstrap)
	 *
	 * @return string
	 */
	public function getBadgeColor(): string
	{
		return match ($this->status) {
			self::STATUS_NEW => 'bg-primary',
			self::STATUS_ASSIGNED => 'bg-warning text-dark',
			self::STATUS_IN_PROGRESS => 'bg-info',
			self::STATUS_DONE => 'bg-success', // исправлено: было bg-danger, должно быть bg-success для «Выполнен»
			self::STATUS_CANCELLED => 'bg-danger',
			default => 'bg-secondary' // резервный вариант для неизвестных статусов
		};
	}
}
