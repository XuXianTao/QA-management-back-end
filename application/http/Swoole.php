<?php

namespace app\http;

use think\swoole\Server;

class Swoole extends Server
{
    protected $host = '0.0.0.0';
    protected $port = 9508;
    protected $serverType = 'socket';
    protected $mode = SWOOLE_PROCESS;
    protected $sockType = SWOOLE_SOCK_TCP;
    protected $option = [
        'worker_num' => 4,
        'daemonize' => true,
        'backlog' => 128,
        'task_worker_num' => 1,
        'task_ipc_mode' => 3,
        'message_queue_key' => 0x70001001,
        'task_tmpdir' =>  __DIR__ . '/../../runtime/swoole/task/',
    ];

    protected function init()
    {

    }

    public function onOpen($server, $request)
    {
        echo "server: handshake success with fd{$request->fd}\n";
    }

    public function onReceive($server, $fd, $from_id, $data)
    {
        $server->send($fd, 'Swoole: ' . $data);
    }

    public function onMessage($server, $frame)
    {
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
        $mes_type = gettype($frame->data);
        $handle_fun = 'handle' . ucfirst($mes_type);

        if (method_exists($this, $handle_fun)) self::$handle_fun($server, $frame);
        else {
            $server->push($frame->fd, json_encode(['id' => $frame->fd, 'ori_mes' => $frame->data, 'mes' => 'this is server']));
        }
    }

    //
    public function onTask($serv, $task_id, $from_id, $data)
    {
        switch($data['type']) {
            case 'progress': {
                $serv->push((integer)$data['id'], json_encode($data));
                break;
            }
            default: {
                break;
            }
        }
        return $data;
    }

    public function onClose($ser, $fd)
    {
        echo "client {$fd} closed\n";
    }

    protected function handleString($server, $frame)
    {
        switch ($frame->data) {
            case 'getID': {
                $server->push($frame->fd, json_encode(['id' => $frame->fd]));
                break;
            }
        }
    }
}