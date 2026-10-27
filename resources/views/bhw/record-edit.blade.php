<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit record - San Ildefonso</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-300" style="font-family: 'Poppins', sans-serif;">
@php
    $input = 'h-11 w-full rounded-[10px] border border-[#CFC8BA] bg-white px-3 text-sm focus:border-[#4E7A1D] focus:outline-none';
    $label = 'mb-1 block text-xs text-gray-600';
    $card  = 'mx-5 mb-4 rounded-xl border border-[#E8E2D8] bg-white p-4';
@endphp
    <div class="relative mx-auto min-h-screen max-w-md bg-[#F6F4F0] pb-28 shadow-xl">

        <header class="mb-4 bg-[#C80000] px-5 pb-5 pt-12 text-white" style="border-radius: 0 0 24px 24px;">
            <div class="flex items-center gap-2">
                <a href="/bhw/residents" aria-label="Back"><x-heroicon-o-chevron-left class="h-6 w-6" /></a>
                <h1 class="text-[22px] font-semibold">Edit record</h1>
            </div>
            <div class="mt-4 flex items-center gap-3.5">
                <button type="button" id="openPhoto" aria-label="Change patient photo" class="relative flex-none" style="width: 64px; height: 64px;">
                    <span class="flex items-center justify-center overflow-hidden rounded-full bg-white text-[#C80000]" style="width: 64px; height: 64px;">
                        <img id="avatarImg" alt="" class="hidden h-full w-full object-cover">
                        <x-heroicon-o-user id="avatarIcon" style="width: 32px; height: 32px;" />
                    </span>
                    <span class="absolute flex items-center justify-center rounded-full border-2 border-white bg-[#4E7A1D] text-white" style="right: -4px; bottom: -4px; width: 28px; height: 28px;">
                        <x-heroicon-o-camera style="width: 14px; height: 14px;" />
                    </span>
                </button>
                <div>
                    <p class="text-base font-semibold">{{ $resident['name'] }}</p>
                    <p class="text-[13px] text-red-100">{{ $resident['id'] }}</p>
                </div>
            </div>
        </header>
        @if (session('status'))
            <div class="mx-5 mb-4 rounded-xl bg-[#E9F0DD] px-4 py-3 text-sm text-[#3B5E14]">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mx-5 mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-[#A00000]">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form id="recordForm" method="POST" action="/bhw/records/{{ $id }}" enctype="multipart/form-data">
            @csrf

            {{-- Citizen profile --}}
            <section class="{{ $card }}">
                <h2 class="text-base font-semibold">Citizen profile</h2>
                <p class="mb-3 text-xs text-gray-600">Personal details</p>

                <label class="{{ $label }}">Full name</label>
                <input name="full_name" type="text" value="{{ old('full_name', $profile['full_name']) }}" placeholder="[Full name]" required class="{{ $input }} mb-3">

                <div class="mb-3 grid grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $label }}">Birthdate</label>
                        <input name="birthdate" type="date" value="{{ old('birthdate', $profile['birthdate']) }}" max="{{ now()->toDateString() }}" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Sex</label>
                        <select name="sex" class="{{ $input }}">
                            <option value="" @selected(! old('sex', $profile['sex']))>[Select]</option>
                            <option value="female" @selected(old('sex', $profile['sex']) === 'female')>Female</option>
                            <option value="male" @selected(old('sex', $profile['sex']) === 'male')>Male</option>
                        </select>
                    </div>
                </div>

                <label class="{{ $label }}">Address</label>
                <input name="address" type="text" value="{{ old('address', $profile['address']) }}" placeholder="[House no., street]" class="{{ $input }} mb-3">

                <label class="{{ $label }}">Barangay</label>
                <div class="mb-3 flex h-11 items-center justify-between rounded-[10px] border border-[#CFC8BA] bg-[#F6F4F0] px-3 text-sm">
                    <span>{{ $resident['barangay'] }}</span>
                    <button type="button" id="openMove" class="cursor-pointer text-[13px] font-medium text-[#3B5E14]">Change</button>
                </div>

                <label class="{{ $label }}">Contact number</label>
                <input name="contact" type="tel" value="{{ old('contact', $profile['phone']) }}" placeholder="[Phone number]" class="{{ $input }}">
            </section>

            {{-- Socioeconomic and social services --}}
            @php
                $hasOld = session()->hasOldInput();
                $isOn = fn ($name, $default) => $hasOld ? (bool) old($name) : (bool) $default;
            @endphp
            <section class="{{ $card }}">
                <h2 class="text-base font-semibold">Socioeconomic and social services</h2>
                <p class="mb-3 text-xs text-gray-600">Last updated: {{ $socio['updated'] ?? 'not recorded yet' }}</p>
            
                <label class="{{ $label }}">Employment status</label>
                <select name="employment" class="{{ $input }} mb-3">
                    <option value="">[Select]</option>
                    @foreach (['employed' => 'Employed', 'self_employed' => 'Self-employed', 'unemployed' => 'Unemployed', 'retired' => 'Retired'] as $v => $t)
                        <option value="{{ $v }}" @selected(old('employment', $socio['employment']) === $v)>{{ $t }}</option>
                    @endforeach
                </select>
            
                <label class="{{ $label }}">Monthly household income</label>
                <select name="income" class="{{ $input }} mb-2">
                    <option value="">[Select range]</option>
                    @foreach (['below_10k' => 'Below 10,000', '10k_to_20k' => '10,000 - 20,000', 'above_20k' => 'Above 20,000'] as $v => $t)
                        <option value="{{ $v }}" @selected(old('income', $socio['income']) === $v)>{{ $t }}</option>
                    @endforeach
                </select>
            
                @foreach (['philhealth' => 'PhilHealth member', 'fourps' => '4Ps beneficiary'] as $name => $text)
                    <label class="flex h-11 items-center justify-between text-sm">
                        {{ $text }}
                        <input type="checkbox" name="{{ $name }}" value="1" class="peer sr-only" @checked($isOn($name, $socio[$name]))>
                        <span class="relative h-6 w-11 rounded-full bg-[#CFC8BA] transition peer-checked:bg-[#4E7A1D] after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition peer-checked:after:translate-x-5"></span>
                    </label>
                @endforeach
            
                <p class="{{ $label }} mt-2">Social services received</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($serviceList as $svc)
                        <label>
                            <input type="checkbox" name="services[]" value="{{ $svc->id }}" class="peer sr-only"
                                @checked($hasOld ? in_array($svc->id, array_map('intval', old('services', []))) : in_array($svc->id, $serviceIds))>
                            <span class="block rounded-full border border-[#CFC8BA] bg-white px-3.5 py-2 text-[13px] peer-checked:border-[#4E7A1D] peer-checked:bg-[#E9F0DD] peer-checked:font-medium peer-checked:text-[#3B5E14]">{{ $svc->name }}</span>
                        </label>
                    @endforeach
                </div>
            </section>

            {{-- Vital signs --}}
            <section class="{{ $card }}">
                <h2 class="text-base font-semibold">Vital signs</h2>
                <p class="mb-3 text-xs text-gray-600">Last recorded: {{ $last['vitals'] ? \Carbon\Carbon::parse($last['vitals'])->format('M j, Y g:i A') : 'no readings yet' }}</p>
                <div class="grid grid-cols-2 gap-3">
                    @foreach ([
                        ['name' => 'bp',    'label' => 'Blood pressure',          'unit' => 'mmHg',  'type' => 'text',   'ph' => '120/80', 'attrs' => 'inputmode="numeric" pattern="\d{2,3}/\d{2,3}" title="Use the format 120/80"'],
                        ['name' => 'temp',  'label' => 'Temperature',             'unit' => '°C',    'type' => 'number', 'ph' => '[#]',    'attrs' => 'step="0.1" min="30" max="45"'],
                        ['name' => 'spo2',  'label' => 'Oxygen saturation',       'unit' => '%',     'type' => 'number', 'ph' => '[#]',    'attrs' => 'min="0" max="100"'],
                        ['name' => 'resp',  'label' => 'Respiratory rate',        'unit' => '/min',  'type' => 'number', 'ph' => '[#]',    'attrs' => 'min="4" max="80"'],
                        ['name' => 'pulse', 'label' => 'Pulse rate',              'unit' => 'bpm',   'type' => 'number', 'ph' => '[#]',    'attrs' => 'min="20" max="250"'],
                        ['name' => 'cbg',   'label' => 'CBG (capillary glucose)', 'unit' => 'mg/dL', 'type' => 'number', 'ph' => '[#]',    'attrs' => 'min="10" max="800"'],
                        ] as $v)
                        <div>
                            <label class="{{ $label }}">{{ $v['label'] }}</label>
                            <div class="relative">
                                <input name="{{ $v['name'] }}" type="{{ $v['type'] }}" placeholder="{{ $v['ph'] }}" {!! $v['attrs'] !!} class="{{ $input }} pr-14">
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-600">{{ $v['unit'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Nutritional status --}}
            <section class="{{ $card }}">
                <h2 class="text-base font-semibold">Nutritional status</h2>
                <p class="mb-3 text-xs text-gray-600">Last recorded: {{ $last['nutrition'] ? \Carbon\Carbon::parse($last['nutrition'])->format('M j, Y g:i A') : 'no readings yet' }}</p>
                <div class="mb-3 grid grid-cols-2 gap-3">
                    <div><label class="{{ $label }}">Height (cm)</label><input id="height" name="height" type="number" placeholder="[#]" class="{{ $input }}"></div>
                    <div><label class="{{ $label }}">Weight (kg)</label><input id="weight" name="weight" type="number" step="0.1" placeholder="[#]" class="{{ $input }}"></div>
                </div>
                <label class="{{ $label }}">Body mass index (calculated)</label>
                <input id="bmi" type="text" readonly placeholder="[BMI]" class="{{ $input }} bg-[#F6F4F0]">
            </section>

            {{-- Medical status reports --}}
            <section class="{{ $card }}">
                <h2 class="text-base font-semibold">Medical status reports</h2>
                <p class="mb-3 text-xs text-gray-600">Cholesterol, hemoglobin, uric acid and other lab scores. Glucose is recorded as CBG under Vital signs.</p>

                @foreach ($scores as $s)
                    <div class="mb-3 flex items-center justify-between rounded-[10px] border border-[#E8E2D8] px-3 py-2.5">
                        <div>
                            <p class="text-sm font-medium">{{ $s['title'] }}</p>
                            <p class="text-xs text-gray-600">{{ $s['when'] }}</p>
                        </div>
                        <button type="button" data-remove-score="{{ $s['id'] }}" class="py-3 text-[13px] font-medium text-[#A00000]">Remove</button>
                    </div>
                @endforeach

                <div id="scoreList" class="mb-3 space-y-2"></div>
                <button type="button" id="openScore" class="flex h-11 w-full items-center justify-center rounded-[10px] border border-dashed border-[#4E7A1D] text-sm font-medium text-[#3B5E14]">+ Add another score</button>
            </section>

        {{-- Medications --}}
            <section class="{{ $card }}">
                <h2 class="text-base font-semibold">Medications</h2>
                <p class="mb-3 text-xs text-gray-600">What the resident currently takes</p>
                @foreach ($medications as $m)
                    <div class="mb-3 flex items-center justify-between rounded-[10px] border border-[#E8E2D8] px-3 py-2.5">
                        <div>
                            <p class="text-sm font-medium">{{ $m['name'] }}</p>
                            <p class="text-xs text-gray-600">{{ $m['detail'] }}</p>
                        </div>
                            <button type="button" data-remove-med="{{ $m['id'] }}" class="py-3 text-[13px] font-medium text-[#A00000]">Remove</button>
                    </div>
                @endforeach
                <div id="medList"></div>
            <button type="button" id="openMed" class="flex h-11 w-full items-center justify-center rounded-[10px] border border-dashed border-[#4E7A1D] text-sm font-medium text-[#3B5E14]">+ Add medication</button>
            </section>

            {{-- Photo evidence --}}
            <section class="{{ $card }}">
                <h2 class="text-base font-semibold">Photo evidence</h2>
                <p class="mb-3 text-xs text-gray-600">Attach photos to this medical record</p>
                <div class="grid grid-cols-3 gap-3">
                    @for ($i = 0; $i < $photoCount; $i++)
                        <div class="flex h-24 items-center justify-center rounded-[10px] border border-dashed border-[#B5AE9F] bg-[#ECE8E0] text-xs text-gray-600">[Photo]</div>
                    @endfor
                    <label class="flex h-24 cursor-pointer items-center justify-center rounded-[10px] border border-dashed border-[#4E7A1D] px-2 text-center text-xs font-medium text-[#3B5E14]">
                        <span id="photoLabel">+ Add photo</span>
                        <input id="photos" type="file" name="photos[]" accept="image/*" multiple class="hidden">
                    </label>
                </div>
                <p class="mt-3 text-xs text-gray-600">Add a caption and date to each photo</p>
            </section>

            <div class="fixed bottom-0 left-1/2 flex w-full max-w-md -translate-x-1/2 gap-3 border-t border-[#E8E2D8] bg-white px-5 py-4">
                <a href="/bhw/residents" class="flex h-12 flex-1 items-center justify-center rounded-[10px] border border-[#CFC8BA] bg-white text-sm font-medium">Cancel</a>
                <button type="submit" class="h-12 flex-[2] rounded-[10px] bg-[#4E7A1D] text-sm font-medium text-white">Save changes</button>
            </div>
                <input type="file" id="photoInput" name="profile_photo" accept="image/*" class="hidden">
                <input type="hidden" id="removePhoto" name="remove_photo" value="">

                <div id="photoModal" class="fixed inset-0 z-50 hidden items-end justify-center bg-black/50">
                    <div class="w-full max-w-md bg-white px-5 pb-6 pt-3" style="border-radius: 20px 20px 0 0;">
                        <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-[#CFC8BA]"></div>
                        <h2 class="text-center text-lg font-semibold">Patient photo</h2>
                        <p class="mt-0.5 text-center text-[13px] text-gray-600">Shown as the profile picture on this record</p>

                        <div class="mx-auto my-5 flex items-center justify-center overflow-hidden rounded-full border border-dashed border-[#B5AE9F] bg-[#ECE8E0] text-gray-500" style="width: 120px; height: 120px;">
                            <img id="photoPreview" alt="" class="hidden h-full w-full object-cover">
                            <x-heroicon-o-user id="photoPreviewIcon" style="width: 56px; height: 56px;" />
                        </div>

                        <div class="space-y-2.5">
                            <button type="button" id="takePhoto" class="flex h-12 w-full items-center justify-center gap-2 rounded-[10px] border border-[#CFC8BA] bg-white text-sm font-medium">
                                <x-heroicon-o-camera class="h-5 w-5" /> Take photo
                            </button>
                            <button type="button" id="pickPhoto" class="flex h-12 w-full items-center justify-center gap-2 rounded-[10px] border border-[#CFC8BA] bg-white text-sm font-medium">
                                <x-heroicon-o-photo class="h-5 w-5" /> Choose from gallery
                            </button>
                        </div>

                        <button type="button" id="removePhotoBtn" class="flex h-11 w-full items-center justify-center text-sm font-medium text-[#A00000]">Remove photo</button>
                        <p class="mb-3 text-center text-xs text-gray-600">Take photos only with the resident's permission</p>

                        <div class="flex gap-2.5">
                            <button type="button" id="cancelPhoto" class="h-12 flex-1 rounded-[10px] border border-[#CFC8BA] bg-white text-sm font-medium">Cancel</button>
                            <button type="button" id="usePhoto" class="h-12 flex-[2] rounded-[10px] bg-[#4E7A1D] text-sm font-medium text-white">Use this photo</button>
                        </div>
                    </div>
                </div>
        </form>
        <form id="removeMedForm" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
            {{-- Save confirmation modal--}}
                <div id="saveModal" class="fixed inset-0 z-50 hidden items-end justify-center bg-black/50">
                    <div class="w-full max-w-md bg-white px-5 pb-6 pt-3" style="border-radius: 20px 20px 0 0;">
                        <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-[#CFC8BA]"></div>
                        <h2 class="text-center text-lg font-semibold">Save this record?</h2>
                        <p class="mb-4 mt-1 text-center text-[13px] text-gray-600">
                            Your changes for {{ $resident['name'] }} will be saved, and you'll be taken back to the residents list.
                        </p>
                        <div class="flex gap-2.5">
                            <button type="button" id="cancelSave" class="h-12 flex-1 rounded-[10px] border border-[#CFC8BA] bg-white text-sm font-medium">Keep editing</button>
                            <button type="button" id="confirmSave" class="h-12 flex-[2] rounded-[10px] bg-[#4E7A1D] text-sm font-medium text-white">Yes, save</button>
                        </div>
                    </div>
                </div>
        {{-- Medications modal --}}
        <div id="medModal" class="fixed inset-0 z-50 hidden items-end justify-center bg-black/50">
            <div class="max-h-[92vh] w-full max-w-md overflow-y-auto bg-white px-5 pb-6 pt-3" style="border-radius: 20px 20px 0 0;">
                <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-[#CFC8BA]"></div>
                <h2 class="text-center text-lg font-semibold">Add medication</h2>
                <p class="mb-4 mt-0.5 text-center text-[13px] text-gray-600">Adds to what the resident currently takes</p>

                <label class="{{ $label }}">Medicine name</label>
                <select id="medName" class="{{ $input }} mb-3">
                    <option value="" disabled selected>[Select medicine]</option>
                    @foreach (['Amlodipine' => 'Blood pressure', 'Losartan' => 'Blood pressure', 'Metformin' => 'Blood sugar', 'Atorvastatin' => 'Cholesterol', 'Aspirin (low-dose)' => 'Heart protection'] as $name => $purpose)
                        <option value="{{ $name }}">{{ $name }} · {{ $purpose }}</option>
                    @endforeach
                    <option value="__other">Other (type a name)</option>
                </select>
                <div id="medOtherWrap" class="mb-3 hidden">
                    <input id="medOther" type="text" placeholder="[Medicine name]" class="{{ $input }}">
                </div>

                <div class="mb-3 grid grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $label }}">Dose</label>
                        <input id="medDose" type="number" step="any" min="0" placeholder="[#]" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Unit</label>
                        <select id="medUnit" class="{{ $input }}">
                            @foreach (['mg', 'mcg', 'g', 'mL', 'tablet'] as $u)
                                <option>{{ $u }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <label class="{{ $label }}">How often</label>
                <select id="medFreq" class="{{ $input }} mb-3">
                    <option value="" disabled selected>[Select frequency]</option>
                    @foreach (['Once daily', 'Twice daily', 'Three times daily', 'Every 4 to 6 hours', 'Weekly', 'As needed', 'Other'] as $f)
                        <option>{{ $f }}</option>
                    @endforeach
                </select>

                <label class="{{ $label }}">Start date</label>
                <input id="medStart" type="date" class="{{ $input }} mb-2">

                <label class="flex h-11 items-center justify-between text-sm">
                    Currently taking
                    <input id="medActive" type="checkbox" checked class="peer sr-only">
                    <span class="relative h-6 w-11 rounded-full bg-[#CFC8BA] transition peer-checked:bg-[#4E7A1D] after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition peer-checked:after:translate-x-5"></span>
                </label>

                <p id="medError" class="my-2 hidden text-xs text-[#A00000]"></p>

                <div class="mt-3 flex gap-2.5">
                    <button type="button" id="cancelMed" class="h-12 flex-1 rounded-[10px] border border-[#CFC8BA] bg-white text-sm font-medium">Cancel</button>
                    <button type="button" id="saveMed" class="h-12 flex-[2] rounded-[10px] bg-[#4E7A1D] text-sm font-medium text-white">Add medication</button>
                </div>
            </div>
        </div>

        {{-- Score modal --}}
        <div id="scoreModal" class="fixed inset-0 z-50 hidden items-end justify-center bg-black/50">
            <div class="max-h-[92vh] w-full max-w-md overflow-y-auto bg-white px-5 pb-6 pt-3" style="border-radius: 20px 20px 0 0;">
                <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-[#CFC8BA]"></div>
                <h2 class="text-center text-lg font-semibold">Add score</h2>
                <p class="mb-4 mt-0.5 text-center text-[13px] text-gray-600">Cholesterol, hemoglobin, uric acid and other lab scores</p>

                <label class="{{ $label }}">Score type</label>
                <select id="scoreType" class="{{ $input }} mb-3">
                    <option>Cholesterol</option>
                    <option>Hemoglobin</option>
                    <option>Uric acid</option>
                    <option>Other</option>
                </select>

                <div id="otherWrap" class="mb-3 hidden">
                    <label class="{{ $label }}">Score name</label>
                    <input id="scoreOtherName" type="text" placeholder="[Score name]" class="{{ $input }}">
                </div>

                <div class="mb-3 grid grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $label }}">Value</label>
                        <input id="scoreValue" type="number" step="any" min="0" placeholder="[#]" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Unit</label>
                        <select id="scoreUnit" class="{{ $input }}"></select>
                    </div>
                </div>

                <div class="mb-2 grid grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $label }}">Date</label>
                        <input id="scoreDate" type="date" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Time</label>
                        <input id="scoreTime" type="time" class="{{ $input }}">
                    </div>
                </div>

                <p id="scoreError" class="my-2 hidden text-xs text-[#A00000]"></p>

                <div class="mt-3 flex gap-2.5">
                    <button type="button" id="cancelScore" class="h-12 flex-1 rounded-[10px] border border-[#CFC8BA] bg-white text-sm font-medium">Cancel</button>
                    <button type="button" id="saveScore" class="h-12 flex-[2] rounded-[10px] bg-[#4E7A1D] text-sm font-medium text-white">Add score</button>
                </div>
            </div>
        </div>

            {{-- Address/Barangay --}}
        <div id="moveModal" class="fixed inset-0 z-50 hidden items-end justify-center bg-black/50">
            <form method="POST" action="/bhw/records/{{ $id }}/move" onsubmit="return confirm('Move this resident to the new barangay? You will no longer see their record.')"
                class="max-h-[92vh] w-full max-w-md overflow-y-auto bg-white px-5 pb-6 pt-3" style="border-radius: 20px 20px 0 0;">
                @csrf
                <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-[#CFC8BA]"></div>
                <h2 class="text-center text-lg font-semibold">Change barangay</h2>
                <p class="mb-3.5 mt-0.5 text-center text-[13px] text-gray-600">Use this when the resident moves to a new home</p>

                <div class="mb-4 rounded-[10px] bg-[#FFF1D6] px-3 py-2.5 text-xs text-[#7A4B00]">
                    After you confirm, this resident moves to the new barangay's list and you will no longer see their record. Their past records stay with them.
                </div>

                <label class="{{ $label }}">New barangay</label>
                <select name="barangay_id" required class="{{ $input }} mb-3">
                    <option value="" disabled selected>[Select barangay]</option>
                    @foreach ($barangays as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>

                <label class="{{ $label }}">Date moved</label>
                <input id="moveDate" name="moved_on" type="date" required max="{{ now()->toDateString() }}" class="{{ $input }} mb-3">

                <label class="{{ $label }}">New address</label>
                <input name="address" type="text" required placeholder="[House no., street]" class="{{ $input }} mb-4">

                <div class="flex gap-2.5">
                    <button type="button" id="cancelMove" class="h-12 flex-1 rounded-[10px] border border-[#CFC8BA] bg-white text-sm font-medium">Cancel</button>
                    <button type="submit" class="h-12 flex-[2] rounded-[10px] bg-[#4E7A1D] text-sm font-medium text-white">Confirm move</button>
                </div>
            </form>
        </div>
                
        

    <script>
        // ADD PHOTO SCRIPT
        const h = document.getElementById('height'), w = document.getElementById('weight'), b = document.getElementById('bmi');
        function calcBmi() {
            const m = parseFloat(h.value) / 100, kg = parseFloat(w.value);
            b.value = (m > 0 && kg > 0) ? (kg / (m * m)).toFixed(1) : '';
        }
        h.addEventListener('input', calcBmi);
        w.addEventListener('input', calcBmi);

        document.getElementById('photos').addEventListener('change', function () {
            document.getElementById('photoLabel').textContent = this.files.length ? this.files.length + ' selected' : '+ Add photo';
        });

        // MODAL FOR PROFILE PICTURE CAPTURE

        const photoInput = document.getElementById('photoInput');
        const photoModal = document.getElementById('photoModal');
        const photoPreview = document.getElementById('photoPreview');
        const photoPreviewIcon = document.getElementById('photoPreviewIcon');
        const avatarImg = document.getElementById('avatarImg');
        const avatarIcon = document.getElementById('avatarIcon');
        const removeFlag = document.getElementById('removePhoto');
        let confirmedFile = null, confirmedUrl = null, pendingUrl = null;

        function showPreview(url) {
            photoPreview.src = url || '';
            photoPreview.classList.toggle('hidden', !url);
            photoPreviewIcon.classList.toggle('hidden', !!url);
        }
        function setAvatar(url) {
            avatarImg.src = url || '';
            avatarImg.classList.toggle('hidden', !url);
            avatarIcon.classList.toggle('hidden', !!url);
        }
        function openModal() {
            pendingUrl = null;
            showPreview(confirmedUrl);
            photoModal.classList.remove('hidden');
            photoModal.classList.add('flex');
        }
        function closeModal() {
            photoModal.classList.add('hidden');
            photoModal.classList.remove('flex');
        }
        function restoreInput() {
            const dt = new DataTransfer();
            if (confirmedFile) dt.items.add(confirmedFile);
            photoInput.files = dt.files;
        }

        document.getElementById('openPhoto').addEventListener('click', openModal);
        document.getElementById('takePhoto').addEventListener('click', () => {
            photoInput.setAttribute('capture', 'environment');
            photoInput.click();
        });
        document.getElementById('pickPhoto').addEventListener('click', () => {
            photoInput.removeAttribute('capture');
            photoInput.click();
        });

        photoInput.addEventListener('change', () => {
            const file = photoInput.files[0];
            if (!file) return;
            pendingUrl = URL.createObjectURL(file);
            showPreview(pendingUrl);
        });

        document.getElementById('usePhoto').addEventListener('click', () => {
            if (pendingUrl) {
                confirmedFile = photoInput.files[0];
                confirmedUrl = pendingUrl;
                removeFlag.value = '';
            }
            setAvatar(confirmedUrl);
            closeModal();
        });
        document.getElementById('cancelPhoto').addEventListener('click', () => {
            restoreInput();
            closeModal();
        });
        document.getElementById('removePhotoBtn').addEventListener('click', () => {
            confirmedFile = null; confirmedUrl = null; pendingUrl = null;
            photoInput.value = '';
            removeFlag.value = '1';
            setAvatar(null);
            closeModal();
        });

        // Units allowed for each score type. The Unit dropdown is rebuilt from this list
        // whenever the Score type changes, so a score cannot be saved in a unit that makes
        // no sense for it (for example, hemoglobin in mg/dL).
        // Keep it identical to $scoreUnits in the POST /bhw/records/{id} route in
        // routes/web.php, because the server checks the same list again when you save.
        const SCORE_UNITS = {
            'Cholesterol': ['mg/dL', 'mmol/L'],
            'Hemoglobin': ['g/dL', 'g/L'],
            'Uric acid': ['mg/dL', 'µmol/L'],
            'Other': ['mg/dL', 'mmol/L', 'g/dL', '%', 'U/L'],
        };
        const scoreModal = document.getElementById('scoreModal');
        const scoreType = document.getElementById('scoreType');
        const scoreUnit = document.getElementById('scoreUnit');
        const scoreValue = document.getElementById('scoreValue');
        const scoreDate = document.getElementById('scoreDate');
        const scoreTime = document.getElementById('scoreTime');
        const scoreError = document.getElementById('scoreError');
        const otherWrap = document.getElementById('otherWrap');
        const scoreOtherName = document.getElementById('scoreOtherName');
        const scoreList = document.getElementById('scoreList');
        let scoreIndex = 0;

        function fillUnits() {
            scoreUnit.innerHTML = '';
            SCORE_UNITS[scoreType.value].forEach(u => scoreUnit.add(new Option(u, u)));
            otherWrap.classList.toggle('hidden', scoreType.value !== 'Other');
        }
        function openScore() {
            scoreType.value = 'Cholesterol';
            fillUnits();
            scoreValue.value = '';
            scoreOtherName.value = '';
            const n = new Date(), p = v => String(v).padStart(2, '0');
            scoreDate.value = `${n.getFullYear()}-${p(n.getMonth() + 1)}-${p(n.getDate())}`;
            scoreTime.value = `${p(n.getHours())}:${p(n.getMinutes())}`;
            scoreError.classList.add('hidden');
            scoreModal.classList.remove('hidden');
            scoreModal.classList.add('flex');
        }
        function closeScore() {
            scoreModal.classList.add('hidden');
            scoreModal.classList.remove('flex');
        }
        function showScoreError(msg) {
            scoreError.textContent = msg;
            scoreError.classList.remove('hidden');
        }
        function addScoreRow(s) {
            const i = scoreIndex++;
            const row = document.createElement('div');
            row.className = 'flex items-center justify-between rounded-[10px] border border-[#E8E2D8] px-3 py-2.5';

            const info = document.createElement('div');
            const title = document.createElement('p');
            title.className = 'text-sm font-medium';
            title.textContent = `${s.type === 'Other' ? s.label : s.type}: ${s.value} ${s.unit}`;
            const detail = document.createElement('p');
            detail.className = 'text-xs text-gray-600';
            detail.textContent = `${s.date} ${s.time} (not saved yet)`;
            info.append(title, detail);

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'py-3 text-[13px] font-medium text-[#A00000]';
            remove.textContent = 'Remove';
            remove.addEventListener('click', () => row.remove());

            row.append(info, remove);
            Object.entries(s).forEach(([k, v]) => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = `scores[${i}][${k}]`;
                hidden.value = v;
                row.append(hidden);
            });
            scoreList.append(row);
        }

        document.getElementById('openScore').addEventListener('click', openScore);
        document.getElementById('cancelScore').addEventListener('click', closeScore);
        scoreType.addEventListener('change', fillUnits);
        document.getElementById('saveScore').addEventListener('click', () => {
            const value = parseFloat(scoreValue.value);
            if (isNaN(value) || value <= 0) {
                showScoreError('Enter a value for the score.');
                return;
            }
            if (scoreType.value === 'Other' && scoreOtherName.value.trim() === '') {
                showScoreError('Give the score a name.');
                return;
            }
            addScoreRow({
                type: scoreType.value,
                label: scoreType.value === 'Other' ? scoreOtherName.value.trim() : '',
                value: value,
                unit: scoreUnit.value,
                date: scoreDate.value,
                time: scoreTime.value,
            });
            closeScore();
        });

        // Remove a score that is already saved (uses the same hidden form as medications)
        document.querySelectorAll('[data-remove-score]').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!confirm('Remove this score? Use this only for a mistake.')) return;
                removeMedForm.action = '/bhw/records/{{ $id }}/scores/' + btn.dataset.removeScore;
                removeMedForm.submit();
            });
        });

        //Add Another Medication from EDIT RECORD
        const medModal = document.getElementById('medModal');
        const medName = document.getElementById('medName');
        const medOther = document.getElementById('medOther');
        const medOtherWrap = document.getElementById('medOtherWrap');
        const medDose = document.getElementById('medDose');
        const medUnit = document.getElementById('medUnit');
        const medFreq = document.getElementById('medFreq');
        const medStart = document.getElementById('medStart');
        const medActive = document.getElementById('medActive');
        const medError = document.getElementById('medError');
        const medList = document.getElementById('medList');
        let medIndex = 0;

        function openMed() {
            medName.value = '';
            medOther.value = '';
            medOtherWrap.classList.add('hidden');
            medDose.value = '';
            medUnit.value = 'mg';
            medFreq.value = '';
            const n = new Date(), p = v => String(v).padStart(2, '0');
            medStart.value = `${n.getFullYear()}-${p(n.getMonth() + 1)}-${p(n.getDate())}`;
            medActive.checked = true;
            medError.classList.add('hidden');
            medModal.classList.remove('hidden');
            medModal.classList.add('flex');
        }
        function closeMed() {
            medModal.classList.add('hidden');
            medModal.classList.remove('flex');
        }
        function addMedRow(m) {
            const i = medIndex++;
            const row = document.createElement('div');
            row.className = 'mb-3 flex items-center justify-between rounded-[10px] border border-[#E8E2D8] px-3 py-2.5';

            const info = document.createElement('div');
            const title = document.createElement('p');
            title.className = 'text-sm font-medium';
            title.textContent = m.name;
            const detail = document.createElement('p');
            detail.className = 'text-xs text-gray-600';
            detail.textContent = `${m.dose} ${m.unit} · ${m.frequency}` + (m.active ? '' : ' · Stopped');
            info.append(title, detail);

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'py-3 text-[13px] font-medium text-[#A00000]';
            remove.textContent = 'Remove';
            remove.addEventListener('click', () => row.remove());

            row.append(info, remove);
            Object.entries(m).forEach(([k, v]) => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = `medications[${i}][${k}]`;
                hidden.value = v;
                row.append(hidden);
            });
            medList.append(row);
        }

        document.getElementById('openMed').addEventListener('click', openMed);
        document.getElementById('cancelMed').addEventListener('click', closeMed);
        medName.addEventListener('change', () => {
            medOtherWrap.classList.toggle('hidden', medName.value !== '__other');
        });
        document.getElementById('saveMed').addEventListener('click', () => {
            const name = medName.value === '__other' ? medOther.value.trim() : medName.value;
            const dose = parseFloat(medDose.value);
            if (!name || isNaN(dose) || dose <= 0 || !medFreq.value) {
                medError.textContent = 'Choose a medicine, enter the dose, and pick how often.';
                medError.classList.remove('hidden');
                return;
            }
            addMedRow({
                name: name,
                dose: dose,
                unit: medUnit.value,
                frequency: medFreq.value,
                start_date: medStart.value,
                active: medActive.checked ? 1 : 0,
            });
            closeMed();
        });

        // Address
        const moveModal = document.getElementById('moveModal');
        const moveDate = document.getElementById('moveDate');

        function openMove() {
            const n = new Date(), p = v => String(v).padStart(2, '0');
            moveDate.value = `${n.getFullYear()}-${p(n.getMonth() + 1)}-${p(n.getDate())}`;
            moveModal.classList.remove('hidden');
            moveModal.classList.add('flex');
        }
        function closeMove() {
            moveModal.classList.add('hidden');
            moveModal.classList.remove('flex');
        }
        document.getElementById('openMove').addEventListener('click', openMove);
        document.getElementById('cancelMove').addEventListener('click', closeMove);
        
        const recordForm = document.getElementById('recordForm');
        const saveBtn = recordForm.querySelector('button[type="submit"]');
        const saveModal = document.getElementById('saveModal');
        const confirmBtn = document.getElementById('confirmSave');
        let confirmed = false;

        function showSaveModal(show) {
            saveModal.classList.toggle('hidden', !show);
            saveModal.classList.toggle('flex', show);
        }

        // First submit: ask. Second submit (after "Yes, save"): go through.
        recordForm.addEventListener('submit', (e) => {
            if (!confirmed) {
                e.preventDefault();
                showSaveModal(true);
                return;
            }
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';
        });

        document.getElementById('cancelSave').addEventListener('click', () => showSaveModal(false));

        confirmBtn.addEventListener('click', () => {
            confirmed = true;
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Saving...';
            recordForm.requestSubmit();
        });

        // Coming back with the Back button: reset everything
        window.addEventListener('pageshow', () => {
            confirmed = false;
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Changes';
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Yes, save';
            showSaveModal(false);
        });
        
        // Medication removal confirmation
        const removeMedForm = document.getElementById('removeMedForm');
        document.querySelectorAll('[data-remove-med]').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!confirm('Remove this medication? Use this only for a mistake.')) return;
                removeMedForm.action = '/bhw/records/{{ $id }}/medications/' + btn.dataset.removeMed;
                removeMedForm.submit();
            });
        });
    </script>
    
</body>
</html>