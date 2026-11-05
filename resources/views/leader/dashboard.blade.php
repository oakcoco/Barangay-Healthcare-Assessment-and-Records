<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mother Leader - San Ildefonso</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F6F4F0] text-[#1C1C1C]" style="font-family: 'Poppins', sans-serif;">
@php
    $input = 'h-10 w-full rounded-lg border border-[#CFC8BA] bg-white px-3 text-sm focus:border-[#4E7A1D] focus:outline-none';
    $label = 'mb-1 block text-xs text-gray-600';
    $rowCols = 'grid-cols-[1.3fr_1fr_0.8fr_1.3fr_1.4fr]';
    $bhwCols = 'grid-cols-[1.3fr_1fr_0.8fr_2.7fr]';
@endphp
<div class="flex min-h-screen min-w-[1100px]">

    {{-- SIDEBAR --}}
    <aside class="sticky top-0 flex h-screen w-60 flex-none flex-col border-r border-[#E8E2D8] bg-white px-4 py-6">
        <div class="mb-7 flex items-center gap-2.5 px-1">
            <img src="{{ asset('images/seal.png') }}" alt="" class="h-10 w-10 object-contain">
            <div>
                <p class="text-[15px] font-semibold text-[#6B9A2D]">San Ildefonso</p>
                <p class="text-xs text-gray-600">Mother Leader</p>
            </div>
        </div>

        <nav class="space-y-1 text-sm">
            <a href="#overview" class="flex items-center gap-2.5 rounded-[10px] bg-[#FBE9E9] px-3 py-2.5 font-medium text-[#A00000]">
                <x-heroicon-o-home class="h-5 w-5" /> Overview
            </a>
            <a href="#approvals" class="flex items-center gap-2.5 rounded-[10px] px-3 py-2.5 text-[#3D3D3D] hover:bg-[#F6F4F0]">
                <x-heroicon-o-check-circle class="h-5 w-5" /> Approvals
                <span class="ml-auto rounded-full bg-[#C80000] px-2 text-[11px] text-white">{{ $counts[0]['value'] }}</span>
            </a>
            <a href="#statistics" class="flex items-center gap-2.5 rounded-[10px] px-3 py-2.5 text-[#3D3D3D] hover:bg-[#F6F4F0]">
                <x-heroicon-o-chart-bar class="h-5 w-5" /> Statistics
            </a>
            <a href="#events" class="flex items-center gap-2.5 rounded-[10px] px-3 py-2.5 text-[#3D3D3D] hover:bg-[#F6F4F0]">
                <x-heroicon-o-calendar-days class="h-5 w-5" /> Events
            </a>
        </nav>

        <div class="mt-auto border-t border-[#E8E2D8] px-1 pt-4">
            <p class="text-[13px] font-medium">{{ auth()->user()->name }}</p>
            <form method="POST" action="/logout" class="mt-1">
                @csrf
                <button type="submit" class="text-[13px] text-[#A00000] hover:underline">Log out</button>
            </form>
        </div>
    </aside>

    {{-- MAIN --}}
    <main class="flex-1 px-8 py-7">

        <div id="overview" class="mb-5 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Overview</h1>
                <p class="text-[13px] text-gray-600">{{ now()->format('l, F j, Y') }}</p>
            </div>
            {{-- The barangay filter is only a layout for now; it does not filter yet --}}
            <select class="h-10 w-52 rounded-lg border border-[#CFC8BA] bg-white px-3 text-[13px]">
                <option>All barangays</option>
                @foreach ($barangays as $b)
                    <option>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-xl bg-[#E9F0DD] px-4 py-3 text-sm text-[#3B5E14]">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-[#A00000]">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- COUNT CARDS --}}
        <div class="mb-5 grid grid-cols-4 gap-4">
            @foreach ($counts as $c)
                <div class="rounded-[14px] border border-[#E8E2D8] bg-white px-5 py-[18px]">
                    <p class="text-xs text-gray-600">{{ $c['label'] }}</p>
                    <p class="text-[28px] font-semibold">{{ $c['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- APPROVALS + POST EVENT --}}
        <div class="mb-5 grid grid-cols-[1.7fr_1fr] gap-5">

            <section id="approvals" class="rounded-[14px] border border-[#E8E2D8] bg-white pb-2 pt-5">
                <div class="flex items-center justify-between px-5 pb-3.5">
                    <h2 class="text-base font-semibold">Pending approvals</h2>
                    <div class="flex gap-2">
                        <button type="button" data-tab="residents" class="rounded-lg border border-[#4E7A1D] bg-[#E9F0DD] px-4 py-1.5 text-[13px] font-medium text-[#3B5E14]">Residents {{ count($pendingResidents) }}</button>
                        <button type="button" data-tab="bhws" class="rounded-lg border border-[#CFC8BA] bg-white px-4 py-1.5 text-[13px]">Health workers {{ count($pendingBhws) }}</button>
                    </div>
                </div>

                {{-- Residents tab: approving also assigns a health worker --}}
                <div id="panel-residents">
                    <div class="grid {{ $rowCols }} gap-3 px-5 pb-2 text-xs text-gray-600">
                        <span>Name</span><span>Barangay</span><span>Applied</span><span>Assign health worker</span><span></span>
                    </div>
                    @forelse ($pendingResidents as $p)
                        <form method="POST" action="/leader/residents/{{ $p['id'] }}/approve" class="grid {{ $rowCols }} items-center gap-3 border-t border-[#EFEAE0] px-5 py-2.5 text-[13px]">
                            @csrf
                            <span class="font-medium">{{ $p['name'] }}</span>
                            <span>{{ $p['barangay'] }}</span>
                            <span>{{ $p['applied'] }}</span>
                            <select name="bhw_id" class="h-[34px] rounded-lg border border-[#CFC8BA] bg-white px-2 text-xs">
                                <option value="">[Select BHW]</option>
                                @foreach ($bhws as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                                @endforeach
                            </select>
                            <div class="flex gap-2">
                                <button type="submit" class="h-[34px] rounded-lg bg-[#4E7A1D] px-3.5 text-[13px] font-medium text-white">Approve</button>
                                <button type="submit" formaction="/leader/residents/{{ $p['id'] }}/reject" onclick="return confirm('Reject this sign-up?')" class="h-[34px] rounded-lg border border-[#CFC8BA] bg-white px-3.5 text-[13px] font-medium text-[#A00000]">Reject</button>
                            </div>
                        </form>
                    @empty
                        <p class="border-t border-[#EFEAE0] px-5 py-4 text-sm text-gray-600">No residents are waiting for approval.</p>
                    @endforelse
                </div>

                {{-- Health workers tab --}}
                <div id="panel-bhws" class="hidden">
                    <div class="grid {{ $bhwCols }} gap-3 px-5 pb-2 text-xs text-gray-600">
                        <span>Name</span><span>Barangay</span><span>Applied</span><span></span>
                    </div>
                    @forelse ($pendingBhws as $p)
                        <form method="POST" action="/leader/bhws/{{ $p['id'] }}/approve" class="grid {{ $bhwCols }} items-center gap-3 border-t border-[#EFEAE0] px-5 py-2.5 text-[13px]">
                            @csrf
                            <span class="font-medium">{{ $p['name'] }}</span>
                            <span>{{ $p['barangay'] }}</span>
                            <span>{{ $p['applied'] }}</span>
                            <div class="flex gap-2">
                                <button type="submit" class="h-[34px] rounded-lg bg-[#4E7A1D] px-3.5 text-[13px] font-medium text-white">Approve</button>
                                <button type="submit" formaction="/leader/bhws/{{ $p['id'] }}/reject" onclick="return confirm('Reject this sign-up?')" class="h-[34px] rounded-lg border border-[#CFC8BA] bg-white px-3.5 text-[13px] font-medium text-[#A00000]">Reject</button>
                            </div>
                        </form>
                    @empty
                        <p class="border-t border-[#EFEAE0] px-5 py-4 text-sm text-gray-600">No health workers are waiting for approval.</p>
                    @endforelse
                </div>
            </section>

            <section id="events" class="rounded-[14px] border border-[#E8E2D8] bg-white p-5">
                <h2 class="mb-3.5 text-base font-semibold">Post event</h2>
                <form method="POST" action="/leader/events">
                    @csrf
                    <div class="mb-3.5 flex gap-2">
                        @foreach (['Event', 'Announcement'] as $t)
                            <label class="flex-1">
                                <input type="radio" name="type" value="{{ $t }}" class="peer sr-only" @checked(old('type', 'Event') === $t)>
                                <span class="block rounded-lg border border-[#CFC8BA] bg-white py-2 text-center text-[13px] peer-checked:border-[#4E7A1D] peer-checked:bg-[#E9F0DD] peer-checked:font-medium peer-checked:text-[#3B5E14]">{{ $t }}</span>
                            </label>
                        @endforeach
                    </div>

                    <label class="{{ $label }}">Title</label>
                    <input name="title" type="text" value="{{ old('title') }}" placeholder="[Event title]" class="{{ $input }} mb-3" required>

                    <div id="eventFields">
                        <div class="mb-3 grid grid-cols-2 gap-2.5">
                            <div><label class="{{ $label }}">Date</label><input name="date" type="date" value="{{ old('date') }}" class="{{ $input }}" required></div>
                            <div><label class="{{ $label }}">Time</label><input name="time" type="time" value="{{ old('time') }}" class="{{ $input }}" required></div>
                        </div>
                        <label class="{{ $label }}">Venue</label>
                        <input name="venue" type="text" value="{{ old('venue') }}" placeholder="[Venue]" class="{{ $input }} mb-3" required>
                    </div>

                    <label class="{{ $label }}">Barangay</label>
                    <select name="barangay_id" class="{{ $input }} mb-3">
                        <option value="all">All barangays</option>
                        @foreach ($barangays as $b)
                            <option value="{{ $b->id }}" @selected(old('barangay_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="mt-1 h-10 w-full rounded-lg bg-[#4E7A1D] text-[13px] font-medium text-white">Post event</button>
                </form>
            </section>
        </div>

        {{-- STATISTICS (counts of what the BHWs recorded; bar sizes are placeholders for now) --}}
        <div id="statistics" class="grid grid-cols-3 gap-5">

            <section class="rounded-[14px] border border-[#E8E2D8] bg-white p-5">
                <h3 class="text-[15px] font-semibold">Residents per barangay</h3>
                <p class="mb-4 text-xs text-gray-600">Counts from the records BHWs keep</p>
                <div class="space-y-3.5">
                    @foreach ($stats['perBarangay'] as $row)
                        <div>
                            <div class="mb-1 flex justify-between text-xs"><span>{{ $row['label'] }}</span><span>{{ $row['value'] }}</span></div>
                            <div class="h-3.5 rounded bg-[#4E7A1D]" style="width: {{ $row['pct'] }}%"></div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-[14px] border border-[#E8E2D8] bg-white p-5">
                <h3 class="text-[15px] font-semibold">Residents by age group</h3>
                <p class="mb-4 text-xs text-gray-600">From the birthdates BHWs recorded</p>
                <div class="flex h-[130px] items-end gap-5 border-b border-[#E8E2D8] px-2">
                    @foreach ($stats['ageGroups'] as $row)
                        <div class="flex-1 text-center">
                            <p class="text-xs">{{ $row['value'] }}</p>
                            <div class="rounded-t bg-[#4E7A1D]" style="height: {{ $row['h'] }}px"></div>
                        </div>
                    @endforeach
                </div>
                <div class="flex gap-5 px-2 pt-1.5">
                    @foreach ($stats['ageGroups'] as $row)
                        <p class="flex-1 text-center text-xs">{{ $row['label'] }}</p>
                    @endforeach
                </div>
            </section>

            <section class="rounded-[14px] border border-[#E8E2D8] bg-white p-5">
                <h3 class="text-[15px] font-semibold">Health readings recorded</h3>
                <p class="mb-4 text-xs text-gray-600">Readings entered by BHWs, last 6 months</p>
                <div class="flex h-[130px] items-end gap-3.5 border-b border-[#E8E2D8] px-1.5">
                    @foreach ($stats['monthly'] as $row)
                        <div class="flex-1 text-center">
                            <div class="rounded-t bg-[#4E7A1D]" style="height: {{ $row['h'] }}px"></div>
                        </div>
                    @endforeach
                </div>
                <div class="flex gap-3.5 px-1.5 pt-1.5">
                    @foreach ($stats['monthly'] as $row)
                        <p class="flex-1 text-center text-xs">{{ $row['label'] }}</p>
                    @endforeach
                </div>
            </section>
        </div>
    </main>
</div>

<script>
    // Residents / Health workers tabs
    const tabs = document.querySelectorAll('[data-tab]');
    const panels = {
        residents: document.getElementById('panel-residents'),
        bhws: document.getElementById('panel-bhws'),
    };
    const activeClasses = ['border-[#4E7A1D]', 'bg-[#E9F0DD]', 'text-[#3B5E14]', 'font-medium'];
    const idleClasses = ['border-[#CFC8BA]', 'bg-white'];

    tabs.forEach(tab => tab.addEventListener('click', () => {
        tabs.forEach(t => {
            const on = t === tab;
            t.classList.remove(...(on ? idleClasses : activeClasses));
            t.classList.add(...(on ? activeClasses : idleClasses));
        });
        Object.entries(panels).forEach(([name, el]) => el.classList.toggle('hidden', name !== tab.dataset.tab));
    }));

    // Announcements have no date, time or venue
    const eventFields = document.getElementById('eventFields');
    function syncType() {
        const isEvent = document.querySelector('input[name="type"]:checked').value === 'Event';
        eventFields.classList.toggle('hidden', !isEvent);
        eventFields.querySelectorAll('input').forEach(i => i.disabled = !isEvent);
    }
    document.querySelectorAll('input[name="type"]').forEach(r => r.addEventListener('change', syncType));
    syncType();
</script>
</body>
</html>
