<?php

namespace App\Http\Requests;

use App\Enums\ApiError;
use App\Models\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AbstractMasterRequestRequest extends FormRequest
{
	/**
	 * @param string $status (Request::STATUS_ASSIGNED|Request::STATUS_IN_PROGRESS)
	 *
	 * @return bool
	 * @throws AuthorizationException
	 */
    public function authorizeForMaster(string $status): bool
    {
		$user = auth()->user();

		if (!$user || !$user->hasRole('master')) {
			throw new AuthorizationException(
				ApiError::Forbidden->getDescription(),
				403
			);
		}

		$requestId = $this->route('id');
		$request = Request::find($requestId);

		if ($request->status !== $status) {
			throw new AuthorizationException(
				ApiError::StatusInvalid->getDescription(),
				403
			);
		}

		if ($request->assigned_to !== $user->id) {
			throw new AuthorizationException(
				ApiError::AssignmentMismatch->getDescription(),
				403
			);
		}

		return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }
}
