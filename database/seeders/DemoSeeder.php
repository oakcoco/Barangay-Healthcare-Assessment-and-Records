<?php

namespace Database\Seeders;

use App\Models\Barangay;
use App\Models\BhwProfile;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('DEMO_PASSWORD');
        if (! $password) {
            throw new \RuntimeException('Set DEMO_PASSWORD in .env first (at least 8 characters).');
        }

        $barangay = Barangay::firstOrCreate(['name' => 'Barangay 1']);
        $now = now()->toDateTimeString();

        // Two health workers: one current, one former (for the assignment history)
        $bhw = $this->bhw('Demo BHW', '09170000001', 'BHW-0001', $barangay->id, $password);
        $former = $this->bhw('Former BHW', '09170000004', 'BHW-0002', $barangay->id, $password);

        // Resident accounts: one approved, one waiting for approval
        $residentUser = $this->account('Demo Resident', '09170000002', 'approved', $password);
        $pendingUser = $this->account('Pending Resident', '09170000003', 'pending', $password);

        $demo = $this->resident('Demo Resident', 'SI-2026-0001', $barangay->id, $residentUser, ['birthdate' => '1955-03-14', 'sex' => 'female']);
        $second = $this->resident('Sample Resident Two', 'SI-2026-0002', $barangay->id, null, ['birthdate' => '1948-09-02', 'sex' => 'male']);
        $this->resident('Pending Resident', 'SI-2026-0003', $barangay->id, $pendingUser);

        foreach ([$demo, $second] as $r) {
            $this->once('resident_residences', 'resident_id', $r->id, [[
                'resident_id' => $r->id, 'barangay_id' => $barangay->id, 'address' => '12 Sample Street',
                'moved_in_on' => '2020-01-01', 'moved_out_on' => null, 'recorded_by' => $bhw->id, 'created_at' => $now,
            ]]);
        }

        // Assignment history: a past BHW and the current one
        $this->once('bhw_assignments', 'resident_id', $demo->id, [
            ['resident_id' => $demo->id, 'bhw_user_id' => $former->id, 'assigned_on' => '2024-01-01', 'ended_on' => '2025-12-31', 'assigned_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['resident_id' => $demo->id, 'bhw_user_id' => $bhw->id, 'assigned_on' => '2026-01-01', 'ended_on' => null, 'assigned_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->once('bhw_assignments', 'resident_id', $second->id, [
            ['resident_id' => $second->id, 'bhw_user_id' => $bhw->id, 'assigned_on' => '2026-01-01', 'ended_on' => null, 'assigned_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Two vital-sign readings (the newest one shows on the resident home)
        $this->once('vital_signs', 'resident_id', $demo->id, [
            ['resident_id' => $demo->id, 'recorded_by' => $bhw->id, 'recorded_at' => now()->subDays(7)->toDateTimeString(), 'bp_systolic' => 132, 'bp_diastolic' => 86, 'temperature_c' => 36.7, 'spo2_percent' => 97, 'respiratory_rate' => 18, 'pulse_bpm' => 80, 'cbg_mgdl' => 118, 'created_at' => $now],
            ['resident_id' => $demo->id, 'recorded_by' => $bhw->id, 'recorded_at' => now()->subDays(1)->toDateTimeString(), 'bp_systolic' => 128, 'bp_diastolic' => 82, 'temperature_c' => 36.5, 'spo2_percent' => 98, 'respiratory_rate' => 17, 'pulse_bpm' => 76, 'cbg_mgdl' => 104, 'created_at' => $now],
        ]);

        // Medications: the stopped one must NOT show on the resident card
        $this->once('medications', 'resident_id', $demo->id, [
            ['resident_id' => $demo->id, 'added_by' => $bhw->id, 'name' => 'Amlodipine', 'dose' => 5, 'dose_unit' => 'mg', 'frequency' => 'once_daily', 'start_date' => '2025-06-01', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['resident_id' => $demo->id, 'added_by' => $bhw->id, 'name' => 'Metformin', 'dose' => 500, 'dose_unit' => 'mg', 'frequency' => 'twice_daily', 'start_date' => '2025-06-01', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['resident_id' => $demo->id, 'added_by' => $bhw->id, 'name' => 'Atorvastatin', 'dose' => 20, 'dose_unit' => 'mg', 'frequency' => 'once_daily', 'start_date' => '2024-01-01', 'is_active' => 0, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        ]);

        // One upcoming event and one announcement
        $this->once('events', 'posted_by', $bhw->id, [
            ['barangay_id' => $barangay->id, 'posted_by' => $bhw->id, 'type' => 'event', 'title' => 'Free blood pressure screening', 'event_date' => now()->addDays(7)->toDateString(), 'event_time' => '09:00:00', 'venue' => 'Barangay Hall', 'description' => 'Open to all residents.', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['barangay_id' => $barangay->id, 'posted_by' => $bhw->id, 'type' => 'announcement', 'title' => 'Health center hours changed', 'event_date' => null, 'event_time' => null, 'venue' => null, 'description' => 'Open from 8 AM to 4 PM on weekdays.', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        ]);
    }

    private function bhw(string $name, string $phone, string $workerNo, int $barangayId, string $password): User
    {
        $user = User::firstOrCreate(
            ['phone' => $phone],
            ['name' => $name, 'password' => $password, 'role' => 'bhw', 'status' => 'approved', 'approved_at' => now()]
        );
        BhwProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['worker_no' => $workerNo, 'barangay_id' => $barangayId, 'purok' => 'Purok 1']
        );

        return $user;
    }

    private function account(string $name, string $phone, string $status, string $password): User
    {
        return User::firstOrCreate(
            ['phone' => $phone],
            ['name' => $name, 'password' => $password, 'role' => 'resident', 'status' => $status,
             'approved_at' => $status === 'approved' ? now() : null]
        );
    }

    private function resident(string $name, string $no, int $barangayId, ?User $user, array $extra = []): Resident
    {
        return Resident::firstOrCreate(
            ['resident_no' => $no],
            array_merge([
                'user_id' => $user?->id,
                'barangay_id' => $barangayId,
                'full_name' => $name,
                'phone' => $user?->phone,
                'purok' => 'Purok 1',
            ], $extra)
        );
    }

    // Insert rows only if that owner has none yet, so the seeder is safe to run twice.
    private function once(string $table, string $key, int $id, array $rows): void
    {
        if (! DB::table($table)->where($key, $id)->exists()) {
            DB::table($table)->insert($rows);
        }
    }
}