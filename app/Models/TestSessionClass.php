<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestSessionClass extends Model
{
    protected $fillable = [
        'test_session_id',
        'origin_class',
    ];

    public function testSession()
    {
        return $this->belongsTo(TestSession::class);
    }
}
