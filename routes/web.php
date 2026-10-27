<?php

use App\Http\Controllers\AuthController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// The home address sends everyone to the login page.
Route::redirect('/', '/login');

// Pages for people who are NOT logged in.
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showStep1']);
    Route::post('/register', [AuthController::class, 'storeStep1']);
    Route::get('/register/step-2', [AuthController::class, 'showStep2']);
    Route::post('/register/step-2', [AuthController::class, 'storeStep2']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// PROTECTED PAGES: must be logged in, and the role must match the URL (/resident or /bhw)
Route::middleware(['auth', 'role'])->group(function () {

    // RESIDENT HOME PAGE (real data)
    Route::get('/resident/home', function (Request $request) {
        $resident = $request->user()->resident()->with('barangay')->first();
        abort_unless($resident, 403, 'No resident record is linked to this account.');

        $day = fn ($d) => $d ? Carbon::parse($d)->format('M j, Y') : '—';

        $next = DB::table('events')
            ->where('barangay_id', $resident->barangay_id)
            ->where('type', 'event')->whereNull('deleted_at')
            ->whereDate('event_date', '>=', today())
            ->orderBy('event_date')->orderBy('event_time')->first();

        $posts = DB::table('events')
            ->where('barangay_id', $resident->barangay_id)->whereNull('deleted_at')
            ->orderByDesc('created_at')->limit(5)->get()
            ->map(fn ($e) => [
                'type' => ucfirst($e->type),
                'title' => $e->title,
                'date' => $day($e->event_date ?? $e->created_at),
            ])->all();

        // The newest vital-sign reading, with the name of the BHW who recorded it
        $v = DB::table('vital_signs as v')
            ->leftJoin('users as u', 'u.id', '=', 'v.recorded_by')
            ->where('v.resident_id', $resident->id)
            ->orderByDesc('v.recorded_at')
            ->select('v.*', 'u.name as bhw_name')->first();

        $show = fn ($x) => $x ?? '—';

        return view('resident.home', [
            'resident' => [
                'name' => $resident->full_name,
                'id' => $resident->resident_no ?? '—',
                'barangay' => $resident->barangay->name,
                'purok' => $resident->purok ?? '—',
            ],
            'featured' => [
                'title' => $next->title ?? 'No upcoming events',
                'date' => $next ? $day($next->event_date) : '—',
                'time' => $next ? Carbon::parse($next->event_time)->format('g:i A') : '—',
                'venue' => $next->venue ?? '—',
            ],
            'posts' => $posts,
            // RESIDENT MEDICAL RECORDS (read only)
            'record' => [
                'updated_by' => $v->bhw_name ?? '—',
                'updated_on' => $v ? $day($v->recorded_at) : '—',
                'tiles' => [
                    ['label' => 'Blood pressure',          'value' => ($v && $v->bp_systolic) ? $v->bp_systolic.'/'.$v->bp_diastolic : '—', 'unit' => 'mmHg'],
                    ['label' => 'Temperature',             'value' => $show($v?->temperature_c),    'unit' => '°C'],
                    ['label' => 'Oxygen saturation',       'value' => $show($v?->spo2_percent),     'unit' => '%'],
                    ['label' => 'Respiratory rate',        'value' => $show($v?->respiratory_rate), 'unit' => '/min'],
                    ['label' => 'Pulse rate',              'value' => $show($v?->pulse_bpm),        'unit' => 'bpm'],
                    ['label' => 'CBG (capillary glucose)', 'value' => $show($v?->cbg_mgdl),         'unit' => 'mg/dL'],
                ],
                'medications' => DB::table('medications')
                    ->where('resident_id', $resident->id)->where('is_active', 1)->whereNull('deleted_at')
                    ->orderBy('name')->pluck('name')->all(),
            ],
        ]);
    });
    // RESIDENT REFERRALS (history of assigned BHWs, real data)
    Route::get('/resident/referrals', function (Request $request) {
        $resident = $request->user()->resident;
        abort_unless($resident, 403, 'No resident record is linked to this account.');

        $assignments = DB::table('bhw_assignments as a')
            ->join('users as u', 'u.id', '=', 'a.bhw_user_id')
            ->leftJoin('bhw_profiles as p', 'p.user_id', '=', 'u.id')
            ->leftJoin('barangays as b', 'b.id', '=', 'p.barangay_id')
            ->where('a.resident_id', $resident->id)
            ->orderByDesc('a.assigned_on')
            ->select('a.assigned_on', 'a.ended_on', 'u.name', 'b.name as barangay')
            ->get()
            ->map(fn ($r) => [
                'status' => $r->ended_on ? 'Past' : 'Current',
                'name' => $r->name,
                'barangay' => $r->barangay ?? '—',
                'period' => Carbon::parse($r->assigned_on)->format('M j, Y').' to '
                    .($r->ended_on ? Carbon::parse($r->ended_on)->format('M j, Y') : 'present'),
            ])->all();

        $filter = $request->query('status', 'All');
        if ($filter !== 'All') {
            $assignments = array_filter($assignments, fn ($a) => $a['status'] === $filter);
        }

        return view('resident.referrals', [
            'assignments' => $assignments,
            'filter' => $filter,
            'filters' => ['All', 'Current', 'Past'],
        ]);
    });

    // RESIDENT PROFILE (real data)
    Route::get('/resident/profile', function (Request $request) {
        $user = $request->user();
        $resident = $user->resident()->with('barangay')->first();
        abort_unless($resident, 403, 'No resident record is linked to this account.');

        return view('resident.profile', [
            'resident' => [
                'name' => $resident->full_name,
                'id' => $resident->resident_no ?? '—',
                'barangay' => $resident->barangay->name,
                'purok' => $resident->purok ?? '—',
                'phone' => $resident->phone ?? $user->phone,
                'email' => $resident->email ?? $user->email ?? '—',
            ],
        ]);
    });

    // BHW DASHBOARD (real data)
    Route::get('/bhw/dashboard', function (Request $request) {
        $user = $request->user();
        $profile = $user->bhwProfile()->with('barangay')->first();
        abort_unless($profile, 403, 'No BHW profile is linked to this account.');

        $day = fn ($d) => $d ? Carbon::parse($d)->format('M j, Y') : '—';

        $assigned = DB::table('bhw_assignments')
            ->where('bhw_user_id', $user->id)->whereNull('ended_on')->count();

        $upcoming = DB::table('events')
            ->where('barangay_id', $profile->barangay_id)->where('type', 'event')
            ->whereNull('deleted_at')->whereDate('event_date', '>=', today())->count();

        $events = DB::table('events')
            ->where('posted_by', $user->id)->whereNull('deleted_at')
            ->orderByDesc('created_at')->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'date' => $day($e->event_date ?? $e->created_at),
                'venue' => $e->venue ?? 'Announcement',
            ])->all();

        $pending = DB::table('users as u')
            ->join('residents as r', 'r.user_id', '=', 'u.id')
            ->join('barangays as b', 'b.id', '=', 'r.barangay_id')
            ->where('u.status', 'pending')->where('u.role', 'resident')
            ->where('r.barangay_id', $profile->barangay_id)
            ->select('u.id', 'u.name', 'b.name as barangay')->get()
            ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'barangay' => $p->barangay])->all();

        return view('bhw.dashboard', [
            'bhw' => ['name' => $user->name, 'barangay' => $profile->barangay->name],
            'counts' => [
                ['value' => $assigned, 'label' => 'Residents'],
                ['value' => $upcoming, 'label' => 'Upcoming Events'],
                ['value' => count($pending), 'label' => 'Pending'],
            ],
            'events' => $events,
            'pending' => $pending,
        ]);
        
    });

    // APPROVE A RESIDENT SIGN-UP (only residents of the BHW's own barangay)
    Route::post('/bhw/signups/{id}/approve', function (Request $request, $id) {
        $bhw = $request->user();
        $profile = $bhw->bhwProfile;
        abort_unless($profile, 403);

        $resident = DB::table('residents')
            ->join('users', 'users.id', '=', 'residents.user_id')
            ->where('users.id', $id)->where('users.status', 'pending')->where('users.role', 'resident')
            ->where('residents.barangay_id', $profile->barangay_id)
            ->select('residents.id as resident_id')->first();
        abort_unless($resident, 404);

        DB::transaction(function () use ($id, $bhw, $resident) {
            DB::table('users')->where('id', $id)->update([
                'status' => 'approved',
                'approved_by' => $bhw->id,
                'approved_at' => now(),
                'updated_at' => now(),
            ]);

            // Demo shortcut: the approving BHW becomes the resident's BHW.
            // Later, the Mother Leader site will make these assignments instead.
            DB::table('bhw_assignments')->insert([
                'resident_id' => $resident->resident_id,
                'bhw_user_id' => $bhw->id,
                'assigned_on' => today()->toDateString(),
                'ended_on' => null,
                'assigned_by' => $bhw->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect('/bhw/dashboard')->with('status', 'Sign-up approved.');
    });

    // REJECT A RESIDENT SIGN-UP
    Route::post('/bhw/signups/{id}/reject', function (Request $request, $id) {
        $profile = $request->user()->bhwProfile;
        abort_unless($profile, 403);

        $rejected = DB::table('users')
            ->join('residents', 'residents.user_id', '=', 'users.id')
            ->where('users.id', $id)->where('users.status', 'pending')->where('users.role', 'resident')
            ->where('residents.barangay_id', $profile->barangay_id)
            ->update([
                'users.status' => 'rejected',
                'users.approved_by' => $request->user()->id,
                'users.approved_at' => now(),
                'users.updated_at' => now(),
            ]);
        abort_unless($rejected, 404);

        return redirect('/bhw/dashboard')->with('status', 'Sign-up rejected.');
    });

    
    // MY RESIDENTS: residents currently assigned to this BHW
    Route::get('/bhw/residents', function (Request $request) {
        $user = $request->user();
        $q = trim((string) $request->query('q', ''));

        $residents = DB::table('bhw_assignments as a')
            ->join('residents as r', 'r.id', '=', 'a.resident_id')
            ->where('a.bhw_user_id', $user->id)->whereNull('a.ended_on')->whereNull('r.deleted_at')
            ->when($q !== '', fn ($query) => $query->where('r.full_name', 'like', '%'.$q.'%'))
            ->orderBy('r.full_name')
            ->select('r.id', 'r.full_name', 'r.resident_no', 'r.birthdate',
                DB::raw('(select max(recorded_at) from vital_signs v where v.resident_id = r.id) as last_vitals'))
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->full_name,
                'no' => $r->resident_no ?? '—',
                'age' => $r->birthdate ? Carbon::parse($r->birthdate)->age.' Years Old' : '—',
                'last' => $r->last_vitals ? Carbon::parse($r->last_vitals)->format('M j, Y') : 'No readings yet',
            ])->all();

        return view('bhw.residents', ['residents' => $residents, 'q' => $q]);
    });

    // EDIT RECORD: only for a resident currently assigned to this BHW
    Route::get('/bhw/records/{id}/edit', function (Request $request, $id) {
        $user = $request->user();

        $resident = DB::table('residents as r')
            ->join('bhw_assignments as a', function ($j) use ($user) {
                $j->on('a.resident_id', '=', 'r.id')->where('a.bhw_user_id', $user->id)->whereNull('a.ended_on');
            })
            ->join('barangays as b', 'b.id', '=', 'r.barangay_id')
            ->where('r.id', $id)->whereNull('r.deleted_at')
            ->select('r.*', 'b.name as barangay_name')->first();

        $address = DB::table('resident_residences')
            ->where('resident_id', $resident->id)->whereNull('moved_out_on')->value('address');

        $medications = DB::table('medications')
            ->where('resident_id', $resident->id)->where('is_active', 1)->whereNull('deleted_at')
            ->orderBy('name')->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'detail' => rtrim(rtrim(number_format($m->dose, 2, '.', ''), '0'), '.').' '.$m->dose_unit
                    .' · '.str_replace('_', ' ', $m->frequency),
            ])->all();

        $socioRow = DB::table('socioeconomic_profiles')->where('resident_id', $resident->id)->first();
        
        return view('bhw.record-edit', [
            'id' => $resident->id,
            'resident' => [
                'name' => $resident->full_name,
                'id' => $resident->resident_no ?? '—',
                'barangay' => $resident->barangay_name,
                ],

            'barangays' => DB::table('barangays')->where('id', '!=', $resident->barangay_id)->orderBy('name')->get(),
            'last' => [
                'vitals' => DB::table('vital_signs')->where('resident_id', $resident->id)->max('recorded_at'),
                'nutrition' => DB::table('nutritional_records')->where('resident_id', $resident->id)->max('recorded_at'),
                ],
            
            'socio' => [
            'employment' => $socioRow->employment_status ?? null,
            'income' => $socioRow->income_range ?? null,
            'philhealth' => (bool) ($socioRow->is_philhealth_member ?? false),
            'fourps' => (bool) ($socioRow->is_4ps_beneficiary ?? false),
            'updated' => $socioRow ? Carbon::parse($socioRow->updated_at)->format('M j, Y g:i A') : null,
            ],

            'profile' => [
            'full_name' => $resident->full_name,
            'birthdate' => $resident->birthdate,    
            'sex' => $resident->sex,
            'phone' => $resident->phone,
            'address' => $address,
            ],
                
            'scores' => DB::table('medical_scores')->where('resident_id', $resident->id)->whereNull('deleted_at')
            ->orderByDesc('recorded_at')->limit(8)->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'title' => ($s->score_type === 'other' ? $s->label : ucfirst(str_replace('_', ' ', $s->score_type))).': '
                    .rtrim(rtrim(number_format($s->value, 2, '.', ''), '0'), '.').' '.$s->unit,
                'when' => Carbon::parse($s->recorded_at)->format('M j, Y g:i A'),
            ])->all(), 
            'medications' => $medications,
            
            //for profile
            'photoCount' => DB::table('record_photos')->where('resident_id', $resident->id)->whereNull('deleted_at')->count(),
            
            //for socio economic
            'serviceList' => DB::table('social_services')->orderBy('id')->get(),
            'serviceIds' => DB::table('resident_social_services')->where('resident_id', $resident->id)
                ->pluck('social_service_id')->map(fn ($v) => (int) $v)->all(),
        ]);
    });

    // BHW REFERRALS (still placeholder data: this page's purpose is undecided)
    Route::get('/bhw/referrals', function (Request $request) {
        $referrals = [
            ['status' => 'Pending', 'resident' => '[Resident name]', 'facility' => '[Facility name]', 'reason' => '[Reason]', 'date' => '[Date]'],
            ['status' => 'Done', 'resident' => '[Resident name]', 'facility' => '[Facility name]', 'reason' => '[Reason]', 'date' => '[Date]'],
            ['status' => 'Pending', 'resident' => '[Resident name]', 'facility' => '[Facility name]', 'reason' => '[Reason]', 'date' => '[Date]'],
        ];

        $filter = $request->query('status', 'All');
        if ($filter !== 'All') {
            $referrals = array_filter($referrals, fn ($r) => $r['status'] === $filter);
        }

        return view('bhw.referrals', [
            'referrals' => $referrals,
            'filter' => $filter,
            'filters' => ['All', 'Pending', 'Done'],
        ]);
    });

    // BHW PROFILE (real data)
    Route::get('/bhw/profile', function (Request $request) {
        $user = $request->user();
        $profile = $user->bhwProfile()->with('barangay')->first();
        abort_unless($profile, 403, 'No BHW profile is linked to this account.');

        return view('bhw.profile', [
            'bhw' => [
                'name' => $user->name,
                'id' => $profile->worker_no ?? '—',
                'barangay' => $profile->barangay->name,
                'purok' => $profile->purok ?? '—',
                'phone' => $user->phone,
                'email' => $user->email ?? '—',
            ],
        ]);
    });

    // SAVE RECORD: citizen profile, socioeconomic, vital signs, nutrition, scores and medications
    Route::post('/bhw/records/{id}', function (Request $request, $id) {
        $user = $request->user();
    
        $resident = DB::table('residents as r')
            ->join('bhw_assignments as a', function ($j) use ($user) {
                $j->on('a.resident_id', '=', 'r.id')->where('a.bhw_user_id', $user->id)->whereNull('a.ended_on');
            })
            ->where('r.id', $id)->whereNull('r.deleted_at')
            ->select('r.*')->first();
        abort_unless($resident, 404);
    
        $request->merge(['contact' => preg_replace('/[\s\-]/', '', (string) $request->input('contact'))]);
    
        $scoreTypes = ['Cholesterol' => 'cholesterol', 'Hemoglobin' => 'hemoglobin', 'Uric acid' => 'uric_acid', 'Other' => 'other'];
        $scoreUnits = [
            'Cholesterol' => ['mg/dL', 'mmol/L'],
            'Hemoglobin' => ['g/dL', 'g/L'],
            'Uric acid' => ['mg/dL', 'µmol/L'],
            'Other' => ['mg/dL', 'mmol/L', 'g/dL', '%', 'U/L'],
        ];
    
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'birthdate' => 'nullable|date|before_or_equal:today',
            'sex' => 'nullable|in:female,male',
            'contact' => ['nullable', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'address' => 'nullable|string|max:255',
            'bp' => ['nullable', 'regex:/^\d{2,3}\/\d{2,3}$/'],
            'temp' => 'nullable|numeric|between:30,45',
            'spo2' => 'nullable|integer|between:0,100',
            'resp' => 'nullable|integer|between:4,80',
            'pulse' => 'nullable|integer|between:20,250',
            'cbg' => 'nullable|integer|between:10,800',
            'height' => 'nullable|numeric|between:30,250',
            'weight' => 'nullable|numeric|between:2,400',
            'employment' => ['nullable', \Illuminate\Validation\Rule::in(['employed', 'self_employed', 'unemployed', 'retired'])],
            'income' => ['nullable', \Illuminate\Validation\Rule::in(['below_10k', '10k_to_20k', 'above_20k'])],
            'services' => 'nullable|array',
            'services.*' => 'integer|exists:social_services,id',
            'scores' => 'nullable|array',
            'scores.*.type' => ['required', \Illuminate\Validation\Rule::in(array_keys($scoreTypes))],
            'scores.*.value' => 'required|numeric|gt:0|max:99999',
            'scores.*.unit' => 'required|string|max:10',
            'scores.*.label' => 'nullable|string|max:80',
            'scores.*.date' => 'required|date|before_or_equal:today',
            'scores.*.time' => 'required|date_format:H:i',
            'medications' => 'nullable|array',
            'medications.*.name' => 'required|string|max:120',
            'medications.*.dose' => 'required|numeric|gt:0',
            'medications.*.unit' => 'required|in:mg,mcg,g,mL,tablet',
            'medications.*.frequency' => 'required|in:Once daily,Twice daily,Three times daily,Every 4 to 6 hours,Weekly,As needed,Other',
            'medications.*.start_date' => 'nullable|date',
            'medications.*.active' => 'nullable|in:0,1',
        ], [
            'full_name.required' => 'The resident needs a full name.',
            'birthdate.before_or_equal' => 'Birthdate cannot be in the future.',
            'contact.regex' => 'Enter a valid mobile number, like 09171234567.',
            'bp.regex' => 'Blood pressure must look like 120/80.',
            'temp.between' => 'Temperature must be between 30 and 45 °C.',
            'spo2.between' => 'Oxygen saturation must be between 0 and 100 %.',
            'resp.between' => 'Respiratory rate must be between 4 and 80 per minute.',
            'pulse.between' => 'Pulse rate must be between 20 and 250 bpm.',
            'cbg.between' => 'CBG must be between 10 and 800 mg/dL.',
            'height.between' => 'Height must be between 30 and 250 cm.',
            'weight.between' => 'Weight must be between 2 and 400 kg.',
            'scores.*.value.gt' => 'A score value must be greater than 0.',
            'scores.*.date.before_or_equal' => 'A score date cannot be in the future.',
        ]);
    
        // Blood pressure is typed as "120/80" and stored as two numbers
        $sys = $dia = null;
        if (! empty($data['bp'])) {
            [$sys, $dia] = array_map('intval', explode('/', $data['bp']));
            if ($sys < 40 || $sys > 300 || $dia < 20 || $dia > 200 || $sys <= $dia) {
                return back()->withErrors(['bp' => 'Blood pressure is out of range. The top number must be higher than the bottom one.'])->withInput();
            }
        }
    
        // Each score's unit must suit its type, and "Other" needs a name
        $scores = $data['scores'] ?? [];
        foreach ($scores as $s) {
            if (! in_array($s['unit'], $scoreUnits[$s['type']], true)) {
                return back()->withErrors(['scores' => $s['type'].' cannot be recorded in '.$s['unit'].'.'])->withInput();
            }
            if ($s['type'] === 'Other' && trim($s['label'] ?? '') === '') {
                return back()->withErrors(['scores' => 'Give the score a name when the type is Other.'])->withInput();
            }
        }
    
        // --- Citizen profile: did anything actually change? ---
        $newProfile = [
            'full_name' => trim($data['full_name']),
            'birthdate' => $data['birthdate'] ?? null,
            'sex' => $data['sex'] ?? null,
            'phone' => ($data['contact'] ?? '') !== '' ? $data['contact'] : null,
        ];
        $oldProfile = [
            'full_name' => $resident->full_name,
            'birthdate' => $resident->birthdate,
            'sex' => $resident->sex,
            'phone' => $resident->phone,
        ];
        $profileChanged = $newProfile != $oldProfile;
    
        $currentResidence = DB::table('resident_residences')
            ->where('resident_id', $resident->id)->whereNull('moved_out_on')->first();
        $newAddress = ($data['address'] ?? '') !== '' ? trim($data['address']) : null;
        $addressChanged = ($currentResidence->address ?? null) != $newAddress;
    
        // --- Vital signs ---
        $readings = [
            'temperature_c' => $data['temp'] ?? null,
            'spo2_percent' => $data['spo2'] ?? null,
            'respiratory_rate' => $data['resp'] ?? null,
            'pulse_bpm' => $data['pulse'] ?? null,
            'cbg_mgdl' => $data['cbg'] ?? null,
        ];
        $hasVitals = $sys !== null || count(array_filter($readings, fn ($v) => $v !== null)) > 0;
    
        if ($hasVitals) {
            $dupe = DB::table('vital_signs')
                ->where('resident_id', $resident->id)->where('recorded_by', $user->id)
                ->where('recorded_at', '>=', now()->subMinutes(2))
                ->where('bp_systolic', $sys)->where('bp_diastolic', $dia)
                ->where('temperature_c', $readings['temperature_c'])
                ->where('spo2_percent', $readings['spo2_percent'])
                ->where('respiratory_rate', $readings['respiratory_rate'])
                ->where('pulse_bpm', $readings['pulse_bpm'])
                ->where('cbg_mgdl', $readings['cbg_mgdl'])
                ->exists();
    
            if ($dupe) {
                $hasVitals = false;
            }
        }
    
        // --- Nutritional status (the database calculates BMI itself) ---
        $height = $data['height'] ?? null;
        $weight = $data['weight'] ?? null;
        $hasNutrition = $height !== null || $weight !== null;
    
        if ($hasNutrition) {
            $dupeNutrition = DB::table('nutritional_records')
                ->where('resident_id', $resident->id)->where('recorded_by', $user->id)
                ->where('recorded_at', '>=', now()->subMinutes(2))
                ->where('height_cm', $height)->where('weight_kg', $weight)
                ->exists();
    
            if ($dupeNutrition) {
                $hasNutrition = false;
            }
        }
    
        $meds = $data['medications'] ?? [];
    
        // --- Socioeconomic and social services: meron b nangyari d2 hirap? ---
        $oldSocioRow = DB::table('socioeconomic_profiles')->where('resident_id', $resident->id)->first();
        $newSocio = [
            'employment_status' => $data['employment'] ?? null,
            'income_range' => $data['income'] ?? null,
            'is_philhealth_member' => $request->boolean('philhealth') ? 1 : 0,
            'is_4ps_beneficiary' => $request->boolean('fourps') ? 1 : 0,
        ];
        $oldSocio = [
            'employment_status' => $oldSocioRow->employment_status ?? null,
            'income_range' => $oldSocioRow->income_range ?? null,
            'is_philhealth_member' => (int) ($oldSocioRow->is_philhealth_member ?? 0),
            'is_4ps_beneficiary' => (int) ($oldSocioRow->is_4ps_beneficiary ?? 0),
        ];
        $newServices = collect($data['services'] ?? [])->map(fn ($v) => (int) $v)->unique()->sort()->values()->all();
        $oldServices = DB::table('resident_social_services')->where('resident_id', $resident->id)
            ->pluck('social_service_id')->map(fn ($v) => (int) $v)->sort()->values()->all();
        $socioChanged = $newSocio != $oldSocio || $newServices != $oldServices;
    
        if (! $hasVitals && ! $hasNutrition && ! $scores && ! $meds && ! $profileChanged && ! $addressChanged && ! $socioChanged) {
            return back()->with('status', 'Nothing new to save. Only the citizen profile, socioeconomic details, vital signs, nutrition, scores and medications are stored for now.');
        }
    
        DB::transaction(function () use ($hasVitals, $hasNutrition, $height, $weight, $sys, $dia, $readings, $scores, $scoreTypes, $meds, $resident, $user, $profileChanged, $addressChanged, $newProfile, $currentResidence, $newAddress, $socioChanged, $newSocio, $oldSocioRow, $newServices, $oldServices) {
            if ($profileChanged) {
                DB::table('residents')->where('id', $resident->id)->update($newProfile + ['updated_at' => now()]);
            }
    
            if ($addressChanged) {
                if ($currentResidence) {
                    DB::table('resident_residences')->where('id', $currentResidence->id)->update(['address' => $newAddress]);
                } else {
                    DB::table('resident_residences')->insert([
                        'resident_id' => $resident->id,
                        'barangay_id' => $resident->barangay_id,
                        'address' => $newAddress,
                        'moved_in_on' => today()->toDateString(),
                        'recorded_by' => $user->id,
                        'created_at' => now(),
                    ]);
                }
            }
    
            if ($hasVitals) {
                DB::table('vital_signs')->insert(array_merge($readings, [
                    'resident_id' => $resident->id,
                    'recorded_by' => $user->id,
                    'recorded_at' => now(),
                    'bp_systolic' => $sys,
                    'bp_diastolic' => $dia,
                    'created_at' => now(),
                ]));
            }
    
            if ($hasNutrition) {
                DB::table('nutritional_records')->insert([
                    'resident_id' => $resident->id,
                    'recorded_by' => $user->id,
                    'recorded_at' => now(),
                    'height_cm' => $height,
                    'weight_kg' => $weight,
                    'created_at' => now(),
                ]);
            }
    
            if ($socioChanged) {
                if ($oldSocioRow) {
                    DB::table('socioeconomic_profiles')->where('resident_id', $resident->id)
                        ->update($newSocio + ['updated_by' => $user->id, 'updated_at' => now()]);
                } else {
                    DB::table('socioeconomic_profiles')->insert($newSocio + [
                        'resident_id' => $resident->id,
                        'updated_by' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
    
                // keep only the services that are ticked
                DB::table('resident_social_services')->where('resident_id', $resident->id)
                    ->whereNotIn('social_service_id', $newServices)->delete();
    
                foreach (array_diff($newServices, $oldServices) as $serviceId) {
                    DB::table('resident_social_services')->insert([
                        'resident_id' => $resident->id,
                        'social_service_id' => $serviceId,
                        'created_at' => now(),
                    ]);
                }
            }
    
            foreach ($scores as $s) {
                DB::table('medical_scores')->insert([
                    'resident_id' => $resident->id,
                    'recorded_by' => $user->id,
                    'score_type' => $scoreTypes[$s['type']],
                    'label' => $s['type'] === 'Other' ? trim($s['label']) : null,
                    'value' => $s['value'],
                    'unit' => $s['unit'],
                    'measured_as' => null,
                    'recorded_at' => $s['date'].' '.$s['time'].':00',
                    'created_at' => now(),
                ]);
            }
    
            foreach ($meds as $m) {
                DB::table('medications')->insert([
                    'resident_id' => $resident->id,
                    'added_by' => $user->id,
                    'name' => trim($m['name']),
                    'dose' => $m['dose'],
                    'dose_unit' => $m['unit'],
                    'frequency' => \Illuminate\Support\Str::slug($m['frequency'], '_'),
                    'start_date' => $m['start_date'] ?? null,
                    'is_active' => (int) ($m['active'] ?? 1),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    
        $saved = array_filter([
            ($profileChanged || $addressChanged) ? 'citizen profile' : null,
            $socioChanged ? 'socioeconomic details' : null,
            $hasVitals ? 'vital signs' : null,
            $hasNutrition ? 'nutrition' : null,
            $scores ? count($scores).' score'.(count($scores) > 1 ? 's' : '') : null,
            $meds ? count($meds).' medication'.(count($meds) > 1 ? 's' : '') : null,
        ]);
    
        return redirect('/bhw/residents')->with('status', 'Saved for '.$resident->full_name.': '.implode(', ', $saved).'.');
    });
    
    // REMOVE A SCORE ADDED BY MISTAKE (hidden, not erased)
    Route::delete('/bhw/records/{id}/scores/{score}', function (Request $request, $id, $score) {
        $allowed = DB::table('bhw_assignments')
            ->where('resident_id', $id)->where('bhw_user_id', $request->user()->id)->whereNull('ended_on')->exists();
        abort_unless($allowed, 404);
    
        $removed = DB::table('medical_scores')
            ->where('id', $score)->where('resident_id', $id)->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);
        abort_unless($removed, 404);
    
        return redirect("/bhw/records/{$id}/edit")->with('status', 'Score removed.');
    });
    

    // POST EVENT FORM
    Route::get('/bhw/events/create', function () {
        return view('bhw.event-form', ['event' => null]);
    });

    // SAVE A NEW EVENT OR ANNOUNCEMENT
    Route::post('/bhw/events', function (Request $request) {
    $data = $request->validate([
        'type' => 'required|in:Event,Announcement',
        'title' => 'required|string|max:100',
        'date' => 'required_if:type,Event|nullable|date',
        'time' => 'required_if:type,Event|nullable',
        'venue' => 'required_if:type,Event|nullable|string|max:100',
        'description' => 'nullable|string|max:1000',
    ]);

    $user = $request->user();
    $profile = $user->bhwProfile;
    abort_unless($profile, 403);

    $isEvent = $data['type'] === 'Event';

    DB::table('events')->insert([
        'barangay_id' => $profile->barangay_id,
        'posted_by' => $user->id,
        'type' => strtolower($data['type']),
        'title' => $data['title'],
        'event_date' => $isEvent ? ($data['date'] ?? null) : null,
        'event_time' => $isEvent ? ($data['time'] ?? null) : null,
        'venue' => $isEvent ? ($data['venue'] ?? null) : null,
        'description' => $data['description'] ?? null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect('/bhw/dashboard')->with('status', $data['type'].' posted.');
    });

    // OPEN THE EDIT FORM (a BHW can only edit what they posted for now)
    Route::get('/bhw/events/{id}/edit', function (Request $request, $id) {
        $e = DB::table('events')
            ->where('id', $id)->where('posted_by', $request->user()->id)
            ->whereNull('deleted_at')->first();
        abort_unless($e, 404);

        return view('bhw.event-form', [
            'event' => [
                'id' => $e->id,
                'type' => ucfirst($e->type),
                'title' => $e->title,
                'date' => $e->event_date,
                'time' => $e->event_time ? substr($e->event_time, 0, 5) : null,
                'venue' => $e->venue,
                'description' => $e->description,
            ],
        ]);
    });

    // SAVE CHANGES (the type cannot be changed once posted)
    Route::put('/bhw/events/{id}', function (Request $request, $id) {
        $data = $request->validate([
            'type' => 'required|in:Event,Announcement',
            'title' => 'required|string|max:100',
            'date' => 'required_if:type,Event|nullable|date',
            'time' => 'required_if:type,Event|nullable',
            'venue' => 'required_if:type,Event|nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);

        $existing = DB::table('events')
            ->where('id', $id)->where('posted_by', $request->user()->id)
            ->whereNull('deleted_at')->first();
        abort_unless($existing, 404);

        $isEvent = $existing->type === 'event';

        DB::table('events')->where('id', $id)->update([
            'title' => $data['title'],
            'event_date' => $isEvent ? ($data['date'] ?? null) : null,
            'event_time' => $isEvent ? ($data['time'] ?? null) : null,
            'venue' => $isEvent ? ($data['venue'] ?? null) : null,
            'description' => $data['description'] ?? null,
            'updated_at' => now(),
        ]);

        return redirect('/bhw/dashboard')->with('status', 'Changes saved.');
    });

    // DELETE (kept in the database, only hidden: deleted_at is filled in)
    Route::delete('/bhw/events/{id}', function (Request $request, $id) {
        $deleted = DB::table('events')
            ->where('id', $id)->where('posted_by', $request->user()->id)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);
        abort_unless($deleted, 404);

        return redirect('/bhw/dashboard')->with('status', 'Event deleted.');
    });


   // CHANGE BARANGAY: a move keeps the old address as history and ends the BHW assignment
    Route::post('/bhw/records/{id}/move', function (Request $request, $id) {
        $user = $request->user();

        $resident = DB::table('residents as r')
            ->join('bhw_assignments as a', function ($j) use ($user) {
                $j->on('a.resident_id', '=', 'r.id')->where('a.bhw_user_id', $user->id)->whereNull('a.ended_on');
            })
            ->where('r.id', $id)->whereNull('r.deleted_at')
            ->select('r.*')->first();
        abort_unless($resident, 404);

        $data = $request->validate([
            'barangay_id' => 'required|exists:barangays,id|not_in:'.$resident->barangay_id,
            'moved_on' => 'required|date|before_or_equal:today',
            'address' => 'required|string|max:255',
        ], [
            'barangay_id.required' => 'Choose the new barangay.',
            'barangay_id.not_in' => 'The resident already lives in that barangay.',
            'moved_on.before_or_equal' => 'The move date cannot be in the future.',
        ]);

        $current = DB::table('resident_residences')
            ->where('resident_id', $resident->id)->whereNull('moved_out_on')->first();

        if ($current && $data['moved_on'] < $current->moved_in_on) {
            return back()->withErrors([
                'moved_on' => 'The move date cannot be before they moved into their current address ('
                    .Carbon::parse($current->moved_in_on)->format('M j, Y').').',
            ]);
        }

        DB::transaction(function () use ($data, $resident, $current, $user) {
            // 1. close the old address first (only one open row is allowed per resident)
            if ($current) {
                DB::table('resident_residences')->where('id', $current->id)
                    ->update(['moved_out_on' => $data['moved_on']]);
            }

            // 2. open the new one
            DB::table('resident_residences')->insert([
                'resident_id' => $resident->id,
                'barangay_id' => $data['barangay_id'],
                'address' => trim($data['address']),
                'moved_in_on' => $data['moved_on'],
                'recorded_by' => $user->id,
                'created_at' => now(),
            ]);

            // 3. the resident now belongs to the new barangay (the old purok no longer applies)
            DB::table('residents')->where('id', $resident->id)->update([
                'barangay_id' => $data['barangay_id'],
                'purok' => null,
                'updated_at' => now(),
            ]);

            // 4. end this BHW's assignment (never earlier than the day it started)
            DB::update(
                'UPDATE bhw_assignments SET ended_on = GREATEST(assigned_on, ?), updated_at = ? WHERE resident_id = ? AND ended_on IS NULL',
                [$data['moved_on'], now(), $resident->id]
            );
        });

        $barangay = DB::table('barangays')->where('id', $data['barangay_id'])->value('name');

        return redirect('/bhw/residents')
            ->with('status', $resident->full_name.' moved to '.$barangay.'. They are no longer on your list.');
    });
    
    // REMOVE A MEDICATION ADDED BY MISTAKE (hidden, not erased)
    Route::delete('/bhw/records/{id}/medications/{med}', function (Request $request, $id, $med) {
        $user = $request->user();

        $allowed = DB::table('bhw_assignments')
            ->where('resident_id', $id)->where('bhw_user_id', $user->id)->whereNull('ended_on')->exists();
        abort_unless($allowed, 404);

        $removed = DB::table('medications')
            ->where('id', $med)->where('resident_id', $id)->whereNull('deleted_at')
            ->update(['deleted_at' => now(), 'updated_at' => now()]);
        abort_unless($removed, 404);

        return redirect("/bhw/records/{$id}/edit")->with('status', 'Medication removed.');
    });

});
