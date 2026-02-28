<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Models\TaxRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'store_id' => $user->store_id,
                    'role' => $user->role?->name,
                    'role_slug' => $user->role?->slug,
                    'role_color' => $user->role?->color,
                    'permissions' => $user->role?->permissions ?? [],
                    'root_user' => $user->isRootUser(),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'appSettings' => $this->getAppSettings(),
        ];
    }

    /**
     * Get cached app settings for frontend
     */
    protected function getAppSettings(): array
    {
        return Cache::remember('app_settings_shared', 300, function () {
            $settings = Setting::pluck('value', 'key')->toArray();
            $defaultTax = TaxRate::where('is_default', true)->where('is_active', true)->first();

            return [
                // Store info
                'store_name' => $settings['store_name'] ?? 'Baqala POS',
                'store_address' => $settings['store_address'] ?? '',
                'store_phone' => $settings['store_phone'] ?? '',
                'store_email' => $settings['store_email'] ?? '',
                'tax_number' => $settings['tax_number'] ?? '',

                // Currency
                'currency' => $settings['currency'] ?? 'SAR',
                'currency_symbol' => $settings['currency_symbol'] ?? '',
                'currency_position' => $settings['currency_position'] ?? 'before',

                // Tax
                'default_tax_rate' => $defaultTax?->rate ?? (float) ($settings['default_tax_rate'] ?? 15),
                'tax_name' => $defaultTax?->name ?? 'VAT',
                'prices_include_tax' => filter_var($settings['prices_include_tax'] ?? false, FILTER_VALIDATE_BOOLEAN),

                // Receipt
                'receipt_header' => $settings['receipt_header'] ?? '',
                'receipt_footer' => $settings['receipt_footer'] ?? 'Thank you for your purchase!',
                'auto_print_receipt' => filter_var($settings['auto_print_receipt'] ?? false, FILTER_VALIDATE_BOOLEAN),

                // Inventory
                'low_stock_threshold' => (int) ($settings['low_stock_threshold'] ?? 10),
                'allow_negative_stock' => filter_var($settings['allow_negative_stock'] ?? false, FILTER_VALIDATE_BOOLEAN),

                // Loyalty
                'loyalty_enabled' => filter_var($settings['loyalty_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'loyalty_points_per_currency' => (float) ($settings['loyalty_points_per_currency'] ?? 1),
                'loyalty_point_value' => (float) ($settings['loyalty_point_value'] ?? 0.01),
            ];
        });
    }
}
