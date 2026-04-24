<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'drivers';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'userId',
        'transTypeId',
        'image',
        'IDImage',
        'carNumber',
        'insurance',
        'mechanics',
        'type'
    ];

    protected $casts = [
        'userId' => 'integer',
        'transTypeId' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // العلاقات
    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function transType()
    {
        return $this->belongsTo(CarType::class, 'transTypeId');
    }
}
