<?php

namespace Tests\Feature\Access;

use App\Enums\ApiError;
use App\Models\Request;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		$this->seed(UserSeeder::class);
	}

	/**
	 * @param string $email
	 *
	 * @return User
	 */
	protected function loginUserByEmail(string $email): User
	{
		$user = User::where('email', $email)->first();
		$this->actingAs($user);
		return $user;
	}

	public function testMasterCanViewAndWorkWithRequestsButCantCancel(): void
	{
		$user = $this->loginUserByEmail('master@example.com');

		$response = $this->get(route('requests.index'));
		$response->assertStatus(200);

		if (
			$request = Request::with('assigned')
				->where('assigned_to', '=', $user->id)
				->where('status', Request::STATUS_ASSIGNED)
				->first()
		) {
			$response = $this->patch(route('requests.take', ['id' => $request->id]));
			$response->assertRedirect();
			$response->assertSessionHas('success', ApiError::NoError->getDescription());

			$response = $this->patch(route('requests.done', ['id' => $request->id]));
			$response->assertRedirect();
			$response->assertSessionHas('success', ApiError::NoError->getDescription());

			$response = $this->patch(route('requests.cancel', ['id' => $request->id]));
			$this->assertStringContainsString('Forbidden', $response->getContent());
		}
	}

	public function testDispatcherCanCreateAndAssignRequests(): void
	{
		$this->loginUserByEmail('dispatcher@example.com');

		$response = $this->get(route('requests.create'));
		$response->assertStatus(200);

		$response = $this->post(route('requests.store'), [
			'client_name' => 'Иван Иванов',
			'phone' => '+79991234567',
			'address' => 'г.Иваново, д.1, кв.34',
			'problem_text' => 'Ремонт холодильника',
		]);
		$response->assertRedirect();
		$response->assertSessionHas('success', ApiError::NoError->getDescription());

		$request = Request::where('status', Request::STATUS_NEW)->first();
		$master = User::where('email', '=', 'master@example.com')->first();

		if ($request && $master) {
			$response = $this->post(route('requests.assign', ['id' => $request->id]), [
				'master_id' => $master->id
			]);

			$response->assertRedirect();
			$response->assertSessionHas('success', ApiError::NoError->getDescription());

			$response = $this->patch(route('requests.take', ['id' => $request->id]));
			$this->assertStringContainsString('Forbidden', $response->getContent());
		}
	}

	public function testUserWithoutRolesCannotAccessRequestRoutes(): void
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->get(route('requests.index'));
		$response->assertRedirect();
		$response->assertSessionHas('error', ApiError::Forbidden->getDescription());

		$response = $this->post(route('requests.store'), [
			'client_name' => 'Петр Петров',
			'phone' => '+798146161111',
			'address' => 'г.Петропавловск-Камчатский, д.9, кв.25',
			'problem_text' => 'Ремонт печки',
		]);
		$response->assertRedirect();
		$response->assertSessionHas('error', ApiError::Forbidden->getDescription());

		$request = Request::where('status', Request::STATUS_NEW)->first();
		if ($request) {
			$response = $this->patch(route('requests.assign', ['id' => $request->id]), [
				'master_id' => $user->id
			]);
			$this->assertStringContainsString('Forbidden', $response->getContent());
		}
	}

	public function testGuestCannotAccessRequestRoutes(): void
	{
		$response = $this->get(route('requests.index'));
		$response->assertRedirect('/login');
	}

}
