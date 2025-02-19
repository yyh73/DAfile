<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class TasksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        foreach(range(1,3) as $num) {
            DB::table('tasks')->insert([
                'user_id' => 1,
                'title' => "サンプルタスク {$num}",
                'description' => "タスクの詳細 {$num}",
                'due_date' => Carbon::now()->addDay($num),
                'priority' => $num, //優先度
                'finished_status' => 0, // 0:未完了, 1:完了
                
            ]);
        }
    }
}
