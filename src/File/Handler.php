<?php

declare(strict_types=1);

namespace PHPSessionManager\File;

use PHPSessionManager\SetGet;
use SessionHandlerInterface;

class Handler extends SetGet implements SessionHandlerInterface
{
    private $savePath;

    public function __construct(array $config)
    {
        parent::__construct($config['encrypt_data'], $config['salt_key']);
    }

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
        $data = '';
        if(is_file("{$this->savePath}/sess_{$id}")){
            $data = (string) file_get_contents("{$this->savePath}/sess_{$id}");
        }
        return ($data == '') ? '' : $this->get($data);
    }

    public function write($id, $data): bool
    {
        return file_put_contents( "{$this->savePath}/sess_{$id}", $this->set($data)) !== false;
    }

    public function destroy($id): bool
    {
        $file = "{$this->savePath}/sess_{$id}";
        if (file_exists($file))
        {
            unlink($file);
        }

        return true;
    }

    public function gc($max_life_time): bool
    {
        $time = time();
        foreach (glob("{$this->savePath}/sess_*") as $file)
        {
            if (is_file($file) && filemtime($file) + $max_life_time < $time)
            {
                unlink($file);
            }
        }

        return true;
    }
}
