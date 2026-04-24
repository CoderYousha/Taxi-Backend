<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;  // مهم للتوكنات
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'number',        // رقم الهاتف (فريد)
        'firstName',     // الاسم الأول
        'lastName',      // الاسم الأخير
        'password',      // كلمة المرور
        'roll',          // الدور: Admin, Driver, Customer
        'banned',        // حالة الحظر
        'expireDate',    // تاريخ انتهاء الصلاحية
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'banned' => 'boolean',
            'expireDate' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->roll === 'Admin';
    }

    /**
     * Check if user is driver.
     */
    public function isDriver(): bool
    {
        return $this->roll == 'Driver';
    }

    /**
     * Check if user is customer.
     */
    public function isCustomer(): bool
    {
        return $this->roll == 'Customer';
    }

    /**
     * Check if user is banned.
     */
    public function isBanned(): bool
    {
        return $this->banned == true;
    }

    /**
     * Get the driver record associated with the user.
     */
    public function driver()
    {
        return $this->hasOne(Driver::class, 'userId');
    }

    /**
     * Get the requests for the user.
     */
    public function requests()
    {
        return $this->hasMany(RequestModel::class, 'userId');
    }

    /**
     * Get the rates for the user.
     */
    public function rates()
    {
        return $this->hasMany(Rate::class, 'userId');
    }

    /**
     * Get the complaints for the user.
     */
    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'userId');
    }

    /**
     * Get the used discounts for the user.
     */
    public function usedDiscounts()
    {
        return $this->hasMany(UsedDiscount::class, 'userId');
    }
}
