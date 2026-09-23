<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $arctique = DB::table('marine_areas')->where('name', 'océan Arctique')->value('id');
        $austral = DB::table('marine_areas')->where('name', 'océan Austral')->value('id');

        DB::table('marine_areas')->insert([
            [
                'parent_id' => $arctique,
                'name' => 'Pôle Nord',
                'status' => 1,
                'slug' => 'pole-nord',
                'type' => 'other',
                'ocean_group' => 'arctique',
                'center_lat' => 90,
                'center_lng' => 0,
                'center_zoom' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'parent_id' => $austral,
                'name' => 'Pôle Sud',
                'status' => 1,
                'slug' => 'pole-sud',
                'type' => 'other',
                'ocean_group' => 'austral',
                'center_lat' => -90,
                'center_lng' => 0,
                'center_zoom' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('marine_areas')->whereIn('slug', ['pole-nord', 'pole-sud'])->delete();
    }
};
