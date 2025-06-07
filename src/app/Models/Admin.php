<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;



// class Admin extends Model
// {
//     use HasFactory;
// }

class Admin extends Authenticatable
{
    use Notifiable;
    use HasFactory;

    // もし guarded / fillable など指定するならここに追加
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
