<?php
class Client
{
    private $c;

    public function __construct()
    {
        $this->c = new Swoole\Client(SWOOLE_SOCK_TCP);
    }

    public function connect ()
    {
        if(!$this->c->connect("127.0.0.1",9051,1)){
            echo "错误：{$this->c->errMsg}[{$this->c->errCode}]\n";
        }

        fwrite(STDOUT,"请输入消息：");
        $msg = trim(fgets(STDIN));
        $this->c->send($msg);
        sleep(2);
        $message = $this->c->recv();
        echo "服务器返回消息：{$message}\n";
    }
}

$c = new Client();
$c->connect();