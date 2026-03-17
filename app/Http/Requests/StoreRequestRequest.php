<?php

namespace App\Http\Requests;

use App\Helpers\RegexPatten;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequestRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'client_name' => 'required|string|max:255',
			'phone' => 'required|string|regex:' . RegexPatten::PHONE_REGEX,
			'address' => 'required|string|max:500',
			'problem_text' => 'required|string',
		];
	}

	/**
	 * Custom validation messages.
	 *
	 * @return array<string, string>
	 */
	public function messages(): array
	{
		return [
			'client_name.required' => 'Имя клиента обязательно для заполнения',
			'client_name.string' => 'Имя клиента должно быть текстовым значением',
			'client_name.max' => 'Имя клиента не может превышать :max символов',

			'phone.required' => 'Номер телефона обязателен для заполнения',
			'phone.string' => 'Телефон должен быть текстовым значением',
			'phone.regex' => 'Номер телефона должен быть в международном формате (E.164)',

			'address.required' => 'Адрес обязателен для заполнения',
			'address.string' => 'Адрес должен быть текстовым значением',
			'address.max' => 'Адрес не может превышать :max символов',

			'problem_text.required' => 'Описание проблемы обязательно для заполнения',
			'problem_text.string' => 'Описание проблемы должно быть текстовым значением',
		];
	}

}
