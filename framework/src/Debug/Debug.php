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
interface Debug
{
    /**
     * Get array of placeholders
     *
     * @return array Array of strings
     */
    public function getPlaceholders();

    /**
     * Get array of locators
     *
     * @return array Array of \Selenium\Locator
     */
    public function getLocators();

    /**
     * Get array of elements
     *
     * @param array $placeholders Associative array: placeholder => value
     * @return array Array of \Selenium\Element
     */
    public function getElements($placeholders = array());

    /**
     * Get locator by key
     *
     * @param string $key
     * @return \Selenium\Locator
     */
    public function getLocator($key);

    /**
     * Add/Change locator
     *
     * @param string $key
     * @param \Selenium\Locator $locator
     */
    public function addLocator($key, $locator);

    /**
     * Add placeholder
     *
     * @param string $key
     */
    public function addPlaceholder($key);

    /**
     * Delete placeholder
     *
     * @param string $key
     */
    public function deletePlaceholder($key);

    /**
     * Delete locator
     *
     * @param string $key
     */
    public function deleteLocator($key);

    /**
     * Save all changed data
     */
    public function save();
}
