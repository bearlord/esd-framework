<?php


namespace ESD\Server\Co\Http\Factory;


use ESD\Core\DI\Factory;
use ESD\Server\Co\Http\SwooleRequest;

class RequestFactory implements Factory
{

    public function create($params)
    {
        return new SwooleRequest();
    }
}