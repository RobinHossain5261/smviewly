<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public function getSeoDescriptionAttribute(): string
    {
        $text = html_entity_decode(preg_replace('/<[^>]+>/', ' ', $this->content ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        return \Illuminate\Support\Str::limit($text ?: $this->title, 155);
    }

    protected $fillable = [
        'title',
        'slug',
        'content',
        'image_path',
        'category_id',
        'status',
    ];

    /**
     * Get the category that owns the post.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image_path) {
            return asset('images/default-blog.jpg');
        }
        
        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        if (str_starts_with($this->image_path, 'images/')) {
            return asset($this->image_path);
        }

        return asset('storage/' . $this->image_path);
    }
}
