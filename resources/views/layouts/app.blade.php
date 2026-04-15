<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'PORKY') }}</title>

    <!-- @vite(['resources/css/app.css', 'resources/js/app.js']) -->
</head>
<body class="bg-gray-50 text-gray-800">

    @include('partials.header')

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

</body>
</html>