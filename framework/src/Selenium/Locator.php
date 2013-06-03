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
class Locator extends \PHPUnit_Extensions_Selenium2TestCase_ElementCriteria
{
    /**#@+
     * Locator strategy
     */
    const XPATH = 'xpath';
    const CSS_SELECTOR = 'css selector';
    const ID = 'id';
    const NAME = 'name';
    const CLASS_NAME = 'class name';
    const TAG_NAME = 'tag name';
    const LINK_TEXT = 'link text';
    /**#@-*/

    /**
     * @param string $value
     * @param string $strategy
     */
    public function __construct($value, $strategy = self::XPATH)
    {
        parent::__construct($strategy);
        $this->value($value);
    }
}
