<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //タスク一覧表示
        $task = Task::orderBy('created_at','desc')->get();
        return view('tasks.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //タスク作成フォーム
        return view('tasks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //タスク保存
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //タスク編集フォーム
        $task = Task::find($task_id);
        return view('task.edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //タスク更新
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //タスク削除
        $task->delete();
        redirect()->route('tasks.index')->with('success', 'タスクを削除しました');
    }
}
