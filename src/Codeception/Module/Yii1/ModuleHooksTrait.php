<?php

namespace Codeception\Module\Yii1;

use Codeception\Exception\ModuleConfigException;

/**
 * Overload the methods from the base class \Codeception\Module:
 *
 * _initialize() triggered after module is created and configuration is loaded
 * onReconfigure()
 * validateConfig()
 */
trait ModuleHooksTrait
{
    /**
     * @var array The contents of $_SERVER upon initialization of this object.
     * This is only used to restore it upon object destruction.
     * It MUST not be used anywhere else.
     */
    private $server;

    public function _initialize()
    {
        $this->defineConstants();
        $this->server = $_SERVER; // backup
        $_SERVER = $this->getServerGlobal();
    }

    protected function validateConfig()
    {
        parent::validateConfig();

        if (empty($this->config['entryScript'])) {
            throw new ModuleConfigException(
                __CLASS__,
                "The path to the entry point is missing. Please configure 'entryScript'."
            );
        }

        $pathToConfig = codecept_absolute_path($this->config['configFile']);
        if (!is_file($pathToConfig)) {
            throw new ModuleConfigException(
                __CLASS__,
                "The application config file '{$this->config['configFile']}' does not exist: '$pathToConfig'"
            );
        }
    }

    protected function getModuleConfig(string $name = '')
    {
        return $name ? ($this->config[$name] ?? null) : $this->config;
    }

    protected function getServerGlobal(): array
    {
        $entryUrl = $this->config['entryUrl'];
        $entryWebPath = parse_url($entryUrl, PHP_URL_PATH);
        $entryServerPath = $this->config['entryScript'];
        return array_merge(
            $this->server,
            [
                'SCRIPT_FILENAME' => $entryServerPath, // server path, e.g. "/srv/www/index-test.php"
                'SCRIPT_NAME' => $entryWebPath, // web path, e.g. "/index-test.php"
                'SERVER_NAME' => parse_url($entryUrl, PHP_URL_HOST),
                'SERVER_PORT' => parse_url($entryUrl, PHP_URL_PORT) ?: '80',
                'HTTPS' => parse_url($entryUrl, PHP_URL_SCHEME) === 'https',
                'REMOTE_ADDR' => '127.0.0.1',
            ]
        );
    }

    private function defineConstants(): void
    {
        defined('YII_DEBUG') or define('YII_DEBUG', true);
        defined('YII_ENV') or define('YII_ENV', 'test');
        defined('YII_ENABLE_EXCEPTION_HANDLER') or define('YII_ENABLE_EXCEPTION_HANDLER', false);
        defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', false);
    }
}
