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
use \Selenium\Locator;
class DebugElements
{
    /**
     * Styles for highlighting elements
     *
     * @var array
     */
    private $_styles = array();

    /**
     * Highlighted elements
     *
     * @var array
     */
    private $_highlightedElements = array();

    /**
     * Argument for execute to revert highlighting
     *
     * @var array
     */
    private $_revertData;

    /**
     * Session
     *
     * @var \PHPUnit_Extensions_Selenium2TestCase
     */
    private $_session;

    public function __construct(\PHPUnit_Extensions_Selenium2TestCase $session)
    {
        $this->_session = $session;
        $this->_styles = array(
            'style.border' => function($key, Locator $locator) {return '2px dashed blue';},
            'title' => function ($key, Locator $locator) {
                return '"' . $key . '"[' . $locator['using'] . ']: ' . $locator['value'];
            }
        );
    }

    /**
     * Delete style
     *
     * @param string $key
     * @return DebugElements
     */
    public function deleteStyle($key)
    {
        unset($this->_styles[$key]);
        return $this;
    }

    /**
     * Set style
     *
     * @param string $key
     * @param callback $callback
     * @return DebugElements
     */
    public function setStyle($key, $callback)
    {
        $this->_styles[$key] = $callback;
        return $this;
    }

    /**
     * Get JavaScript for setting element style
     *
     * @param string $style
     * @param string $value
     * @return string
     */
    private function _getElementStyleScript($style, $value)
    {
        return 'arguments[0].' . $style . ' = ' . json_encode($value);
    }

    /**
     * Add element for highlighting
     *
     * @param int $elementId
     * @param string $key
     * @param Locator $locator
     * @return DebugElements
     */
    public function addElement($elementId, $key, Locator $locator)
    {
        $this->_highlightedElements[] = array('key' => $key, 'element' => $elementId, 'locator' => $locator);
        return $this;
    }

    /**
     * Get JavaScript code part for getting element styles data
     *
     * @param int $i
     * @return string
     */
    private function _getOriginalDataScript($i)
    {
        $parts = array();
        foreach ($this->_styles as $style => $callback) {
            $parts[] = json_encode($style) . ':arguments[' . $i . '].' . $style;
        }
        return '{' . implode(',', $parts) . '}';
    }

    /**
     * Get JavaScript code part for setting element styles
     *
     * @param int $i
     * @param string $key
     * @param string $locator
     * @return string
     */
    private function _setStylesDataScript($i, $key, $locator)
    {
        $parts = array();
        foreach ($this->_styles as $style => $callback) {
            $parts[] = 'arguments[' . $i . '].' . $style . '=' . json_encode($callback($key, $locator));
        }
        return implode(";", $parts);
    }

    /**
     * Get JavaScript code part for reverting element styles
     *
     * @param int $i
     * @param string $styles
     * @return string
     */
    private function _revertStylesDataScript($i, $styles)
    {
        $parts = array();
        foreach ($this->_styles as $style => $callback) {
            $parts[] = 'arguments[' . $i . '].' . $style . '=' . json_encode($styles[$style]);
        }
        return implode(";", $parts);
    }

    /**
     * Highlight added elements
     */
    public function highlightElements()
    {
        if (empty($this->_highlightedElements)) {
            $this->_revertData = null;
            return;
        }
        $i = 0;
        $originalDataScriptParts = array();
        $setStylesScriptParts = array();
        $scriptArguments = array();
        foreach ($this->_highlightedElements as $value) {
            $originalDataScriptParts[$i] = $i . ':' . $this->_getOriginalDataScript($i);
            $setStylesScriptParts[$i] = $this->_setStylesDataScript($i, $value['key'], $value['locator']);
            $scriptArguments[$i] = array('ELEMENT' => $value['element']);
            ++$i;
        }
        $originalData = $this->_session->execute(array(
            'script' => 'return {' . implode(',', $originalDataScriptParts) . '}',
            'args' => $scriptArguments
        ));
        $revertScriptParts = array();
        foreach ($originalData as $i => $value) {
            $revertScriptParts[$i] = $this->_revertStylesDataScript($i, $value);
        }
        $this->_revertData = array(
            'script' => implode(';', $revertScriptParts),
            'args' => $scriptArguments
        );
        $this->_session->execute(array(
            'script' => implode(';', $setStylesScriptParts),
            'args' => $scriptArguments
        ));
        $this->_highlightedElements = array();
    }

    /**
     * Revert highlighted elements to original style values
     */
    public function revertHighlighting()
    {
        if (isset($this->_revertData)) {
            $this->_session->execute($this->_revertData);
            $this->_revertData = null;
        }
    }
}
