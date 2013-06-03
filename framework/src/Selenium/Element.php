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
class Element
{
    /**
     * Element locator
     *
     * @var Locator
     */
    private $_locator;

    /**
     * Session
     *
     * @var \PHPUnit_Extensions_Selenium2TestCase
     */
    private $_session;

    /**
     * Real element object
     *
     * @var \PHPUnit_Extensions_Selenium2TestCase_Element
     */
    private $_element;

    /**
     * @param \PHPUnit_Extensions_Selenium2TestCase $session
     * @param Locator $locator
     */
    public function __construct(\PHPUnit_Extensions_Selenium2TestCase $session, Locator $locator)
    {
        $this->_session = $session;
        $this->_locator = $locator;
    }

    /**
     * Check if element is present
     *
     * @return bool
     */
    public function isPresent()
    {
        if (isset($this->_element)) {
            return true;
        }
        return count($this->_session->elements($this->_locator)) > 0;
    }

    /**
     * Get real element by locator
     */
    private function _prepare()
    {
        if (!isset($this->_element)) {
            $this->_element = $this->_session->element($this->_locator);
        }
    }

    /**
     * Execute Javascript on the element
     *
     * @param string $script Javascript code. Use arguments[0] for the element
     * @param array $args
     * @return string
     */
    public function execute($script, $args = array())
    {
        $this->_prepare();
        array_unshift($args, array('ELEMENT' => $this->_element->getId()));
        return $this->_session->execute(array(
            'script' => $script,
            'args' => $args
        ));
    }

    /**
     * Get element object
     *
     * @return \PHPUnit_Extensions_Selenium2TestCase_Element
     */
    public function element()
    {
        $this->_prepare();
        return $this->_element;
    }

    /**
     * Get elements array
     *
     * @return array Array of \PHPUnit_Extensions_Selenium2TestCase_Element
     */
    public function elements()
    {
        return $this->_session->elements($this->_locator);
    }

    /**
     * Get locator
     *
     * @return Locator
     */
    public function locator()
    {
        return $this->_locator;
    }
}
