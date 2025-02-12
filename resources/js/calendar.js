// calendar.js

import axios from "axios";
import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction'; 

// 日付を-1してYYYY-MM-DDの書式で返すメソッド
function formatDate(date, pos) {
    const dt = new Date(date);

    if(pos==="end"){
        dt.setDate(dt.getDate() - 1);
    }
    const year = dt.getFullYear();
    const month = ('0' + (dt.getMonth() + 1)).slice(-2);
    const day = ('0' + dt.getDate()).slice(-2);
    const hours = ('0' + dt.getHours()).slice(-2);
    const minutes = ('0' + dt.getMinutes()).slice(-2);
    const seconds = ('0' + dt.getSeconds()).slice(-2); // MySQL の `DATETIME` は秒まで含む

    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

console.log(formatDate("2025-02-12T21:09", "start")); // 2025-02-12 21:09:00
console.log(formatDate("2025-02-12T21:09", "end"));   // 2025-02-11 21:09:00

const calendarEl = document.getElementById("calendar");

if (calendarEl) {
    const calendar = new Calendar(calendarEl, {
        // プラグインの導入(import忘れずに)
        plugins: [interactionPlugin,dayGridPlugin, timeGridPlugin],
    
        // 最初の「月」カレンダー表示
        initialView: "dayGridMonth", // 最初に表示させるページの形式
        
        headerToolbar: {
            start: "prev,next today",
            center: "title",
            end: "eventAddButton dayGridMonth,timeGridWeek,timeGridDay", // 週・日表示は分単位
        },
       
        height: "auto", 

        views: {
            dayGridMonth: {
                slotDuration: "00:01:00", // 月カレンダーでの入力を1分単位にする
            },
            timeGridWeek: {
                slotDuration: "00:10:00", // 週カレンダーでは10分単位
                slotLabelInterval: "00:10:00",
            },
            timeGridDay: {
                slotDuration: "00:10:00", // 日カレンダーでは10分単位
                slotLabelInterval: "00:10:00",
            }
        },
       

        minTime: "00:00:00",
        maxTime: "24:00:00",
        nowIndicator: true, 
        allDaySlot: false,
      
        // カレンダーで日程を指定して新規予定追加
    selectable: true, // 日程の選択を可能にする
    select: function (info) { 
        // 日程を選択した後に行う処理を記述
        // 選択した日程を反映（のこりは初期化）

        document.getElementById("new-id").value = "";
        document.getElementById("new-event_title").value = "";
        document.getElementById("new-start_date").value = formatDate(info.start); // 選択した開始日を反映
        document.getElementById("new-end_date").value = formatDate(info.end, "end"); // 選択した終了日を反映
        document.getElementById("new-event_body").value = "";
        document.getElementById("new-event_color").value = "blue";

        // 新規予定追加モーダルを開く
        document.getElementById('modal-add').style.display = 'flex';
    },

         // DBに登録した予定を表示する
    events: function (info, successCallback, failureCallback) { // eventsはページが切り替わるたびに実行される
        axios.post("/calendar/get", {
        // 現在カレンダーが表示している日付の期間(1月ならば、start_date=1月1日、end_date=1月31日となる)
                start_date: info.start.valueOf(),
                end_date: info.end.valueOf(),
            })
            .then((response) => {
                // 既に表示されているイベントを削除（重複防止）
                calendar.removeAllEvents(); // ver.6でもどうやら使える（ドキュメントにはない？）
                // カレンダーに読み込み
                successCallback(response.data); // successCallbackに予定をオブジェクト型で入れるとカレンダーに表示できる
            })
            .catch((error) => {
                // バリデーションエラーなど
                alert("登録に失敗しました。");
            });
 },
         // 予定をクリックすると予定編集モーダルが表示される
    eventClick: function(info) {
        // console.log(info.event); // info.event内に予定の全情報が入っているので、必要に応じて参照すること
        document.getElementById("id").value = info.event.id;
        document.getElementById("delete-id").value = info.event.id; 
        document.getElementById("event_title").value = info.event.title;
        document.getElementById("start_date").value = formatDate(info.event.start);
        document.getElementById("end_date").value = formatDate(info.event.end, "end");
        document.getElementById("event_body").value = info.event.extendedProps.description;
        document.getElementById("event_color").value = info.event.backgroundColor;

        document.getElementById('modal-update').style.display = 'flex';

        // 予定編集モーダルを開く
        document.getElementById('modal-update').style.display = 'flex';
    },
        
        customButtons: { // カスタムボタン
            eventAddButton: { // 新規予定追加ボタン
                text: '予定を追加',
                click: function() {
                    // 初期化（以前入力した値をクリアする）
                    document.getElementById("new-id").value = "";
                    document.getElementById("new-event_title").value = "";
                    document.getElementById("new-start_date").value = "";
                    document.getElementById("new-end_date").value = "";
                    document.getElementById("new-event_body").value = "";
                    document.getElementById("new-event_color").value = "blue";
    
                    // 新規予定追加モーダルを開く
                    document.getElementById('modal-add').style.display = 'flex';
                }
            }
        },

    });
    
    // カレンダーのレンダリング
    calendar.render();

    // 新規予定追加モーダルを閉じる
    window.closeAddModal = function(){
        document.getElementById('modal-add').style.display = 'none';
    }

    // 予定編集モーダルを閉じる
window.closeUpdateModal = function(){
    document.getElementById('modal-update').style.display = 'none';
}

window.deleteEvent = function(){
    'use strict'

    if (confirm('削除すると復元できません。\n本当に削除しますか？')) {
        document.getElementById('delete-form').submit();
    }
}
        
}
