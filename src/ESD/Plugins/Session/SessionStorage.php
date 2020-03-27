<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/7
 * Time: 14:43
 */

namespace ESD\Plugins\Session;


interface SessionStorage
{
    public function get(string $id);
    public function set(string $id,string $data);
    public function remove(string $id);
}