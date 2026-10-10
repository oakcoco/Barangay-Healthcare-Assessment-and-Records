<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $event ? 'Edit event' : 'Post event' }} - San Ildefonso</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-300" style="font-family: 'Poppins', sans-serif;">
@php
    $isEdit = !empty($event);
    $input = 'h-11 w-full rounded-[10px] border border-[#CFC8BA] bg-white px-3 text-sm focus:border-[#4E7A1D] focus:outline-none';
    $label = 'mb-1 block text-xs text-gray-600';
    $type  = old('type', $event['type'] ?? 'Event');
@endphp
    <div class="relative mx-auto min-h-screen max-w-md bg-[#F6F4F0] pb-28 shadow-xl">

        <header class="mb-5 bg-[#C80000] px-5 pb-5 pt-12 text-white" style="border-radius: 0 0 24px 24px;">
            <div class="flex items-center gap-2">
                <a href="/bhw/dashboard" aria-label="Back"><x-heroicon-o-chevron-left class="h-6 w-6" /></a>
                <h1 class="text-[22px] font-semibold">{{ $isEdit ? 'Edit event' : 'Post event' }}</h1>
            </div>
            <p class="ml-8 mt-1 text-[13px] text-red-100">
                {{ $isEdit ? 'Residents will see the changes on their home feed' : 'Residents will see this on their home feed' }}
            </p>
        </header>

        @if ($errors->any())
            <div class="mx-5 mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-[#A00000]">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ $isEdit ? '/bhw/events/' . $event['id'] : '/bhw/events' }}" class="px-5">
            @csrf
            @if ($isEdit) @method('PUT') @endif

            <p class="{{ $label }}">Type</p>
            @if ($isEdit)
                <input type="hidden" name="type" value="{{ $type }}">
            @endif
            <div class="mb-4 flex gap-2">
                @foreach (['Event', 'Announcement'] as $t)
                    <label class="flex-1 {{ $isEdit ? 'opacity-70' : '' }}">
                        <input type="radio" @unless ($isEdit) name="type" @endunless value="{{ $t }}" class="peer sr-only" @checked($type === $t) @disabled($isEdit)>
                        <span class="block rounded-[10px] border border-[#CFC8BA] bg-white py-2.5 text-center text-[13px] peer-checked:border-[#4E7A1D] peer-checked:bg-[#E9F0DD] peer-checked:font-medium peer-checked:text-[#3B5E14]">{{ $t }}</span>
                    </label>
                @endforeach
            </div>

            <label class="{{ $label }}">Title</label>
            <input name="title" type="text" value="{{ old('title', $event['title'] ?? '') }}" placeholder="[Event title]" class="{{ $input }} mb-4" required>

            <div id="eventFields">
                <div class="mb-4 grid grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $label }}">Date</label>
                        <input name="date" type="date" value="{{ old('date', $event['date'] ?? '') }}" class="{{ $input }}" required>
                    </div>
                    <div>
                        <label class="{{ $label }}">Time</label>
                        <input name="time" type="time" value="{{ old('time', $event['time'] ?? '') }}" class="{{ $input }}" required>
                    </div>
                </div>
                <label class="{{ $label }}">Venue</label>
                <input name="venue" type="text" value="{{ old('venue', $event['venue'] ?? '') }}" placeholder="[Venue]" class="{{ $input }} mb-4" required>
            </div>

            <label class="{{ $label }}">Description</label>
            <textarea name="description" rows="4" placeholder="[What residents should know]" class="w-full rounded-[10px] border border-[#CFC8BA] bg-white p-3 text-sm focus:border-[#4E7A1D] focus:outline-none">{{ old('description', $event['description'] ?? '') }}</textarea>

            <div class="fixed bottom-0 left-1/2 flex w-full max-w-md -translate-x-1/2 gap-3 border-t border-[#E8E2D8] bg-white px-5 py-4">
                <a href="/bhw/dashboard" class="flex h-12 flex-1 items-center justify-center rounded-[10px] border border-[#CFC8BA] bg-white text-sm font-medium">Cancel</a>
                <button type="submit" class="h-12 flex-[2] rounded-[10px] bg-[#4E7A1D] text-sm font-medium text-white">{{ $isEdit ? 'Save changes' : 'Post event' }}</button>
            </div>
        </form>
    </div>

    <script>
        const eventFields = document.getElementById('eventFields');
        function currentType() {
            const hidden = document.querySelector('input[type="hidden"][name="type"]');
            return hidden ? hidden.value : document.querySelector('input[name="type"]:checked').value;
        }
        function syncType() {
            const isEvent = currentType() === 'Event';
            eventFields.classList.toggle('hidden', !isEvent);
            eventFields.querySelectorAll('input').forEach(i => i.disabled = !isEvent);
        }
        document.querySelectorAll('input[name="type"]').forEach(r => r.addEventListener('change', syncType));
        syncType();
    </script>
</body>
</html>