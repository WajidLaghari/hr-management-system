<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'department',
        'position',
        'date_of_joining'
    ];

    public function performanceReview()
    {
        return $this->hasMany(PerformanceReview::class);
    }
}
