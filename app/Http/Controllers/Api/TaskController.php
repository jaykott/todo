<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
	/**
	 * Display a listing of tasks.
	 */
	public function index(Request $request)
	{
		$tasks = Task::query()->orderBy('order')->get();
		return response()->json($tasks);
	}

	/**
	 * Store a newly created task.
	 */
	public function store(Request $request)
	{
		$validated = $request->validate([
			'name' => ['required', 'string'],
			'order' => ['nullable', 'integer'],
			'completed' => ['nullable', 'boolean'],
			'due_date' => ['required', 'date'],
		]);

		if (!isset($validated['order'])) {
			$maxOrder = Task::max('order') ?? 0;
			$validated['order'] = $maxOrder + 1;
		}

		$task = Task::create([
			'name' => $validated['name'],
			'order' => $validated['order'],
			'completed' => $validated['completed'] ?? false,
			'due_date' => $validated['due_date'],
		]);

		return response()->json($task, Response::HTTP_CREATED);
	}

	/**
	 * Update the specified task.
	 */
	public function update(Request $request, Task $task)
	{
		$validated = $request->validate([
			'name' => ['sometimes', 'string'],
			'order' => ['sometimes', 'integer'],
			'completed' => ['sometimes', 'boolean'],
			'due_date' => ['sometimes', 'required', 'date'],
		]);

		$task->fill($validated);
		$task->save();

		return response()->json($task);
	}

	/**
	 * Remove the specified task from storage.
	 */
	public function destroy(Task $task)
	{
		$task->delete();
		return response()->json(['status' => 'deleted']);
	}

	/**
	 * Reorder tasks based on provided sequence.
	 */
	public function reorder(Request $request)
	{
		$validated = $request->validate([
			'ids' => ['required', 'array', 'min:1'],
			'ids.*' => ['integer', 'exists:tasks,id'],
		]);

		DB::transaction(function () use ($validated) {
			foreach ($validated['ids'] as $index => $taskId) {
				Task::where('id', $taskId)->update(['order' => $index + 1]);
			}
		});

		return response()->json(['status' => 'ok']);
	}
}


