<?php

namespace App\Http\Controllers;

use App\Models\Request;
use App\Http\Requests\StoreRequestRequest;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
	public function index(IlluminateRequest $request)
	{
		$user = auth()->user();
		$query = Request::query();

		if ($user->hasRole('master')) {
			$query->where('assigned_to', $user->id);
		}

		if (!empty($request->has('status')) && $request->status) {
			$query->where('status', $request->status);
		}

		$requests = $query->paginate(10);
		return view('requests.index', compact('requests'));
	}

	public function create()
	{
		return view('requests.create');
	}

	public function store(StoreRequestRequest $request)
	{
		Request::create($request->validated());
		return redirect()->route('requests.index')->with('success', 'Request created successfully');
	}

	public function assign(IlluminateRequest $request, $id)
	{
		$req = Request::findOrFail($id);
		$req->update([
			'assigned_to' => $request->master_id,
			'status' => 'assigned'
		]);
		return redirect()->back()->with('success', 'Master assigned');
	}

	public function takeIntoWork($id)
	{
		$result = DB::transaction(function () use ($id) {
			$request = Request::where('id', $id)
				->where('status', 'assigned')
				->lockForUpdate()
				->first();

			if (!$request) {
				return response('', 409);
			}

			$request->update(['status' => 'in_progress']);
			return response('', 200);
		});

		return $result;
	}

	public function statusUpdate($id)
	{
		$request = Request::findOrFail($id);
		$request->update(['status' => 'done']);
		return redirect()->back()->with('success', 'Request completed');
	}
}
