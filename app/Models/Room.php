<?php

namespace App\Models;

use Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;
    // protected $guarderd = [];
    protected $fillable = [
        'name',
        'room_no',
        'floor'
    ];
    public function students()
    {
        return $this->hasMany(Student::class);
    }
    public function enrollment()
    {
        return $this->hasOne(Enrollment::class);
    }
    public function attendance()
    {
        return $this->hasOne(Attendance::class);
    }
}