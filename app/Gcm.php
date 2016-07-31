<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Gcm
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $registeredId
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Gcm whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Gcm whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Gcm whereRegisteredId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Gcm whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Gcm whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Gcm extends Model
{
    protected $fillable = ['user_id', 'registeredId'];
    protected $hidden = ['id', 'user_id'];
}
