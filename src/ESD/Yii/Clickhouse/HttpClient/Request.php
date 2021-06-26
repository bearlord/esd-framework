<?php
namespace ESD\Yii\Clickhouse\HttpClient;

/**
 * Class Request
 * @package ESD\Yii\Clickhouse\HttpClient
 * tmp FIX
 */
class Request extends \ESD\Yii\HttpClient\Request
{

    public function prepare()
    {
        parent::prepare();
        $this->afterPrepareUrl();

    }

    /**
     * Normalizes [[url]] value, filling it with actual string URL value.
     */
    private function afterPrepareUrl()
    {
        $url = $this->getUrl();
        if (strpos($url,'?') !== false) {
            $url = trim($url,'/');
            $this->setUrl($url);
        }
    }

}