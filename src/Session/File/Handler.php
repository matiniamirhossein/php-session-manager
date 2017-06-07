<?php

namespace Session\File;
use Session;


class Handler implements \SessionHandlerInterface
{

    private $savePath;

    public function open($savePath, $sessionName): bool
    {
        $this->savePath = $savePath;
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id): string
    {
        $data = (string) @file_get_contents($this->savePath . '/sess_' . $id);
        return Session::encrypt($data);
    }

    public function write($id, $data): bool
    {
        return (file_put_contents($this->savePath . '/sess_' . $id, Session::encrypt($data)) !== false);
    }

    public function destroy($id): bool
    {
        $file = $this->savePath . '/sess_' . $id;
        if (file_exists($file))
        {
            unlink($file);
        }

        return true;
    }

    public function gc($max_life_time): bool
    {
        $time = time();
        foreach (glob($this->savePath . '/sess_*') as $file)
        {
            if (filemtime($file) + $max_life_time < $time && file_exists($file))
            {
                unlink($file);
            }
        }

        return true;
    }
}