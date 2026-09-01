<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Delete demo users and cascaded relations permanently
        DB::table('users')->whereIn('email', [
            'customer@tgt.com',
            'customer@polosim.com',
        ])->orWhereIn('name', [
            'Ahmet Yılmaz',
            'Mehmet Kaya',
        ])->delete();
    }

    public function down(): void
    {
        // No rollback needed
    }
};
