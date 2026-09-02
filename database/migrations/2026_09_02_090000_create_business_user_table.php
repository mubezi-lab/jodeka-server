<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            // This project has a legacy signed INT users.id in the live schema.
            $table->integer('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('access_level', 30)->default('employee');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['business_id', 'user_id']);
            $table->index(['user_id', 'is_active']);
        });

        // Preserve every existing user's current business assignment.
        DB::table('users')
            ->whereNotNull('business_id')
            ->orderBy('id')
            ->each(function (object $user): void {
                $roleName = DB::table('roles')
                    ->where('id', $user->role_id)
                    ->value('name');

                DB::table('business_user')->insert([
                    'business_id' => $user->business_id,
                    'user_id' => $user->id,
                    'access_level' => in_array($roleName, ['admin', 'manager'], true)
                        ? $roleName
                        : 'employee',
                    'is_primary' => true,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_user');
    }
};
