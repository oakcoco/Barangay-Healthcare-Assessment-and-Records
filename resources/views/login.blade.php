<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - San Ildefonso</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-200" style="font-family: 'Poppins', sans-serif;">
    <div class="mx-auto flex min-h-screen max-w-md flex-col bg-white shadow-xl">

        <div class="flex flex-col items-center px-6 pb-10 pt-12 text-center">
            <img src="{{ asset('images/seal.png') }}" alt="Municipality of San Ildefonso seal" class="h-40 w-40 object-contain">
            <h1 class="mt-8 text-4xl font-extrabold tracking-widest text-[#6B9A2D]">San Ildefonso</h1>
            <p class="mt-6 text-sm font-bold">Malusog na Barangay, Malakas na Lalawigan:</p>
            <p class="text-sm italic">A healthy barangay makes a strong province</p>
            <p class="mt-5 text-sm tracking-wide">Community Assessment and Records System</p>
        </div>

        <div class="flex-1 rounded-t-2xl bg-[#C80000] px-8 pb-10 pt-6 text-white">
            <p class="mb-6 text-sm">
                <span class="mr-2 text-lg">文A</span>Language: <span class="underline">English</span>
            </p>

            @if (session('status'))
                <div class="mb-4 rounded-lg bg-white px-3 py-2 text-xs text-[#3B5E14]">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-white px-3 py-2 text-xs text-[#A00000]">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="/login">
                @csrf
                <label class="mb-2 block text-sm font-semibold">Email or Phone Number:</label>
                <input name="login" type="text" value="{{ old('login') }}" required class="mb-6 w-full rounded-lg bg-[#D9D9D9] px-4 py-3 text-sm text-black focus:outline-none">

                <label class="mb-2 block text-sm font-semibold">Password:</label>
                <input name="password" type="password" required class="mb-10 w-full rounded-lg border border-black bg-[#D9D9D9] px-4 py-3 text-sm text-black focus:outline-none">

                <button type="submit" class="mx-auto block w-4/5 rounded-lg border border-black bg-[#6F9A2E] py-3 text-center text-sm font-semibold">Log In</button>
            </form>

            <p class="mt-8 text-center text-xs text-red-200">Don't have an account? <a href="/register" class="underline">Click here to Sign Up.</a></p>
        </div>
    </div>
</body>
</html>