<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClockInCorrect extends Model
{
    use HasFactory;
    protected $table = 'clock_in_corrects';
    protected $fillable = ['attendance_correct_id', 'default_time', 'requested_time'];
}
