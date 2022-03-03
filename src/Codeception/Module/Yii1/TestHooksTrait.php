<?php

namespace Codeception\Module\Yii1;

use Codeception\TestInterface;
use Codeception\Lib\Connector\Yii1 as Yii1Connector;

/**
 * Overload the methods from the base class \Codeception\Module:
 * _after()
 * _afterStep()
 * _afterSuite()
 * _before()
 * _beforeStep()
 * _beforeSuite()
 * _failed()
 */
trait TestHooksTrait
{
    use TransactionTrait;

    /**
     * @var \Codeception\Lib\Connector\Yii1
     */
    public $client;

    abstract protected function getModuleConfig(string $name = '');
    abstract protected function getServerGlobal();

    public function _before(TestInterface $test)
    {
        $this->createClient();
        $_SERVER = $this->getServerGlobal();
        $this->client->startApplication();
        if ($this->getModuleConfig('transaction')) {
            $this->startTransaction();
        }
    }

    public function _after(TestInterface $test)
    {
        codecept_debug('Test done, restoring state');
        $_SESSION = [];
        $_FILES = [];
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];
        $_REQUEST = [];

        if ($this->getModuleConfig('transaction')) {
            $this->rollbackTransaction();
        }
        if ($this->client !== null) {
            $this->client->resetApplication();
        }
        parent::_after($test);
    }

    public function _afterSuite()
    {
        parent::_afterSuite();
        codecept_debug('Suite done, restoring $_SERVER to original');
        $_SERVER = $this->getServerGlobal();
    }

    /*
     * Create the client connector. Called before each test by _before().
     */
    private function createClient()
    {
        $this->client = new Yii1Connector($this->getServerGlobal());
        $this->configureClient($this->getModuleConfig());
        $this->client->resetApplication();
    }

    private function configureClient(array $settings)
    {
        $settings['configFile'] = codecept_absolute_path($settings['configFile']);
        foreach ($settings as $key => $value) {
            if (property_exists($this->client, $key)) {
                $this->client->$key = $value;
            }
        }
        $this->client->applicationClass = $settings['applicationClass'];
    }
}
