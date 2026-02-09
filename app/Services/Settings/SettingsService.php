<?php

namespace App\Services\Settings;

use App\Models\Setting;
use App\Models\SettingGroup;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    protected string $cacheKey = 'app_settings';
    protected int $cacheTtl = 86400; // 24 hours

    public function get(string $key, $default = null, ?int $storeId = null)
    {
        return Setting::get($key, $default, $storeId);
    }

    public function set(string $key, $value, ?int $storeId = null): void
    {
        Setting::set($key, $value, $storeId);
        $this->clearCache();
    }

    public function getAll(?int $storeId = null): array
    {
        $cacheKey = $this->cacheKey . ($storeId ? "_{$storeId}" : '');

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($storeId) {
            return Setting::query()
                ->when($storeId, function ($q) use ($storeId) {
                    $q->where(function ($q) use ($storeId) {
                        $q->where('store_id', $storeId)
                            ->orWhereNull('store_id');
                    });
                }, function ($q) {
                    $q->whereNull('store_id');
                })
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    public function getByGroup(?int $storeId = null): array
    {
        return SettingGroup::with(['settings' => function ($q) use ($storeId) {
            $q->when($storeId, function ($q) use ($storeId) {
                $q->where(function ($q) use ($storeId) {
                    $q->where('store_id', $storeId)
                        ->orWhereNull('store_id');
                });
            }, function ($q) {
                $q->whereNull('store_id');
            });
        }])
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(function ($group) {
                return [
                    $group->slug => [
                        'name' => $group->name,
                        'icon' => $group->icon,
                        'settings' => $group->settings->mapWithKeys(function ($setting) {
                            return [
                                $setting->key => [
                                    'value' => $setting->value,
                                    'type' => $setting->type,
                                    'label' => $setting->label,
                                    'description' => $setting->description,
                                    'options' => $setting->options,
                                ],
                            ];
                        }),
                    ],
                ];
            })
            ->toArray();
    }

    public function updateMany(array $settings, ?int $storeId = null): void
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value, $storeId);
        }
    }

    public function getPublicSettings(?int $storeId = null): array
    {
        return Setting::query()
            ->where('is_public', true)
            ->when($storeId, function ($q) use ($storeId) {
                $q->where(function ($q) use ($storeId) {
                    $q->where('store_id', $storeId)
                        ->orWhereNull('store_id');
                });
            }, function ($q) {
                $q->whereNull('store_id');
            })
            ->pluck('value', 'key')
            ->toArray();
    }

    public function clearCache(): void
    {
        Cache::forget($this->cacheKey);
    }

    public function getDefaultSettings(): array
    {
        return [
            // Shop settings
            'shop_name' => 'Baqala POS',
            'shop_phone' => '',
            'shop_email' => '',
            'shop_address' => '',
            'shop_vat_number' => '',
            'shop_cr_number' => '',
            'shop_logo' => '',

            // Tax settings
            'default_tax_rate' => 15,
            'tax_included_in_price' => false,

            // Currency settings
            'currency_code' => 'SAR',
            'currency_symbol' => 'ر.س',
            'currency_position' => 'after', // before, after

            // Receipt settings
            'receipt_header' => '',
            'receipt_footer' => 'Thank you for your purchase!',
            'receipt_show_logo' => true,
            'receipt_show_qr' => true,

            // Loyalty settings
            'loyalty_enabled' => true,
            'loyalty_points_per_sar' => 1,
            'loyalty_point_value' => 0.01,
            'loyalty_max_redeem_percent' => 50,

            // Inventory settings
            'track_inventory' => true,
            'low_stock_threshold' => 5,
            'allow_negative_stock' => false,
        ];
    }
}
