<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestimonialTranslation extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    public function testimonial()
    {
        return $this->belongsTo(Testimonial::class);
    }
}
