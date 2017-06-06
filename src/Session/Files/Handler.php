<?php

namespace Session\Files;


class Handler extends \SessionHandler
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function read($id)
    {
        $data = parent::read($id);
        if ($this->config['encrypt_data'] === true)
        {
            $data = ( ! $data) ? '' :  \Session::decrypt($data, $this->config['salt']);
        }
        return $data;
    }

    public function write($id, $data)
    {
        if ($this->config['encrypt_data'] === true)
        {
            $data = \Session::encrypt($data, $this->config['salt']);
        }
        return parent::write($id, $data);
    }
}