<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Account - San Ildefonso</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-200" style="font-family: 'Poppins', sans-serif;">
    <div class="mx-auto flex min-h-screen max-w-md flex-col overflow-hidden bg-white shadow-xl">

        <div class="relative px-6 pb-6 pt-12 text-center">
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('images/municipal-hall.jpg') }}')"></div>
            <div class="absolute inset-0 bg-white/75"></div>
            <div class="relative flex flex-col items-center">
                <img src="{{ asset('images/seal.png') }}" alt="Municipality of San Ildefonso seal" class="h-32 w-32 object-contain">
                <h1 class="mt-5 text-4xl font-extrabold tracking-widest text-[#6B9A2D]">San Ildefonso</h1>
                <p class="mt-4 text-sm font-bold">Malusog na Barangay, Malakas na Lalawigan:</p>
                <p class="text-sm italic">A healthy barangay makes a strong province</p>
                <p class="mt-4 text-sm tracking-wide">Community Assessment and Records System</p>
                <p class="mt-8 text-sm font-semibold">Create Your Account</p>
            </div>
        </div>

        <div class="flex-1 rounded-t-2xl bg-[#C80000] px-6 pb-10 pt-4 text-white">
            <div class="mb-6 flex items-center gap-3 text-sm font-medium">
                <span>Step 1 of 2</span>
                <span class="h-1.5 flex-1 rounded-full bg-white"></span>
                <span class="h-1.5 flex-1 rounded-full bg-[#E86B6B]"></span>
            </div>

            <label class="mb-2 block text-sm font-semibold">Full Name:</label>
            <input type="text" class="mb-5 w-full rounded-lg bg-[#D9D9D9] px-4 py-3 text-sm text-black focus:outline-none">

            <label class="mb-2 block text-sm font-semibold">Barangay:</label>
            <select class="mb-5 w-full rounded-lg bg-[#D9D9D9] px-4 py-3 text-sm text-black focus:outline-none">
                <option>Select barangay</option>
                <option>[Barangay 1]</option>
                <option>[Barangay 2]</option>
            </select>

            <p class="mb-2 text-sm font-semibold">Role:</p>
            <div class="mb-8 grid grid-cols-2 gap-3">
                <label class="cursor-pointer">
                    <input type="radio" name="role" value="resident" checked class="peer sr-only">
                    <span class="block rounded-lg bg-[#6F9A2E] py-3 text-center text-sm font-semibold opacity-70 peer-checked:opacity-100 peer-checked:ring-2 peer-checked:ring-white">Resident</span>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="role" value="bhw" class="peer sr-only">
                    <span class="block rounded-lg bg-[#6F9A2E] py-3 text-center text-sm font-semibold opacity-70 peer-checked:opacity-100 peer-checked:ring-2 peer-checked:ring-white">Healthcare Worker</span>
                </label>
            </div>

            <a href="/register/step-2" class="mx-auto block w-fit rounded-lg bg-[#D9D9D9] px-10 py-2.5 text-sm text-black">Next Step &rarr;</a>

            <p class="mt-8 text-center text-xs text-red-200">Already have an account? <a href="/login" class="underline">Click Here to Sign in</a></p>
        </div>
    </div>
</body>
</html>