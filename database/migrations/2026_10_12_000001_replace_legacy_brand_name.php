<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            foreach (DB::table('site_settings')->whereIn('key', [
                'brand_name', 'meta_title', 'meta_description', 'about_text',
                'home_blog_title', 'home_blog_description', 'brand_expand',
            ])->get() as $row) {
                $value = $this->replaceBrand($row->value);
                if ($value !== $row->value) {
                    DB::table('site_settings')->where('key', $row->key)->update(['value' => $value]);
                }
            }

            foreach (['posts' => ['title', 'content'], 'faqs' => ['question', 'answer'], 'categories' => ['name']] as $table => $columns) {
                DB::table($table)->orderBy('id')->chunkById(100, function ($rows) use ($table, $columns) {
                    foreach ($rows as $row) {
                        $changes = [];
                        foreach ($columns as $column) {
                            $value = $this->replaceBrand($row->$column);
                            if ($value !== $row->$column) {
                                $changes[$column] = $value;
                            }
                        }
                        if ($changes) {
                            DB::table($table)->where('id', $row->id)->update($changes);
                        }
                    }
                });
            }
        });

        Cache::forget('site_settings_cache');
        \App\Support\SitemapBuilder::flush();
    }

    private function replaceBrand(?string $value): ?string
    {
        return $value === null ? null : preg_replace('/\bSM[\s_-]+Viewer\b/i', 'smviewly', $value);
    }

    public function down(): void
    {
        // Original editorial wording cannot be reconstructed from the new brand.
        // Restore the content backup if this data change needs to be reversed.
    }
};
