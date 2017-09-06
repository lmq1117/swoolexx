<?php
/**
 *
 */
class Server
{
    private $serv;

    public function __construct()
    {
        $this->serv = new swoole_server("127.0.0.1",9088);
        $this->serv->set([
            'worker_num'=>8,
            'daemonize'=>false,
            'max_request'=>10000,
            'dispatch_mode'=>2,
            'task_worker_num'=>8,
        ]);

        $this->serv->on('start',[$this,'onStart']);
        $this->serv->on('connect',[$this,'onConnect']);
        $this->serv->on('Receive',[$this,'onReceive']);
        $this->serv->on('Close',[$this,'onClose']);

        //bind callback
        $this->serv->on('Task',[$this,'onTask']);
        $this->serv->on('Finish',[$this,'onFinish']);
    }

    public function onStart($serv)
    {
        echo "Start\n";
    }

    public function onConnect($serv, $fd, $from_id)
    {
        echo "Client {$fd} connect\n";
    }

    public function onClose($serv, $fd, $from_id)
    {
        echo "Client {$fd} close connection\n";
    }

    /**
     *
     * @param swoole_server $serv
     * @param $fd
     * @param $from_id
     * @param $data
     */
    public function onReceive( swoole_server $serv, $fd, $from_id, $data)
    {
        echo "Get Message From Client {$fd}:{$data}\n";

        // 1 存放传给Task的数据
        $data = [
            'task'=>'task_1',
            'params'=>$data,
            'fd'=>$fd,
        ];
        // 2 work进程中，通过task方法，把数据传出去，只能传字符串，通知到taskwork进程
        //投递一个异步任务到task_worker池中。此函数是非阻塞的，执行完毕会立即返回。
        //Worker进程可以继续处理新的请求。
        //使用Task功能，必须先设置 task_worker_num，并且必须设置Server的onTask和onFinish事件回调函数。
        $serv->task(json_encode($data));
    }

    // task
    public function onTask($serv, $task_id, $from_id, $data)
    {
        echo "This Task {$task_id} from Worker {$from_id}\n";
        echo "Data:{$data}\n";
        $data = json_decode($data,true);
        echo "Receive Task:{$data['task']}\n";
        var_dump($data['params']);
        //给客户端发数据
        $serv->send($data['fd'],"Hello Task");
        //return信息给work进程
        return 'Finished';
    }

    public function onFinish($serv, $task_id, $data)
    {
        echo "Task {$task_id} finish\n";
        echo "Result:{$data}\n";
    }
}

$server = new Server();