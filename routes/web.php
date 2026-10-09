<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// default laravel welcome page
Route::get('/', function () {
    return view('welcome');
});

//self explanatory. a directory translator. kung baga tinatanggal lang yung back slash.
Route::view('/dashboard', 'dashboard');
Route::view('/login', 'login');
Route::view('/register', 'register');
Route::view('/register/step-2', 'register-step2');

// RESIDENT HOME PAGE
Route::get('/resident/home', function () {
    return view('resident.home', [
        'resident' => [
            'name' => '[Resident Name]',
            'id' => '[ID Number]',
            'barangay' => '[Barangay Name]',
            'purok' => '[Purok]',
        ],
        'featured' => [
            'title' => '[Event title]',
            'date' => '[Date]',
            'time' => '[Time]',
            'venue' => '[Venue]',
        ],
        'posts' => [
            ['type' => 'Event', 'title' => '[Event title]', 'date' => '[Date]'],
            ['type' => 'Announcement', 'title' => '[Announcement title]', 'date' => '[Date]'],
        ],
        //RESIDENT MEDICAL RECORDS
        'record' => [
        'updated_by' => '[BHW name]',
        'updated_on' => '[Date]',
        'tiles' => [
            ['label' => 'Blood pressure', 'value' => '[120/80]', 'unit' => 'mmHg'],
            ['label' => 'Pulse rate', 'value' => '[#]', 'unit' => 'bpm'],
            ['label' => 'Temperature', 'value' => '[#]', 'unit' => '°C'],
            ['label' => 'Blood glucose', 'value' => '[#]', 'unit' => 'mg/dL'],
            ['label' => 'Weight', 'value' => '[#]', 'unit' => 'kg'],
            ['label' => 'BMI', 'value' => '[BMI]', 'unit' => ''],
        ],
        'medications' => ['[Medicine name]', '[Medicine name]'],
        ],
    ]);
});

// DASHBOARD ROUTE (TEMPORARY PAGE)
Route::get('/bhw/dashboard', function () {
    return view('bhw.dashboard', [
        'bhw' => ['name' => '[BHW Name]', 'barangay' => '[Barangay name]'],
        'counts' => [
            ['value' => '[#]', 'label' => 'Residents'],
            ['value' => '[#]', 'label' => 'Upcoming Events'],
            ['value' => '[#]', 'label' => 'Referrals'],
        ],
        'events' => [
            ['title' => '[Event title]', 'date' => '[Date]', 'venue' => '[Venue]'],
            ['title' => '[Event title]', 'date' => '[Date]', 'venue' => '[Venue]'],
        ],
        'pending' => [
            ['name' => '[Resident name]', 'barangay' => '[Barangay]'],
        ],
    ]);
});

// RESIDENT REFERRALS
Route::get('/resident/referrals', function (Request $request) {
    $assignments = [
        ['status' => 'Current', 'name' => '[BHW name]', 'barangay' => '[Barangay]', 'period' => '[Start date] to present'],
        ['status' => 'Past', 'name' => '[BHW name]', 'barangay' => '[Barangay]', 'period' => '[Start date] to [End date]'],
        ['status' => 'Past', 'name' => '[BHW name]', 'barangay' => '[Barangay]', 'period' => '[Start date] to [End date]'],
    ];

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

// BHW PROFILE
Route::get('/resident/profile', function () {
    return view('resident.profile', [
        'resident' => [
            'name' => '[Resident name]',
            'id' => '[ID number]',
            'barangay' => '[Barangay name]',
            'purok' => '[Purok]',
            'phone' => '[Phone number]',
            'email' => '[Email]',
        ],
    ]);
});

// BHW REFERRALS
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

// BHW PROFILE
Route::get('/bhw/profile', function () {
    return view('bhw.profile', [
        'bhw' => [
            'name' => '[BHW name]',
            'id' => '[Worker ID]',
            'barangay' => '[Barangay name]',
            'purok' => '[Purok]',
            'phone' => '[Phone number]',
            'email' => '[Email]',
        ],
    ]);
});

// EDIT BHW RECORDS
Route::get('/bhw/records/{id}/edit', function ($id) {
    return view('bhw.record-edit', [
        'id' => $id,
        'resident' => ['name' => '[Resident name]', 'id' => '[ID number]'],
        'medications' => [
            ['name' => '[Medicine name]', 'detail' => '[Dose] · [How often]'],
            ['name' => '[Medicine name]', 'detail' => '[Dose] · [How often]'],
        ],
        'photoCount' => 2,
    ]);
});

// EVENT FORM
Route::post('/bhw/records/{id}', function ($id) {
    return back()->with('status', 'Changes saved (demo only, nothing is stored yet).');
});

Route::get('/bhw/events/create', function () {
    return view('bhw.event-form');
});

Route::post('/bhw/events', function (Request $request) {
    $request->validate([
        'type' => 'required|in:Event,Announcement',
        'title' => 'required|string|max:100',
        'date' => 'required_if:type,Event|nullable|date',
        'time' => 'required_if:type,Event|nullable',
        'venue' => 'required_if:type,Event|nullable|string|max:100',
        'description' => 'nullable|string|max:1000',
    ]);

    return redirect('/bhw/dashboard')->with('status', 'Posted (demo only, nothing is stored yet).');
});

//