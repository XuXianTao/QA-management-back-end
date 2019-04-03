<?php
/**
 * Created by PhpStorm.
 * User: xxt
 * Date: 19-1-24
 * Time: 上午1:03
 */

namespace app\http;

class SwooleTask
{
    protected $queueId;
    protected $workerId;
    protected $taskId = 0;

    function __construct($key, $workerId = 0)
    {
        $this->queueId = msg_get_queue($key);
        if ($this->queueId === false)
        {
            throw new \Swoole\Exception("msg_get_queue() failed.");
        }
        $this->workerId = $workerId;
    }

    protected function pack($data)
    {
        $fromFd = 0;
        $type = 7;
        if (!is_string($data))
        {
            $data = serialize($data);
            $fromFd |= 2;
        }
        if (strlen($data) >= 8180)
        {
            $tmpFile = tempnam('/tmp/', 'swoole.task');
            file_put_contents($tmpFile, $data);
            $data = pack('l', strlen($data)) . $tmpFile . "\0";
            $fromFd |= 1;
            $len = 128 + 24;
        }
        else
        {
            $len = strlen($data);
        }
        //typedef struct _swDataHead
        //{
        //    int fd;
        //    uint16_t len;
        //    int16_t from_id;
        //    uint8_t type;
        //    uint8_t flags;
        //    uint16_t from_fd;
        //} swDataHead;
        return pack('lSsCCS', $this->taskId++, $len, $this->workerId, $type, 0, $fromFd) . $data;
    }

    function dispatch($data)
    {
        if (!msg_send($this->queueId, $this->workerId + 1, $this->pack($data), false))
        {
            return false;
        }
        else
        {
            return true;
        }
    }
}
