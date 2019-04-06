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

    /**
     * write aliyun logs.
     * @return object
     */
    public function putLogs($data)
    {
        $topic = 'testTopic';
        $logItem = new \Aliyun_Log_Models_LogItem();
        $logItem->setTime(time());
        $logItem->setContents($data);
        $logitems = [$logItem];
        $request = new \Aliyun_Log_Models_PutLogsRequest(
            $this->config['project'],
            $this->config['$logstore'],
            $topic,
            null,
            $logitems
        );

        try {
            $response = $this->client->putLogs($request);
            $this->logVarDump($response);
        } catch (\Aliyun_Log_Exception $ex) {
            $this->logVarDump($ex);
        } catch (Exception $ex) {
            $this->logVarDump($ex);
        }
    }

    public function logVarDump($expression)
    {
        print '<br>loginfo begin = ' . get_class($expression) . '<br>';
        var_dump($expression);
        print '<br>loginfo end<br>';
    }
}
