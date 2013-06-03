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
class ElementsFabric
{
    /**
     * Create element
     *
     * @param \PHPUnit_Extensions_Selenium2TestCase $session
     * @param Locator $locator
     * @return Element
     */
    public function create(\PHPUnit_Extensions_Selenium2TestCase $session, Locator $locator)
    {
        return new Element($session, $locator);
    }
}
