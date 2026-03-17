<?php

namespace App\Services;

use App\Enums\ApiError;
use App\Exceptions\UserException;
use App\Http\Requests\AssignMasterRequest;
use App\Models\Request as Request;
use App\Repositories\RequestRepository;
use Illuminate\Support\Facades\DB;

/**
 * Сервис работы с заявками
 *
 */
class RequestService
{
	public function __construct(public RequestRepository $repository) {}

	/**
	 * Создать новую
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public function create(array $data): bool
	{
		return (!!Request::create($data)) > 0;
	}

	/**
	 * Назначить мастера
	 *
	 * @param AssignMasterRequest $request
	 * @param int $id Request ID
	 *
	 * @return bool
	 */
	public function assign(AssignMasterRequest $request, int $id): bool
	{
		return Request::where('id', $id)->update([
				'assigned_to' => $request->master_id,
				'status' => Request::STATUS_ASSIGNED
			]) > 0;
	}

	/**
	 * Обновить статус заявки
	 *
	 * @param array $data
	 * @param int $requestId
	 * @param string $status
	 * @param ?bool $lock Необходимость блокировки запроса (защита от параллельных запросов)
	 *
	 * @return bool|UserException
	 */
	public function updateStatus(array $data, int $requestId, string $status, bool $lock = false): bool|UserException
	{
		if (!in_array($status, array_keys(Request::getStatuses()))) {
			return false;
		}

		if ($lock === true) {
			$result = DB::transaction(function () use ($requestId, $status) {

				$request = Request::where('id', $requestId)
					->lockForUpdate()
					->first();

				if ($request->status === Request::STATUS_IN_PROGRESS) {
					throw new UserException(
						ApiError::Conflict,
						ApiError::Conflict->getDescription(),
						ApiError::Conflict->value,
					);
				}

				$request
					->update(['status' => $status]);
			});
		} else {
			$result = Request::where('id', $requestId)
				->update(['status' => $status]);
		}

		return $result === null || $result > 0;
	}
}
