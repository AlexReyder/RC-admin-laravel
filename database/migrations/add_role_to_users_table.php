<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default(UserRole::ADMIN->value)->after('email');
        });

        // Для первого этапа делаем существующих пользователей superadmin,
        // чтобы ты сразу мог зайти в /admin/users и управлять аккаунтами.
        DB::table('users')->update([
            'role' => UserRole::SUPERADMIN->value,
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};