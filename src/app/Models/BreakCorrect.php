<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakCorrect extends Model
{
    use HasFactory;
    protected $table = 'break_corrects';
    protected $fillable = ['attendance_correct_id', 'break_id','default_start','default_end','requested_start', 'requested_end'];
}
