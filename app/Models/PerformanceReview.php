<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceReview extends Model
{
    use HasFactory;

    public $table = 'performance_reviews';

    protected $fillable = [
        'employee_id',
        'review_date',
        'kpi_score',
        'feedback',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
