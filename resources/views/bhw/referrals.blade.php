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
            <p class="mt-1 text-[13px] text-red-100">Referrals you gave to residents</p>
        </header>

        <div class="flex gap-2 px-5 py-4">
            @foreach ($filters as $f)
                <a href="/bhw/referrals?status={{ urlencode($f) }}"
                   class="rounded-full px-4 py-2 text-[13px] {{ $filter === $f ? 'bg-[#C80000] font-medium text-white' : 'border border-[#CFC8BA] bg-white' }}">
                    {{ $f }}
                </a>
            @endforeach
        </div>

        <a href="#" class="mx-5 mb-4 flex h-11 items-center justify-center rounded-[10px] bg-[#4E7A1D] text-sm font-medium text-white">
            + New referral (This will not be a feature)
        </a>
        {{-- let it be the button be here, i will think a new way what the contents there --}}
        

        <div class="space-y-3 px-5">
            @foreach ($referrals as $r)
                <div class="rounded-xl border border-[#E8E2D8] bg-white p-4">
                    <span class="inline-block rounded-full px-3 py-1 text-xs font-medium {{ $r['status'] === 'Pending' ? 'bg-[#FFF1D6] text-[#7A4B00]' : 'bg-[#E9F0DD] text-[#3B5E14]' }}">
                        {{ $r['status'] }}
                    </span>
                    <p class="mt-2 text-base font-medium">{{ $r['resident'] }}</p>
                    <p class="text-xs text-gray-600">Referred to: {{ $r['facility'] }}</p>
                    <p class="text-xs text-gray-600">Reason: {{ $r['reason'] }}</p>
                    <p class="text-xs text-gray-600">{{ $r['date'] }}</p>
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <a href="/bhw/records/1/edit" class="flex h-11 items-center justify-center rounded-[10px] border border-[#CFC8BA] bg-white text-sm font-medium">Edit</a>
                        <a href="#" class="flex h-11 items-center justify-center rounded-[10px] border border-[#CFC8BA] bg-white text-sm font-medium text-[#A00000]">Delete</a>
                    </div>
                </div>
            @endforeach
        </div>

        <nav class="fixed bottom-0 left-1/2 flex w-full max-w-md -translate-x-1/2 border-t border-gray-300 bg-white py-2 text-[11px]">
            <a href="/bhw/dashboard" class="flex flex-1 flex-col items-center gap-0.5 text-gray-600">
                <x-heroicon-o-home class="h-6 w-6" /> Home
            </a>
            <a href="/bhw/referrals" class="flex flex-1 flex-col items-center gap-0.5 font-semibold text-[#C80000]">
                <x-heroicon-o-clipboard-document-list class="h-6 w-6" /> Records
            </a>
            <a href="/bhw/profile" class="flex flex-1 flex-col items-center gap-0.5 text-gray-600">
                <x-heroicon-o-user class="h-6 w-6" /> Profile
            </a>
        </nav>
    </div>
</body>
</html>