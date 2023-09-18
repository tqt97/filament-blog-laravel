<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** @Entity */
class Article extends Model
{
    use HasFactory;

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_PREVIEW = 'preview';
    const STATUS_PRIVATE = 'private';

    protected $guarded = ['id'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /** @Column(type="string") */
    private $status;

    public function setStatus($status): void
    {
        if (!in_array($status, array(
            self::STATUS_DRAFT,
            self::STATUS_PUBLISHED,
            self::STATUS_PREVIEW,
            self::STATUS_PRIVATE))) {
            throw new \InvalidArgumentException("Invalid status");
        }
        $this->status = $status;
    }
}
