<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'name', 'description', 'subject_id', 'type','deadline', 'estimated_hours', 'worked_hours', 'link', 'status'
    ];
}
