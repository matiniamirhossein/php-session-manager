<?php

namespace Session\Cookie;


class Handler implements \SessionHandlerInterface
{

    public function open($savePath, $sessionName)
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id): string
    {
        if (isset($_COOKIE[$id]) === false)
        {
            return '';
        }

        return \Session::decrypt($_COOKIE[$id]);
    }

    public function write($id, $data): bool
    {
        $_ = session_get_cookie_params();
        return setcookie($id, \Session::encrypt($data), $_['lifetime'], $_['path'], $_['domain'], $_['secure'], $_['httponly']);
    }

    public function destroy($id): bool
    {
        #No need using set cookie already done that in Save class
        return true;
    }

    public function gc($max_life_time): bool
    {
        return true;
    }
}