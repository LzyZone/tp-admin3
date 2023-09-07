<?php
namespace Common\Model;

use Think\Model;

abstract class AdminBaseModel extends Model {
    protected $connection = 'DB_ADMIN';
}
