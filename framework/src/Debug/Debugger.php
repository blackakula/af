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
namespace Debug;
class Debugger
{
    /**
     * Debug window name (handle)
     */
    const WINDOW_NAME = 'Debug';

    /**
     * Timeout waiting popup is ready (seconds)
     */
    const POPUP_TIMEOUT = 5;

    /**
     * JavaScript popup window variable
     */
    const WINDOW_VARIABLE = 'debugWindow';

    /**
     * Session
     *
     * @var \PHPUnit_Extensions_Selenium2TestCase
     */
    private $_session;

    /**
     * Origin session window handle
     *
     * @var string
     */
    private $_originWindow;

    /**
     * @param \PHPUnit_Extensions_Selenium2TestCase $session
     */
    public function __construct(\PHPUnit_Extensions_Selenium2TestCase $session)
    {
        $this->_session = $session;
    }

    /**
     * Shop debug popup
     *
     * @param Debug $subject
     * @param Popup $popup
     */
    private function _showPopup(Debug $subject, Popup $popup)
    {
        $this->_originWindow = $this->_session->windowHandle();
        $this->_session->execute(array(
            'script' => 'window.' . self::WINDOW_VARIABLE . ' = window.open("", '
                . json_encode(self::WINDOW_NAME) . ', "width=640,height=480,resizable,scrollbars");
                    ' . self::WINDOW_VARIABLE . '.placeholders = arguments[0];
                    ' . self::WINDOW_VARIABLE . '.locators = arguments[1];
                    ' . self::WINDOW_VARIABLE . '.document.write(' . json_encode($popup->getHtml()) . ');
                    ' . self::WINDOW_VARIABLE . '.document.close();',
            'args' => array(
                $subject->getPlaceholders(),
                array_map(function ($locator) {
                    return array(
                        $locator['using'],
                        $locator['value'],
                    );
                }, $subject->getLocators())
            ),
        ));
        $debugger = $this;
        $this->_session->waitUntil(function() use ($debugger) {
            return $debugger->isPopupReady() ? true : null;
        }, self::POPUP_TIMEOUT * 1000);
    }

    /**
     * Check if popup is ready
     *
     * @return bool
     */
    public function isPopupReady()
    {
        $readyVariable = $this->_isPopupFocused()
            ? 'window.debugIsReady'
            : 'window.' . self::WINDOW_VARIABLE . '.debugIsReady';
        return $this->_session->execute(array(
            'script' => 'return typeof(' . $readyVariable . ') != "undefined"',
            'args' => array()
        ));
    }

    /**
     * Check if popup window is focused
     *
     * @return bool
     */
    private function _isPopupFocused()
    {
        return $this->_session->windowHandle() == self::WINDOW_NAME;
    }

    /**
     * Execute JavaScript function
     *
     * @param string $function
     * @return string
     */
    private function _executeFunction($function)
    {
        $window = $this->_isPopupFocused() ? 'window.' : 'window.' . self::WINDOW_VARIABLE . '.';
        return $this->_session->execute(array(
            'script' => 'return ' . $window . $function . ';',
            'args' => array()
        ));
    }

    /**
     * Process debug on given subject
     *
     * @param Debug $subject
     * @param DebugElements $highlightedElements
     * @param Popup $popup
     */
    public function debug(Debug $subject, DebugElements $highlightedElements, Popup $popup) {
        $this->_showPopup($subject, $popup);
        do {
            $this->_processDebugActions($subject);
            if ($this->_executeFunction('isCheckRequested()')) {
                $this->_executeFunction('unmarkCheckRequest()');
                $placeholders = $this->_executeFunction('getPlaceholders()');
                $this->_session->window($this->_originWindow);
                $highlightedElements->revertHighlighting();
                $missedLocators = array();
                $isSingleChecker = $this->_executeFunction('isSingleChecker()');
                /** @var $element \Selenium\Element */
                foreach ($subject->getElements($placeholders) as $key => $element)
                {
                    try {
                        if ($element->isPresent()) {
                            $seleniumElements = $isSingleChecker ? array($element->element()) : $element->elements();
                            foreach ($seleniumElements as $seleniumElement) {
                                $highlightedElements->addElement($seleniumElement->getId(),
                                    $key, $subject->getLocator($key));
                            }
                        } else {
                            $missedLocators[] = $key;
                        }
                    } catch (\PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
                        $missedLocators[] = $key;
                    }
                }
                $highlightedElements->highlightElements();
                $this->_executeFunction('highlightLocators(' . json_encode($missedLocators) . ')');
            }
            usleep(500000);
            $isDebugging = $this->_isPopupFocused()
                || self::WINDOW_NAME == $this->_session->execute(array(
                    'script' => 'return window.' . self::WINDOW_VARIABLE . '.name',
                    'args' => array())
                );
        } while ($isDebugging);
        $highlightedElements->revertHighlighting();
        $subject->save();
    }

    /**
     * Process changes on debug popup
     *
     * @param Debug $subject
     */
    private function _processDebugActions(Debug $subject)
    {
        $actions = $this->_executeFunction('popActions()');
        foreach ($actions as $action) {
            switch ($action['action']) {
                case 'deletePlaceholder':
                    $subject->deletePlaceholder($action['key']);
                    break;
                case 'addPlaceholder':
                    $subject->addPlaceholder($action['key']);
                    break;
                case 'deleteLocator':
                    $subject->deleteLocator($action['key']);
                    break;
                case 'setLocator':
                    $subject->deleteLocator($action['oldKey']);
                    $subject->addLocator($action['key'],
                        new \Selenium\Locator($action['locatorValue'], $action['locatorType']));
                    break;
            }
        }
    }
}
