<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            // Order statuses
            ['category_type' => 'Order', 'code' => 'pending', 'name' => 'Pending', 'color' => '#FFA500', 'display_order' => 1, 'is_default' => true],
            ['category_type' => 'Order', 'code' => 'processing', 'name' => 'Processing', 'color' => '#007BFF', 'display_order' => 2],
            ['category_type' => 'Order', 'code' => 'completed', 'name' => 'Completed', 'color' => '#28A745', 'display_order' => 3],
            ['category_type' => 'Order', 'code' => 'cancelled', 'name' => 'Cancelled', 'color' => '#DC3545', 'display_order' => 4],
            ['category_type' => 'Order', 'code' => 'refunded', 'name' => 'Refunded', 'color' => '#6C757D', 'display_order' => 5],

            // PurchaseOrder statuses
            ['category_type' => 'PurchaseOrder', 'code' => 'draft', 'name' => 'Draft', 'color' => '#6C757D', 'display_order' => 1, 'is_default' => true],
            ['category_type' => 'PurchaseOrder', 'code' => 'pending', 'name' => 'Pending Approval', 'color' => '#FFA500', 'display_order' => 2],
            ['category_type' => 'PurchaseOrder', 'code' => 'approved', 'name' => 'Approved', 'color' => '#17A2B8', 'display_order' => 3],
            ['category_type' => 'PurchaseOrder', 'code' => 'ordered', 'name' => 'Ordered', 'color' => '#007BFF', 'display_order' => 4],
            ['category_type' => 'PurchaseOrder', 'code' => 'partial', 'name' => 'Partially Received', 'color' => '#FFC107', 'display_order' => 5],
            ['category_type' => 'PurchaseOrder', 'code' => 'received', 'name' => 'Received', 'color' => '#28A745', 'display_order' => 6],
            ['category_type' => 'PurchaseOrder', 'code' => 'cancelled', 'name' => 'Cancelled', 'color' => '#DC3545', 'display_order' => 7],

            // StockTransfer statuses
            ['category_type' => 'StockTransfer', 'code' => 'pending', 'name' => 'Pending', 'color' => '#FFA500', 'display_order' => 1, 'is_default' => true],
            ['category_type' => 'StockTransfer', 'code' => 'approved', 'name' => 'Approved', 'color' => '#17A2B8', 'display_order' => 2],
            ['category_type' => 'StockTransfer', 'code' => 'in_transit', 'name' => 'In Transit', 'color' => '#007BFF', 'display_order' => 3],
            ['category_type' => 'StockTransfer', 'code' => 'completed', 'name' => 'Completed', 'color' => '#28A745', 'display_order' => 4],
            ['category_type' => 'StockTransfer', 'code' => 'cancelled', 'name' => 'Cancelled', 'color' => '#DC3545', 'display_order' => 5],

            // OrderReturn statuses
            ['category_type' => 'OrderReturn', 'code' => 'pending', 'name' => 'Pending', 'color' => '#FFA500', 'display_order' => 1, 'is_default' => true],
            ['category_type' => 'OrderReturn', 'code' => 'approved', 'name' => 'Approved', 'color' => '#17A2B8', 'display_order' => 2],
            ['category_type' => 'OrderReturn', 'code' => 'processed', 'name' => 'Processed', 'color' => '#28A745', 'display_order' => 3],
            ['category_type' => 'OrderReturn', 'code' => 'rejected', 'name' => 'Rejected', 'color' => '#DC3545', 'display_order' => 4],
        ];

        foreach ($statuses as $status) {
            Status::updateOrCreate(
                [
                    'category_type' => $status['category_type'],
                    'code' => $status['code'],
                ],
                $status
            );
        }
    }
}
