<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlowchartNode extends Model
{
    protected $fillable = [
        'flowchart_id',
        'node_key',
        'type',
        'text',
        'code',
        'position_x',
        'position_y',
    ];

    protected function casts(): array
    {
        return [
            'position_x' => 'float',
            'position_y' => 'float',
        ];
    }

    public function flowchart(): BelongsTo
    {
        return $this->belongsTo(Flowchart::class);
    }
}

