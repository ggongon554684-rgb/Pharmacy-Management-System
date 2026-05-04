<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // View 1: vw_product_stock
        DB::statement("
            CREATE OR REPLACE VIEW vw_product_stock AS
            SELECT
                p.id AS product_id,
                p.sku AS sku,
                p.name AS product_name,
                p.generic_name AS generic_name,
                p.price AS price,
                p.reorder_level AS reorder_level,
                COALESCE(SUM(ib.quantity), 0) AS total_stock,
                COALESCE(SUM(CASE
                    WHEN ib.quantity > 0
                    AND ib.expiry_date >= CURRENT_DATE
                    AND ib.deleted_at IS NULL
                    THEN ib.quantity ELSE 0
                END), 0) AS releasable_stock
            FROM products p
            LEFT JOIN inventory_batches ib
                ON ib.product_id = p.id AND ib.deleted_at IS NULL
            WHERE p.deleted_at IS NULL
            GROUP BY p.id, p.sku, p.name, p.generic_name, p.price, p.reorder_level
        ");

        // View 2: vw_low_stock_products
        DB::statement("
            CREATE OR REPLACE VIEW vw_low_stock_products AS
            SELECT
                p.id AS product_id,
                p.sku AS sku,
                p.name AS product_name,
                p.generic_name AS generic_name,
                p.reorder_level AS reorder_level,
                COALESCE(SUM(CASE
                    WHEN ib.quantity > 0
                    AND ib.expiry_date >= CURRENT_DATE
                    AND ib.deleted_at IS NULL
                    THEN ib.quantity ELSE 0
                END), 0) AS releasable_stock,
                p.reorder_level - COALESCE(SUM(CASE
                    WHEN ib.quantity > 0
                    AND ib.expiry_date >= CURRENT_DATE
                    AND ib.deleted_at IS NULL
                    THEN ib.quantity ELSE 0
                END), 0) AS shortfall
            FROM products p
            LEFT JOIN inventory_batches ib ON ib.product_id = p.id
            WHERE p.deleted_at IS NULL
            GROUP BY p.id, p.sku, p.name, p.generic_name, p.reorder_level
            HAVING COALESCE(SUM(CASE
                WHEN ib.quantity > 0
                AND ib.expiry_date >= CURRENT_DATE
                AND ib.deleted_at IS NULL
                THEN ib.quantity ELSE 0
            END), 0) <= p.reorder_level
            ORDER BY shortfall DESC
        ");

        // View 3: vw_sales_summary
        DB::statement("
            CREATE OR REPLACE VIEW vw_sales_summary AS
            SELECT
                s.id AS sale_id,
                CAST(s.created_at AS DATE) AS sale_date,
                s.created_at AS sold_at,
                u.name AS cashier_name,
                COALESCE(pat.name, 'Walk-in') AS patient_name,
                COUNT(sli.id) AS item_count,
                COALESCE(SUM(sli.quantity), 0) AS total_units_sold,
                s.total_amount AS total_amount,
                s.payment_method AS payment_method,
                s.payment_tendered AS payment_tendered,
                s.payment_change_due AS payment_change_due,
                s.payment_reference AS payment_reference,
                s.insurance_provider AS insurance_provider,
                s.insurance_policy_number AS insurance_policy_number,
                s.insurance_authorization_code AS insurance_authorization_code,
                CASE WHEN s.prescription_id IS NOT NULL THEN 'Yes' ELSE 'No' END AS has_prescription
            FROM sales s
            JOIN users u ON u.id = s.user_id
            LEFT JOIN patients pat ON pat.id = s.patient_id
            LEFT JOIN sale_line_items sli ON sli.sale_id = s.id
            WHERE s.deleted_at IS NULL
            GROUP BY
                s.id, sale_date, s.created_at, u.name, pat.name,
                s.total_amount, s.payment_method, s.payment_tendered,
                s.payment_change_due, s.payment_reference,
                s.insurance_provider, s.insurance_policy_number,
                s.insurance_authorization_code, s.prescription_id
            ORDER BY s.created_at DESC
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS vw_sales_summary');
        DB::statement('DROP VIEW IF EXISTS vw_low_stock_products');
        DB::statement('DROP VIEW IF EXISTS vw_product_stock');
    }
};
