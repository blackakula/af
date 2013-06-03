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
class SubjectsFabric
{
    /**
     * Create subject
     *
     * @param \PHPUnit_Extensions_Selenium2TestCase $session
     * @param \Tools\File $file
     * @param \Selenium\ElementsFabric $elementsFabric
     * @return Subject
     */
    public function create(\PHPUnit_Extensions_Selenium2TestCase $session, \Tools\File $file,
        \Selenium\ElementsFabric $elementsFabric = null)
    {
        return new Subject($session, $file, $elementsFabric);
    }
}
