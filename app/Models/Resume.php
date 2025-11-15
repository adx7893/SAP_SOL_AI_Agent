<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    protected $fillable = [
        'original_filename',
        'stored_path',
        'mime_type',
        'text_content',
    ];

    public function logs()
    {
        return $this->hasMany(AgentLog::class);
    }
}
