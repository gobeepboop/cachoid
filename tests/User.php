<?php

namespace Beep\Cachoid\Tests;

use Beep\Cachoid\Cacheable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class User
 *
 * @property int    $id
 * @property string $name
 */
class User extends Model
{
    use Cacheable;

    protected $fillable = ['name'];
}
