<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'requestHistories';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'requestId',
        'driverId',
        'finalCost',
        'descountId'
    ];

    protected $casts = [
        'finalCost' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // العلاقات
    public function request()
    {
        return $this->belongsTo(RequestModel::class, 'requestId');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driverId');
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class, 'descountId');
    }
    // أضف هذه العلاقة
    public function usedDiscount()
    {
        return $this->belongsTo(UsedDiscount::class, 'descountId');
    }
}
