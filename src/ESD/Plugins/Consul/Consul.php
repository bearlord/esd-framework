<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Consul;

use ESD\Consul\Health;
use ESD\Consul\KV;
use ESD\Consul\ServiceFactory;
use ESD\Consul\Session;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\Consul\Beans\ConsulServiceInfo;
use ESD\Plugins\Consul\Beans\ConsulServiceListInfo;
use ESD\Plugins\Consul\Config\ConsulConfig;
use ESD\Plugins\Consul\Event\ConsulAddServiceMonitorEvent;
use ESD\Plugins\Consul\Event\ConsulLeaderChangeEvent;
use ESD\Plugins\Consul\Event\ConsulServiceChangeEvent;
use ESD\Server\Coroutine\Server;
use SensioLabs\Consul\ConsulResponse;
use SensioLabs\Consul\Services\Agent;
use SensioLabs\Consul\Services\AgentInterface;
use SensioLabs\Consul\Services\HealthInterface;
use SensioLabs\Consul\Services\KVInterface;
use SensioLabs\Consul\Services\SessionInterface;

/**
 * Class Consul
 * @package ESD\Plugins\Consul
 */
class Consul
{
    use GetLogger;

    /**
     * @var bool
     */
    private $isLeader = false;
    /**
     * @var ConsulConfig
     */
    private $consulConfig;

    /**
     * @var string[]
     */
    private $listonServices = [];

    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var ServiceFactory
     */
    private $sf;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var KV
     */
    private $kv;

    /**
     * @var Agent
     */
    private $agent;

    /**
     * @var Health
     */
    private $health;

    /**
     * Synchronize
     * @var \SensioLabs\Consul\ServiceFactory
     */
    private $syncSf;

    /**
     * Synchronize session
     * @var SessionInterface
     */
    private $syncSession;

    /**
     * Synchronize agent
     * @var AgentInterface
     */
    private $syncAgent;

    /**
     * Consul constructor.
     * @param ConsulConfig $consulConfig
     * @throws \Exception
     */
    public function __construct(ConsulConfig $consulConfig)
    {
        $this->consulConfig = $consulConfig;
        //Generate a configuration file and register
        $this->sf = new ServiceFactory(["base_uri" => $consulConfig->getHost(), "http_errors" => false], Server::$instance->getLog());
        $this->session = $this->sf->get(SessionInterface::class);
        $this->kv = $this->sf->get(KVInterface::class);
        $this->agent = $this->sf->get(AgentInterface::class);
        $this->health = $this->sf->get(HealthInterface::class);

        $this->syncSf = new \SensioLabs\Consul\ServiceFactory(["base_uri" => $this->consulConfig->getHost(), "http_errors" => false], Server::$instance->getLog());
        $this->syncSession = $this->syncSf->get(SessionInterface::class);
        $this->syncAgent = $this->sf->get(AgentInterface::class);

        foreach ($this->consulConfig->getServiceConfigs() as $consulServiceConfig) {
            $body = $consulServiceConfig->buildConfig();
            $serviceId = $consulServiceConfig->getId() ?? $consulServiceConfig->getName();
            $this->debug(sprintf("Register Service：%s", $serviceId));
            $this->agent->registerService($body);
        }

        //Listen for events on services that need to be monitored
        $call = Server::$instance->getEventDispatcher()->listen(ConsulAddServiceMonitorEvent::ConsulAddServiceMonitorEvent);
        $call->call(function (ConsulAddServiceMonitorEvent $consulAddServiceMonitorEvent) {
            $service = $consulAddServiceMonitorEvent->getService();
            if (!array_key_exists($service, $this->listonServices)) {
                goWithContext(function () use ($service) {
                    $this->monitorService($service, 0);
                });
            }
        });

        //Leader listening
        if (!empty($consulConfig->getLeaderName())) {
            goWithContext(function () {
                //Try to get the leader first
                $this->getLeader();
            });
        } else {
            $this->setIsLeader(true);
        }
    }

