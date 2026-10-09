<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Referrals - San Ildefonso</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-300" style="font-family: 'Poppins', sans-serif;">
    <div class="relative mx-auto min-h-screen max-w-md bg-[#F6F4F0] pb-24 shadow-xl">

        <header class="bg-[#C80000] px-5 pb-6 pt-12 text-white" style="border-radius: 0 0 24px 24px;">
            <h1 class="text-[22px] font-semibold">Referrals</h1>
            <p class="mt-1 text-[13px] text-red-100">Health workers assigned to you</p>
        </header>

        <div class="flex gap-2 px-5 py-4">
            @foreach ($filters as $f)
                <a href="/resident/referrals?status={{ urlencode($f) }}"
                   class="rounded-full px-4 py-2 text-[13px] {{ $filter === $f ? 'bg-[#C80000] font-medium text-white' : 'border border-[#CFC8BA] bg-white' }}">
                    {{ $f }}
                </a>
            @endforeach
        </div>

        <div class="space-y-3 px-5">
            @foreach ($assignments as $a)
                <div class="rounded-xl border border-[#E8E2D8] bg-white p-4">
                    <span class="inline-block rounded-full px-3 py-1 text-xs font-medium {{ $a['status'] === 'Current' ? 'bg-[#E9F0DD] text-[#3B5E14]' : 'bg-[#ECE8E0] text-[#4A463E]' }}">
                        {{ $a['status'] }}
                    </span>
                    <p class="mt-2 text-base font-medium">{{ $a['name'] }}</p>
                    <p class="text-xs text-gray-600">Barangay Health Worker · {{ $a['barangay'] }}</p>
                    <p class="text-xs text-gray-600">Assigned: {{ $a['period'] }}</p>
                </div>
            @endforeach
        </div>

        <nav class="fixed bottom-0 left-1/2 flex w-full max-w-md -translate-x-1/2 border-t border-gray-300 bg-white py-2 text-[11px]">
            <a href="/resident/home" class="flex flex-1 flex-col items-center gap-0.5 text-gray-600">
                <x-heroicon-o-home class="h-6 w-6" /> Home
            </a>
            <a href="/resident/referrals" class="flex flex-1 flex-col items-center gap-0.5 font-semibold text-[#C80000]">
                <x-heroicon-o-clipboard-document-list class="h-6 w-6" /> Referrals
            </a>
            <a href="/resident/profile" class="flex flex-1 flex-col items-center gap-0.5 text-gray-600">
                <x-heroicon-o-user class="h-6 w-6" /> Profile
            </a>
        </nav>
    </div>
</body>
</html>