<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Request;
use App\Models\User;
use App\Repositories\RequestRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Сервис работы с заявками
 *
 */
class RequestService
{
	public function __construct(
		private RequestRepository $repository,
	) {}

	/**
	 * Создать новую
	 *
	 * @param array $data
	 *
	 * @return Request
	 */
	public function createRequest(array $data): Request
	{
		$request = Request::create($data);
		$request->save();
		return $request;
	}

	/**
	 * Назначить мастера
	 *
	 * @param Request $request
	 * @param User $master
	 *
	 * @return bool
	 */
	public function assignMaster(Request $request, User $master): bool
	{
		if (!$master->hasRole(Role::MASTER)) {
			return false;
		}

		return Request::where('id', $request->id)->update([
				'assigned_to' => $master->id,
				'status' => Request::STATUS_ASSIGNED
			]) > 0;
	}

	/**
	 * Взять  в работу (с защитой от гонки)
	 *
	 * @param Request $request
	 *
	 * @return bool
	 */
	public function takeIntoWork(Request $request): bool
	{
		return DB::transaction(function () use ($request) {
			$request = Request::where('id', $request->id)->lockForUpdate()->first();

			if (!$request || $request->status !== Request::STATUS_ASSIGNED) {
				return false;
			}

			return $this->updateStatus($request, Request::STATUS_IN_PROGRESS);
		});
	}

	/**
	 * Завершить выполнение
	 *
	 * @param Request $request
	 *
	 * @return bool
	 */
	public function complete(Request $request): bool
	{
		return $this->updateStatus($request, Request::STATUS_DONE);
	}

	/**
	 * Отменить
	 *
	 * @param Request $request
	 *
	 * @return bool
	 */
	public function cancel(Request $request): bool
	{
		return $this->updateStatus($request, Request::STATUS_CANCELLED);
	}

	/**
	 * Обновить статус запроса
	 *
	 * @param Request $request
	 * @param string $status
	 *
	 * @return bool
	 */
	public function updateStatus(Request $request, string $status): bool
	{
		if (!in_array($status, array_keys(Request::getStatuses()))) {
			return false;
		}
		return Request::where('id', $request->id)->update(['status' => $status]) > 0;
	}
}
