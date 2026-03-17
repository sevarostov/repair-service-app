<?php

namespace App\Http\Controllers;

use App\Enums\ApiError;
use App\Http\Requests\AssignMasterRequest;
use App\Http\Requests\CancelRequestRequest;
use App\Http\Requests\DoneRequestRequest;
use App\Http\Requests\TakeRequestRequest;
use App\Models\Request;
use App\Http\Requests\StoreRequestRequest;
use App\Repositories\RequestRepository;
use App\Services\RequestService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request as IlluminateRequest;

class RequestController extends Controller
{
	public function __construct(
		public readonly RequestRepository $requestRepository,
		public RequestService $requestService,
	) {}

	/**
	 * Список
	 *
	 * @param IlluminateRequest $request
	 *
	 * @return View
	 */
	public function index(IlluminateRequest $request): View
	{
		return view('requests.index', [
			'requests' => $this->requestRepository->getListBy($request)
		]);
	}

	/**
	 * Создать
	 * @return View
	 */
	public function create(): View
	{
		return view('requests.create');
	}

	/**
	 * Сохранить
	 *
	 * @param StoreRequestRequest $request
	 *
	 * @return RedirectResponse
	 */
	public function store(StoreRequestRequest $request): RedirectResponse
	{
		if ($this->requestService->create($request->validated())) {
			return redirect()->route('requests.index')->with('success', ApiError::NoError->getDescription());
		}
		return redirect()->route('requests.index')->with('error', ApiError::RuntimeError->getDescription());
	}

	/**
	 * Назначить мастера
	 *
	 * @param AssignMasterRequest $request
	 * @param int $id Request ID
	 *
	 * @return RedirectResponse
	 */
	public function assign(AssignMasterRequest $request, int $id): RedirectResponse
	{
		if ($this->requestService->assign($request, $id)) {
			return redirect()->route('requests.index')->with('success', ApiError::NoError->getDescription());
		}
		return redirect()->route('requests.index')->with('error', ApiError::RuntimeError->getDescription());
	}

	/**
	 * Отменить
	 *
	 * @param CancelRequestRequest $request
	 * @param int $id Request ID
	 *
	 * @return RedirectResponse
	 */
	public function cancel(CancelRequestRequest $request, int $id): RedirectResponse
	{
		if ($this->requestService->updateStatus($request->validated(), $id, Request::STATUS_CANCELLED)) {
			return redirect()->route('requests.index')->with('success', ApiError::NoError->getDescription());
		}
		return redirect()->route('requests.index')->with('error', ApiError::RuntimeError->getDescription());
	}

	/**
	 * Взять в работу
	 *
	 * @param TakeRequestRequest $request
	 * @param $id
	 *
	 * @return RedirectResponse
	 */
	public function take(TakeRequestRequest $request, $id): RedirectResponse
	{
		if ($this->requestService->updateStatus($request->validated(), $id, Request::STATUS_IN_PROGRESS, lock: true)) {
			return redirect()->route('requests.index')->with('success', ApiError::NoError->getDescription());
		}
		return redirect()->route('requests.index')->with('error', ApiError::RuntimeError->getDescription());
	}

	/**
	 * Завершить
	 *
	 * @param DoneRequestRequest $request
	 * @param $id
	 *
	 * @return RedirectResponse
	 */
	public function done(DoneRequestRequest $request, $id): RedirectResponse
	{
		if ($this->requestService->updateStatus($request->validated(), $id, Request::STATUS_DONE)) {
			return redirect()->route('requests.index')->with('success', ApiError::NoError->getDescription());
		}
		return redirect()->route('requests.index')->with('error', ApiError::RuntimeError->getDescription());
	}
}
