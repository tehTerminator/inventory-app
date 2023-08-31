<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Support\Facades\Hash;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'username',
        'mobile',
        'password',
        'auth_token',
        'role_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function generateToken()
    {
        $this->auth_token = bin2hex(random_bytes(60));
        $this->save();

        return $this->auth_token;
    }

    /**
     * Checks if User is Admin or Not
     * 
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->role_id === 1;
    }

    /**
     * Authenticates Users
     * 
     * @return User Object if Authentication is success
     */
    public function login(string $password) {

        if (!Hash::check($password, $this->password)) {
            return response()->json(['message' => 'Invalid Password'], 401);
        }

        $this->generateToken();
        return $this->fresh();

    }
}
