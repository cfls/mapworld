<?php

use App\Models\Country;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sign_videos', function (Blueprint $table) {
            $table->string('signable_type')->nullable()->after('id');
            $table->unsignedBigInteger('signable_id')->nullable()->after('signable_type');
            $table->index(['signable_type', 'signable_id'], 'sign_videos_signable_index');
        });

        DB::table('sign_videos')
            ->whereNotNull('country_id')
            ->update([
                'signable_type' => Country::class,
                'signable_id' => DB::raw('country_id'),
            ]);

        // SQLite cannot drop a column that is part of a FK definition without
        // recreating the table. MySQL supports it directly.
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            DB::statement('
                CREATE TABLE sign_videos_new AS
                SELECT id, signable_type, signable_id, type,
                       cloudinary_public_id, cloudinary_url,
                       thumbnail_url, duration_seconds,
                       created_at, updated_at
                FROM sign_videos
            ');
            DB::statement('DROP TABLE sign_videos');
            DB::statement('ALTER TABLE sign_videos_new RENAME TO sign_videos');
            DB::statement('CREATE UNIQUE INDEX sign_videos_cloudinary_public_id_unique ON sign_videos (cloudinary_public_id)');
            DB::statement('CREATE INDEX sign_videos_signable_index ON sign_videos (signable_type, signable_id)');
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            Schema::table('sign_videos', function (Blueprint $table) {
                $table->dropForeign(['country_id']);
                $table->dropIndex('sign_videos_country_id_index');
                $table->dropColumn('country_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('sign_videos', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        DB::table('sign_videos')
            ->where('signable_type', Country::class)
            ->update(['country_id' => DB::raw('signable_id')]);

        Schema::table('sign_videos', function (Blueprint $table) {
            $table->dropIndex('sign_videos_signable_index');
            $table->dropColumn(['signable_type', 'signable_id']);
        });
    }
};
