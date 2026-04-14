<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $exists = DB::table('permissions')
            ->where('name', 'recruitment.view.own_jobs')
            ->where('guard_name', 'web')
            ->exists();

        if (!$exists) {
            DB::table('permissions')->insert([
                'name' => 'recruitment.view.own_jobs',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $permission = DB::table('permissions')
            ->where('name', 'recruitment.view.own_jobs')
            ->where('guard_name', 'web')
            ->first();

        if (!$permission) {
            return;
        }

        DB::table('role_has_permissions')->where('permission_id', $permission->id)->delete();
        DB::table('model_has_permissions')->where('permission_id', $permission->id)->delete();
        DB::table('permissions')->where('id', $permission->id)->delete();
    }
};
