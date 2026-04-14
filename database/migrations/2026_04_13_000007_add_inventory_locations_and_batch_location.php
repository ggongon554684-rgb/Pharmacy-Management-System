<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if (! Schema::hasTable('inventory_locations')) {
            Schema::create('inventory_locations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->boolean('is_front_shop')->default(false);
                $table->timestamps();
            });
        }

        DB::table('inventory_locations')->updateOrInsert(
            ['code' => 'back'],
            [
                'name' => 'Back Inventory',
                'is_front_shop' => false,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
        DB::table('inventory_locations')->updateOrInsert(
            ['code' => 'front'],
            [
                'name' => 'Front Shop',
                'is_front_shop' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        if (! Schema::hasColumn('inventory_batches', 'location_id')) {
            Schema::table('inventory_batches', function (Blueprint $table) {
                $table->foreignId('location_id')
                    ->default(1)
                    ->after('product_id')
                    ->constrained('inventory_locations')
                    ->restrictOnDelete();
            });
        }

        // Some existing databases still have legacy composite foreign keys
        // referencing inventory_batches(product_id, batch_number). MySQL blocks
        // dropping the old unique index until those FK constraints are removed.
        if ($driver === 'mysql') {
            $legacyConstraints = DB::select(
                "
                    SELECT TABLE_NAME as table_name, CONSTRAINT_NAME as constraint_name
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND REFERENCED_TABLE_NAME = 'inventory_batches'
                      AND REFERENCED_COLUMN_NAME IN ('product_id', 'batch_number')
                    GROUP BY TABLE_NAME, CONSTRAINT_NAME
                "
            );

            foreach ($legacyConstraints as $constraint) {
                DB::statement("ALTER TABLE `{$constraint->table_name}` DROP FOREIGN KEY `{$constraint->constraint_name}`");
            }
        }

        if ($driver === 'mysql') {
            $hasNewUnique = DB::selectOne("
                SELECT COUNT(*) as aggregate
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'inventory_batches'
                  AND INDEX_NAME = 'inventory_batches_product_id_batch_number_location_id_unique'
            ");

            if ((int) ($hasNewUnique->aggregate ?? 0) === 0) {
                Schema::table('inventory_batches', function (Blueprint $table) {
                    $table->unique(['product_id', 'batch_number', 'location_id']);
                });
            }
        } else {
            try {
                Schema::table('inventory_batches', function (Blueprint $table) {
                    $table->unique(['product_id', 'batch_number', 'location_id']);
                });
            } catch (\Throwable $exception) {
                // Index already exists on repeated/partial runs.
            }
        }

        if ($driver === 'mysql') {
            $hasOldUnique = DB::selectOne("
                SELECT COUNT(*) as aggregate
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'inventory_batches'
                  AND INDEX_NAME = 'inventory_batches_product_id_batch_number_unique'
            ");

            if ((int) ($hasOldUnique->aggregate ?? 0) > 0) {
                DB::statement('ALTER TABLE `inventory_batches` DROP INDEX `inventory_batches_product_id_batch_number_unique`');
            }
        } else {
            DB::statement('DROP INDEX IF EXISTS inventory_batches_product_id_batch_number_unique');
        }
    }

    public function down(): void
    {
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'batch_number', 'location_id']);
            $table->unique(['product_id', 'batch_number']);
            $table->dropConstrainedForeignId('location_id');
        });

        Schema::dropIfExists('inventory_locations');
    }
};
