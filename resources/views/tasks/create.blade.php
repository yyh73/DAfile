@extends('layouts.app')

@section('content')
<div class="container">
    <h1>タスク作成</h1>
    <form action="{{ route('tasks.store') }}" method="POST">
        @csrf
        <label>タイトル</label>
        <input type="text" name="title" class="form-control" required>

        <label>詳細</label>
        <textarea name="description" class="form-control"></textarea>

        <label>優先度 (1~5)</label>
        <input type="number" name="priority" class="form-control" min="1" max="5" required>

        <label>期限</label>
        <input type="date" name="due_date" class="form-control">

        <button type="submit" class="btn btn-success mt-3">作成</button>
    </form>
</div>
@endsection
