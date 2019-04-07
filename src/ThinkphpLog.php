<?php

namespace Yuleicc\AliyunLog;

class ThinkphpLog
{
    /**
     * client
     */
    protected $client;

    /**
     * @var array
     */

    protected $config = [
        'time_format' => 'Y-m-d H:i:s',
        'file_size' => 2097152,
        'path' => LOG_PATH,
        'endpoint' => 'http://cn-shanghai-corp.sls.aliyuncs.com',
        'accessKeyId' => '',
        'accessKey' => '',
        'project' => '',
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

        $endpoint = $this->config['endpoint'];
        $accessKeyId = $this->config['accessKeyId'];
        $accessKey = $this->config['accessKey'];

        $this->client = new \Aliyun_Log_Client($endpoint, $accessKeyId, $accessKey);
    }

    /**
     * write to aliyun
     * @param $source
     * @param $contents
     * @param $logType
     */
    private function write($source, $contents, $logType)
    {
        $project = $this->config['project'];
        $topic = 'testTopic';
        $logItem = new \Aliyun_Log_Models_LogItem();
        $logItem->setTime(time());
        $logItem->setContents($contents);
        $logitems = [$logItem];
        $request = new \Aliyun_Log_Models_PutLogsRequest(
            $project,
            'access',
            $topic,
            $source,
            $logitems
        );

        $this->client->putLogs($request);

        if ($logType == 'error') {
            $request = new \Aliyun_Log_Models_PutLogsRequest($project, 'error', $topic, $source, $logitems);
            $this->client->putLogs($request);
        }
    }

    /**
     * save log
     * @access public
     * @param array $log
     * @return bool
     */
    public function save(array $log = [])
    {
        $now = date($this->config['time_format']);
        $destination = $this->config['path'] . date('y_m_d') . '.log';

        !is_dir($this->config['path']) && mkdir($this->config['path'], 0755, true);

        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if (is_file($destination) && floor($this->config['file_size']) <= filesize($destination)) {
            rename($destination, dirname($destination) . DS . $_SERVER['REQUEST_TIME'] . '-' . basename($destination));
        }

        // 获取基本信息
        if (isset($_SERVER['HTTP_HOST'])) {
            $current_uri = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        } else {
            $current_uri = 'cmd:' . implode(' ', $_SERVER['argv']);
        }
        $runtime = (number_format(microtime(true), 8, '.', '') - THINK_START_TIME) ?: 0.00000001;
        $reqs = number_format(1 / number_format($runtime, 8), 2);
        $time_str = ' [运行时间：' . number_format($runtime, 6) . 's][吞吐率：' . $reqs . 'req/s]';
        $memory_use = number_format((memory_get_usage() - THINK_START_MEM) / 1024, 2);
        $memory_str = ' [内存消耗：' . $memory_use . 'kb]';
        $file_load = ' [文件加载：' . count(get_included_files()) . ']';

        $contents = [];
        $info = '[ log ] ' . $current_uri . $time_str . $memory_str . $file_load . "\r\n";
        $contents['log'] = $current_uri . $time_str . $memory_str . $file_load;

        $logType = 'info';
        foreach ($log as $type => $val) {
            if ($type == 'error') {
                $logType = $type;
            }
            foreach ($val as $msg) {
                if (!is_string($msg)) {
                    $msg = var_export($msg, true);
                }
                $info .= '[ ' . $type . ' ] ' . $msg . "\r\n";
            }
            if (is_array($val)) {
                foreach ($val as $key => $value) {
                    $contents[strtoupper("[ $type $key ]")] = is_object($value) ? json_encode($value) : $value;
                }
            } else {
                $contents[strtoupper("[ $type ]")] = is_object($val) ? json_encode($val) : $val;
            }
        }
        $server = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '0.0.0.0';
        $remote = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'CLI';
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $this->write($server, $contents, $logType);

        return error_log("[{$now}] {$server} {$remote} {$method} {$uri}\r\n{$info}\r\n", 3, $destination);
    }
}
