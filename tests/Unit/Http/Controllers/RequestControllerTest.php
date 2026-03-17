<?php

namespace Tests\Unit\Http\Controllers;

use App\Enums\ApiError;
use App\Http\Controllers\RequestController;
use App\Models\User;
use App\Repositories\RequestRepository;
use App\Services\RequestService;
use Illuminate\Foundation\Testing\Concerns\InteractsWithContainer;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\View\View;
use Tests\TestCase;

class RequestControllerTest extends TestCase
{
	use InteractsWithContainer;

	protected RequestController $controller;
	protected RequestRepository $repository;
	protected RequestService $service;

	protected function setUp(): void
	{
		parent::setUp();
		$this->repository = new RequestRepository();
		$this->service = new RequestService($this->repository);
		$this->controller = new RequestController($this->repository, $this->service);
		$user = User::where('email', 'dispatcher@example.com')->first();
		if ($user) {
			$this->actingAs($user);
		}
	}

	/**
	 * Тест метода index — отображение списка заявок
	 */
	public function testIndex(): void
	{
		$mockRequest = new IlluminateRequest();

		$response = $this->controller->index($mockRequest);
		$this->assertInstanceOf(View::class, $response);
		$this->assertEquals('requests.index', $response->getName());
	}

	/**
	 * Тест метода create — отображение формы создания заявки
	 */
	public function testCreate(): void
	{
		$response = $this->controller->create();
		$this->assertInstanceOf(View::class, $response);
		$this->assertEquals('requests.create', $response->getName());
	}

	/**
	 * Тест успешного создания заявки
	 */
	public function testStoreSuccess(): void
	{
		$response = $this->post(route('requests.store'), [
			'client_name' => 'Федор Федоров',
			'address' => 'г.Федорово, д.45, кв.14',
			'problem_text' => 'Ремонт котла',
			'phone' => '+79111111177',
		]);
		$response->assertRedirect();
		$response->assertSessionHas('success', ApiError::NoError->getDescription());
	}

}
