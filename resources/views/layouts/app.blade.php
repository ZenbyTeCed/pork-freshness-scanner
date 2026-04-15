<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'PORKY') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['public/css/general.css'])

</head>
<body class="porky-body">

    @include('partials.header')

    <main class="porky-main">
        <div class="porky-container">
            @yield('content')
        </div>
    </main>

    @include('partials.footer')

</body>
</html>