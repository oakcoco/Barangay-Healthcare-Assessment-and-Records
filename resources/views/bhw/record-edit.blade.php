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
                <a href="/bhw/referrals" aria-label="Back"><x-heroicon-o-chevron-left class="h-6 w-6" /></a>
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

        <form method="POST" action="/bhw/records/{{ $id }}" enctype="multipart/form-data">
            @csrf

            {{-- Household profile --}}
            <section class="{{ $card }}">
                <h2 class="text-base font-semibold">Household profile</h2>
                <p class="mb-3 text-xs text-gray-600">Main household details</p>
                <label class="{{ $label }}">Household head</label>
                <input name="household_head" type="text" placeholder="[Full name]" class="{{ $input }} mb-3">
                <label class="{{ $label }}">Address</label>
                <input name="address" type="text" placeholder="[House no., street]" class="{{ $input }} mb-3">
                <div class="mb-3 grid grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $label }}">Purok</label>
                        <select name="purok" class="{{ $input }}">
                            <option>[Purok]</option><option>Purok 1</option><option>Purok 2</option><option>Purok 3</option>
                        </select>
                    </div>
                    <div>
                        <label class="{{ $label }}">Members</label>
                        <input name="members" type="number" min="1" placeholder="[#]" class="{{ $input }}">
                    </div>
                </div>
                <label class="{{ $label }}">Contact number</label>
                <input name="contact" type="tel" placeholder="[Phone number]" class="{{ $input }}">
            </section>

            {{-- Socioeconomic and social services --}}
            <section class="{{ $card }}">
                <h2 class="text-base font-semibold">Socioeconomic and social services</h2>
                <p class="mb-3 text-xs text-gray-600">Last updated: [Date]</p>
                <label class="{{ $label }}">Employment status</label>
                <select name="employment" class="{{ $input }} mb-3">
                    <option>[Select]</option><option>Employed</option><option>Self-employed</option><option>Unemployed</option><option>Retired</option>
                </select>
                <label class="{{ $label }}">Monthly household income</label>
                <select name="income" class="{{ $input }} mb-2">
                    <option>[Select range]</option><option>Below 10,000</option><option>10,000 - 20,000</option><option>Above 20,000</option>
                </select>

                @foreach (['philhealth' => ['PhilHealth member', true], 'fourps' => ['4Ps beneficiary', false]] as $name => [$text, $on])
                    <label class="flex h-11 items-center justify-between text-sm">
                        {{ $text }}
                        <input type="checkbox" name="{{ $name }}" class="peer sr-only" @checked($on)>
                        <span class="relative h-6 w-11 rounded-full bg-[#CFC8BA] transition peer-checked:bg-[#4E7A1D] after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition peer-checked:after:translate-x-5"></span>
                    </label>
                @endforeach

                <p class="{{ $label }} mt-2">Social services received</p>
                <div class="flex flex-wrap gap-2">
                    @foreach (['Food packs', 'Cash aid', 'Senior pension', 'Scholarship'] as $s)
                        <label>
                            <input type="checkbox" name="services[]" value="{{ $s }}" class="peer sr-only" @checked($s === 'Food packs')>
                            <span class="block rounded-full border border-[#CFC8BA] bg-white px-3.5 py-2 text-[13px] peer-checked:border-[#4E7A1D] peer-checked:bg-[#E9F0DD] peer-checked:font-medium peer-checked:text-[#3B5E14]">{{ $s }}</span>
                        </label>
                    @endforeach
                </div>
            </section>

            {{-- Vital signs --}}
            <section class="{{ $card }}">
                <h2 class="text-base font-semibold">Vital signs</h2>
                <p class="mb-3 text-xs text-gray-600">Last recorded: [Date, time]</p>
                <div class="mb-3 grid grid-cols-2 gap-3">
                    <div><label class="{{ $label }}">Blood pressure (mmHg)</label><input name="bp" type="text" placeholder="120/80" class="{{ $input }}"></div>
                    <div><label class="{{ $label }}">Pulse rate (bpm)</label><input name="pulse" type="number" placeholder="[#]" class="{{ $input }}"></div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="{{ $label }}">Respiratory rate (/min)</label><input name="resp" type="number" placeholder="[#]" class="{{ $input }}"></div>
                    <div><label class="{{ $label }}">Temperature (°C)</label><input name="temp" type="number" step="0.1" placeholder="[#]" class="{{ $input }}"></div>
                </div>
            </section>

            {{-- Nutritional status --}}
            <section class="{{ $card }}">
                <h2 class="text-base font-semibold">Nutritional status</h2>
                <p class="mb-3 text-xs text-gray-600">Last recorded: [Date]</p>
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
                <p class="mb-3 text-xs text-gray-600">Last recorded: [Date]</p>
                <div class="mb-3 grid grid-cols-2 gap-3">
                    <div><label class="{{ $label }}">Blood glucose (mg/dL)</label><input name="glucose" type="number" placeholder="[#]" class="{{ $input }}"></div>
                    <div>
                        <label class="{{ $label }}">Measured</label>
                        <select name="glucose_type" class="{{ $input }}"><option>[Select]</option><option>Fasting</option><option>After meal</option></select>
                    </div>
                </div>
                <a href="#" class="flex h-11 items-center justify-center rounded-[10px] border border-dashed border-[#4E7A1D] text-sm font-medium text-[#3B5E14]">+ Add another score</a>
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
                        <a href="#" class="py-3 text-[13px] font-medium text-[#A00000]">Remove</a>
                    </div>
                @endforeach
                <a href="#" class="flex h-11 items-center justify-center rounded-[10px] border border-dashed border-[#4E7A1D] text-sm font-medium text-[#3B5E14]">+ Add medication</a>
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
                <a href="/bhw/referrals" class="flex h-12 flex-1 items-center justify-center rounded-[10px] border border-[#CFC8BA] bg-white text-sm font-medium">Cancel</a>
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
    </div>

    <script>
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
    </script>
</body>
</html>