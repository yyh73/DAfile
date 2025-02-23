<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Carbon\Carbon;


class EventController extends Controller
{
    //カレンダー表示
    public function show(){
        return view("calendars/calendar");
    } 

    // 新規予定追加
    public function create(Request $request, Event $event){
        \Log::info('受け取ったリクエストデータ:', $request->all());

        // バリデーション（eventsテーブルの中でNULLを許容していないものをrequired）
        $request->validate([
            'event_title' => 'required',
            'start_date' => 'required|date_format:Y-m-d\TH:i',
            'end_date' => 'required|date_format:Y-m-d\TH:i|after_or_equal:start_date',
            'event_color' => 'required',
            'reminder_time' => 'nullable|integer|min:1',
        ]);
        
        \Log::info('バリデーション通過');

        try {
            // start_date と end_date のフォーマットを確認
            $startDate = Carbon::createFromFormat('Y-m-d\TH:i', $request->input('start_date'))->format('Y-m-d H:i:s');
            $endDate = Carbon::createFromFormat('Y-m-d\TH:i', $request->input('end_date'))->addDay()->format('Y-m-d H:i:s');
    
            \Log::info("変換後の start_date: {$startDate}, end_date: {$endDate}");

        // フロントエンドから受け取った値を取得（nullの場合はデフォルト値を設定）
        $showInMonth = $request->input('show_in_month', false); 
        $showInWeek = $request->input('show_in_week', true); 
        $showInDay = $request->input('show_in_day', true);

        $reminderMinutes = $request->input('reminder_time', null);
        $reminderTime = null; 

        if ($reminderMinutes !== null) {
            $reminderTime = Carbon::parse($startDate)->subMinutes($reminderMinutes)->format('Y-m-d H:i:s');
        }

        \Log::info("リマインダー時間: " . ($reminderTime ?? 'なし'));

        // イベント登録処理
        $event->event_title = $request->input('event_title');
        $event->event_body = $request->input('event_body');
        $event->start_date = Carbon::createFromFormat('Y-m-d\TH:i', $request->input('start_date'))->format('Y-m-d H:i:s');
        $event->end_date = Carbon::createFromFormat('Y-m-d\TH:i', $request->input('end_date'))->addDay()->format('Y-m-d H:i:s'); // FullCalendarが登録する終了日は仕様で1日ずれるので、その修正を行っている
        $event->event_color = $request->input('event_color');
        $event->event_border_color = $request->input('event_color');

         // フィルタリング用カラムを保存
        $event->show_in_month = $showInMonth;
        $event->show_in_week = $showInWeek;
        $event->show_in_day = $showInDay;

        $event->reminder_time = $reminderTime;

        $event->save();

        \Log::info('イベント保存完了');
    } catch (\Exception $e) {
        \Log::error('イベント保存エラー: ' . $e->getMessage());
    }

        // カレンダー表示画面にリダイレクトする
        return redirect(route("show"));
    }


    // DBから予定取得
    public function get(Request $request, Event $event){

        \Log::info('リクエストデータ: ', $request->all());


       // バリデーション
    $request->validate([
        'start_date' => 'required|date_format:Y-m-d',
        'end_date' => 'required|date_format:Y-m-d|after:start_date',
    ]);

    \Log::info('バリデーション通過');

    // 現在カレンダーが表示している日付の期間
    $start_date = $request->input('start_date');// 日付変換
    $end_date = $request->input('end_date');
    $view = $request->input('view'); // "month", "week", "day" のどれか

    \Log::info("取得する範囲: {$start_date} ~ {$end_date}");

    // 予定取得処理（これがaxiosのresponse.dataに入る）
    $query = $event->query()
        // DBから取得する際にFullCalendarの形式にカラム名を変更する
        ->select(
            'id',
            'event_title as title',
            'event_body as description',
            \DB::raw("DATE_FORMAT(start_date, '%Y-%m-%d %H:%i') as start"),
            \DB::raw("DATE_FORMAT(end_date, '%Y-%m-%d %H:%i') as end"),
            'event_color as backgroundColor',
            'event_border_color as borderColor',
            'show_in_month',
            'show_in_week',
            'show_in_day'
        )

        ->where('end_date', '>=', $start_date)
        ->where('start_date', '<=', $end_date);
        

        if ($view === 'dayGridMonth') {
            $query->where('show_in_month', 1);
        } elseif ($view === 'timeGridWeek') {
            $query->where('show_in_week', 1);
        } elseif ($view === 'timeGridDay') {
            $query->where('show_in_day', 1);
        }

        $events = $query->get();

        // デバッグ: 取得したデータを確認
        \Log::info("取得したイベントデータ: " . json_encode($events->toArray()));

        return response()->json($events);
    }

    // 予定の更新
    public function update(Request $request, Event $event){
        // 予定を取得
        $event = $event->find($request->input('id'));

        // 予定が見つからない場合はエラーレスポンスを返す
        if (!$event) {
            return response()->json(['error' => 'イベントが見つかりません'], 404);
        }

        // データを更新
        $event->event_title = $request->input('event_title');
        $event->event_body = $request->input('event_body');
        $event->start_date = $request->input('start_date');
        $event->end_date = $request->input('end_date');
        $event->event_color = $request->input('event_color');
        $event->event_border_color = $request->input('event_color');

         // 表示設定のチェックボックスを反映
        $event->show_in_month = $request->input('show_in_month', 1);
        $event->show_in_week = $request->input('show_in_week', 1);
        $event->show_in_day = $request->input('show_in_day', 1);

        // 保存
        $event->save();

        // カレンダー画面にリダイレクト
        return redirect()->route("show");

    }

    // 予定の削除
    public function delete(Request $request, Event $event){
        // 削除する予定をDBから探し（find）、DBから物理削除する（delete）
        $event->find($request->input('id'))->delete();

         // カレンダー表示画面にリダイレクトする
         return redirect(route("show"));
        }
    }