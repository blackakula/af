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
namespace Browser;
abstract class Page
{
    /**
     * Page load time (seconds)
     * May be overridden by specific pages
     *
     * @var int
     */
    protected $_loadTime = 20;

    /**
     * Session
     *
     * @var \PHPUnit_Extensions_Selenium2TestCase
     */
    protected $_session;

    /**
     * Subject for debug
     *
     * @var \Debug\Subject
     */
    protected $_subject;

    /**
     * Debugger object
     *
     * @var \Debug\Debugger
     */
    protected $_debugger;

    /**
     * @param \PHPUnit_Extensions_Selenium2TestCase $session
     * @param \Debug\Debugger $debugger
     * @param \Tools\File $file
     * @param \Debug\SubjectsFabric $subjectsFabric
     */
    public function __construct(\PHPUnit_Extensions_Selenium2TestCase $session, \Debug\Debugger $debugger = null,
        \Tools\File $file = null, \Debug\SubjectsFabric $subjectsFabric = null)
    {
        $this->_session = $session;
        if (!isset($file)) {
            $fileName = str_replace('\\', '.', get_class($this));
            $file = new \Tools\File(ROOT_DIR . '/var/data/' . $fileName);
        }
        if (!isset($subjectsFabric)) {
            $subjectsFabric = new \Debug\SubjectsFabric();
        }
        $this->_debugger = isset($debugger) ? $debugger : new \Debug\Debugger($session);
        $this->_subject = $subjectsFabric->create($session, $file);
    }

    /**
     * Debug page
     *
     * @param \Debug\DebugElements $decorator
     */
    public function debug(\Debug\DebugElements $decorator = null)
    {
        if (!isset($decorator)) {
            $decorator = new \Debug\DebugElements($this->_session);
        }
        $this->_debugger->debug($this->_subject, $decorator, new \Debug\Popup());
    }

    /**
     * Get element by key
     *
     * @param string $key
     * @param array $placeholders
     * @return \Selenium\Element
     */
    protected function _getElement($key, $placeholders = array())
    {
        return $this->_subject->getElement($key, $placeholders);
    }

    /**
     * Wait for page to load
     *
     * @param null|int $timeout seconds
     */
    protected function _waitForLoad($timeout = null)
    {
        $timeout = isset($timeout) ? $timeout : $this->_loadTime;
        $this->_session->waitUntil(function(\PHPUnit_Extensions_Selenium2TestCase $session) {
            $result = $session->execute(array(
                'script' => "return document['readyState']",
                'args' => array()
            ));
            return $result == 'complete' ? 1 : null;
        }, $timeout * 1000);
    }
}
