<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Residents - San Ildefonso</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-300" style="font-family: 'Poppins', sans-serif;">
    <div class="relative mx-auto min-h-screen max-w-md bg-[#F6F4F0] pb-24 shadow-xl">

        <header class="bg-[#C80000] px-5 pb-6 pt-12 text-white" style="border-radius: 0 0 24px 24px;">
            <h1 class="text-[22px] font-semibold">Residents</h1>
            <p class="mt-1 text-[13px] text-red-100">Residents assigned to you</p>
        </header>
        
        {{-- confirmation message --}}
        @if (session('status'))
            <div class="mx-5 mt-4 rounded-xl bg-[#E9F0DD] px-4 py-3 text-sm text-[#3B5E14]">{{ session('status') }}</div>
        @endif

        <form method="GET" action="/bhw/residents" class="flex gap-2 px-5 py-4">
            <input name="q" type="text" value="{{ $q }}" placeholder="Search by name"
                   class="h-11 flex-1 rounded-[10px] border border-[#CFC8BA] bg-white px-3 text-sm focus:border-[#4E7A1D] focus:outline-none">
            <button type="submit" class="h-11 rounded-[10px] bg-[#4E7A1D] px-4 text-sm font-medium text-white">Search</button>
        </form>

        <div class="space-y-3 px-5">
            @forelse ($residents as $r)
                <div class="rounded-xl border border-[#E8E2D8] bg-white p-4">
                    <p class="text-base font-medium">{{ $r['name'] }}</p>
                    <p class="text-xs text-gray-600">{{ $r['no'] }} · {{ $r['age'] }}</p>
                    <p class="text-xs text-gray-600">Last reading: {{ $r['last'] }}</p>
                    <a href="/bhw/records/{{ $r['id'] }}/edit" class="mt-3 flex h-11 items-center justify-center rounded-[10px] border border-[#CFC8BA] bg-white text-sm font-medium">Open record</a>
                </div>
            @empty
                <p class="rounded-xl bg-white p-4 text-sm text-gray-600">
                    {{ $q !== '' ? 'No residents match your search.' : 'No residents are assigned to you yet.' }}
                </p>
            @endforelse
        </div>

        <nav class="fixed bottom-0 left-1/2 flex w-full max-w-md -translate-x-1/2 border-t border-gray-300 bg-white py-2 text-[11px]">
            <a href="/bhw/dashboard" class="flex flex-1 flex-col items-center gap-0.5 text-gray-600">
                <x-heroicon-o-home class="h-6 w-6" /> Home
            </a>
            <a href="/bhw/residents" class="flex flex-1 flex-col items-center gap-0.5 font-semibold text-[#C80000]">
                <x-heroicon-o-clipboard-document-list class="h-6 w-6" /> Records
            </a>
            <a href="/bhw/profile" class="flex flex-1 flex-col items-center gap-0.5 text-gray-600">
                <x-heroicon-o-user class="h-6 w-6" /> Profile
            </a>
        </nav>
    </div>
</body>
</html>