    /**
     * Monitor service
     *
     * @param string $service
     * @param int $index
     * @throws \Exception
     */
    private function monitorService(string $service, int $index)
    {
        try {
            $response = $this->health->service($service, ["passing" => true, "index" => $index, "wait" => "1m"], 120);
        } catch (\Throwable $e) {
            //Error will keep retrying
            $this->error($e);
            $this->monitorService($service, $index);
            return;
        }
        if ($response instanceof ConsulResponse) {
            $index = $response->getHeaders()["x-consul-index"][0];
            $consulServiceInfos = [];
            foreach ($response->json() as $one) {
                $oneService = $one['Service'];
                $consulServiceInfo = new ConsulServiceInfo($oneService['Service'], $oneService['ID'], $oneService['Address'], $oneService['Port'], $oneService['Meta'], $oneService['Tags']);
                $consulServiceInfos[] = $consulServiceInfo;
            }

            //broadcast
            Server::$instance->getEventDispatcher()->dispatchProcessEvent(
                new ConsulServiceChangeEvent(new ConsulServiceListInfo($service, $consulServiceInfos)),
                ... Server::$instance->getProcessManager()->getProcesses()
            );
            $this->monitorService($service, $index);
        }
    }

    /**
     * Get leader
     *
     * @throws \Exception
     */
    private function getLeader()
    {
        if ($this->sessionId != null) {
            $this->debug(sprintf("Release session：%s", $this->sessionId));
            $this->session->destroy($this->sessionId);
        }
        try {
            //Get SessionId
            $this->sessionId = $this->session->create(
                [
                    'LockDelay' => 0,
                    'Behavior' => 'release',
                    'Name' => $this->consulConfig->getLeaderName()
                ])->json()['ID'];
            $this->debug(sprintf("Get SessionId： %s", $this->sessionId));

            $lockAcquired = $this->kv->put("{$this->consulConfig->getLeaderName()}/leader", 'a value', ['acquire' => $this->sessionId])->json();
            if (false === $lockAcquired) {
                $this->setIsLeader(false);
                $this->debug("No Leader");
            } else {
                $this->debug("Get Leader");
                $this->setIsLeader(true);
            }

            $this->monitorLeader(0);
        } catch (\Throwable $e) {
            $this->error($e);
            $this->getLeader();
        }
    }

    /**
     * Monitor leader
     *
     * @param int $index
     * @throws \Exception
     */
    private function monitorLeader(int $index)
    {
        try {
            $response = $this->kv->get("{$this->consulConfig->getLeaderName()}/leader", ["index" => $index, "wait" => "1m"], 120);
        } catch (\Throwable $e) {
            //Error will keep retrying
            $this->error($e);
            $this->monitorLeader($index);
            return;
        }
        if ($response instanceof ConsulResponse) {
            $index = $response->getHeaders()["x-consul-index"][0];
            $session = $response->json()[0]['Session'] ?? null;
            if ($session == null) {
                $this->debug("There is currently no leader in the cluster");
                $this->getLeader();
            } else {
                if ($session != $this->sessionId) {
                    $this->debug("Leader currently exists in the cluster, monitor Leader changes");
                    $this->setIsLeader(false);
                }
                $this->monitorLeader($index);
            }
        }
    }

    /**
     * @return bool
     */
    public function isLeader(): bool
    {
        return $this->isLeader;
    }

    /**
     * @param bool $isLeader
     * @throws \Exception
     */
    public function setIsLeader(bool $isLeader): void
    {
        if ($this->isLeader != $isLeader) {
            $this->isLeader = $isLeader;
            //广播
            Server::$instance->getEventDispatcher()->dispatchProcessEvent(
                new ConsulLeaderChangeEvent($isLeader),
                ... Server::$instance->getProcessManager()->getProcesses()
            );
        }
    }

    /**
     * Release leader
     *
     * @param bool $useAsync
     * @throws \Exception
     */
    public function releaseLeader($useAsync = true)
    {
        if (!empty($this->sessionId)) {
            $this->debug(sprintf("Release session：%s", $this->sessionId));
            if ($useAsync) {
                $this->session->destroy($this->sessionId);
            } else {
                //Note that you need to use synchronous requests here, because Guanfu cannot use the coroutine scheme.
                $this->syncSession->destroy($this->sessionId);
            }
            $this->setIsLeader(false);
        }
    }

    /**
     * Deregister Service
     *
     * @param bool $useAsync
     * @throws \Exception
     */
    public function deregisterService($useAsync = true)
    {
        foreach ($this->consulConfig->getServiceConfigs() as $serviceConfig) {
            $serviceId = $serviceConfig->getId() ?? $serviceConfig->getName();
            $this->debug(sprintf("Deregister service：%s", $serviceId));
            if ($useAsync) {
                $this->agent->deregisterService($serviceId);
            } else {
                $this->syncAgent->deregisterService($serviceId);
            }
        }
    }
}