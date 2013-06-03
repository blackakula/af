<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * For the full copyright and license information, please view the LICENSE
 *
 * @category    blackakula
 * @package     af
 * @copyright   Copyright (c) Sergii Akulinin <blackakula@gmail.com>
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Selenium;
use Symfony\Component\Yaml\Yaml;
class TestCase extends \PHPUnit_Extensions_Selenium2TestCase
{
    /**
     * Config data
     *
     * @var array
     */
    private static $_configParameters;

    /**
     * Constructs a test case with the given name and set up session configuration
     *
     * @param string $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->_setUpFromConfig(self::_getConfig());
    }

    /**
     * Retrieve configuration data
     *
     * @param string|null $section
     * @return array|string
     * @throws \OutOfRangeException
     */
    private static function _getConfig($section = null)
    {
        if (!isset(self::$_configParameters)) {
            $configFile = ROOT_DIR . '/config/config.yml';
            if (!is_readable($configFile)) {
                throw new \OutOfRangeException('"config/config.yml" file is not created.');
            }
            self::$_configParameters = Yaml::parse($configFile);
        }
        return isset($section) && isset(self::$_configParameters[$section])
            ? self::$_configParameters[$section]
            : self::$_configParameters;
    }

    /**
     * Set up session from given configuration array
     *
     * @param array $config
     * @param array $sessionParams
     */
    private function _setUpFromConfig(array $config, $sessionParams = array())
    {
        $this->setupSpecificBrowser(array_merge($config['session'], $sessionParams));
        $this->setBrowserUrl('about:blank');
    }

    /**
     * Start isolated session as separate object
     *
     * @param array $params
     * @return TestCase
     */
    protected static function _startSession(array $params = array())
    {
        $session = new TestCase();
        $params['sessionStrategy'] = 'isolated';
        $session->_setUpFromConfig(self::_getConfig(), $params);
        $session->prepareSession();
        return $session;
    }

    /**
     * Add maximize window, clear cookies
     *
     * @return \PHPUnit_Extensions_Selenium2TestCase_Session
     */
    public function prepareSession()
    {
        $isSessionNew = !$this->getSessionId();
        $session = parent::prepareSession();
        if ($isSessionNew) {
            $session->currentWindow()->maximize();
            $session->cookie()->clear();
        }
        return $session;
    }

    /**
     * Init session before call any Selenium API.
     *
     * @param string $command
     * @param array $arguments
     * @return mixed
     */
    public function __call($command, $arguments)
    {
        if (!$this->getSessionId()) {
            $this->prepareSession();
        }
        return parent::__call($command, $arguments);
    }

    /**
     * Get url(s) from config
     *
     * @param null|int|string $index
     * @return array|null|string
     */
    protected function _getUrl($index = null)
    {
        $urls = self::_getConfig('urls');
        if (!isset($index)) {
            return $urls;
        }
        return isset($urls[$index]) ? $urls[$index] : null;
    }
}
