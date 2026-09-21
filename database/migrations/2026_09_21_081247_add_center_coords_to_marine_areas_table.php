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
            $table->decimal('center_lat', 8, 4)->nullable()->after('max_depth_m');
            $table->decimal('center_lng', 8, 4)->nullable()->after('center_lat');
            $table->unsignedTinyInteger('center_zoom')->nullable()->after('center_lng');
        });

        // Set coordinates for areas that have no GeoJSON polygon
        $coords = [
            'la-manche' => [50.2, -1.5, 6],
            'mer-baltique' => [58.0, 19.0, 4],
            'mer-du-nord' => [56.5,  3.0, 5],
        ];

        foreach ($coords as $slug => [$lat, $lng, $zoom]) {
            MarineArea::where('slug', $slug)->update([
                'center_lat' => $lat,
                'center_lng' => $lng,
                'center_zoom' => $zoom,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('marine_areas', function (Blueprint $table) {
            $table->dropColumn(['center_lat', 'center_lng', 'center_zoom']);
        });
    }
};
