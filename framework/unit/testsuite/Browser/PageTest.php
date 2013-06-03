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
namespace Browser;
class PageTest extends \PHPUnit_Framework_TestCase
{
    public function testDebug()
    {
        $subject = $this->getMock('\\Debug\\Debug');
        $session = $this->getMockBuilder('\\PHPUnit_Extensions_Selenium2TestCase')
            ->disableOriginalConstructor()
            ->getMock();
        $debugger = $this->getMockBuilder('\\Debug\\Debugger')
            ->disableOriginalConstructor()
            ->getMock();
        $file = $this->getMockBuilder('\\Tools\\File')
            ->disableOriginalConstructor()
            ->getMock();
        $subjectFabric = $this->getMock('\\Debug\\SubjectsFabric');
        $subjectFabric->expects($this->once())
            ->method('create')
            ->with($this->equalTo($session), $this->equalTo($file))
            ->will($this->returnValue($subject));
        $decorator = $this->getMockBuilder('\\Debug\\DebugElements')
            ->disableOriginalConstructor()
            ->getMock();
        $debugger->expects($this->once())
            ->method('debug')
            ->with($this->equalTo($subject), $this->equalTo($decorator), $this->equalTo(new \Debug\Popup()));
        $sut = $this->getMockForAbstractClass('\\Browser\\Page', array($session, $debugger, $file, $subjectFabric));
        $sut->debug($decorator);
        unset($sut);
        unset($decorator);
        unset($subjectFabric);
        unset($debugger);
        unset($session);
        unset($subject);
    }
}
