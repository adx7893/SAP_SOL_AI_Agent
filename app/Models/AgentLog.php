<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentLog extends Model
{
    protected $fillable = [
        'resume_id',
        'step',
        'tool',
        'input',
        'output',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }
}
