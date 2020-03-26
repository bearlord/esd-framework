<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Actuator;

use ESD\Core\Memory\CrossProcess\Table;
use ESD\Server\Co\Server;

/**
 * Class ActuatorController
 * @package ESD\Plugins\Actuator
 */
class ActuatorController
{
    public function index()
    {
        return json_encode(["status" => "UP", "server" => "esd-server"]);
    }

    public function health()
    {

        /**
         * @var $table Table
         */
        $table = DIGet('RouteCountTable');
        $output = [];
        foreach ($table as $path  => $num) {
            $output[$path] = [$num['num_60'] , $num['num_3600'], $num['num_86400']];
        }
        return json_encode(["status"=>"UP", 'route' => $output]);
    }

    public function info()
    {
        $serverStats = Server::$instance->stats();
        $output['server'] = 'esd-server';
        $output['Start time']      = date('Y-m-d H:i:s', $serverStats->getStartTime());
        $output['Accept count']    = $serverStats->getAcceptCount();
        $output['Close count']     = $serverStats->getCloseCount();
        $output['Request count']   = $serverStats->getRequestCount();
        $output['Coroutine num']   = $serverStats->getCoroutineNum();
        $output['Connection num']  = $serverStats->getConnectionNum();
        $output['Tasking num']     = $serverStats->getTaskingNum();
        $output['TaskQueue bytes'] = $serverStats->getTaskQueueBytes();
        $output['Worker dispatch count'] = $serverStats->getWorkerDispatchCount();
        $output['Worker request count']  = $serverStats->getWorkerRequestCount();
        return json_encode($output);
    }

}