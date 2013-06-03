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
use \Selenium\ElementsFabric;
class Subject implements Debug
{
    /**
     * Session
     *
     * @var \PHPUnit_Extensions_Selenium2TestCase
     */
    private $_session;

    /**
     * Element fabric
     *
     * @var \Selenium\ElementsFabric
     */
    private $_elementsFabric;

    /**
     * File to write the data
     *
     * @var \Tools\File
     */
    private $_file;

    /**
     * Locators
     *
     * @var array
     */
    private $_locators;

    /**
     * Placeholders
     *
     * @var array
     */
    private $_placeholders;

    /**
     * @param \PHPUnit_Extensions_Selenium2TestCase $session
     * @param \Tools\File $file
     * @param ElementsFabric $elementsFabric
     */
    public function __construct(\PHPUnit_Extensions_Selenium2TestCase $session, \Tools\File $file,
        ElementsFabric $elementsFabric = null)
    {
        $this->_session = $session;
        $this->_file = $file;
        $this->_elementsFabric = isset($elementsFabric) ? $elementsFabric : new ElementsFabric();
        list($this->_locators, $this->_placeholders) = $file->exists()
            ? unserialize($file->read())
            : array(array(), array());
    }

    /**
     * Get array of placeholders
     *
     * @return array Array of strings
     */
    public function getPlaceholders()
    {
        return array_keys($this->_placeholders);
    }

    /**
     * Get array of locators
     *
     * @return array Array of \Selenium\Locator
     */
    public function getLocators()
    {
        return $this->_locators;
    }

    /**
     * Get array of elements
     *
     * @param array $placeholders Associative array: placeholder => value
     * @return array Array of \Selenium\Element
     */
    public function getElements($placeholders = array())
    {
        $placeholdersKeys = self::getPlaceholdersKeys($placeholders);
        $placeholdersValues = array_values($placeholders);
        $elements = array();
        foreach ($this->_locators as $key => $locator) {
            $elementLocator = clone $locator;
            $elementLocator['value'] = str_replace($placeholdersKeys, $placeholdersValues, $elementLocator['value']);
            $elements[$key] = $this->_elementsFabric->create($this->_session, $elementLocator);
        }
        return $elements;
    }

    /**
     * Get locator by key
     *
     * @param string $key
     * @return \Selenium\Locator
     */
    public function getLocator($key)
    {
        return $this->_locators[$key];
    }

    /**
     * Add/Change locator
     *
     * @param string $key
     * @param \Selenium\Locator $locator
     */
    public function addLocator($key, $locator)
    {
        $this->_locators[$key] = $locator;
    }

    /**
     * Add placeholder
     *
     * @param string $key
     */
    public function addPlaceholder($key)
    {
        $this->_placeholders[$key] = 1;
    }

    /**
     * Delete placeholder
     *
     * @param string $key
     */
    public function deletePlaceholder($key)
    {
        unset($this->_placeholders[$key]);
    }

    /**
     * Delete locator
     *
     * @param string $key
     */
    public function deleteLocator($key)
    {
        unset($this->_locators[$key]);
    }

    /**
     * Save all changed data
     */
    public function save()
    {
        $this->_file->write(serialize(array($this->_locators, $this->_placeholders)));
    }

    /**
     * Get element by key
     *
     * @param string $key
     * @param array $placeholders
     * @return \Selenium\Element
     */
    public function getElement($key, $placeholders = array())
    {
        $locator = clone $this->_locators[$key];
        $locator['value'] = str_replace(self::getPlaceholdersKeys($placeholders),
            array_values($placeholders), $locator['value']);
        return $this->_elementsFabric->create($this->_session, $locator);
    }

    /**
     * Get placeholders keys
     *
     * @param array $placeholders
     * @return array
     */
    private static function getPlaceholdersKeys($placeholders)
    {
        foreach ($placeholders as $key => $value) {
            if (empty($value)) {
                unset($placeholders[$key]);
            }
        }
        $placeholdersKeys = array_map(function($key) {
            return '%' . $key . '%';
        }, array_keys($placeholders));
        return $placeholdersKeys;
    }
}
