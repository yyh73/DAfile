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
   
    return `${year}-${month}-${day} ${hours}:${minutes}`;
}

function formatDateForInput(date) {
    if (!date) return '';

    const dt = new Date(date);
    const year = dt.getFullYear();
    const month = ('0' + (dt.getMonth() + 1)).slice(-2);
    const day = ('0' + dt.getDate()).slice(-2);
    const hours = ('0' + dt.getHours()).slice(-2);
    const minutes = ('0' + dt.getMinutes()).slice(-2);

    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

console.log(formatDate("2025-02-12T21:09", "start")); // 2025-02-12 21:09:00
console.log(formatDate("2025-02-12T21:09", "end"));   // 2025-02-11 21:09:00

let currentView = "dayGridMonth";
const calendarEl = document.getElementById("calendar");

if (calendarEl) {
    console.error("エラー: #calendar の要素が見つかりません！");
    const calendar = new Calendar(calendarEl, {
        // プラグインの導入(import忘れずに)
        plugins: [interactionPlugin,dayGridPlugin, timeGridPlugin],
        // 最初の「月」カレンダー表示
        initialView: "dayGridMonth", // 最初に表示させるページの形式
        locale: "ja",

        buttonText: {
            today: '今日',
            month: '月',
            week: '週',
            day: '日',
        },
        
        headerToolbar: {
            start: "prev,next today",
            center: "title",
            end: "eventAddButton dayGridMonth,timeGridWeek,timeGridDay", // 週・日表示は分単位
        },
       
        height: "auto", 

        datesSet: function(info) {
            currentView = info.view.type;
            console.log("ビューが変更されました:", currentView);
             // ビュー変更時に再取得
             calendar.refetchEvents();
        },

        views: {
            dayGridMonth: {
                displayEventTime: false,
            },

            timeGridWeek: {
                slotDuration: "00:10:00", // 週カレンダーでは10分単位
                slotLabelInterval: "00:10:00",
                slotLabelFormat: {
                    hour: "2-digit", // 時刻の形式を設定
                    minute: "2-digit",
                    hour12: false, // 24時間表記
                    omitZeroMinute: false, // 0分も表示する
                }
            },
            timeGridDay: {
                slotDuration: "00:10:00", // 日カレンダーでは10分単位
                slotLabelInterval: "00:10:00",
                slotLabelFormat: {
                    hour: "2-digit",
                    minute: "2-digit",
                    hour12: false,
                    omitZeroMinute: false,
                }
            }
        },
       
        nowIndicator: true, 
        allDaySlot: false,

        eventTimeFormat: {
            hour: "2-digit",
            minute: "2-digit",
            hour12: false, // 24時間表記
            omitZeroMinute: false,
        },
      
        // カレンダーで日程を指定して新規予定追加
    selectable: true, // 日程の選択を可能にする

    //新規予定
    select: function (info) { 
        // 日程を選択した後に行う処理を記述
        // 選択した日程を反映（のこりは初期化）

        document.getElementById("new-id").value = "";
        document.getElementById("new-event_title").value = "";
        document.getElementById("new-start_date").value = formatDate(info.start, "start"); // 選択した開始日を反映
        document.getElementById("new-end_date").value = formatDate(info.end, "end"); // 選択した終了日を反映
        document.getElementById("new-event_body").value = "";
        document.getElementById("new-event_color").value = "blue";

        // 新規予定追加モーダルを開く
        document.getElementById('modal-add').style.display = 'flex';
    },

    // DBに登録した予定を表示する
    events: function (info, successCallback, failureCallback) { 
       console.log("APIリクエスト開始:", info.start, info.end); //デバッグ用 
        
        // eventsはページが切り替わるたびに実行される
        axios.post("/calendar/get", {
        // 現在カレンダーが表示している日付の期間(1月ならば、start_date=1月1日、end_date=1月31日となる)
                start_date: info.start.toISOString().split("T")[0],
                end_date: info.end.toISOString().split("T")[0],
                view: currentView
            })
            .then((response) => {
                console.log("APIレスポンス:", response.data); // デバッグ用
                
                if (!Array.isArray(response.data)) {
                    console.error("データの形式が正しくありません:", response.data);
                    alert("イベントデータの形式が正しくありません。");
                    return;
                }

                const formattedEvents = response.data.map(event => ({
                    id: event.id,
                    title: event.title,
                    start: event.start,
                    end: event.end,
                    backgroundColor: event.backgroundColor || "blue",
                    borderColor: event.borderColor || "blue",
                    description: event.description || "",
                }));

                calendar.removeAllEvents(); // 既存のイベントを削除
                successCallback(response.data); // カレンダーにデータを渡す
            })
            .catch((error) => {
                console.error("イベントの取得に失敗:", error);
                alert("登録に失敗しました。");
                failureCallback(error);
            });
            
 },
         // 予定をクリックすると予定編集モーダルが表示される
    eventClick: function(info) {
        // console.log(info.event); // info.event内に予定の全情報が入っているので、必要に応じて参照すること
        console.log("クリックしたイベント:", info.event);

        document.getElementById("id").value = info.event.id;
        document.getElementById("delete-id").value = info.event.id; 
        document.getElementById("event_title").value = info.event.title;
        document.getElementById("start_date").value = formatDateForInput(info.event.start);
        document.getElementById("end_date").value = formatDateForInput(info.event.end);
        document.getElementById("event_body").value = info.event.extendedProps.description;
        document.getElementById("event_color").value = info.event.backgroundColor;

        let showInMonth = info.event.extendedProps.show_in_month || false;
        let showInWeek = info.event.extendedProps.show_in_week || false;
        let showInDay = info.event.extendedProps.show_in_day || false;

        let monthCheckbox = document.getElementById("show_in_month");
        let weekCheckbox = document.getElementById("show_in_week");
        let dayCheckbox = document.getElementById("show_in_day");

        if (monthCheckbox) monthCheckbox.checked = showInMonth;
        if (weekCheckbox) weekCheckbox.checked = showInWeek;
        if (dayCheckbox) dayCheckbox.checked = showInDay;

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

    // 予定を更新した後にカレンダーをリロード
document.getElementById('update-form').addEventListener('submit', function() {
    setTimeout(() => {
        calendar.refetchEvents();
    }, 500);
});

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
