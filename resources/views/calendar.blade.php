<!-- calendar.blade.php -->

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>FullCalendar</title>
        <!-- Fonts -->
        <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js']) <!-- vite用の記述忘れずに -->
    </head>
    <body>
        <!-- 以下のdivタグ内にカレンダーを表示 -->
        <div id='calendar'></div>
    </body>
</html>