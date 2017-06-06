<?php

namespace Session;

class Segment
{
    private $segment = null;

    private $config = null;

    private $handler = null;

    public function __construct(string $name, \stdClass $config, $handler)
    {
        $this->segment = 'segment:' . $name;
        $this->config = $config;
        $this->handler = $handler;
    }

    public function __set(string $name, $value)
    {
        $this->config->segment = $this->segment;
        return $this->handler->{$name} = $value;
    }

    public function __get(string $name)
    {
        $this->config->segment = $this->segment;
        return $this->handler->{$name};
    }
}