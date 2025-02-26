import axios from "axios";
import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";

// 日付フォーマット関数
function formatDate(date, pos) {
    const dt = new Date(date);
    if (pos === "end") {
        dt.setDate(dt.getDate() - 1);
    }
    return dt.toISOString().slice(0, 16).replace("T", " ");
}

function formatDateForInput(date) {
    if (!date) return "";
    return new Date(date).toISOString().slice(0, 16);
}

// ページ読み込み時の処理
document.addEventListener("DOMContentLoaded", function () {
    const saveButton = document.getElementById("save-new-event-button");
    

    if (saveButton) {
    saveButton.addEventListener("click", function (){
        
    });
}
    
    function scheduleReminder(event) {
        if (!event.reminder_time || Notification.permission !== "granted") {
            return;
        }

        const eventStartTime = new Date(event.start).getTime();
        const reminderTime = event.reminder_time * 60 * 1000; // 分 → ミリ秒変換
        const reminderTimestamp = eventStartTime - reminderTime;

        const now = Date.now();
        if (reminderTimestamp > now) {
            setTimeout(() => {
                new Notification("リマインダー", {
                    body: `予定: ${event.title} がまもなく始まります！`,
                    icon: "/path/to/icon.png",
                });
            }, reminderTimestamp - now);
        }
    }

    // モーダルの開閉処理
    window.closeAddModal = function () {
        document.getElementById("modal-add").style.display = "none";
    };

    window.closeUpdateModal = function () {
        document.getElementById("modal-update").style.display = "none";
    };

    window.deleteEvent = function () {
        if (confirm("削除すると復元できません。\n本当に削除しますか？")) {
            document.getElementById("delete-form").submit();
        }
    };

    //ブラウザ通知の許可リクエスト
    if ("Notification" in window) {
        Notification.requestPermission().then(permission => {
            console.log("通知の許可状態:", permission);
        });
    } else {
        console.log("このブラウザは通知をサポートしていません。");
    }

    const calendarEl = document.getElementById("calendar");
    if (!calendarEl) {
        console.error("エラー: #calendar の要素が見つかりません！");
        return;
    }

    let currentView = "dayGridMonth";

    const calendar = new Calendar(calendarEl, {
        plugins: [interactionPlugin, dayGridPlugin, timeGridPlugin],
        initialView: "dayGridMonth",
        locale: "ja",
        buttonText: {
            today: "今日",
            month: "月",
            week: "週",
            day: "日",
        },
        headerToolbar: {
            start: "prev,next today",
            center: "title",
            end: "eventAddButton dayGridMonth,timeGridWeek,timeGridDay",
        },
        height: "auto",
        nowIndicator: true,
        allDaySlot: false,
        eventTimeFormat: {
            hour: "2-digit",
            minute: "2-digit",
            hour12: false,
            omitZeroMinute: false,
        },
        views: {
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
        selectable: true,

        events: function (info, successCallback, failureCallback) {
            console.log("📤 予定を取得するリクエストを送信:", {
                start_date: info.start.toISOString().split("T")[0],
                end_date: info.end.toISOString().split("T")[0],
                view: currentView,
            });

            axios.post("/calendar/get", {
                start_date: info.start.toISOString().split("T")[0],
                end_date: info.end.toISOString().split("T")[0],
                view: currentView,
            })
            .then((response) => {
                console.log("📥 サーバーから受け取ったデータ:", response.data);

                if (!Array.isArray(response.data)) {
                    console.error("データの形式が正しくありません:", response.data);
                    alert("イベントデータの形式が正しくありません。");
                    return;
                }

                const formattedEvents = response.data.map(event => {
                    const eventData = {
                        id: event.id,
                        title: event.title,
                        start: event.start,
                        end: event.end,
                        backgroundColor: event.backgroundColor || "blue",
                        borderColor: event.borderColor || "blue",
                        description: event.description || "",
                        reminder_time: event.reminder_time || null
                    };
                    scheduleReminder(eventData);
                    return eventData;
                });
                console.log("✅ フォーマット後の予定データ:", formattedEvents);
                successCallback(formattedEvents);
                
            })
            .catch((error) => {
                console.error("イベントの取得に失敗:", error);
                alert("登録に失敗しました。");
                failureCallback(error);
            });
        },

        select: function (info) {
            document.getElementById("id").value = "";
            document.getElementById("event_title").value = "";
            document.getElementById("start_date").value = formatDate(info.start, "start");
            document.getElementById("end_date").value = formatDate(info.end, "end");
            document.getElementById("event_body").value = "";
            document.getElementById("event_color").value = "blue";
            document.getElementById('modal-add').style.display = 'flex';
    },


        eventClick: function (info) {
            document.getElementById("id").value = info.event.id;
            document.getElementById("delete-id").value = info.event.id;
            document.getElementById("event_title").value = info.event.title;
            document.getElementById("start_date").value = formatDateForInput(info.event.start);
            document.getElementById("end_date").value = formatDateForInput(info.event.end);
            document.getElementById("event_body").value = info.event.extendedProps.description;
            document.getElementById("event_color").value = info.event.backgroundColor;
            document.getElementById("modal-update").style.display = "flex";
        },

        customButtons: { 
            eventAddButton: { 
                text: "予定を追加",
                click: function () {
                    document.getElementById("id").value = "";
                    document.getElementById("event_title").value = "";
                    document.getElementById("start_date").value = "";
                    document.getElementById("end_date").value = "";
                    document.getElementById("event_body").value = "";
                    document.getElementById("event_color").value = "blue";
                    document.getElementById("modal-add").style.display = "flex";
                }
            }
        }
});
    
    calendar.render();

    document.getElementById("save-new-event-button").addEventListener("click", function (event) {event.preventDefault();
        const eventTitle = document.getElementById("new-event_title").value;
        const startDate = document.getElementById("new-start_date").value;
        const endDate = document.getElementById("new-end_date").value;
        const eventBody = document.getElementById("new-event_body").value;
        const eventColor = document.getElementById("new-event_color").value;
        const reminderTime = document.getElementById("reminder_time")?.value || null; // リマインダー時間を取得
        console.log("送信するリマインダー時間:", reminderTime); // 送信データの確認用

        console.log("📤 リクエストを送信するよ！");
        axios.post("/calendar/create", {
            event_title: document.getElementById("event_title").value,
            start_date: document.getElementById("start_date").value,
            end_date: document.getElementById("end_date").value,
            event_body: document.getElementById("event_body").value,
            event_color: document.getElementById("event_color").value,
            // 送信するリマインダー時間
            reminder_time: document.getElementById("reminder_time")?.value || null,
        })
        .then(response => {
            console.log("✅ 予定が追加されました:", response.data);
            calendar.refetchEvents(); // 予定をリロードして表示更新
            document.getElementById("modal-add").style.display = "none"; // モーダルを閉じる
        })
        .catch(error => {
            console.error("❌ エラー:", error);
        });
        console.log("📤 axios.post を実行した直後！");
    });
    
    document.getElementById("update-form")?.addEventListener("submit", function () {
        setTimeout(() => {
            calendar.refetchEvents();
        }, 500);
    });
});
