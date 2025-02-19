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
        dd($request->all()); // ここでリクエストデータを確認

        // バリデーション（eventsテーブルの中でNULLを許容していないものをrequired）
        $request->validate([
            'event_title' => 'required',
            'start_date' => 'required|date_format:Y-m-d\TH:i',
            'end_date' => 'required|date_format:Y-m-d\TH:i|after_or_equal:start_date',
            'event_color' => 'required',
        ]);

        // フロントエンドから受け取った値を取得（nullの場合はデフォルト値を設定）
        $showInMonth = $request->input('show_in_month', false); 
        $showInWeek = $request->input('show_in_week', true); 
        $showInDay = $request->input('show_in_day', true);
        

        // 登録処理
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

        $event->save();

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