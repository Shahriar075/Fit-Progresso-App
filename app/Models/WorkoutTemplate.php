<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'created_by'];

    public function exercises()
    {
        return $this->belongsToMany(Exercise::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
