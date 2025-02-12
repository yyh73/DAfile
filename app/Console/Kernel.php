<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\EventReminder;


class Kernel extends ConsoleKernel
{
    //アプリのコマンドスケジュールを定義
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        
        //リマインダー通知チェック
        $schedule->call(function () {
            $now = now();

            //時間が来た予定を取得
            $events = Event::where('reminder_time', '<=', $now)
                            ->where('end_date', '>=', $now->toDateString())
                            ->get();
        
        foreach ($events as $event) {
            Notification::route('mail', 'your@email.com')
            ->notify(new EventReminder($event));  //通知を送信

            //ログに記録
            Log::info("リマインダー通知を送信しました: " . $event->event_title);

        }})->everyMinute(); // 毎分実行
    }


    //アプリのコマンド登録
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
