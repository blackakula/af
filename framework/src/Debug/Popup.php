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
class Popup
{
    /**
     * Get popup HTML
     *
     * @return string
     */
    public function getHtml()
    {
        return file_get_contents(__DIR__ . '/_files/index.html');
    }
}
