<?php

namespace Admin\Model;
use Think\Model;
class ManagerModel extends Model{
    protected $_auto = [
        ['create_time','time',self::MODEL_INSERT,'function']
        ];
}