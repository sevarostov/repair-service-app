@extends('layouts.app')

@section('content')
	<div class="container">
		<div class="row mb-4">
			<div class="col-12">
				<h1>Заявки в ремонтную службу</h1>
				
				@if(auth()->user()->hasRole('dispatcher'))
					<!-- Фильтры -->
					<div class="card mb-4">
						<div class="card-body">
							<form method="GET" action="{{ route('requests.index') }}">
								<div class="row">
									<div class="col-md-4">
										<label for="status" class="form-label">Статус</label>
										<select name="status" id="status" class="form-select">
											<option value="">Все статусы</option>
											@foreach(App\Models\Request::getStatuses() as $key => $label)
												<option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
													{{ $label }}
												</option>
											@endforeach
										</select>
									</div>
									<div class="col-md-8 d-flex align-items-end">
										<button type="submit" class="btn btn-primary me-2">Применить</button>
										<a href="{{ route('requests.index') }}" class="btn btn-secondary">Сбросить</a>
									</div>
								</div>
							</form>
						</div>
					</div>
				@endif
				
				
				<!-- Сообщения -->
				@if(session('success'))
					<div class="alert alert-success">{{ session('success') }}</div>
				@endif
				
				@if(session('error'))
					<div class="alert alert-danger">{{ session('error') }}</div>
				@endif
				
				<div class="row mb-4">
					<div class="col">
						<a href="{{ route('requests.create') }}" target="_blank" class="btn btn-success">
							Новая заявка
						</a>
					</div>
				</div>
				
				<!-- Таблица заявок -->
				<div class="card">
					<div class="card-body">
						<div class="table-responsive">
							<table class="table table-striped table-hover">
								<thead>
								<tr>
									<th>Клиент</th>
									<th>Телефон</th>
									<th>Адрес</th>
									<th>Проблема</th>
									<th>Статус</th>
									@if(auth()->user()->hasRole('dispatcher'))
										<th>Назначенный мастер</th>
									@endif
									<th>Действия</th>
								</tr>
								</thead>
								<tbody>
								@forelse($requests as $request)
									<tr>
										<td>{{ $request->client_name }}</td>
										<td>{{ $request->phone }}</td>
										<td>{{ $request->address }}</td>
										<td>{{ Str::limit($request->problem_text, 50) }}</td>
										<td>
							                <span class="badge {{$request->getBadgeColor()}}">
												{{ App\Models\Request::getStatusLabel($request->status) }}
							                </span>
										</td>
										
										@if(auth()->user()->hasRole('dispatcher'))
											<td>
												@if($request->assigned)
													{{ $request->assigned->name }}
												@else
													<span class="text-muted">Не назначен</span>
												@endif
											</td>
										@endif
										
										<td>
											<!-- Действия для диспетчера -->
											@if(auth()->user()->hasRole('dispatcher'))
												@if($request->status === \App\Models\Request::STATUS_NEW)
													<!-- Форма назначения мастера -->
													<form method="POST"
													      action="{{ route('requests.assign', $request->id) }}"
													      class="d-inline">
														@csrf
														<select name="master_id"
														        class="form-select form-select-sm d-inline w-auto">
															@foreach(\App\Models\User::role('master')->get() as $master)
																<option value="{{ $master->id }}">{{ $master->name }}</option>
															@endforeach
														</select>
														<button type="submit" class="btn btn-sm btn-outline-primary">
															Назначить
														</button>
													</form>
												@elseif($request->status !== \App\Models\Request::STATUS_DONE && $request->status !== \App\Models\Request::STATUS_CANCELLED)
													<a href="#" class="btn btn-sm btn-outline-danger"
													   onclick="event.preventDefault();
                                                        document.getElementById('cancel-form-{{ $request->id }}').submit();">
														Отменить
													</a>
													<form id="cancel-form-{{ $request->id }}"
													      action="{{ route('requests.cancel', $request->id) }}"
													      style="display: none;"
													>
														@csrf
														@method('PATCH')
													</form>
												@endif
											@elseif(auth()->user()->hasRole('master'))
												<!-- Действия для мастера -->
												@if($request->status === \App\Models\Request::STATUS_ASSIGNED)
													<a href="#" class="btn btn-sm btn-primary"
													   onclick="event.preventDefault();
                                                        document.getElementById('assign-form-{{ $request->id }}').submit();">
														Взять в работу
													</a>
													<form method="POST"
													      id="assign-form-{{ $request->id }}"
													      action="{{ route('requests.take', $request->id) }}"
													      class="d-inline"
													>
														@csrf
														@method('PATCH')
													</form>
												@elseif($request->status === \App\Models\Request::STATUS_IN_PROGRESS)
													<form method="POST"
													      action="{{ route('requests.done', $request->id) }}"
													      class="d-inline"
													>
														@csrf
														@method('PATCH')
														<button type="submit" class="btn btn-sm btn-success">Завершить
														</button>
													</form>
												@endif
											@endif
										</td>
									</tr>
								@empty
									<tr>
										<td colspan="{{ auth()->user()->hasRole('dispatcher') ? 7 : 6 }}"
										    class="text-center text-muted">
											Заявок не найдено
										</td>
									</tr>
								@endforelse
								</tbody>
							</table>
						</div>
						
						<!-- Пагинация -->
						<div class="d-flex justify-content-center">
							{{ $requests->links() }}
						</div>
					</div>
				</div>
			</div>
		</div>
@endsection
