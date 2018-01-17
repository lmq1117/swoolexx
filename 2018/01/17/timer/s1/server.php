<?php
/**
 * Timer 简单示例1
 */
class MySQLPool
{
    private $server;
    private $pdo;

    public function __construct()
    {
        $this->server = new Swoole\Server("127.0.0.1",9051);
        $this->server->set([
            'worker_num'=>2,
            'task_worker_num'=>2,
            'daemonize'=>false,
            'max_request'=>10000,
            'dispatch_mode'=>2,
            'debug_mode'=>1
        ]);

        $this->server->on('WorkerStart',[$this,'onWorkerStart']);
        $this->server->on('Connect',[$this,'onConnect']);
        $this->server->on('Receive',[$this,'onReceive']);
        $this->server->on('Close',[$this,'onClose']);

        //bind callback
        $this->server->on('Task',[$this,'onTask']);
        $this->server->on('Finish',[$this,'onFinish']);
        $this->server->start();
    }

    /**
     * 有新的连接进入时，在worker进程中回调
     * @param $server           Swoole\Server对象
     * @param $fd               连接的文件描述符，发送数据/关闭连接时需要此参数
     * @param $reactorId        来自哪个Reactor线程
     */
    public function onConnect($server, $fd, $reactorId)
    {
        echo "Client 文件描述符是：=={$fd}== connect From Reactor Reactor线程ID是: =={$reactorId}==\n";
    }

    /**
     * TCP客户端连接关闭后，在worker进程中回调此函数
     * @param $serv
     * @param $fd
     * @param $from_id
     */
    public function onClose($serv, $fd, $from_id)
    {
        echo "Client 文件描述符是： =={$fd}== close connection\n";
    }

    //public function onStart($serv)
    //{
    //    echo "Start\n";
    //}

    /**
     * 此事件在Worker进程/Task进程启动时发生。
     * 这里创建的对象可以在进程生命周期内使用。
     * @param \Swoole\Server $server
     * @param $worker_id
     */
    public function onWorkerStart(Swoole\Server $server, $worker_id){
        if ($server->taskworker) { //task进程

        } else { //worker进程
            echo "WorkerProcess Start..\n";
            if ($worker_id == 0) {
                swoole_timer_tick(1000, function ($timer_id, $params) {
                    echo "Timer Tick Running..\n";
                    echo $timer_id."--recv--".$params."\n";
                }, 'Hello,Timer\n');
            }
        }
    }

    public function onReceive( Swoole\Server $server, $fd, $reactor_id, $data)
    {
        swoole_timer_after(2000, function () use ($server, $fd) {
            echo "Timer after Run One time\n";
            $server->send($fd,"Hello Later from After timer\n");
        });
    }


    /**
     * 在task_worker进程内被调用。
     * worker进程可以使用swoole_server_task函数向task_worker进程投递新的任务。
     * 当前的Task进程在调用onTask回调函数时会将进程状态切换为忙碌，
     * 这时将不再接收新的Task，当onTask函数返回时会将进程状态切换为空闲然后继续接收新的Task。
     * @param $server
     * @param $task_id
     * @param $src_worker_id
     * @param $data
     * @return bool
     */
    public function onTask($server, $task_id, $src_worker_id, $data)
    {
        //try{
        //    // 1 接受并解析task传递过来的任务数据
        //    $data = json_decode($data,true);
        //
        //    // 2 通过pdo连接创建一个statment,并传递预处理参数
        //    $statment = $this->pdo->prepare($data['sql']);
        //    $statment->execute($data['params']);
        //
        //    //将返回结果给客户端
        //    $server->send($data['fd'],"Insert成功，自增id是".$this->pdo->lastInsertId());
        //
        //    return true;
        //} catch (PDOException $e) {
        //    var_dump($e);
        //    return false;
        //}
    }

    /**
     * 当worker进程投递的任务在task_worker中完成时，
     * task进程会通过swoole_server->finish()方法将任务处理的结果发送给worker进程。
     * @param $server
     * @param $task_id
     * @param $data
     */
    public function onFinish($server, $task_id, $data)
    {
        var_dump("result: " . $data);

    }



}

new MySQLPool();