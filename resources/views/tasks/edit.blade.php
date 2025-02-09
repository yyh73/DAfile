@extends('layouts.app')

@section('content')
<div class="container">
    <h1>タスク編集</h1>
    <form action="{{ route('tasks.update', $task->id) }}" method="POST">
        @csrf
        @method('PUT')

        <label>タイトル</label>
        <input type="text" name="title" class="form-control" value="{{ $task->title }}" required>

        <label>詳細</label>
        <textarea name="description" class="form-control">{{ $task->description }}</textarea>

        <label>優先度 (1~5)</label>
        <input type="number" name="priority" class="form-control" min="1" max="5" value="{{ $task->priority }}" required>

        <label>期限</label>
        <input type="date" name="due_date" class="form-control" value="{{ $task->due_date }}">

        <label>状態</label>
        <select name="finished_status" class="form-control">
            <option value="0" {{ $task->finished_status == 0 ? 'selected' : '' }}>未完了</option>
            <option value="1" {{ $task->finished_status == 1 ? 'selected' : '' }}>完了</option>
        </select>

        <button type="submit" class="btn btn-success mt-3">更新</button>
    </form>
</div>
@endsection
