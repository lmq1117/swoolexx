<?php
/**
 *
 */
class Test{
    public $Test_test = 0;
}
class Server
{
    private $serv;
    private $test;

    public function __construct()
    {
        $this->serv = new Swoole\Server("127.0.0.1",9051);
        $this->serv->set([
            'worker_num'=>4,
            'daemonize'=>false,
            'max_request'=>10000,
            'dispatch_mode'=>2,
            'task_worker_num'=>4,
        ]);

        $this->serv->on('start',[$this,'onStart']);
        $this->serv->on('onWorkerStart',[$this,'onWorkerStart']);
        $this->serv->on('Connect',[$this,'onConnect']);
        $this->serv->on('Receive',[$this,'onReceive']);
        $this->serv->on('Close',[$this,'onClose']);

        //bind callback
        $this->serv->on('Task',[$this,'onTask']);
        $this->serv->on('Finish',[$this,'onFinish']);
        $this->serv->start();
    }

    public function onStart($serv)
    {
        echo "Start\n";
    }

    public function onWorkerStart(Swoole\Server $server, $worker_id){

    }


    public function onConnect($serv, $fd, $reactorId)
    {
        echo "Client =={$fd}== connect From Reactor =={$reactorId}==\n";
    }


    public function onReceive( Swoole\Server $server, $fd, $reactor_id, $data)
    {
        echo "Get Message From Client =={$fd}==:=={$data}==\n";




    }


    public function onTask($serv, $task_id, $src_worker_id, $data)
    {
        echo "This Task =={$task_id}== from Worker =={$src_worker_id}==\n";
        $serv->send($fd,"hello,{$receive['req_words']}，你的客户端fd是{$receive['fd']}，通过描述符给客户端发送数据: ".date('Y-m-d H:i:s',time()));
        //return信息给work进程
        return 'Finished';
    }


    public function onFinish($serv, $task_id, $data)
    {
        echo "Task =={$task_id}== finish\n";
        echo "Result:=={$data}==\n";
        var_dump($this->test);

    }


    public function onClose($serv, $fd, $from_id)
    {
        echo "Client =={$fd}== close connection\n";
    }
}

$server = new Server();