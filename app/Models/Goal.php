<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    use HasFactory;

    public function tasks(){
        //1つの科目を多数の生徒が履修。
        return $this->belongsToMany(Task::class);
    }
}
