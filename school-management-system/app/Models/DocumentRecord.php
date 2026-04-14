<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRecord extends Model
{
    protected $fillable = [
        'path',
        'collection_name',
        'document_id',
        'parent_path',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}

