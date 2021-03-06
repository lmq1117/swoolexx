<?php
/**
 * Timer 简单示例1
 * Created by PhpStorm.
 * User: kevin
 * Date: 2017/9/6
 * Time: 17:15
 */
class Client
{
    private $client;

    public function __construct()
    {
        $this->client = new Swoole\Client(SWOOLE_SOCK_TCP);
    }

    public function connect()
    {
        if (!$this->client->connect("127.0.0.1",9051,1))
        {
            echo "Error:{$this->client->errMsg}[{$this->client->errCode}]\n";
        }

        fwrite(STDOUT,"请输入消息：");
        $msg = trim(fgets(STDIN));
        $this->client->send($msg);
        sleep(2);
        $message = $this->client->recv();
        echo "Get Message From Server:{$message}\n";
    }

    public function test(){
        $this->client = new Swoole\Server(SWOOLE_SOCK_TCP);
    }
}

$client = new Client();
$client->connect();