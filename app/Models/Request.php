<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'requests';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'userId',
        'carTypeId',
        'type',
        'status',
        'startLocationId',
        'destLocationId',
        'requestDate',
        'locationDesc',
        'predectedCost'
    ];

    protected $casts = [
        'requestDate' => 'datetime',
        'predectedCost' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ثوابت أنواع الطلب
    const TYPE_SCHEDULE = 'Schedual';
    const TYPE_IMMEDIATE = 'Immediate';

    // ثوابت حالات الطلب
    const STATUS_PENDING = 'Pending';
    const STATUS_RUNNING = 'Running';
    const STATUS_FINISHED = 'Finished';
    const STATUS_REMOVED = 'Removed';
    const STATUS_RESERVED = 'Reserved'; // حالة إضافية للحجز المسبق

    // العلاقات
    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function carType()
    {
        return $this->belongsTo(CarType::class, 'carTypeId');
    }

    public function startLocation()
    {
        return $this->belongsTo(Location::class, 'startLocationId');
    }

    public function destLocation()
    {
        return $this->belongsTo(Location::class, 'destLocationId');
    }

    public function history()
    {
        return $this->hasOne(RequestHistory::class, 'requestId');
    }

    // Scope للحجوزات المسبقة
    public function scopeScheduled($query)
    {
        return $query->where('type', self::TYPE_SCHEDULE);
    }

    // Scope للطلبات الفورية
    public function scopeImmediate($query)
    {
        return $query->where('type', self::TYPE_IMMEDIATE);
    }

    // Scope للطلبات المنتظرة
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}
