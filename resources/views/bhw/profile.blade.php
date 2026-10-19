<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile - San Ildefonso</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-300" style="font-family: 'Poppins', sans-serif;">
    <div class="relative mx-auto min-h-screen max-w-md bg-[#F6F4F0] pb-24 shadow-xl">

        <header class="flex flex-col items-center bg-[#C80000] px-5 pb-7 pt-12 text-white" style="border-radius: 0 0 24px 24px;">
            <div class="flex items-center justify-center rounded-full bg-white text-[#C80000]" style="width: 72px; height: 72px;">
                <x-heroicon-o-user style="width: 36px; height: 36px;" />
            </div>
            <p class="mt-3 text-xl font-semibold">{{ $bhw['name'] }}</p>
            <p class="text-[13px] text-red-100">Barangay Health Worker</p>
        </header>

        <div class="mx-5 mt-4 rounded-xl border border-[#E8E2D8] bg-white px-4">
            @foreach ([
                'Worker ID' => $bhw['id'],
                'Barangay' => $bhw['barangay'],
                'Assigned purok' => $bhw['purok'],
                'Phone number' => $bhw['phone'],
                'Email' => $bhw['email'],
            ] as $label => $value)
                <div class="border-b border-[#E8E2D8] py-3 last:border-b-0">
                    <p class="text-xs text-gray-600">{{ $label }}</p>
                    <p class="text-sm font-medium">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <form method="POST" action="/logout" class="mx-5 mt-5">
            @csrf
            <button type="submit" class="flex h-11 w-full items-center justify-center rounded-[10px] border border-[#C80000] bg-white text-sm font-medium text-[#C80000]">Log out</button>
        </form>

        <nav class="fixed bottom-0 left-1/2 flex w-full max-w-md -translate-x-1/2 border-t border-gray-300 bg-white py-2 text-[11px]">
            <a href="/bhw/dashboard" class="flex flex-1 flex-col items-center gap-0.5 text-gray-600">
                <x-heroicon-o-home class="h-6 w-6" /> Home
            </a>
            <a href="/bhw/referrals" class="flex flex-1 flex-col items-center gap-0.5 text-gray-600">
                <x-heroicon-o-clipboard-document-list class="h-6 w-6" /> Records
            </a>
            <a href="/bhw/profile" class="flex flex-1 flex-col items-center gap-0.5 font-semibold text-[#C80000]">
                <x-heroicon-o-user class="h-6 w-6" /> Profile
            </a>
        </nav>
    </div>
</body>
</html>