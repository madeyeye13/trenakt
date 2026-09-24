<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Superseded - left as a no-op instead of deleted so migration history/
 * batch numbering stays intact.
 *
 * This migration duplicated database/migrations/2026_09_18_170209_create_permission_tables.php,
 * which already creates the permissions/roles/model_has_permissions/
 * model_has_roles/role_has_permissions tables and had already run on this
 * database (that's the migration actually responsible for those tables
 * existing). This file was written later, in the mistaken belief that the
 * permission tables were still missing, without checking that the earlier
 * migration already covered them - it never successfully ran (every attempt
 * failed with "relation already exists"), so nothing here was ever applied
 * and there's nothing to roll back.
 *
 * up()/down() are intentionally empty. Do not recreate these tables here -
 * see 2026_09_18_170209_create_permission_tables.php instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Intentionally empty - see class docblock above.
    }

    public function down(): void
    {
        // Intentionally empty - see class docblock above.
    }
};
