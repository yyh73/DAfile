@extends('layouts.app')

@section('content')
<div class="container">
    <h1>タスク詳細</h1>

    <table class="table">
        <tr><th>タイトル</th><td>{{ $task->title }}</td></tr>
        <tr><th>詳細</th><td>{{ $task->description }}</td></tr>
        <tr><th>優先度</th><td>{{ $task->priority }}</td></tr>
        <tr><th>完了状態</th><td>{{ $task->finished_status == 1 ? '完了' : '未完了' }}</td></tr>
        <tr><th>期限</th><td>{{ $task->due_date ?? 'なし' }}</td></tr>
    </table>

    <a href="{{ route('tasks.index') }}" class="btn btn-secondary">戻る</a>
</div>
@endsection
