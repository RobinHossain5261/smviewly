<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\SiteSetting;
use App\Support\SitemapBuilder;

return new class extends Migration
{
    public function up(): void
    {
        // Preserve editorial records while removing references to the retired platform.
        $clean = static fn (string $value): string => str_ireplace(
            ['Instagram, ', 'instagram-tips', 'instagram', 'Five platforms', '5 Platforms'],
            ['', 'social-media-tips', 'social media', 'Four platforms', '4 Platforms'],
            $value
        );

        DB::transaction(function () use ($clean) {
            foreach ([
                'site_settings' => ['key', ['value']],
                'categories' => ['id', ['name', 'slug']],
                'posts' => ['id', ['title', 'content', 'slug']],
                'faqs' => ['id', ['question', 'answer']],
            ] as $table => [$key, $columns]) {
                foreach (DB::table($table)->get() as $row) {
                    $changes = [];
                    foreach ($columns as $column) {
                        if (is_string($row->$column)) {
                            $value = $clean($row->$column);
                            if ($column === 'slug') {
                                $value = str_replace('social media', 'social-media', $value);
                            }
                            if ($value !== $row->$column) {
                                $changes[$column] = $value;
                            }
                        }
                    }
                    if ($changes) {
                        DB::table($table)->where($key, $row->$key)->update($changes);
                    }
                }
            }
        });

        SiteSetting::clearCache();
        SitemapBuilder::flush();
    }

    public function down(): void
    {
        // Editorial text changes cannot be reconstructed automatically.
    }
};
