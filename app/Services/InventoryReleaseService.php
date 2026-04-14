<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\InventoryBatch;

class InventoryReleaseService
{
    /**
     * @return array<int, array{
     *     inventory_batch_id:int,
     *     quantity:int,
     *     batch_number:string,
     *     expiry_date:string|null,
     *     cost_price:string|null
     * }>
     */
    public function releaseProduct(
        int $productId,
        int $requestedQty,
        string $errorKey,
        string $productName,
        ?string $locationCode = null
    ): array
    {
        $query = InventoryBatch::query()
            ->where('product_id', $productId)
            ->releasable()
            ->fefo()
            ->lockForUpdate();

        if ($locationCode !== null) {
            $query->forLocationCode($locationCode);
        }

        $batches = $query->get();

        $available = (int) $batches->sum('quantity');
        if ($available < $requestedQty) {
            throw new InsufficientStockException(
                $errorKey,
                "Insufficient stock for {$productName}. Requested {$requestedQty}, available {$available}."
            );
        }

        $remaining = $requestedQty;
        $allocations = [];
        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $deduct = min((int) $batch->quantity, $remaining);
            $batch->decrement('quantity', $deduct);

            $allocations[] = [
                'inventory_batch_id' => $batch->id,
                'quantity' => $deduct,
                'batch_number' => $batch->batch_number,
                'expiry_date' => optional($batch->expiry_date)->toDateString(),
                'cost_price' => $batch->cost_price,
            ];

            $remaining -= $deduct;
        }

        return $allocations;
    }
}
