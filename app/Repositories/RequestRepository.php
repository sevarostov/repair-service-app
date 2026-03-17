<?php

namespace App\Repositories;

use App\Models\Request as Request;
use Illuminate\Pagination\LengthAwarePaginator;

readonly class RequestRepository
{
	/**
	 * Получить список тикетов с пагинацией и фильтрацией
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param int $perPage
	 *
	 * @return LengthAwarePaginator
	 */
	public function getListBy(\Illuminate\Http\Request $request, int $perPage = 10): LengthAwarePaginator
	{
		$query = Request::query()
			->orderBy('created_at', 'desc');

		$user = auth()->user();
		if ($user->hasRole('master')) {
			$query->where('assigned_to', $user->id);
		}

		if ($request->has('status') && !empty($request->status)) {
			$query->where('status', $request->status);
		}

		return $query->paginate($perPage)->withQueryString();
	}
}
