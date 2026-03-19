<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FIX: Add unique constraint to prevent duplicate open claims per warranty
 *
 * ISSUE: Race condition in duplicate claim prevention
 *        Check happens, then claim created - gap allows duplicates
 *
 * SOLUTION: Partial unique index (status = open)
 *
 * NOTE: MySQL doesn't support partial indexes directly
 *       Using trigger approach for MySQL compatibility
 */
return new class extends Migration
{
    public function up(): void
    {
        // For MySQL 8.0+, we can use functional index
        // For older MySQL, use a trigger

        DB::statement("
            CREATE TRIGGER prevent_duplicate_open_claims
            BEFORE INSERT ON warranty_claims
            FOR EACH ROW
            BEGIN
                IF NEW.status NOT IN ('resolved', 'closed', 'rejected') AND
                   EXISTS (
                       SELECT 1 FROM warranty_claims
                       WHERE warranty_id = NEW.warranty_id
                       AND status NOT IN ('resolved', 'closed', 'rejected')
                       AND id != COALESCE(NEW.id, 0)
                   ) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cannot create duplicate open claim for warranty';
                END IF;
            END;
        ");

        DB::statement("
            CREATE TRIGGER prevent_duplicate_open_claims_update
            BEFORE UPDATE ON warranty_claims
            FOR EACH ROW
            BEGIN
                IF NEW.status NOT IN ('resolved', 'closed', 'rejected') AND
                   OLD.status IN ('resolved', 'closed', 'rejected') AND
                   EXISTS (
                       SELECT 1 FROM warranty_claims
                       WHERE warranty_id = NEW.warranty_id
                       AND status NOT IN ('resolved', 'closed', 'rejected')
                       AND id != NEW.id
                   ) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cannot reopen claim with existing open claim';
                END IF;
            END;
        ");
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS prevent_duplicate_open_claims');
        DB::statement('DROP TRIGGER IF EXISTS prevent_duplicate_open_claims_update');
    }
};
