<?php
/**
 * Time使用实例s1
 */
class TimerS
{
    private $s;

    public function __construct()
    {
        $this->s = new Swoole\Server('127.0.0.1',9051);

        $this->s->set([
            'worker_num'=>2,
            'task_worker_num'=>2,
            'daemonize'=>false,
            'max_request'=>10000,
            'dispatch_mode'=>2,
            'debug_mode'=>2
        ]);

        $this->s->on('Connect',[$this,'onConnect']);
        $this->s->on('Close',[$this,'onClose']);
        $this->s->on('WorkerStart',[$this,'onWorkerStart']);
        $this->s->on('Receive',[$this,'onReceive']);
        $this->s->on('Task',[$this,'onTask']);
        $this->s->on('Finish',[$this,'onFinish']);
        $this->s->start();
    }

    /**
     * 在worker/taskwork进程启动时发生
     * 这里创建的对象可以再进程声明周期内使用
     * @param \Swoole\Server $s
     * @param $worker_id
     */
    public function onWorkerStart (Swoole\Server $s, $worker_id)
    {
        if($s->taskworker)
        {//taskwork进程

        } else { //work进程
            echo "Work进程启动【{$worker_id}】\n";
            if($worker_id == 0){
                swoole_timer_tick(1000,function ($timer_id,$params=''){
                    echo "swoole_time_tick定时器正在运行".date('Y-m-d H:i:s',time())."\n";
                    echo $timer_id."--recv--".$params."\n";
                },'Hello,Timer,this-is-param2s\n');
            }
        }
    }


    public function onReceive (Swoole\Server $s, $fd, $reactor_id, $data)
    {
        swoole_timer_after(2000,function () use ($s, $fd) {
            echo "一次性定时器swoole_timer_after延迟2秒执行中\n";
            $s->send($fd, "一次性定时器里面发出的消息~~~\n");
        });
    }

    public function onTask(Swoole\Server $s, $task_id, $src_worker_id, $data)
    {

    }

    public function onFinish(Swoole\Server $s, $task_id, $data)
    {

    }
































    /**
     * 有新的连接进来时，在work进程中回调
     * @param $s
     * @param $fd
     * @param $reacterId
     */
    public function onConnect (Swoole\Server $s,$fd,$reacterId)
    {
        echo "客户端[文件描述符：{$fd}]连接上了，Reactor线程ID是{$reacterId}\n";
    }


    /**
     * TCP连接关闭后，在work进程中回调
     * @param $s
     * @param $fd
     * @param $from_id
     */
    public function onClose (Swoole\Server $s,$fd,$reacterId)
    {
        echo "客户端[文件描述符：{$fd}]关闭了，Reactor线程ID是{$reacterId}\n";
    }

}

new TimerS();