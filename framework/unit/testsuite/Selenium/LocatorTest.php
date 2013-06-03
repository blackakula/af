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
class LocatorTest extends \PHPUnit_Framework_TestCase
{
    public function testLocator()
    {
        $value = 'some value';
        $locator = new Locator($value);
        $this->assertEquals(Locator::XPATH, $locator['using']);
        $this->assertEquals($value, $locator['value']);
        $strategy = 'some strategy';
        $locator = new Locator($value, $strategy);
        $this->assertEquals($strategy, $locator['using']);
        $this->assertEquals($value, $locator['value']);
        unset($locator);
    }
}
