<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Name <span class="text-rose-500">*</span></label>
        <input type="text" name="name" value="{{ old('name', $package?->name) }}" required maxlength="150"
               class="form-input w-full" placeholder="e.g. Bed & Breakfast, Romantic Dinner">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
        <textarea name="description" rows="2" class="form-input w-full resize-none" maxlength="500"
                  placeholder="What's included…">{{ old('description', $package?->description) }}</textarea>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Price <span class="text-rose-500">*</span></label>
            <input type="number" name="price" step="0.01" min="0" required
                   value="{{ old('price', $package?->price) }}"
                   class="form-input w-full" placeholder="0.00">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Pricing Type <span class="text-rose-500">*</span></label>
            <select name="pricing_type" class="form-input w-full" required>
                <option value="per_night" @selected(old('pricing_type', $package?->pricing_type ?? 'per_stay') === 'per_night')>Per Night (board type)</option>
                <option value="per_stay" @selected(old('pricing_type', $package?->pricing_type ?? 'per_stay') === 'per_stay')>Per Stay (flat add-on)</option>
                <option value="per_guest" @selected(old('pricing_type', $package?->pricing_type ?? 'per_stay') === 'per_guest')>Per Guest</option>
            </select>
        </div>
    </div>

    <div>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" name="active" value="1"
                {{ old('active', $package?->active ?? true) ? 'checked' : '' }}
                class="rounded accent-navy">
            <span class="text-sm text-slate-700 dark:text-slate-200">Active — visible to guests at checkout</span>
        </label>
    </div>

    <div class="flex gap-3 pt-1">
        <button type="submit" class="flex-1 btn-primary">
            {{ $package ? 'Save Changes' : 'Add Meal Package' }}
        </button>
    </div>
</form>
