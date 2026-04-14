<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlowchartEdge extends Model
{
    protected $fillable = [
        'flowchart_id',
        'edge_key',
        'from_node',
        'to_node',
        'condition',
    ];

    public function flowchart(): BelongsTo
    {
        return $this->belongsTo(Flowchart::class);
    }
}

