<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            if (blank($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
            if ($post->is_published && ! $post->published_at) {
                $post->published_at = now();
            }
        });
    }

    /** Visible on the storefront: published and not scheduled for the future. */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)->where('published_at', '<=', now());
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    public function getSummaryAttribute(): string
    {
        return $this->excerpt ?: Str::limit(trim(strip_tags($this->body)), 160);
    }
}
