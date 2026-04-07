<?php

namespace Database\Seeders;

use App\Models\InventoryBatch;
use App\Models\Product;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'product' => [
                    'name' => 'Amoxicillin 500mg',
                    'generic_name' => 'Amoxicillin',
                    'sku' => 'MED-AMOX-500',
                    'price' => 18.50,
                    'reorder_level' => 40,
                ],
                'batches' => [
                    ['batch_number' => 'AMOX-A1', 'quantity' => 120, 'cost_price' => 11.00, 'expiry_date' => now()->addMonths(9)->toDateString()],
                    ['batch_number' => 'AMOX-A2', 'quantity' => 80, 'cost_price' => 11.50, 'expiry_date' => now()->addMonths(14)->toDateString()],
                ],
            ],
            [
                'product' => [
                    'name' => 'Paracetamol 500mg',
                    'generic_name' => 'Paracetamol',
                    'sku' => 'MED-PARA-500',
                    'price' => 6.75,
                    'reorder_level' => 100,
                ],
                'batches' => [
                    ['batch_number' => 'PARA-B1', 'quantity' => 300, 'cost_price' => 3.20, 'expiry_date' => now()->addMonths(10)->toDateString()],
                    ['batch_number' => 'PARA-B2', 'quantity' => 250, 'cost_price' => 3.10, 'expiry_date' => now()->addMonths(16)->toDateString()],
                ],
            ],
            [
                'product' => [
                    'name' => 'Cetirizine 10mg',
                    'generic_name' => 'Cetirizine',
                    'sku' => 'MED-CETI-10',
                    'price' => 9.00,
                    'reorder_level' => 60,
                ],
                'batches' => [
                    ['batch_number' => 'CETI-C1', 'quantity' => 140, 'cost_price' => 4.90, 'expiry_date' => now()->addMonths(8)->toDateString()],
                ],
            ],
            [
                'product' => [
                    'name' => 'Metformin 500mg',
                    'generic_name' => 'Metformin',
                    'sku' => 'MED-METF-500',
                    'price' => 12.25,
                    'reorder_level' => 70,
                ],
                'batches' => [
                    ['batch_number' => 'METF-D1', 'quantity' => 90, 'cost_price' => 7.10, 'expiry_date' => now()->addMonths(12)->toDateString()],
                    ['batch_number' => 'METF-D2', 'quantity' => 60, 'cost_price' => 7.00, 'expiry_date' => now()->addMonths(18)->toDateString()],
                ],
            ],
            [
                'product' => [
                    'name' => 'Losartan 50mg',
                    'generic_name' => 'Losartan',
                    'sku' => 'MED-LOSA-50',
                    'price' => 14.30,
                    'reorder_level' => 50,
                ],
                'batches' => [
                    ['batch_number' => 'LOSA-E1', 'quantity' => 110, 'cost_price' => 8.40, 'expiry_date' => now()->addMonths(11)->toDateString()],
                ],
            ],
        ];

        foreach ($items as $item) {
            $product = Product::updateOrCreate(
                ['sku' => $item['product']['sku']],
                $item['product']
            );

            foreach ($item['batches'] as $batch) {
                InventoryBatch::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'batch_number' => $batch['batch_number'],
                    ],
                    [
                        'quantity' => $batch['quantity'],
                        'cost_price' => $batch['cost_price'],
                        'expiry_date' => $batch['expiry_date'],
                    ]
                );
            }
        }
    }
}
