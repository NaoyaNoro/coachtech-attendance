<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakAdd extends Model
{
    use HasFactory;
    protected $table = 'break_adds';
    protected $fillable = ['attendance_correct_id', 'break_id', 'add_start', 'add_end'];
}
