<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;
    protected $table = 'patients';
//    public $timestamps = false;  // Disable timestamps

    protected $fillable = [
        'user_id',
        'gender',
        'birth_at',
        'weight',
        'diagnosis',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function stats(){
        $this->hasMany(PatientStatistic::class);
    }



}
