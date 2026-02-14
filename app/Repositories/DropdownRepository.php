<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DropdownRepository
{
    /**
     * Fetch dropdown options by type
     */
    public function fetchOptions(string $type, ?string $keyword = null, array $data = []): array
    {
        $context = $data['context'] ?? null;

        return match ($type) {
            'users' => $this->getUsers($keyword, $data, $context),
            'customers' => $this->getCustomers($keyword, $data, $context),
            'products' => $this->getProducts($keyword, $data, $context),
            'categories' => $this->getCategories($keyword, $data, $context),
            'stores' => $this->getStores($keyword, $data, $context),
            'payment_methods' => $this->getPaymentMethods(),
            'order_statuses' => $this->getOrderStatuses(),
            'return_types' => $this->getReturnTypes(),
            'return_statuses' => $this->getReturnStatuses(),
            default => [],
        };
    }

    /**
     * Apply context filter (form = active only, filter = all)
     */
    protected function applyContextFilter(Builder $query, ?string $context, string $statusColumn = 'status', string $activeValue = 'Active'): Builder
    {
        if ($context === 'form') {
            $query->where($statusColumn, $activeValue);
        }
        return $query;
    }

    /**
     * Apply keyword search on columns
     */
    protected function applyKeywordSearch(Builder $query, ?string $keyword, array $columns): Builder
    {
        if (!$keyword) {
            return $query;
        }

        $keyword = strtolower($keyword);

        return $query->where(function ($q) use ($keyword, $columns) {
            foreach ($columns as $column) {
                $q->orWhereRaw("LOWER({$column}) LIKE ?", ["%{$keyword}%"]);
            }
        });
    }

    /**
     * Apply full name search (first_name + last_name)
     */
    protected function applyFullNameSearch(Builder $query, ?string $keyword, string $firstNameColumn = 'first_name', string $lastNameColumn = 'last_name'): Builder
    {
        if (!$keyword) {
            return $query;
        }

        $keyword = strtolower($keyword);

        return $query->where(function ($q) use ($keyword, $firstNameColumn, $lastNameColumn) {
            $q->whereRaw("LOWER({$firstNameColumn}) LIKE ?", ["%{$keyword}%"])
              ->orWhereRaw("LOWER({$lastNameColumn}) LIKE ?", ["%{$keyword}%"])
              ->orWhereRaw("LOWER(CONCAT({$firstNameColumn}, ' ', {$lastNameColumn})) LIKE ?", ["%{$keyword}%"]);
        });
    }

    /**
     * Get users dropdown
     */
    protected function getUsers(?string $keyword, array $data, ?string $context): array
    {
        $query = User::query()->select('id', 'first_name', 'last_name', 'email', 'status');

        $this->applyContextFilter($query, $context);
        $this->applyFullNameSearch($query, $keyword);

        if (!empty($data['store_id'])) {
            $query->whereHas('stores', fn($q) => $q->where('stores.id', $data['store_id']));
        }

        return $query->limit(50)->get()->map(fn($user) => [
            'value' => $user->id,
            'label' => "{$user->first_name} {$user->last_name}",
            'description' => $user->email,
            'status' => $user->status,
        ])->toArray();
    }

    /**
     * Get customers dropdown
     */
    protected function getCustomers(?string $keyword, array $data, ?string $context): array
    {
        $query = Customer::query()->select('id', 'first_name', 'last_name', 'phone', 'email', 'status', 'loyalty_card_number');

        $this->applyContextFilter($query, $context);
        $this->applyFullNameSearch($query, $keyword);

        if (!empty($data['exclude_ids'])) {
            $query->whereNotIn('id', (array) $data['exclude_ids']);
        }

        return $query->limit(50)->get()->map(fn($customer) => [
            'value' => $customer->id,
            'label' => "{$customer->first_name} {$customer->last_name}",
            'description' => $customer->phone ?? $customer->email,
            'loyalty_card' => $customer->loyalty_card_number,
            'status' => $customer->status,
        ])->toArray();
    }

    /**
     * Get products dropdown
     */
    protected function getProducts(?string $keyword, array $data, ?string $context): array
    {
        $query = Product::query()
            ->select('id', 'name', 'sku', 'barcode', 'sale_price', 'status', 'category_id')
            ->with('category:id,name');

        $this->applyContextFilter($query, $context);
        $this->applyKeywordSearch($query, $keyword, ['name', 'sku', 'barcode']);

        if (!empty($data['category_id'])) {
            $query->where('category_id', $data['category_id']);
        }

        if (!empty($data['store_id'])) {
            $query->whereHas('inventories', fn($q) => $q->where('store_id', $data['store_id'])->where('quantity', '>', 0));
        }

        return $query->limit(50)->get()->map(fn($product) => [
            'value' => $product->id,
            'label' => $product->name,
            'description' => $product->sku,
            'barcode' => $product->barcode,
            'price' => $product->sale_price,
            'category' => $product->category?->name,
            'status' => $product->status,
        ])->toArray();
    }

    /**
     * Get categories dropdown
     */
    protected function getCategories(?string $keyword, array $data, ?string $context): array
    {
        $query = Category::query()->select('id', 'name', 'code', 'is_active');

        if ($context === 'form') {
            $query->where('is_active', true);
        }

        $this->applyKeywordSearch($query, $keyword, ['name', 'code']);

        return $query->orderBy('sort_order')->limit(50)->get()->map(fn($category) => [
            'value' => $category->id,
            'label' => $category->name,
            'description' => $category->code,
            'is_active' => $category->is_active,
        ])->toArray();
    }

    /**
     * Get stores dropdown
     */
    protected function getStores(?string $keyword, array $data, ?string $context): array
    {
        $query = Store::query()->select('id', 'name', 'code', 'city', 'is_active');

        if ($context === 'form') {
            $query->where('is_active', true);
        }

        $this->applyKeywordSearch($query, $keyword, ['name', 'code', 'city']);

        $user = Auth::user();
        if ($user && !$user->is_super_user) {
            $query->whereHas('users', fn($q) => $q->where('users.id', $user->id));
        }

        return $query->limit(50)->get()->map(fn($store) => [
            'value' => $store->id,
            'label' => $store->name,
            'description' => $store->city,
            'code' => $store->code,
            'is_active' => $store->is_active,
        ])->toArray();
    }

    /**
     * Get payment methods (static)
     */
    protected function getPaymentMethods(): array
    {
        return [
            ['value' => 'cash', 'label' => 'Cash'],
            ['value' => 'card', 'label' => 'Card'],
            ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
            ['value' => 'credit', 'label' => 'Store Credit'],
        ];
    }

    /**
     * Get order statuses (static)
     */
    protected function getOrderStatuses(): array
    {
        return [
            ['value' => 'pending', 'label' => 'Pending'],
            ['value' => 'processing', 'label' => 'Processing'],
            ['value' => 'completed', 'label' => 'Completed'],
            ['value' => 'cancelled', 'label' => 'Cancelled'],
            ['value' => 'refunded', 'label' => 'Refunded'],
        ];
    }

    /**
     * Get return types (static)
     */
    protected function getReturnTypes(): array
    {
        return [
            ['value' => 'refund', 'label' => 'Refund'],
            ['value' => 'exchange', 'label' => 'Exchange'],
            ['value' => 'store_credit', 'label' => 'Store Credit'],
        ];
    }

    /**
     * Get return statuses (static)
     */
    protected function getReturnStatuses(): array
    {
        return [
            ['value' => 'pending', 'label' => 'Pending'],
            ['value' => 'approved', 'label' => 'Approved'],
            ['value' => 'processing', 'label' => 'Processing'],
            ['value' => 'completed', 'label' => 'Completed'],
            ['value' => 'rejected', 'label' => 'Rejected'],
        ];
    }
}
