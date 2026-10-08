<?php

namespace App\Models\Sai;

use Illuminate\Database\Eloquent\Model;

class FireBaseToken extends Model
{
    /**
     * @var array
     */

     protected $table = 'firebase_tokens';
    protected $fillable = ['user_id', 'token', 'type' , 'created_at', 'updated_at'];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
