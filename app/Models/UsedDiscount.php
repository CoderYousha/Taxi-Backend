<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsedDiscount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'usedDiscounts';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'requestId',
        'userId',
        'discountId'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // العلاقات
    public function request()
    {
        return $this->belongsTo(RequestModel::class, 'requestId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class, 'discountId');
    }

    // التحقق مما إذا كان المستخدم قد استخدم هذا الكود من قبل
    public static function isUsedByUser($userId, $discountId)
    {
        return self::where('userId', $userId)
            ->where('discountId', $discountId)
            ->exists();
    }

    // عدد مرات استخدام كود خصم معين
    public static function usageCount($discountId)
    {
        return self::where('discountId', $discountId)->count();
    }
}
