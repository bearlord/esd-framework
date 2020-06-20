<?php


namespace ESD\Server\Co\Http\Factory;


use ESD\Core\DI\Factory;
use ESD\Server\Co\Http\SwooleResponse;

class ResponseFactory implements Factory
{

    public function create($params)
    {
        return new SwooleResponse();
    }
}