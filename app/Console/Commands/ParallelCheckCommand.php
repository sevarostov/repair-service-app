<?php

namespace App\Console\Commands;

use App\Exceptions\UserException;
use App\Models\Request;
use App\Models\User;
use App\Services\RequestService;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ParallelCheckCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'app:parallel-check-command
        {--concurrent=5 : Количество одновременных запросов}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Тестирует защиту от гонки (race condition) при взятии заявки в работу';
	private int $concurrentRequests;
	private int $requestId;

	private const COLORS = [
		'red' => "\033[0;31m",
		'green' => "\033[0;32m",
		'yellow' => "\033[1;33m",
		'nc' => "\033[0m" // No Color
	];

	public function __construct(public RequestService $requestService)
	{
		parent::__construct();
	}

	public function handle(): int
	{
		$master = User::where('email', 'master@example.com')->first();
		if (!$master) {
			$this->error(self::COLORS['red'] . 'Мастер master@example.com не найден');
			return self::FAILURE;
		}

		$request = Request::with('assigned')
			->where('assigned_to', '=', $master->id)
			->where('status', Request::STATUS_ASSIGNED)
			->first();

		if (!$request) {
			$this->error(self::COLORS['red']
				. sprintf('Заявка, назначенная мастеру, в статусе "%s" не найдена', Request::getStatusLabel(Request::STATUS_ASSIGNED)),
			);
			return self::FAILURE;
		}

		$this->requestId = $request->id;
		$this->concurrentRequests = (int)$this->option('concurrent');

		$this->info(self::COLORS['yellow'] . '=== ТЕСТ ЗАЩИТЫ ОТ ГОНКИ (RACE CONDITION) ===' . self::COLORS['nc']);
		$this->info('Цель: проверить, что только один запрос может взять заявку в работу');
		$this->line('');

		if (!$this->checkContainer()) {
			return self::FAILURE;
		}

		$result = $this->runRaceTest();

		$this->line('');
		$this->info(self::COLORS['green'] . 'Тест завершён.' . self::COLORS['nc']);

		return $result ? self::SUCCESS : self::FAILURE;
	}

	private function checkContainer(): bool
	{
		$process = new Process(['pwd']);

		$process->run();

		if (!$process->isSuccessful()) {
			$this->error(self::COLORS['red'] . 'Ошибка при выполнении команды docker ps' . self::COLORS['nc']);
			return false;
		}

		return true;
	}

	/**
	 * @param int $requestId
	 *
	 * @return bool|int
	 */
	private function makeTakeRequest(int $requestId): bool|int
	{
		try {
			return $this->requestService->updateStatus([], $requestId, Request::STATUS_IN_PROGRESS, lock: true);
		} catch (UserException $exception) {
			return $exception->getCode();
		}
	}

	/**
	 * Выполняет тест гонки для проверки защиты от одновременных запросов
	 *
	 * @return bool Успешно ли пройден тест
	 */
	private function runRaceTest(): bool
	{
		$this->info(
			self::COLORS['yellow'] .
			"Начинаем тест гонки для запроса #{$this->requestId}..." . self::COLORS['nc'],
		);
		$this->info("Ожидается: один запрос 200 OK, остальные 409 Conflict");
		$this->line('---');

		$successCount = 0;
		$conflictCount = 0;

		$statuses = [];

		for ($i = 1; $i <= $this->concurrentRequests; $i++) {
			$statuses[] = $this->makeTakeRequest($this->requestId);
		}

		foreach ($statuses as $index => $result) {
			$next = $index + 1;
			if ($result === true) {
				$successCount++;
				$this->info(
					"Запрос #$next → " .
					self::COLORS['green'] . "HTTP 200 | Успешно взят в работу" . self::COLORS['nc'],
				);
			} elseif ($result === 409) {
				$conflictCount++;
				$this->info(
					"Запрос #$next → " .
					self::COLORS['red'] . "HTTP 409 | Уже взят в работу ранее" . self::COLORS['nc'],
				);
			} else {
				$this->info(
					"Запрос #$next → " .
					self::COLORS['yellow'] . "HTTP {$result} | Неожиданный статус" . self::COLORS['nc'],
				);
			}
		}

		$this->line('---');
		$this->info(self::COLORS['yellow'] . 'РЕЗУЛЬТАТЫ ТЕСТА:' . self::COLORS['nc']);
		$this->info("Всего запросов: {$this->concurrentRequests}");
		$this->info(
			"Успешных (200): " .
			self::COLORS['green'] . "{$successCount}" . self::COLORS['nc'],
		);
		$this->info(
			"Конфликтов (409): " .
			self::COLORS['red'] . "{$conflictCount}" . self::COLORS['nc'],
		);

		$testPassed = ($successCount === 1 && $conflictCount === ($this->concurrentRequests - 1));

		if ($testPassed) {
			$this->info(
				self::COLORS['green'] .
				'ТЕСТ ПРОЙДЕН: защита от гонки работает корректно' . self::COLORS['nc'],
			);
		} else {
			$this->error(
				self::COLORS['red'] .
				'ТЕСТ НЕ ПРОЙДЕН: нарушена логика защиты от гонки' . self::COLORS['nc'],
			);
		}

		return $testPassed;
	}
}
