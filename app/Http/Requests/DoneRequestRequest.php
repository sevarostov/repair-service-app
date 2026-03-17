<?php

namespace App\Http\Requests;

use App\Models\Request;
use Illuminate\Contracts\Validation\ValidationRule;

class DoneRequestRequest extends AbstractMasterRequestRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool
	{
		return $this->authorizeForMaster(Request::STATUS_IN_PROGRESS);
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
