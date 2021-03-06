<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSEO extends Model
{
    protected $table = 'pages_seo';

    protected $fillable = [
        'title',
        'description',
        'keywords',
        'image',
        'page_id',
    ];

    /**
     * @Relation
     */
    public function page()
    {
        return $this->belongsTo(Page::class, 'page_id');
    }
}
