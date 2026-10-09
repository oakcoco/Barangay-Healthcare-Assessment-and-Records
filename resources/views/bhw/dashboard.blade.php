<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BHW Dashboard - San Ildefonso</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-300" style="font-family: 'Poppins', sans-serif;">
    <div class="relative mx-auto min-h-screen max-w-md bg-[#D9D9D9] pb-24 shadow-xl">

        <header class="rounded-bl-[60px] bg-[#C80000] px-5 pb-10 pt-8 text-white">
            <p class="text-xl font-light">Good day,</p>
            <p class="text-xl font-semibold">{{ $bhw['name'] }}</p>
            <p class="mt-1 text-[11px] text-red-100">Barangay Health Worker · {{ $bhw['barangay'] }}</p>
        </header>

        <div class="-mt-5 grid grid-cols-3 gap-3 px-4">
            @foreach ($counts as $c)
                <div class="rounded-2xl border border-black bg-white p-3">
                    <p class="text-2xl font-medium">{{ $c['value'] }}</p>
                    <p class="text-[10px] text-gray-500">{{ $c['label'] }}</p>
                </div>
            @endforeach
        </div>
            {{-- SUCCESS EVENT INSERT MESSAGE --}}
            @if (session('status'))
            <div class="mx-4 mt-4 rounded-xl bg-[#E9F0DD] px-4 py-3 text-sm text-[#3B5E14]">{{ session('status') }}</div>
            @endif
            
        <div class="mx-4 mb-3 mt-6 flex items-center justify-between">
            <h2 class="text-xl font-medium">My events</h2>
            <a href="/bhw/events/create" class="flex items-center gap-1 rounded-lg border border-black bg-[#4E7A1D] px-4 py-2.5 text-xs font-medium text-white">
                <x-heroicon-o-plus class="h-4 w-4" /> Post event
            </a>
        </div>
        <div class="space-y-3">
            @foreach ($events as $e)
                <div class="mx-4 rounded-2xl border border-black bg-white p-4">
                    <p class="text-sm font-medium">{{ $e['title'] }}</p>
                    <p class="text-[11px] text-gray-500">{{ $e['date'] }} · {{ $e['venue'] }}</p>
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <button class="h-11 rounded-lg border border-black bg-white text-xs">Edit</button>
                        <button class="h-11 rounded-lg border border-black bg-white text-xs text-red-700">Delete</button>
                    </div>
                </div>
            @endforeach
        </div>

        <h2 class="mx-4 mb-3 mt-6 text-xl font-medium">Pending sign-ups</h2>
        <div class="space-y-3">
            @foreach ($pending as $p)
                <div class="mx-4 rounded-2xl border border-black bg-white p-4">
                    <p class="text-sm font-medium">{{ $p['name'] }}</p>
                    <p class="text-[11px] text-gray-500">{{ $p['barangay'] }} · Resident</p>
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <button class="h-11 rounded-lg bg-[#4E7A1D] text-xs font-medium text-white">Approve</button>
                        <button class="h-11 rounded-lg border border-black bg-white text-xs text-red-700">Reject</button>
                    </div>
                </div>
            @endforeach
        </div>

        <h2 class="mx-4 mb-3 mt-6 text-xl font-medium">Coming soon</h2>
        <div class="mx-4 rounded-2xl border border-black bg-white px-6 py-4 text-sm text-gray-600">
            Maternal and child care, Immunization, Reports
        </div>

        <nav class="fixed bottom-0 left-1/2 flex w-full max-w-md -translate-x-1/2 border-t border-gray-300 bg-white py-2 text-[11px]">
            <a href="/bhw/dashboard" class="flex flex-1 flex-col items-center gap-0.5 font-semibold text-black">
                <x-heroicon-o-home class="h-6 w-6" /> Home
            </a>
            <a href="referrals" class="flex flex-1 flex-col items-center gap-0.5 text-black">
                <x-heroicon-o-clipboard-document-list class="h-6 w-6" /> Records
            </a>
            <a href="/bhw/profile" class="flex flex-1 flex-col items-center gap-0.5 text-black">
                <x-heroicon-o-user class="h-6 w-6" /> Profile
            </a>
        </nav>
    </div>
    

</body>
</html>