<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\InventoryBatch;

class InventoryReleaseService
{
    /**
     * Release stock for a product using FEFO (First Expired, First Out) order.
     *
     * CONCURRENCY NOTE: This method MUST be called inside a DB::transaction().
     * The lockForUpdate() call serialises competing transactions at the DB level,
     * preventing phantom reads. Each batch is decremented via a conditional UPDATE
     * (quantity >= deduct) so that even if two transactions somehow race to the
     * same batch, the DB-level constraint prevents the quantity from going negative.
     * After every decrement we verify the affected row count; a 0-row result means
     * another transaction won the race for that batch and we abort immediately.
     *
     * @return array<int, array{
     *     inventory_batch_id:int,
     *     quantity:int,
     *     batch_number:string,
     *     expiry_date:string|null,
     *     cost_price:string|null
     * }>
     *
     * @throws InsufficientStockException
     * @throws \RuntimeException if called outside a transaction
     */
    public function releaseProduct(
        int $productId,
        int $requestedQty,
        string $errorKey,
        string $productName,
        ?string $locationCode = null
    ): array {
        if (!\Illuminate\Support\Facades\DB::transactionLevel()) {
            throw new \RuntimeException(
                'InventoryReleaseService::releaseProduct() must be called inside a database transaction.'
            );
        }

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

            // Atomic conditional decrement: only succeeds when the batch still
            // holds at least $deduct units at the moment the UPDATE executes.
            // This is the key guard against negative stock under concurrency.
            $affected = InventoryBatch::where('id', $batch->id)
                ->where('quantity', '>=', $deduct)
                ->decrement('quantity', $deduct);

            if ($affected === 0) {
                // Another concurrent transaction decremented this batch between
                // our SELECT … FOR UPDATE and this UPDATE. Abort and surface a
                // clear stock-unavailable error so the caller can retry or inform
                // the user, rather than silently under-filling the order.
                throw new InsufficientStockException(
                    $errorKey,
                    "Stock for {$productName} was updated by a concurrent transaction. Please try again."
                );
            }

            $allocations[] = [
                'inventory_batch_id' => $batch->id,
                'quantity'           => $deduct,
                'batch_number'       => $batch->batch_number,
                'expiry_date'        => optional($batch->expiry_date)->toDateString(),
                'cost_price'         => $batch->cost_price,
            ];

            $remaining -= $deduct;
        }

        return $allocations;
    }
}