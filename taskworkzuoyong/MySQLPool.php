<?php
class MySQLPool
{
    private $serv;
    private $pdo;

    public function __construct()
    {
        $this->serv = new swoole_server("0.0.0.0",9501);

        $this->serv->set([
            'worker_num'=>8,
            'daemonize'=>false,
            'max_request'=>10000,
            'dispatch_mode'=>3,
            'debug_mode'=>1,
            'task_worker_num'=>8,
        ]);

        $this->serv->on('WorkerStart',[$this,'onWorkerStart']);
        $this->serv->on('Connect',[$this,'onConnect']);
        $this->serv->on('Receive',[$this,'onReceive']);
        $this->serv->on('Close',[$this,'onClose']);

        //bind callback
        $this->serv->on('Task',[$this,'onTask']);
        $this->serv->on('Finish',[$this,'onFinish']);

        $this->serv->start();
    }

    public function onConnect( $serv, $fd, $from_id )
    {
        echo "Client {$fd} connect\n";
    }

    public function onClose( $serv, $fd, $from_id )
    {
        echo "Client {$fd} close connection\n";
    }

    public function onWorkerStart()
    {
        echo "onWorkerStart\n";
        $this->pdo = new PDO(
            "mysql:localhost;port=3306;dbname=Test",
            'root',
            '123456',
            [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_PERSISTENT => true,
            ]
        );
    }

    public function onReceive(swoole_server $serv, $fd, $from_id, $data )
    {
        $task = [
            'sql' => "insert into test (name,age) values (?,?)",
            'params' => ['swoole'.mt_rand(111,999),mt_rand(1,99)],
            'fd' => $fd
        ];

        $serv->task(json_encode($task));
    }

    public function onTask( $serv, $task_id, $from_id, $data )
    {
        try {
            $data = json_decode($data, true);
            $statment = $this->pdo->prepare($data['sql']);
            $statment->execute($data['params']);
            $serv->send($data['fd'],'Insert success. ');
            return true;
        } catch ( PDOException $e ) {
            var_dump($e);
            return false;
        }
    }

    public function onFinish( $serv, $task_id, $data)
    {
        var_dump("result: " . $data);
    }
}
new MySQLPool();