<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\Hotel;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\BookingService;
use App\Services\InvoiceService;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
        private PricingService $pricingService,
        private InvoiceService $invoiceService,
    ) {}

    public function index(Request $request)
    {
        /** @var Hotel $hotel */
        $hotel  = $request->attributes->get('assigned_hotel');
        $status = $request->input('status', 'all');
        $search = $request->input('search');

        $query = Booking::where('hotel_id', $hotel->id)->with(['user', 'rooms.roomType']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $bookings = $query->latest()->paginate(20)->withQueryString();

        return view('receptionist.bookings.index', compact('hotel', 'bookings', 'status', 'search'));
    }

    public function show(Request $request, Booking $booking)
    {
        /** @var Hotel $hotel */
        $hotel = $request->attributes->get('assigned_hotel');
        abort_if($booking->hotel_id !== $hotel->id, 403);

        $booking->loadMissing(['user', 'rooms.roomType', 'hotel', 'payment', 'invoice', 'mealPackages']);

        return view('receptionist.bookings.show', compact('hotel', 'booking'));
    }

    public function create(Request $request)
    {
        /** @var Hotel $hotel */
        $hotel     = $request->attributes->get('assigned_hotel');
        $roomTypes = RoomType::where('hotel_id', $hotel->id)->get();

        return view('receptionist.bookings.create', compact('hotel', 'roomTypes'));
    }

    public function store(Request $request)
    {
        /** @var Hotel $hotel */
        $hotel = $request->attributes->get('assigned_hotel');

        $data = $request->validate([
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_phone'      => 'nullable|string|max:30',
            'room_type_id'     => 'required|exists:room_types,id',
            'check_in'         => 'required|date|after_or_equal:today',
            'check_out'        => 'required|date|after:check_in',
            'guests_adults'    => 'required|integer|min:1|max:20',
            'guests_children'  => 'nullable|integer|min:0|max:20',
            'notes'            => 'nullable|string|max:1000',
            'payment_method'   => 'required|in:cash,bank_transfer,airtel_money,mpesa',
        ]);

        $roomType = RoomType::where('id', $data['room_type_id'])
            ->where('hotel_id', $hotel->id)
            ->firstOrFail();

        $checkIn  = Carbon::parse($data['check_in']);
        $checkOut = Carbon::parse($data['check_out']);
        $nights   = $checkIn->diffInDays($checkOut);

        // Find an available specific room for this room type on these dates
        $room = Room::where('room_type_id', $roomType->id)
            ->where('hotel_id', $hotel->id)
            ->whereDoesntHave('bookingRooms', function ($q) use ($data) {
                $q->whereHas('booking', fn ($b) => $b->whereNotIn('status', [
                    Booking::STATUS_CANCELLED,
                    Booking::STATUS_CHECKED_OUT,
                ]))->where(function ($q) use ($data) {
                    $q->whereBetween('check_in', [$data['check_in'], $data['check_out']])
                      ->orWhereBetween('check_out', [$data['check_in'], $data['check_out']])
                      ->orWhere(function ($q) use ($data) {
                          $q->where('check_in', '<=', $data['check_in'])
                            ->where('check_out', '>=', $data['check_out']);
                      });
                });
            })->first();

        if (! $room) {
            return back()->withInput()->withErrors(['room_type_id' => 'No rooms available for those dates. Please try different dates.']);
        }

        // Find or create the guest user
        $guest = User::firstOrCreate(
            ['email' => $data['guest_email']],
            [
                'name'     => $data['guest_name'],
                'password' => bcrypt(Str::random(16)),
                'phone'    => $data['guest_phone'] ?? null,
            ]
        );

        if (! $guest->hasRole('customer')) {
            $customerRole = Role::where('name', 'customer')->first();
            if ($customerRole) {
                $guest->roles()->syncWithoutDetaching([$customerRole->id]);
            }
        }

        $subTotal = round($roomType->base_price * $nights, 2);
        $pricing  = $this->pricingService->calculateOrderTotal($subTotal);
        $taxRate    = $pricing['tax_rate'];
        $taxTotal   = $pricing['tax_total'];
        $grandTotal = $pricing['grand_total'];

        $booking = DB::transaction(function () use ($hotel, $guest, $room, $roomType, $data, $nights, $subTotal, $taxRate, $taxTotal, $grandTotal) {
            $booking = Booking::create([
                'booking_number'   => Booking::generateBookingNumber(),
                'user_id'          => $guest->id,
                'hotel_id'         => $hotel->id,
                'status'           => Booking::STATUS_CONFIRMED,
                'check_in'         => $data['check_in'],
                'check_out'        => $data['check_out'],
                'nights'           => $nights,
                'guests_adults'    => $data['guests_adults'],
                'guests_children'  => $data['guests_children'] ?? 0,
                'sub_total'        => $subTotal,
                'tax_total'        => $taxTotal,
                'tax_rate'         => $taxRate,
                'discount_total'   => 0,
                'grand_total'      => $grandTotal,
                'currency'         => config('app.currency'),
                'special_requests' => $data['notes'] ?? null,
                'confirmed_at'     => now(),
            ]);

            BookingRoom::create([
                'booking_id'   => $booking->id,
                'room_id'      => $room->id,
                'room_type_id' => $roomType->id,
                'check_in'     => $data['check_in'],
                'check_out'    => $data['check_out'],
                'nightly_rate' => $roomType->base_price,
                'nights'       => $nights,
                'sub_total'    => $subTotal,
            ]);

            Payment::create([
                'booking_id' => $booking->id,
                'method'     => $data['payment_method'],
                'status'     => 'paid',
                'amount'     => $grandTotal,
                'currency'   => config('app.currency'),
            ]);

            $invoice = $this->invoiceService->generateForBooking($booking);
            $this->invoiceService->markPaid($invoice);

            return $booking;
        });

        return redirect()
            ->route('receptionist.bookings.show', $booking)
            ->with('success', "Booking {$booking->booking_number} created successfully.");
    }

    public function confirm(Request $request, Booking $booking)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_if($booking->hotel_id !== $hotel->id, 403);

        $this->bookingService->confirm($booking);

        return back()->with('success', 'Booking confirmed.');
    }

    public function checkIn(Request $request, Booking $booking)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_if($booking->hotel_id !== $hotel->id, 403);

        $this->bookingService->checkIn($booking);

        return back()->with('success', 'Guest checked in successfully.');
    }

    public function checkOut(Request $request, Booking $booking)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_if($booking->hotel_id !== $hotel->id, 403);

        $this->bookingService->checkOut($booking);

        return back()->with('success', 'Guest checked out successfully.');
    }

    public function cancel(Request $request, Booking $booking)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_if($booking->hotel_id !== $hotel->id, 403);

        $reason = $request->input('reason', 'Cancelled by receptionist.');
        $this->bookingService->cancel($booking, $reason);

        return back()->with('success', 'Booking cancelled.');
    }

    public function invoice(Request $request, Booking $booking)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_if($booking->hotel_id !== $hotel->id, 403);

        $booking->loadMissing(['user', 'rooms.roomType', 'rooms.room', 'hotel', 'payment', 'invoice', 'cancellationApproval', 'mealPackages']);

        $invoice = $booking->invoice;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', compact('booking', 'invoice'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("invoice-{$booking->booking_number}.pdf");
    }
}
