<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 2017/9/8
 * Time: 10:18
 */
$server = new swoole_websocket_server("120.24.19.13",9886);

$server->on('open','onOpen');
$server->on('message','onMessage');
$server->on('close','onClose');


function onOpen(swoole_websocket_server $serv,$request){
    echo "[服务端]:握手成功，客户端id是:{$request->fd}\n";//$request->fd 是客户端id
}

/**
 * @param swoole_websocket_server $serv
 * @param $frame
 */
function onMessage(swoole_websocket_server $serv,$frame){
    //$frame->fd 是客户端id，$frame->data是客户端发送的数据
    //服务端向客户端发送数据是用 $server->push( '客户端id' ,  '内容')
    echo "收到来自客户端id是--{$frame->fd}--的消息:内容是--{$frame->data}--,opcode:--{$frame->opcode}--,fin:--{$frame->finish}--\n";

    $tulingapiurl = "http://www.tuling123.com/openapi/api";
    $tulingapiurl .= "?key=d0b2a59562384fbc9b0869b2ba16cdb2&info={$frame->data}&userid={$frame->fd}";
    file_put_contents('/tmp/tuling.log',date('Y-m-d H:i:s',time()).'----'.$tulingapiurl."\r\n");
    $res = curl_get($tulingapiurl);
    var_dump($res);


    //APIkey:d0b2a59562384fbc9b0869b2ba16cdb2
    //secret：d3a6ffb397c0bd8c



    $serv->push($frame->fd,'我是服务器，我收到你消息了，朕知道了，阅。');
}

function onClose($serv,$fd){
    echo "客户端id为 --{$fd}-- 闪人了！";
}

function curl_get($url)
{
    $ch = curl_init();

    //设置选项，包括URL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);

    $resData = curl_exec($ch);
    curl_close($ch);

    //print_r($resData);
    return $resData;
}

$server->start();