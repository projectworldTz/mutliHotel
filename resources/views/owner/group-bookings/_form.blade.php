<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Event Name <span class="text-rose-500">*</span></label>
        <input type="text" name="event_name" value="{{ old('event_name', $groupBooking?->event_name) }}" required maxlength="150"
               class="form-input w-full" placeholder="e.g. Smith-Jones Wedding, Acme Corp Conference">
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Organizer Name <span class="text-rose-500">*</span></label>
            <input type="text" name="organizer_name" value="{{ old('organizer_name', $groupBooking?->organizer_name) }}" required maxlength="150" class="form-input w-full">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Rooms Requested <span class="text-rose-500">*</span></label>
            <input type="number" name="rooms_requested" min="1" value="{{ old('rooms_requested', $groupBooking?->rooms_requested ?? 1) }}" required class="form-input w-full">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Organizer Email</label>
            <input type="email" name="organizer_email" value="{{ old('organizer_email', $groupBooking?->organizer_email) }}" class="form-input w-full">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Organizer Phone</label>
            <input type="text" name="organizer_phone" value="{{ old('organizer_phone', $groupBooking?->organizer_phone) }}" class="form-input w-full">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Event Start <span class="text-rose-500">*</span></label>
            <input type="date" name="event_start" value="{{ old('event_start', $groupBooking?->event_start?->format('Y-m-d')) }}" required class="form-input w-full">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Event End <span class="text-rose-500">*</span></label>
            <input type="date" name="event_end" value="{{ old('event_end', $groupBooking?->event_end?->format('Y-m-d')) }}" required class="form-input w-full">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status <span class="text-rose-500">*</span></label>
        <select name="status" class="form-input w-full" required>
            @foreach(['inquiry'=>'Inquiry','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled'] as $v => $l)
            <option value="{{ $v }}" @selected(old('status', $groupBooking?->status ?? 'inquiry') === $v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Notes</label>
        <textarea name="notes" rows="3" class="form-input w-full resize-none">{{ old('notes', $groupBooking?->notes) }}</textarea>
    </div>

    <div class="flex gap-3 pt-1">
        <button type="submit" class="flex-1 btn-primary">{{ $groupBooking ? 'Save Changes' : 'Add Group Booking' }}</button>
    </div>
</form>
