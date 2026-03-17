<?php

namespace Tests\Unit\Services;

use App\Http\Requests\AssignMasterRequest;
use App\Models\Request;
use App\Models\User;
use App\Repositories\RequestRepository;
use App\Services\RequestService;
use Illuminate\Foundation\Testing\Concerns\InteractsWithContainer;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RequestServiceTest extends TestCase
{
	use InteractsWithContainer;

	protected RequestService $service;
	protected RequestRepository $repository;

	protected function setUp(): void
	{
		parent::setUp();
		$this->repository = new RequestRepository();
		$this->service = new RequestService($this->repository);
	}

	/**
	 * Тест создания новой заявки
	 */
	public function testCreateRequestWithValidData(): void
	{
		$data = [
			'client_name' => 'Иван Иванов',
			'phone' => '+79991234567',
			'address' => 'г.Иваново, д.1, кв.34',
			'problem_text' => 'Ремонт холодильника',
		];

		$result = $this->service->create($data);

		$this->assertTrue($result);
	}

	/**
	 * Тест назначения мастера на заявку
	 */
	public function testAssignMasterToRequest(): void
	{

		$master = User::where('email', '=', 'master@example.com')->first();
		$request = Request::where('status', Request::STATUS_NEW)->first();

		if ($master && $request) {
			$assignRequest = new AssignMasterRequest(['master_id' => $master->id], []);
			$result = $this->service->assign($assignRequest, $request->id);
			$this->assertTrue($result);
		}

	}

	/**
	 * Тест обновления статуса заявки без блокировки
	 */
	public function testUpdateStatusWithoutLock(): void
	{
		$request = Request::where('status', Request::STATUS_NEW)->first();

		if ($request) {
			$status = Request::STATUS_IN_PROGRESS;

			$this->assertContains($status, array_keys(Request::getStatuses()));

			$result = $this->service->updateStatus([], $request->id, $status, lock: false);
			$this->assertTrue($result);
		}

	}

	/**
	 * Тест обновления статуса с блокировкой
	 */
	public function testUpdateStatusWithLock(): void
	{
		$request = Request::where('status', Request::STATUS_NEW)->first();

		if ($request) {
			$status = Request::STATUS_IN_PROGRESS;
			DB::shouldReceive('transaction')
				->once();
			$this->assertContains($status, array_keys(Request::getStatuses()));

			$result = $this->service->updateStatus([], $request->id, $status, lock: true);
			$this->assertTrue($result);
		}
	}

	/**
	 * Тест неудачного обновления статуса (некорректный статус)
	 */
	public function testUpdateStatusInvalidStatus(): void
	{
		$request = Request::where('status', Request::STATUS_NEW)->first();

		if ($request) {
			$status = 'invalid_status';

			$this->assertNotContains($status, array_keys(Request::getStatuses()));

			$result = $this->service->updateStatus([], $request->id, $status, lock: false);
			$this->assertFalse($result);
		}
	}
}
