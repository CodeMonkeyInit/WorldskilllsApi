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
    protected $primaryKey = 'comment_id';


    protected $hidden = [
      'post_id'
    ];

    protected $fillable = [
        'post_id', 'datatime', 'author', 'comment'
    ];
}