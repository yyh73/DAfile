@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>タスク一覧</h2>
        <a href="{{ route('tasks.create') }}" class="btn btn-success">+ タスク追加</a>
    </div>

    <table class="table table-bordered text-center align-middle">
        <thead class="table-dark">
            <tr>
                <th style="width: 15%;">タイトル</th>
                <th style="width: 30%;">詳細</th>
                <th style="width: 5%;">優先度</th>
                <th style="width: 20%;">期限</th>
                <th style="width: 15%;">状態</th>
                <th style="width: 25%;">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tasks as $task)
                <tr>
                    <td class="align-middle">{{ $task->title }}</td>
                    <td class="align-middle">{{ $task->description ?? 'なし' }}</td>
                    <td class="align-middle">{{ $task->priority }}</td>
                    <td class="align-middle">{{ $task->due_date ?? 'なし' }}</td>

                    <td class="align-middle">
                        <span class="badge {{ $task->finished_status == 1 ? 'bg-success' : 'bg-warning' }} px-3 py-2">
                            {{ $task->finished_status == 1 ? '完了' : '未完了' }}
                        </span>
                    </td>

                    <!-- ✅ 編集・削除ボタンをCSSで横並びにする -->
                    <td class="align-middle">
                        <div style="display: flex; justify-content: center; gap: 10px;">
                            <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-sm btn-primary">編集</a>
                            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('本当に削除しますか？');">
                                    削除
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
