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
                'age' => $r->birthdate ? Carbon::parse($r->birthdate)->age.' yrs' : '—',
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
            ->where('r.id', $id)->whereNull('r.deleted_at')
            ->select('r.id', 'r.full_name', 'r.resident_no')->first();
        abort_unless($resident, 404);

        $medications = DB::table('medications')
            ->where('resident_id', $resident->id)->where('is_active', 1)->whereNull('deleted_at')
            ->orderBy('name')->get()
            ->map(fn ($m) => [
                'name' => $m->name,
                'detail' => rtrim(rtrim(number_format($m->dose, 2, '.', ''), '0'), '.').' '.$m->dose_unit
                    .' · '.str_replace('_', ' ', $m->frequency),
            ])->all();

        return view('bhw.record-edit', [
            'id' => $resident->id,
            'resident' => ['name' => $resident->full_name, 'id' => $resident->resident_no ?? '—'],
            'medications' => $medications,
            'photoCount' => DB::table('record_photos')->where('resident_id', $resident->id)->whereNull('deleted_at')->count(),
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

    // SAVE RECORD: vital signs and medications (the other sections are not stored yet)
    Route::post('/bhw/records/{id}', function (Request $request, $id) {
    $user = $request->user();

        // Only a BHW currently assigned to this resident may save
        $resident = DB::table('residents as r')
            ->join('bhw_assignments as a', function ($j) use ($user) {
                $j->on('a.resident_id', '=', 'r.id')->where('a.bhw_user_id', $user->id)->whereNull('a.ended_on');
            })
            ->where('r.id', $id)->whereNull('r.deleted_at')
            ->select('r.id')->first();
        abort_unless($resident, 404);

        $data = $request->validate([
            'bp' => ['nullable', 'regex:/^\d{2,3}\/\d{2,3}$/'],
            'temp' => 'nullable|numeric|between:30,45',
            'spo2' => 'nullable|integer|between:0,100',
            'resp' => 'nullable|integer|between:4,80',
            'pulse' => 'nullable|integer|between:20,250',
            'cbg' => 'nullable|integer|between:10,800',
            'medications' => 'nullable|array',
            'medications.*.name' => 'required|string|max:120',
            'medications.*.dose' => 'required|numeric|gt:0',
            'medications.*.unit' => 'required|in:mg,mcg,g,mL,tablet',
            'medications.*.frequency' => 'required|in:Once daily,Twice daily,Three times daily,Every 4 to 6 hours,Weekly,As needed,Other',
            'medications.*.start_date' => 'nullable|date',
            'medications.*.active' => 'nullable|in:0,1',
        ], [
            'bp.regex' => 'Blood pressure must look like 120/80.',
            'temp.between' => 'Temperature must be between 30 and 45 °C.',
            'spo2.between' => 'Oxygen saturation must be between 0 and 100 %.',
            'resp.between' => 'Respiratory rate must be between 4 and 80 per minute.',
            'pulse.between' => 'Pulse rate must be between 20 and 250 bpm.',
            'cbg.between' => 'CBG must be between 10 and 800 mg/dL.',
        ]);

        // Blood pressure is typed as "120/80" and stored as two numbers
        $sys = $dia = null;
        if (! empty($data['bp'])) {
            [$sys, $dia] = array_map('intval', explode('/', $data['bp']));
            if ($sys < 40 || $sys > 300 || $dia < 20 || $dia > 200 || $sys <= $dia) {
                return back()->withErrors(['bp' => 'Blood pressure is out of range. The top number must be higher than the bottom one.'])->withInput();
            }
        }

        $readings = [
            'temperature_c' => $data['temp'] ?? null,
            'spo2_percent' => $data['spo2'] ?? null,
            'respiratory_rate' => $data['resp'] ?? null,
            'pulse_bpm' => $data['pulse'] ?? null,
            'cbg_mgdl' => $data['cbg'] ?? null,
        ];
        $hasVitals = $sys !== null || count(array_filter($readings, fn ($v) => $v !== null)) > 0;
        $meds = $data['medications'] ?? [];

        if (! $hasVitals && ! $meds) {
            return back()->with('status', 'Nothing new to save. Only vital signs and medications are stored for now.');
        }

        DB::transaction(function () use ($hasVitals, $sys, $dia, $readings, $meds, $resident, $user) {
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
            $hasVitals ? 'vital signs' : null,
            $meds ? count($meds).' medication'.(count($meds) > 1 ? 's' : '') : null,
        ]);

    return back()->with('status', 'Saved: '.implode(' and ', $saved).'.');
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


    // CHANGE ADDRESS or BARANGAY
    Route::post('/bhw/records/{id}/move', function (Request $request, $id) {
        $request->validate([
            'barangay_id' => 'required',
            'moved_on' => 'required|date|before_or_equal:today',
            'address' => 'required|string|max:255',
        ]);

        return redirect('/bhw/dashboard')->with('status', 'Resident moved to the new barangay (demo only, nothing is stored yet).');
    });

});
