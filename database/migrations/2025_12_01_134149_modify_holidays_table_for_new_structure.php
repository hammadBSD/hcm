<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop old columns if they exist
        $columnsToDrop = [];
        
        if (Schema::hasColumn('holidays', 'country_id')) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->dropForeign(['country_id']);
            });
            $columnsToDrop[] = 'country_id';
        }
        
        if (Schema::hasColumn('holidays', 'province_id')) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->dropForeign(['province_id']);
            });
            $columnsToDrop[] = 'province_id';
        }
        
        if (Schema::hasColumn('holidays', 'type')) {
            $columnsToDrop[] = 'type';
        }
        
        if (Schema::hasColumn('holidays', 'is_recurring')) {
            $columnsToDrop[] = 'is_recurring';
        }
        
        if (Schema::hasColumn('holidays', 'date')) {
            $columnsToDrop[] = 'date';
        }
        
        if (!empty($columnsToDrop)) {
            Schema::table('holidays', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
        
        // Add new columns
        if (!Schema::hasColumn('holidays', 'from_date')) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->date('from_date')->after('name');
            });
        }
        
        if (!Schema::hasColumn('holidays', 'to_date')) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->date('to_date')->nullable()->after('from_date');
            });
        }
        
        if (!Schema::hasColumn('holidays', 'scope_type')) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->enum('scope_type', ['all_employees', 'department', 'role', 'employee'])->default('all_employees')->after('to_date');
            });
        }
        
        if (!Schema::hasColumn('holidays', 'created_by')) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->after('scope_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            if (Schema::hasColumn('holidays', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('holidays', 'scope_type')) {
                $table->dropColumn('scope_type');
            }
            if (Schema::hasColumn('holidays', 'to_date')) {
                $table->dropColumn('to_date');
            }
            if (Schema::hasColumn('holidays', 'from_date')) {
                $table->dropColumn('from_date');
            }
        });
        
        Schema::table('holidays', function (Blueprint $table) {
            $table->date('date')->after('name');
            $table->enum('type', ['national', 'religious', 'regional', 'company'])->default('national')->after('date');
            $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('cascade')->after('type');
            $table->foreignId('province_id')->nullable()->constrained('provinces')->onDelete('cascade')->after('country_id');
            $table->boolean('is_recurring')->default(false)->after('province_id');
        });
    }
};
