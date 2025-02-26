<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TaskController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    //プロフィール管理
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //カレンダー管理
    Route::get('/calendar', [EventController::class, 'show'])->name("show");
    Route::post('/calendar/create', [EventController::class, 'create'])->name("create"); // 予定の新規追加
    Route::put('/calendar/update', [EventController::class, 'update'])->name("update"); // 予定の更新
    Route::post('/calendar/get',  [EventController::class, 'get'])->name("get"); // DBに登録した予定を取得
    Route::delete('/calendar/delete', [EventController::class, 'delete'])->name("delete"); // 予定の削除

    //タスク管理
    Route::resource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggleFinished'])->name('tasks.toggleFinished'); //タスク状態切り替え


});

require __DIR__.'/auth.php';
