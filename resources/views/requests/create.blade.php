@extends('layouts.app')

@section('content')
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-8">
				<div class="card">
					<div class="card-header">
						<h3>Новая заявка на ремонт</h3>
					</div>
					
					<div class="card-body">
						<!-- Сообщения об ошибках валидации -->
						@if ($errors->any())
							<div class="alert alert-danger">
								<ul class="mb-0">
									@foreach ($errors->all() as $error)
										<li>{{ $error }}</li>
									@endforeach
								</ul>
							</div>
						@endif
						
						<form method="POST" action="{{ route('requests.store') }}">
							@csrf
							
							<!-- Имя клиента -->
							<div class="mb-3">
								<label for="client_name" class="form-label">Имя клиента <span class="text-danger">*</span></label>
								<input type="text"
								       id="client_name"
								       name="client_name"
								       class="form-control @error('client_name') is-invalid @enderror"
								       value="{{ old('client_name') }}"
								       required
								       maxlength="255">
								@error('client_name')
								<div class="invalid-feedback">{{ $message }}</div>
								@enderror
							</div>
							
							<!-- Телефон -->
							<div class="mb-3">
								<label for="phone" class="form-label">Телефон <span class="text-danger">*</span></label>
								<input type="tel"
								       id="phone"
								       name="phone"
								       class="form-control @error('phone') is-invalid @enderror"
								       value="{{ old('phone') }}"
								       required
								       maxlength="20">
								@error('phone')
								<div class="invalid-feedback">{{ $message }}</div>
								@enderror
							</div>
							
							<!-- Адрес -->
							<div class="mb-3">
								<label for="address" class="form-label">Адрес <span class="text-danger">*</span></label>
								<textarea id="address"
								          name="address"
								          class="form-control @error('address') is-invalid @enderror"
								          rows="3"
								          required
								          maxlength="500">{{ old('address') }}</textarea>
								@error('address')
								<div class="invalid-feedback">{{ $message }}</div>
								@enderror
							</div>
							
							<!-- Описание проблемы -->
							<div class="mb-3">
								<label for="problem_text" class="form-label">Описание проблемы <span class="text-danger">*</span></label>
								<textarea id="problem_text"
								          name="problem_text"
								          class="form-control @error('problem_text') is-invalid @enderror"
								          rows="5"
								          required>{{ old('problem_text') }}</textarea>
								@error('problem_text')
								<div class="invalid-feedback">{{ $message }}</div>
								@enderror
							</div>
							
							<!-- Кнопки -->
							<div class="d-flex gap-2">
								<button type="submit" class="btn btn-primary">
									Создать заявку
								</button>
								<a href="{{ route('requests.index') }}" class="btn btn-secondary">
									Отмена
								</a>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
@endsection
