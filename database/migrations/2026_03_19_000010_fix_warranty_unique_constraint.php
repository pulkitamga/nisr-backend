<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * FIX: Add unique constraint to prevent duplicate active warranties
 *
 * ISSUE: Race condition in duplicate warranty activation check
 *        Check happens, then activation proceeds - gap allows duplicates
 *
 * SOLUTION: Database triggers to prevent duplicate active warranties
 *
 * NOTE: MySQL doesn't support partial indexes directly
 *       Using trigger approach for MySQL compatibility
 */
return new class extends Migration
{
    public function up(): void
    {
        // Trigger to prevent INSERT of duplicate active warranties
        DB::statement("
            CREATE TRIGGER prevent_duplicate_active_warranties_insert
            BEFORE INSERT ON warranties
            FOR EACH ROW
            BEGIN
                IF NEW.status = 'active' AND
                   EXISTS (
                       SELECT 1 FROM warranties
                       WHERE serial_number = NEW.serial_number
                       AND status = 'active'
                       AND end_date >= CURDATE()
                       AND id != COALESCE(NEW.id, 0)
                   ) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'An active warranty already exists for this serial number';
                END IF;
            END;
        ");

        // Trigger to prevent UPDATE that would create duplicate active warranty
        DB::statement("
            CREATE TRIGGER prevent_duplicate_active_warranties_update
            BEFORE UPDATE ON warranties
            FOR EACH ROW
            BEGIN
                IF NEW.status = 'active' AND OLD.status != 'active' AND
                   EXISTS (
                       SELECT 1 FROM warranties
                       WHERE serial_number = NEW.serial_number
                       AND status = 'active'
                       AND end_date >= CURDATE()
                       AND id != NEW.id
                   ) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cannot activate warranty - an active warranty already exists for this serial number';
                END IF;
            END;
        ");
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS prevent_duplicate_active_warranties_insert');
        DB::statement('DROP TRIGGER IF EXISTS prevent_duplicate_active_warranties_update');
    }
};
