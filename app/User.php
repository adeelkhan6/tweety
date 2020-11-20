<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, Followable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [
    //     'username', 'avatar', 'name', 'email', 'password',
    // ];

    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getAvatarAttribute($value)
    {
        // return "https://i.pravatar.cc/200?u=" . $this->email;

        return asset($value ?: 'images/default-avatar.png');
    }
    
    // $user->password = 'foobar';   it will first be pipe through this method 
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function timeline()
    {
        // find auth user tweets
        // return Tweet::where('user_id', $this->id)
        //     ->latest()
        //     ->get();

        // find auth user tweets and also the tweets of everyone he follows 
        // $ids = $this->follows()->pluck('id');
        // $ids->push($this->id);

        // return Tweet::whereIn('user_id', $ids)->latest()->get();

        // find auth user tweets and also the tweets of everyone he follows
        $friends = $this->follows()->pluck('id');

        return Tweet::whereIn('user_id', $friends)
            ->orwhere('user_id', $this->id)
            ->withLikes()
            ->latest()
            ->paginate(50);
            // ->get();
    }

    public function tweets()
    {
        return $this->hasMany(Tweet::class)->latest();
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function path($append = '')
    {
        $path = route('profile', $this->username);

        return $append ? "{$path}/{$append}" : $path;
    }

    // public function getRouteKeyName()
    // {
    //     return 'name';
    // }
}
