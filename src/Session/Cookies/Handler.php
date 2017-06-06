<?php

namespace Session\Cookies;


class Handler extends \SessionHandler
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function read($id)
    {

    }

    public function write($id, $data)
    {
        
    }
}