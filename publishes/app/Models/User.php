<?php

namespace App\Models;

use App\Casts\BooleanCast;
use App\Casts\StringCast;
use Hungnm28\LaravelModuleAdmin\Traits\HasPermissionsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    use HasPermissionsTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */


    protected $fillable = [
        'name',
        'email',
        'is_admin',
        'password',
    ];
    public static $listFields = ["name", "email", "is_admin", "password"];

    /**
     * Check super admin
     *
     * @return bool
     */
    public function isSuperAdmin()
    {
        $listSuper = env('APP_SUPER_ADMIN');
        $listSuper = explode(",", $listSuper);
        return in_array($this->id, $listSuper);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        "email" => StringCast::class
        , "name" => StringCast::class
        , "is_admin" => BooleanCast::class
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

}
