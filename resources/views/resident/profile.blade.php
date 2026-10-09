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
            <div class="flex h-[72px] w-[72px] items-center justify-center rounded-full bg-white text-[#C80000]">
                <x-heroicon-o-user class="h-9 w-9" />
            </div>
            <p class="mt-3 text-xl font-semibold">{{ $resident['name'] }}</p>
            <p class="text-[13px] text-red-100">Resident</p>
        </header>

        <div class="mx-5 mt-4 divide-y divide-[#E8E2D8] rounded-xl border border-[#E8E2D8] bg-white px-4">
            @foreach ([
                'Resident ID' => $resident['id'],
                'Barangay' => $resident['barangay'],
                'Purok' => $resident['purok'],
                'Phone number' => $resident['phone'],
                'Email' => $resident['email'],
            ] as $label => $value)
                <div class="py-3">
                    <p class="text-xs text-gray-600">{{ $label }}</p>
                    <p class="text-sm font-medium">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <a href="/login" class="mx-5 mt-5 flex h-11 items-center justify-center rounded-[10px] border border-[#C80000] bg-white text-sm font-medium text-[#C80000]">
            Log out
        </a>

        <nav class="fixed bottom-0 left-1/2 flex w-full max-w-md -translate-x-1/2 border-t border-gray-300 bg-white py-2 text-[11px]">
            <a href="/resident/home" class="flex flex-1 flex-col items-center gap-0.5 text-gray-600">
                <x-heroicon-o-home class="h-6 w-6" /> Home
            </a>
            <a href="/resident/referrals" class="flex flex-1 flex-col items-center gap-0.5 text-gray-600">
                <x-heroicon-o-clipboard-document-list class="h-6 w-6" /> Referrals
            </a>
            <a href="/resident/profile" class="flex flex-1 flex-col items-center gap-0.5 font-semibold text-[#C80000]">
                <x-heroicon-o-user class="h-6 w-6" /> Profile
            </a>
        </nav>
    </div>
</body>
</html>