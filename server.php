<?php
/**
 *
 */
class Test{
    public $index = 0;
}
class Server
{
    private $serv;
    private $test;

    public function __construct()
    {
        $this->serv = new swoole_server("127.0.0.1",9051);
        $this->serv->set([
            'worker_num'=>8,
            'daemonize'=>false,
            'max_request'=>10000,
            'dispatch_mode'=>2,
            'task_worker_num'=>8,
        ]);

        $this->serv->on('start',[$this,'onStart']);
        $this->serv->on('Connect',[$this,'onConnect']);
        $this->serv->on('Receive',[$this,'onReceive']);
        $this->serv->on('Close',[$this,'onClose']);

        //bind callback
        $this->serv->on('Task',[$this,'onTask']);
        $this->serv->on('Finish',[$this,'onFinish']);
        $this->serv->start();
    }

    /*
        Server启动在主进程的主线程回调此函数，函数原型
            function onStart(swoole_server $server);
        在此事件之前Swoole Server已进行了如下操作
            已创建了manager进程
            已创建了worker子进程
            已监听所有TCP/UDP端口
            已监听了定时器
        接下来要执行
            主Reactor开始接收事件，客户端可以connect到Server
    */
    public function onStart($serv)
    {
        echo "Start\n";
    }

    //有新的连接进入时，在worker进程中回调。函数原型
    public function onConnect($serv, $fd, $from_id)
    {
        echo "Client =={$fd}== connect From Reactor =={$from_id}==\n";
    }

    public function onClose($serv, $fd, $from_id)
    {
        echo "Client =={$fd}== close connection\n";
        //echo "==========链接分割线============\n";
    }

    /**
     * 接收到数据时回调此函数，发生在worker进程中。函数原型：
     * @param swoole_server $serv
     * @param $fd
     * @param $from_id
     * @param $data
     */
    public function onReceive( swoole_server $serv, $fd, $from_id, $data)
    {
        echo "Get Message From Client =={$fd}==:=={$data}==\n";
        $this->test = new Test();
        var_dump($this->test);
        $serv->task(serialize($this->test));

        // 1 声明一个变量，存放传给Task的数据
        //$data2 = [
        //    //task任务名
        //    'task'=>'task_1',
        //    //收到的来自客户端的数据
        //    'params'=>$data,
        //    //客户端描述符
        //    'fd'=>$fd,
        //];
        // 2 work进程中，通过task方法，把数据传给taskwork进程；只能传字符串，通知到taskwork进程
        //投递一个异步任务到task_worker池中。此函数是非阻塞的，执行完毕会立即返回。
        //Worker进程可以继续处理新的请求。
        //使用Task功能，必须先设置 task_worker_num，并且必须设置Server的onTask和onFinish事件回调函数。
        //$serv->task(json_encode($data2));


        //$serv->task(serialize($this->test) . '-|-' .$fd);
    }

    /**
     * 在task_worker进程内被调用。
     * worker进程可以使用swoole_server_task函数向task_worker进程投递新的任务。
     * 当前的Task进程在调用onTask回调函数时会将进程状态切换为忙碌，这时将不再接收新的Task，
     * 当onTask函数返回时会将进程状态切换为空闲然后继续接收新的Task。
     * @param $serv
     * @param $task_id
     * @param $from_id
     * @param $data
     * @return string
     */
    public function onTask($serv, $task_id, $from_id, $data)
    {
        echo "This Task =={$task_id}== from Worker =={$from_id}==\n";
        //echo "Data:{$data}\n";
        //$data = json_decode($data,true);
        //$arr = explode('-|-',$data);
        //$fd = $arr[1];
        //$data = unserialize($arr[0]);
        //$data->index = 2;
        //var_dump($data);
        //var_dump($this->test);

        //echo "taskwork进程收到任务Receive Task:=={$data['task']}==\n";

        $data = unserialize($data);
        $data->index = 2;
        var_dump($data);
        var_dump($this->index);
        //echo 'line'.__LINE__."\n";
        //var_dump($data['params']);

        //给客户端发数据
        //$serv->send($data['fd'],"通过描述符给客户端发送数据 Hello Task ".date('Y-m-d H:i:s',time()));
        //$serv->send($fd,"通过描述符给客户端发送数据 Hello Task".date('Y-m-d H:i:s',time()));
        //return信息给work进程
        return 'Finished';
    }

    /**
     * 当worker进程投递的任务在task_worker中完成时，task进程会通过swoole_server->finish()方法将任务处理的结果发送给worker进程。
     * @param $serv
     * @param $task_id
     * @param $data
     */
    public function onFinish($serv, $task_id, $data)
    {
        echo "Task =={$task_id}== finish\n";
        echo "Result:=={$data}==\n";
        var_dump($this->test);
    }
}

$server = new Server();