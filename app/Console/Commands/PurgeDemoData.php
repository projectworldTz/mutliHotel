<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\ErrorLog;
use App\Models\FeatureRequest;
use App\Models\HotelVisit;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeDemoData extends Command
{
    protected $signature = 'app:purge-demo-data {--dry-run : Report counts without deleting anything} {--force : Skip the confirmation prompt}';

    protected $description = 'Delete demo/test bookings and guest accounts before handing the app to the client. Never touches hotel/room/pricing setup or staff accounts (super-admin, hotel-owner, receptionist, manager, cashier).';

    private const STAFF_ROLES = ['super-admin', 'hotel-owner', 'receptionist', 'manager', 'cashier'];

    public function handle(): int
    {
        // "Keep" is defined positively by staff role membership, not by a
        // "customer" role, so accounts with no role at all (e.g. a stray
        // seeded test guest) are still correctly swept up as demo data.
        $usersToDelete = User::whereDoesntHave('roles', fn ($q) => $q->whereIn('name', self::STAFF_ROLES))->pluck('id');

        $counts = [
            'transactions'           => Transaction::count(),
            'payments'               => Payment::count(),
            'cancellation_approvals' => DB::table('cancellation_approvals')->count(),
            'invoices'               => DB::table('invoices')->count(),
            'booking_rooms'          => DB::table('booking_rooms')->count(),
            'bookings'               => Booking::count(),
            'reservation_carts'      => DB::table('reservation_carts')->count(),
            'favorites'              => DB::table('favorites')->count(),
            'reviews'                => DB::table('reviews')->count(),
            'non_staff_users'        => $usersToDelete->count(),
            'hotel_visits'           => HotelVisit::count(),
            'feature_requests'       => FeatureRequest::count(),
            'audit_logs'             => AuditLog::count(),
            'error_logs'             => ErrorLog::count(),
        ];

        $this->table(['Table', 'Rows to delete'], collect($counts)->map(fn ($v, $k) => [$k, $v])->values());
        $this->line('Accounts kept (staff roles: '.implode(', ', self::STAFF_ROLES).'):');
        User::whereHas('roles', fn ($q) => $q->whereIn('name', self::STAFF_ROLES))
            ->get()->each(fn (User $u) => $this->line('  - '.$u->email.' ('.$u->roles->pluck('name')->implode(',').')'));

        if ($this->option('dry-run')) {
            $this->info('Dry run — nothing was deleted.');

            return self::SUCCESS;
        }

        if (array_sum($counts) === 0) {
            $this->info('Nothing to purge.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('This will permanently delete the rows listed above. Hotel/room/pricing setup and the staff accounts listed above are never touched. Continue?')) {
            $this->warn('Aborted — no changes made.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($usersToDelete) {
            // Wholesale, not scoped by booking/user id: pre-launch there is no
            // legitimate row in any of these tables that isn't demo data, and
            // wholesale deletion sidesteps any orphaned/inconsistent seed rows
            // that a whereIn(...) on a snapshot of ids could miss.
            Transaction::query()->delete();
            Payment::query()->delete();
            DB::table('cancellation_approvals')->delete();
            DB::table('invoices')->delete();
            DB::table('booking_rooms')->delete();
            Booking::query()->delete();

            DB::table('reservation_cart_items')->delete();
            DB::table('reservation_carts')->delete();
            DB::table('favorites')->delete();
            DB::table('reviews')->delete();

            HotelVisit::query()->delete();
            FeatureRequest::query()->delete();
            AuditLog::query()->delete();
            ErrorLog::query()->delete();

            User::whereIn('id', $usersToDelete)->each(fn (User $user) => $user->delete());
        });

        $this->info('Demo data purged. Hotel/room/pricing setup and staff accounts were left untouched.');

        return self::SUCCESS;
    }
}
