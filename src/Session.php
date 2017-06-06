<?php

class Session
{
    private static $initialized = [];

    private static $started = false;

    private static $class = null;

    private static function init()
    {
        $DS = DIRECTORY_SEPARATOR;
        $path = __DIR__ . $DS . 'Session' . $DS;
        $config = include($path . 'config.php');

        self::$initialized = $config[$config['driver']];
        self::$class = ucfirst($config['driver']);

        if( ! is_dir($path . self::$class))
        {
            throw new RuntimeException('No driver found for ' . self::$class);
        }
        elseif (self::$initialized['encrypt_data'] === true && ! extension_loaded('openssl'))
        {
            trigger_error("You don't have openssl enabled. So session data wont be encrypted.", E_USER_NOTICE);
        }

        $save_path = self::$initialized['save_path'];
        if (trim($save_path) !== '')
        {
            if (is_dir($save_path))
            {
                session_save_path($save_path);
                # For GC in Debian
                ini_set('session.gc_probability', '1');
            }
            else
            {
                throw new RuntimeException(sprintf('Path %s does not exist', $save_path));
            }
        }

        $class = '\Session\\' . self::$class . '\Handler';
        ini_set('session.save_handler', strtolower($config['driver']));
        session_set_save_handler(new $class(self::$initialized), true);

    }

    public static function name(string $name)
    {
        if (empty(self::$initialized))
        {
            self::init();
        }

        if (self::$started)
        {
            throw new \RuntimeException('Session is active. The session name must be set before Session::start().');
        }
        elseif (preg_match('/^[a-zA-Z]([\w]*)$/', $name) < 1)
        {
            throw new \InvalidArgumentException('Invalid Session name. (allows [\w] and can\'t consist of numbers only. must have a letter)');
        }
        else
        {
            self::$initialized['name'] = $name;
        }
    }

    public static function id(string $id)
    {
        if (self::$started)
        {
            throw new \RuntimeException('Session is active. The session id must be set before Session::start().');
        }
        elseif (headers_sent($filename, $line_num))
        {
            throw new \RuntimeException(sprintf('ID must be set before any output is sent to the browser (file: %s, line: %s)', $filename, $line_num));
        }
        elseif (preg_match('/^[\w-,]{1,128}$/', $id) < 1)
        {
            throw new \InvalidArgumentException('Invalid Session ID provide');
        }
        else
        {
            session_id($id);
        }
    }

    public static function start(string $namespace = '__GLOBAL')
    {
        if (empty(self::$initialized))
        {
            self::init();
        }

        self::$started = true;
        self::$initialized['namespace'] = $namespace;
        return new \Session\Save((object) self::$initialized);
    }

    public static function reset(): self
    {
        self::$initialized = [];
        return new self;
    }

    /**
     * decrypt AES 256
     *
     * @param string $edata
     * @param string $password
     * @return string data
     */
    public static function decrypt(string $edata, string $password): string
    {
        $data = base64_decode($edata);
        $salt = substr($data, 0, 16);
        $ct = substr($data, 16);

        $rounds = 3; // depends on key length
        $data00 = $password.$salt;
        $hash = array();
        $hash[0] = hash('sha256', $data00, true);
        $result = $hash[0];
        for ($i = 1; $i < $rounds; $i++)
        {
            $hash[$i] = hash('sha256', $hash[$i - 1].$data00, true);
            $result .= $hash[$i];
        }
        $key = substr($result, 0, 32);
        $iv  = substr($result, 32,16);
        $decrypted = openssl_decrypt($ct, 'AES-256-CBC', $key, true, $iv);

        return ( ! $decrypted) ? '' : $decrypted;
    }

    /**
     * crypt AES 256
     *
     * @param string $data
     * @param string $password
     * @return string encrypted data
     */
    public static function encrypt(string $data, string $password): string
    {
        // Set a random salt
        $salt = openssl_random_pseudo_bytes(16);
        $salted = '';
        $dx = '';
        // Salt the key(32) and iv(16) = 48
        while (strlen($salted) < 48)
        {
            $dx = hash('sha256', $dx.$password.$salt, true);
            $salted .= $dx;
        }

        $key = substr($salted, 0, 32);
        $iv  = substr($salted, 32,16);

        $encrypted_data = openssl_encrypt($data, 'AES-256-CBC', $key, true, $iv);
        return base64_encode($salt . $encrypted_data);
    }
}