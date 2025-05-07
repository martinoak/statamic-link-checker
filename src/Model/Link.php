<?php

namespace Martinoak\StatamicLinkChecker\Model;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $table = 'link-checker_links';

    public $timestamps = true;

    protected $fillable = [
        'app-index',
        'code',
        'url',
        'source',
        'editor'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

}
