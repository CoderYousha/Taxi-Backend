<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'carTypes';

    protected $fillable = [
        'name',
        'type',
        'timePrice',
        'KMPrice'
    ];

    protected $casts = [
        'timePrice' => 'decimal:2',
        'KMPrice'=>'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // نوع السيارة إما KM أو Time
    public const TYPE_KM = 'KM';
    public const TYPE_TIME = 'Time';

    public static function getTypes()
    {
        return [
            self::TYPE_KM => 'كيلومتر',
            self::TYPE_TIME => 'وقت',
        ];
    }

    // Scope للبحث
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'LIKE', "%{$search}%");
    }

    // Scope لفلترة حسب النوع
    public function scopeOfType($query, $type)
    {
        if ($type && in_array($type, [self::TYPE_KM, self::TYPE_TIME])) {
            return $query->where('type', $type);
        }
        return $query;
    }
}
