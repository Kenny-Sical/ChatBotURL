<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['chat_id', 'message', 'type', 'path', 'has_attachment'];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
}
