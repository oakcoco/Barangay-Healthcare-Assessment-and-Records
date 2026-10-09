<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home - San Ildefonso</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-300" style="font-family: 'Poppins', sans-serif;">
    <div class="relative mx-auto min-h-screen max-w-md bg-[#D9D9D9] pb-24 shadow-xl">

        <header class="rounded-bl-[60px] bg-[#C80000] px-6 pb-20 pt-8 text-white">
            <p class="text-xl font-light">Good day,</p>
            <p class="mt-1 pl-6 text-xl font-semibold">{{ $resident['name'] }}</p>
        </header>

        <div class="-mt-14 mx-5 rounded-2xl border border-black bg-white px-4 py-4">
            <p class="text-[10px] text-gray-500">Resident ID</p>
            <p class="text-xl font-medium">{{ $resident['id'] }}</p>
            <hr class="my-3 border-gray-400">
            <div class="flex gap-10">
                <div>
                    <p class="text-[10px] text-gray-500">Barangay</p>
                    <p class="text-lg font-medium">{{ $resident['barangay'] }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-500">Purok</p>
                    <p class="text-lg font-medium">{{ $resident['purok'] }}</p>
                </div>
            </div>
        </div>
            <div class="mx-5 mt-6 rounded-2xl border border-black bg-white p-4">
        <div class="flex items-center justify-between">

            <h2 class="text-base font-semibold">My health record</h2>
            <span class="rounded-full bg-[#ECE8E0] px-2.5 py-0.5 text-[11px] font-medium text-[#4A463E]">View only</span>
        </div>
        <p class="mb-3 mt-0.5 text-xs text-gray-600">Updated by {{ $record['updated_by'] }} · {{ $record['updated_on'] }}</p>

        <div class="grid grid-cols-2 gap-2.5">
            @foreach ($record['tiles'] as $t)
                <div class="rounded-xl bg-[#F6F4F0] p-3">
                    <p class="text-[10px] text-gray-600">{{ $t['label'] }}</p>
                    <p class="text-lg font-medium leading-tight">{{ $t['value'] }}
                        @if ($t['unit'])<span class="text-[11px] font-normal text-gray-600">{{ $t['unit'] }}</span>@endif
                    </p>
                </div>
            @endforeach
        </div>

        <p class="mb-1.5 mt-4 text-[10px] text-gray-600">Current medications</p>
        <div class="flex flex-wrap gap-2">
            @foreach ($record['medications'] as $m)
                <span class="rounded-full border border-[#CFC8BA] px-3 py-1 text-xs">{{ $m }}</span>
            @endforeach
        </div>
            </div>
    
        <h2 class="mx-6 mb-3 mt-8 text-base font-semibold">Upcoming events</h2>
        <div class="mx-5 rounded-2xl border border-black bg-[#4E7A1D] p-4 text-white">
            <span class="inline-block w-32 rounded-full bg-white py-1 text-center text-xs font-medium text-[#3B5E14]">Event</span>
            <p class="mt-3 text-lg font-medium">{{ $featured['title'] }}</p>
            <p class="mt-3 text-xs">{{ $featured['date'] }} · {{ $featured['time'] }}</p>
            <p class="text-xs">{{ $featured['venue'] }}</p>
        </div>

        <h2 class="mx-6 mb-3 mt-8 text-base font-semibold">Latest post</h2>
        <div class="space-y-3">
            @foreach ($posts as $p)
                <div class="mx-5 rounded-2xl border border-black bg-white px-4 py-3">
                    <p class="text-xs font-medium {{ $p['type'] === 'Event' ? 'text-[#4E7A1D]' : 'text-red-600' }}">{{ $p['type'] }}</p>
                    <p class="text-base font-medium">{{ $p['title'] }}</p>
                    <p class="text-xs text-gray-500">{{ $p['date'] }}</p>
                </div>
            @endforeach
        </div>

        <nav class="fixed bottom-0 left-1/2 flex w-full max-w-md -translate-x-1/2 border-t border-gray-300 bg-white py-2 text-[11px]">
            <a href="/resident/home" class="flex flex-1 flex-col items-center gap-0.5 font-semibold text-black">
                <x-heroicon-o-home class="h-6 w-6" /> Home
            </a>
            <a href="/resident/referrals" class="flex flex-1 flex-col items-center gap-0.5 text-black">
                <x-heroicon-o-clipboard-document-list class="h-6 w-6" /> Referrals
            </a>
            <a href="/resident/profile" class="flex flex-1 flex-col items-center gap-0.5 text-black">
                <x-heroicon-o-user class="h-6 w-6" /> Profile
            </a>
        </nav>
    </div>
</body>
</html>