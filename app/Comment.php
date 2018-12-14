<?php
/**
 * Created by PhpStorm.
 * User: deniskuliev
 * Date: 08/11/2018
 * Time: 00:12
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $primaryKey = 'id';


    protected $hidden = [
      'post_id', 'created_at', 'created_at'
    ];

    protected $fillable = [
        'post_id', 'datatime', 'author', 'comment'
    ];
}