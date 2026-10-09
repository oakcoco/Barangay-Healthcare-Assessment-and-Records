<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Barangay Health')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-200">
    <div class="relative mx-auto min-h-screen max-w-md bg-gray-50 shadow-xl pb-20">

        <header class="sticky top-0 z-10 bg-green-600 px-4 py-4 text-white">
            <h1 class="text-lg font-semibold">@yield('heading', 'Barangay Health')</h1>
        </header>

        <main class="p-4">
            @yield('content')
        </main>

        <nav class="fixed bottom-0 left-1/2 flex w-full max-w-md -translate-x-1/2 border-t bg-white text-xs">
            <a href="/dashboard" class="flex-1 py-3 text-center text-green-700">Home</a>
            <a href="/households" class="flex-1 py-3 text-center text-gray-500">Households</a>
            <a href="/records" class="flex-1 py-3 text-center text-gray-500">Records</a>
            <a href="/announcements" class="flex-1 py-3 text-center text-gray-500">News</a>
        </nav>
    </div>
</body>
</html>