<?php

use App\Models\MarineArea;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marine_areas', function (Blueprint $table) {
            $table->json('linked_geojson_ids')->nullable()->after('geojson_id');
        });

        MarineArea::where('slug', 'ocean-pacifique')->update([
            'linked_geojson_ids' => json_encode(['1159115079', '1159115099']),
        ]);
    }

    public function down(): void
    {
        Schema::table('marine_areas', function (Blueprint $table) {
            $table->dropColumn('linked_geojson_ids');
        });
    }
};
