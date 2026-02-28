<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\Store;
use App\Models\TaxRate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $taxRates = TaxRate::orderBy('name')->get()->map(fn($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'rate' => $r->rate,
            'is_default' => $r->is_default,
            'is_active' => $r->is_active,
        ]);

        return Inertia::render('Settings/Index', [
            'settings' => $settings,
            'taxRates' => $taxRates,
        ]);
    }

    public function update(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'store_name' => 'nullable|string|max:255',
            'store_address' => 'nullable|string',
            'store_phone' => 'nullable|string|max:20',
            'store_email' => 'nullable|email',
            'tax_number' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:10',
            'currency_symbol' => 'nullable|string|max:10',
            'currency_position' => 'nullable|string|in:before,after',
            'default_tax_rate' => 'nullable|numeric|min:0|max:100',
            'prices_include_tax' => 'nullable',
            'receipt_header' => 'nullable|string|max:500',
            'receipt_footer' => 'nullable|string|max:500',
            'auto_print_receipt' => 'nullable',
            'low_stock_threshold' => 'nullable|integer|min:1|max:1000',
            'allow_negative_stock' => 'nullable',
            'loyalty_enabled' => 'nullable',
            'loyalty_points_per_currency' => 'nullable|numeric|min:0',
            'loyalty_point_value' => 'nullable|numeric|min:0',
        ]);

        foreach ($validated as $key => $value) {
            // Skip _method field from Laravel method spoofing
            if ($key === '_method') continue;

            // Convert booleans to string
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }
            // Handle null values
            if ($value === null) {
                $value = '';
            }
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // Clear the cached settings
        Cache::forget('app_settings_shared');

        // Return JSON for AJAX requests
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully.',
                'notifications' => [
                    ['type' => 'success', 'message' => 'Settings saved successfully']
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    // Stores
    public function stores(): Response
    {
        $stores = Store::orderBy('name')->get();

        return Inertia::render('Settings/Stores', [
            'stores' => $stores->map(fn($store) => [
                'id' => $store->id,
                'name' => $store->name,
                'code' => $store->code,
                'address' => $store->address,
                'phone' => $store->phone,
                'email' => $store->email,
                'is_active' => $store->is_active,
            ]),
        ]);
    }

    public function storeStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:stores,code',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'is_active' => 'boolean',
        ]);

        Store::create($validated);

        return redirect()->back()->with('success', 'Store created successfully.');
    }

    public function updateStore(Request $request, Store $store): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:stores,code,' . $store->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'is_active' => 'boolean',
        ]);

        $store->update($validated);

        return redirect()->back()->with('success', 'Store updated successfully.');
    }

    public function destroyStore(Store $store): RedirectResponse
    {
        $store->delete();

        return redirect()->back()->with('success', 'Store deleted successfully.');
    }

    // Payment Methods
    public function paymentMethods(): Response
    {
        $methods = PaymentMethod::orderBy('name')->get();

        return Inertia::render('Settings/PaymentMethods', [
            'paymentMethods' => $methods->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'code' => $m->code,
                'type' => $m->type,
                'processing_fee' => $m->processing_fee,
                'is_active' => $m->is_active,
            ]),
        ]);
    }

    public function storePaymentMethod(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:payment_methods,code',
            'type' => 'required|string|in:cash,card,bank_transfer,mobile,credit',
            'processing_fee' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        PaymentMethod::create($validated);

        return redirect()->back()->with('success', 'Payment method created successfully.');
    }

    public function updatePaymentMethod(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:payment_methods,code,' . $paymentMethod->id,
            'type' => 'required|string|in:cash,card,bank_transfer,mobile,credit',
            'processing_fee' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $paymentMethod->update($validated);

        return redirect()->back()->with('success', 'Payment method updated successfully.');
    }

    public function destroyPaymentMethod(PaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->delete();

        return redirect()->back()->with('success', 'Payment method deleted successfully.');
    }

    // Tax Rates
    public function taxRates(): Response
    {
        $rates = TaxRate::orderBy('name')->get();

        return Inertia::render('Settings/TaxRates', [
            'taxRates' => $rates->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'rate' => $r->rate,
                'is_default' => $r->is_default,
                'is_active' => $r->is_active,
            ]),
        ]);
    }

    public function storeTaxRate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // If setting as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            TaxRate::where('is_default', true)->update(['is_default' => false]);
        }

        TaxRate::create($validated);

        return redirect()->back()->with('success', 'Tax rate created successfully.');
    }

    public function updateTaxRate(Request $request, TaxRate $taxRate): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // If setting as default, unset other defaults
        if (($validated['is_default'] ?? false) && !$taxRate->is_default) {
            TaxRate::where('is_default', true)->update(['is_default' => false]);
        }

        $taxRate->update($validated);

        return redirect()->back()->with('success', 'Tax rate updated successfully.');
    }

    public function destroyTaxRate(TaxRate $taxRate): RedirectResponse
    {
        $taxRate->delete();

        return redirect()->back()->with('success', 'Tax rate deleted successfully.');
    }

    // Users
    public function users(): Response
    {
        $users = User::orderBy('first_name')->get();

        return Inertia::render('Settings/Users', [
            'users' => $users->map(fn($u) => [
                'id' => $u->id,
                'first_name' => $u->first_name,
                'last_name' => $u->last_name,
                'email' => $u->email,
                'role' => $u->role,
                'is_active' => $u->is_active,
                'last_login_at' => $u->last_login_at,
            ]),
        ]);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|string|in:admin,manager,cashier,inventory',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->back()->with('success', 'User created successfully.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => 'required|string|in:admin,manager,cashier,inventory',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function destroyUser(User $user): RedirectResponse
    {
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}
