<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    //タスク一覧表示
    public function index()
    {
        //一覧取得で作成日時の降順
        $tasks = Task::orderBy('created_at','desc')->get();
        return view('tasks.index', compact('tasks'));
    }

    
    //タスク作成フォーム
    public function create()
    {
        return view('tasks.create');
    }

    //タスク保存
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|integer|min:1|max:5',
            'due_date' => 'nullable|date',
        ]);

         // タスク作成
         Task::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'finished_status' => 0, // デフォルト未完了
            'due_date' => $request->due_date,
        ]);
        
        return redirect()->route('tasks.index')->with('success', 'タスクを作成しました');
    }

    //タスクの詳細表示
    public function show(string $id)
    {
        $task = Task::findOrFail($id);
        return view('tasks.show', compact('task'));
    }

    // タスク編集フォームを表示
    public function edit(string $id)
    {
        $task = Task::findOrFail($id);
        return view('tasks.edit', compact('task'));
    }

    //タスク更新
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|integer|min:1|max:5',
            'finished_status' => 'required|integer|in:0,1',
            'due_date' => 'nullable|date',
        ]);

        // 指定したタスクを取得
        $task = Task::findOrFail($id);

        

        // データ更新
        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'finished_status' => $request->finished_status,
            'due_date' => $request->due_date,
        ]);

        return redirect()->route('tasks.index')->with('success', 'タスクを更新しました');
    }
    

    //タスク削除
    public function destroy(string $id)
    {
        //タスク削除
        $task = Task::findOrFail($id);
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'タスクを削除しました');
    }
}
