<?php

namespace Session\Sql;
use PDO, Session;


class Handler implements \SessionHandlerInterface
{
    private $config = [];

    private $conn = null;

    private $table = null;

    public function __construct(array $config)
    {
        if ( ! isset($config['sql']))
        {
            throw new \RuntimeException('No sql configuration found in config file.');
        }

        $this->config = $config['sql'];
        $this->table = $config['table'] ?? 'session';
    }

    public function open($savePath, $sessionName)
    {
        $dsn = $this->config['driver'] . ':host=' . $this->config['host'] . ';dbname=' .$this->config['db_name'];
        $user = $this->config['db_user'];
        $pass = $this->config['db_pass'];

        $this->conn = new PDO($dsn, $user, $pass, [
            PDO::ATTR_PERSISTENT => $this->config['persistent_conn'],
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        try
        {
            $this->conn->query('SELECT 1 FROM ' . $this->table . ' LIMIT 1');
        }
        catch (\PDOException  $e)
        {
            $this->conn->query('CREATE TABLE `' . $this->table . '` (
              `id` varchar(500) NOT NULL,
              `data` text NOT NULL,
              `time` int(11) unsigned NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
        }


        return true;
    }

    public function close(): bool
    {
        if ( ! $this->config['persistent_conn'])
        {
            $this->conn = null;
        }
        return true;
    }

    public function read($id): string
    {
        $data = '';
        $statement = $this->conn->prepare('SELECT data FROM ' . $this->table . ' WHERE id = :id');
        if ( $statement->bindParam(':id', $id, PDO::PARAM_STR))
        {
            $result = $statement->fetch();
            $data = $result['data'] ?? '';
        }
        #close
        $statement = null;
        return Session::decrypt($data);
    }

    public function write($id, $data): bool
    {
        $statement = $this->conn->prepare('REPLACE INTO ' . $this->table . ' (id, data, time) VALUES (:id, :data, :time)');
        $statement->bindParam(':id', $id, PDO::PARAM_STR);
        $statement->bindValue(':data', Session::encrypt($data), PDO::PARAM_STR);
        $statement->bindValue(':time', time(), PDO::PARAM_INT);
        $completed = $statement->execute();
        #close
        $statement = null;
        return $completed;
    }

    public function destroy($id): bool
    {
        $statement = $this->conn->prepare('DELETE FROM ' . $this->table . ' WHERE id = :id');
        $statement->bindParam(':id', $id, PDO::PARAM_STR);
        $completed = $statement->execute();
        #close
        $statement = null;
        return $completed;
    }

    public function gc($max_life_time): bool
    {
        $max_life_time = time() - $max_life_time;
        $statement = $this->conn->prepare('DELETE FROM ' . $this->table . ' WHERE time < :time');
        $statement->bindParam(':time', $max_life_time, PDO::PARAM_INT);
        $completed = $statement->execute();
        #close
        $statement = null;
        return $completed;
    }
}