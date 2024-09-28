<?php
/**
 * ESD framework
 * @author bearload <565364226@qq.com>
 */

namespace ESD\Plugins\JsonRpc;

use ESD\Go\GoController;
use ESD\Plugins\Pack\ClientData;
use ESD\Yii\Base\Action;
use ESD\Yii\Base\Exception;
use ESD\Yii\Helpers\ArrayHelper;
use ESD\Yii\Helpers\Json;
use ESD\Yii\Yii;

/**
 * Class JsonRpcController
 * @package ESD\Plugins\JsonRpc
 */
class ServiceController extends GoController
{
    /**
     * @var array
     */
    protected $serviceProvider = [];

    /**
     * @inheritDoc
     * @param string|null $controllerName
     * @param string|null $methodName
     * @param array|null $params
     * @return mixed
     * @throws \Throwable
     */
    public function handle(?string $controllerName, ?string $methodName, ?array $params)
    {
        try {
            /** @var ClientData $clientData */
            $clientData = getContextValueByClassName(ClientData::class);
            $_clientData = $clientData->getData();

            //rpc call with an empty String or Array:
            if (empty($_clientData)) {
                return [
                    "jsonrpc" => "2.0",
                    "error" => [
                        "code" => -32600,
                        "message" => "Invalid Request"
                    ],
                    "id" => null
                ];
            }
            /** @var array | \stdClass $json */
            $json = Json::decode($_clientData, false);
            if (is_array($json)) {
                $result = [];
                foreach ($json as $key => $value) {
                    $result[] = $this->subProcess($value);
                }
                return $result;
            }
            if (!is_object($json)) {
                return [
                    "jsonrpc" => "2.0",
                    "error" => [
                        "code" => -32600,
                        "message" => "Invalid Request"
                    ],
                    "id" => null
                ];
            }
            return $this->subProcess($json);
        } catch (\Throwable $exception) {
            setContextValue("lastException", $exception);
            return $this->onExceptionHandle($exception);
        }
    }

    /**
     * @param object $jsonObject
     * @return array
     */
    protected function subProcess(object $jsonObject)
    {
        $parseData = ArrayHelper::toArray($jsonObject);
        //rpc call with invalid JSON:
        if (empty($parseData)) {
            return [
                "jsonrpc" => "2.0",
                "error" => [
                    "code" => -32700,
                    "message" => "Parse error"
                ],
                "id" => null
            ];
        }

        if (empty($parseData['method']) || empty($parseData['params']) || empty($parseData['id'])) {
            return [
                "jsonrpc" => "2.0",
                "error" => [
                    "code" => -32600,
                    "message" => "Invalid Request"
                ],
                "id" => null
            ];
        }

        //Method
        $callMethodName = $parseData['method'];
        //params
        $realParams = $parseData['params'];
        //id
        $id = $parseData['id'];

        $action = $this->createAction($callMethodName);

        //rpc call of non-existent method
        if (empty($action)) {
            return [
                "jsonrpc" => "2.0",
                "error" => [
                    "code" => -32601,
                    "message" => "Method not found"
                ],
                "id" => $id
            ];
        }

        $result = null;
        try {
            if ($this->beforeAction($action)) {
                // run the action
                $result = $action->runWithParams($realParams);
                $result = $this->afterAction($action, $result);
            }
        } catch (Exception $exception) {
            return [
                "jsonrpc" => "2.0",
                "error" => [
                    "code" => -32602,
                    "message" => "Invalid params"
                ],
                "id" => $id
            ];
        }

        $array = [
            "jsonrpc" => "2.0",
            "result" => $result,
            "id" => $id
        ];
        return $array;
    }

    /**
     * Creates an action based on the given action ID.
     * The method first checks if the action ID has been declared in [[actions()]]. If so,
     * it will use the configuration declared there to create the action object.
     * If not, it will look for a controller method whose name is in the format of `actionXyz`
     * where `xyz` is the action ID. If found, an [[InlineAction]] representing that
     * method will be created and returned.
     * @param string $id the action ID.
     * @return Action|null the newly created action instance. Null if the ID doesn't resolve into any action.
     */
    public function createAction(string $id)
    {
        if (strpos($id, '/') === false) {
            return null;
        }
        list ($serviceName, $methodName) = explode('/', $id);
        $serviceProvider = !empty($this->serviceProvider[$serviceName]) ? $this->serviceProvider[$serviceName] : null;
        if (empty($serviceProvider)) {
            return null;
        }

        $serviceProviderInstance = Yii::createObject($serviceProvider);
        if (preg_match('/^(?:[a-zA-Z0-9_]+-)*[a-zA-Z0-9_]+$/', $methodName)) {
            if (method_exists($serviceProviderInstance, $methodName)) {
                $method = new \ReflectionMethod($serviceProviderInstance, $methodName);
                if (!$method->isPrivate() && $method->getName() === $methodName) {
                    return new InlineAction($id, $serviceProviderInstance, $methodName);
                }
            }
        }
        return null;
    }
}
