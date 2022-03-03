<?php
namespace Codeception\Module;

use Codeception\Lib\Framework;
use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\TestInterface;
use Codeception\Module\Yii1\ModuleHooksTrait;
use Codeception\Module\Yii1\TestHooksTrait;
use Codeception\Util\ReflectionHelper;
use Yii;

/**
 * This module provides integration with [Yii Framework 1.1](http://www.yiiframework.com/doc/guide/).
 *
 * The following configurations are available for this module:
 *
 * * `applicationClass` - Fully qualified class name for the application.
 *    Defaults to "\CWebApplication".
 * * `configFile` *required* - Path to the application config file.
 *    The file should return a configuration array.
 * * `entryUrl` - Initial application url (default: http://localhost/index-test.php).
 *    The file will not be loaded (e.g. "index-test.php" may not exist).
 * * `entryScript` *required* - path to the front script (e.g. : src/www/index-php.php).
 *    Yii derives some internal path from this.
 *
 *
 * You can use this module by setting params in your `functional.suite.yml`:
 *
 * ```yaml
 * actor: FunctionalTester
 * modules:
 *     enabled:
 *         - Yii1:
 *             configFile: "path/to/config/test.php"
 *             entryScript: "path/to/index.php"
 * ```
 *
 * @property \Codeception\Lib\Connector\Yii1 $client
 */
class Yii1 extends Framework implements PartedModule
{
    use ModuleHooksTrait, TestHooksTrait;

    protected $config = [
        'applicationClass' => '\\CWebApplication',
        'configFile' => '',
        'entryScript' => '',
        'entryUrl' => 'http://localhost/index-test.php',
        'transaction' => true,
    ];

    /**
     * @var array
     */
    protected $requiredFields = ['configFile'];

    public function _parts()
    {
        return ['init'];
    }

    /**
     * Returns a list of regex patterns for recognized domain names
     *
     * @return array
     */
    public function getInternalDomains()
    {
        if (Yii::app() === null) {
            throw new \Exception("The Yii app must be initialized.");
        }
        $domains = [$this->getDomainRegex(Yii::app()->getRequest()->getHostInfo())];
        if (Yii::app()->urlManager->urlFormat === 'path') {
            $parent = Yii::app()->urlManager instanceof \CUrlManager ? '\CUrlManager' : null;
            $rules = ReflectionHelper::readPrivateProperty(Yii::app()->urlManager, '_rules', $parent);
            foreach ($rules as $rule) {
                if ($rule->hasHostInfo === true) {
                    $domains[] = $this->getDomainRegex($rule->template, $rule->params);
                }
            }
        }
        return array_unique($domains);
    }

    /**
     * Getting domain regex from rule template and parameters
     *
     * @param string $template
     * @param array $parameters
     * @return string
     */
    private function getDomainRegex($template, $parameters = [])
    {
        $host = parse_url($template, PHP_URL_HOST);
        if ($host) {
            $template = $host;
        }
        if (strpos($template, '<') !== false) {
            $template = str_replace(['<', '>'], '#', $template);
        }
        $template = preg_quote($template);
        foreach ($parameters as $name => $value) {
            $template = str_replace("#$name#", $value, $template);
        }
        return '/^' . $template . '$/u';
    }
}
