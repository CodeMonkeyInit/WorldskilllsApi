<?php
/**
 * Created by PhpStorm.
 * User: deniskuliev
 * Date: 31/10/2018
 * Time: 12:51
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Advert extends Model
{
    protected $fillable = [
        'title', 'number', 'text', 'tags', 'image', 'datatime'
    ];

    public function comments(){
        return $this->hasMany("App\Comment");
    }
}

