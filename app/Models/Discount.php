<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'discounts';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'code',
        'amount',
        'type'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // الثوابت لأنواع الخصم
    const TYPE_PERCENTAGE = 'Percentage';
    const TYPE_FIXED = 'Fixed';

    // دالة مساعدة للحصول على أنواع الخصم
    public static function getTypes()
    {
        return [
            self::TYPE_PERCENTAGE => 'نسبة مئوية',
            self::TYPE_FIXED => 'قيمة ثابتة',
        ];
    }

    // حساب قيمة الخصم بناءً على السعر الأصلي
    public function calculateDiscount($originalPrice)
    {
        if ($this->type === self::TYPE_PERCENTAGE) {
            return ($originalPrice * $this->amount) / 100;
        }

        return $this->amount; // Fixed
    }
    // أضف هذه العلاقات
public function usedDiscounts()
{
    return $this->hasMany(UsedDiscount::class, 'discountId');
}

public function isUsedByUser($userId)
{
    return $this->usedDiscounts()->where('userId', $userId)->exists();
}

public function getUsageCountAttribute()
{
    return $this->usedDiscounts()->count();
}
}
