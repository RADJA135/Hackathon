<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrustCheck extends Model
{
    protected $fillable = [
        'phone_number', 'user_id',
        'sim_swapped', 'sim_swap_last_changed',
        'device_known', 'device_id',
        'location_consistent', 'location_country', 'location_city',
        'trust_score', 'decision', 'agent_reasoning',
        'device_label',
    ];

    protected $casts = [
        'sim_swapped' => 'boolean',
        'device_known' => 'boolean',
        'location_consistent' => 'boolean',
        'trust_score' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
