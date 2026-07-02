<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Employee extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $guard = 'employee';

    protected $fillable = [
        'employee_id', 'name', 'email', 'phone', 'gender', 'dob',
        'department_id', 'designation_id', 'status', 'image',
        'password', 'must_change_password', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password'             => 'hashed',
        'must_change_password' => 'boolean',
        'last_login_at'        => 'datetime',
        'dob'                  => 'date',
    ];

    // Auto-generate employee_id like EMP001
    public static function generateEmployeeId(): string
    {
        $last = self::whereNotNull('employee_id')
                    ->orderByDesc('id')
                    ->value('employee_id');

        if (!$last) return 'EMP001';

        $num = (int) substr($last, 3);
        return 'EMP' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function salary()
    {
        return $this->hasOne(Salary::class)->latestOfMany('effective_from');
    }

    public function salaryHistories()
    {
        return $this->hasMany(SalaryHistory::class)->orderByDesc('effective_from');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class)->orderByDesc('created_at');
    }

    public function leaveBalance()
    {
        return $this->hasOne(LeaveBalance::class)
                    ->where('year', now()->year);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function todayAttendance()
    {
        return $this->hasOne(Attendance::class)
                    ->where('date', today()->toDateString());
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : asset('images/default-avatar.png');
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value !== null ? strtolower(trim($value)) : null,
        );
    }

    protected function employeeId(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value !== null ? strtoupper(trim($value)) : null,
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value !== null ? ucwords(strtolower($value)) : null,
            set: fn (?string $value) => $value !== null ? strtolower(trim($value)) : null,
        );
    }

    protected function phone(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value !== null ? trim($value) : null,
        );
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => ucfirst($this->status ?? ''),
        );
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return ['role' => 'employee'];
    }
}