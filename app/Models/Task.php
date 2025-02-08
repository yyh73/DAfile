<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 

class Task extends Model //TaskモデルはModelを継承
{
    use HasFactory; //ファクトリー機能

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'priority',
        'finished_status',
        'due_date',
    ];

    //タスクがどのユーザーに属するか
    public function user(): BelongsTo
    {
         return $this->belongsTo(User::class);
    }

    //未完了、完了の文字取得
    public function getFinishedStatusAttribute(): string
    {
        return $this->finished_status == 1 ? '完了' : '未完了';
    }

}