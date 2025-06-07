<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClockOutCorrect extends Model
{
    use HasFactory;
    protected $table = 'clock_out_corrects';
    protected $fillable = ['attendance_correct_id', 'default_time', 'requested_time'];
}
