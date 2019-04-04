<?php

namespace Yuleicc\AliyunLog;

class AliyunLog
{
    /**
     * @var array
     */
    protected $config = [
        'endpoint' => 'http://cn-shanghai-corp.sls.aliyuncs.com',
        'accessKeyId' => '',
        'accessKey' => '',
        'project' => '',
        'logstore' => ''
    ];

    /**
     * Aliyun log config construct
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }
        $this->client = new \Aliyun_Log_Client($this->config['endpoint'], $this->config['accessKeyId'], $this->config['accessKey']);
    }

    /**
     * get aliyun logs.
     * @return object
     */
    public function getLogs()
    {
        $req1 = new \Aliyun_Log_Models_ListLogstoresRequest($this->config['project']);
        $res1 = $this->client->listLogstores($req1);
        var_dump($res1);
    }

    public function logVarDump($expression)
    {
        print '<br>loginfo begin = ' . get_class($expression) . '<br>';
        var_dump($expression);
        print '<br>loginfo end<br>';
    }
}
