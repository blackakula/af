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
class ElementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Locator mock
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_locator;

    /**
     * Session mock
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_session;

    /**
     * SUT
     *
     * @var Element
     */
    private $_sut;

    protected function setUp()
    {
        $this->_locator = $this->getMockBuilder('\\Selenium\\Locator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_session = $this->getMockBuilder('\\PHPUnit_Extensions_Selenium2TestCase')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_sut = new Element($this->_session, $this->_locator);
    }

    /**
     * @param int $elementsCount
     * @dataProvider isPresentDataProvider
     */
    public function testIsPresent($elementsCount)
    {
        $elements = $elementsCount ? array_fill(0, $elementsCount, 'some value') : array();
        $this->_session->expects($this->at(0))
            ->method('__call')
            ->with($this->equalTo('elements'), $this->equalTo(array($this->_locator)))
            ->will($this->returnValue($elements));
        $expected = $elementsCount > 0;
        $this->assertEquals($expected, $this->_sut->isPresent());
    }

    public function isPresentDataProvider()
    {
        return array(array(10), array(1), array(0));
    }

    /**
     * Get element mock
     *
     * @param bool $single
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function _getElementMock($single = true)
    {
        $element = $this->getMockBuilder('\\PHPUnit_Extensions_Selenium2TestCase_Element')
            ->disableOriginalConstructor()
            ->getMock();
        $call = $single ? 'element' : 'elements';
        $result = $single ? $element : array($element);
        $this->_session->expects($this->at(0))
            ->method('__call')
            ->with($this->equalTo($call), $this->equalTo(array($this->_locator)))
            ->will($this->returnValue($result));
        return $element;
    }

    public function testExecute()
    {
        $script = 'some javascript';
        $args = array('some argument 1', 'some argument 2');
        $elementId = 'some element id';
        $returnValue = 'some return value';
        $element = $this->_getElementMock();
        $element->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($elementId));
        $expectedArguments = $args;
        array_unshift($expectedArguments, array('ELEMENT' => $elementId));
        $this->_session->expects($this->at(1))
            ->method('__call')
            ->with($this->equalTo('execute'), $this->equalTo(array(array(
                'script' => $script,
                'args' => $expectedArguments
            ))))
            ->will($this->returnValue($returnValue));
        $this->assertEquals($returnValue, $this->_sut->execute($script, $args));
        $this->assertTrue($this->_sut->isPresent());
    }

    public function testElement()
    {
        $element = $this->_getElementMock();
        $this->assertEquals($element, $this->_sut->element());
        $this->assertTrue($this->_sut->isPresent());
    }

    public function testElements()
    {
        $element = $this->_getElementMock(false);
        $this->assertEquals(array($element), $this->_sut->elements());
    }

    public function testLocator()
    {
        $this->assertEquals($this->_locator, $this->_sut->locator());
    }

    protected function tearDown()
    {
        unset($this->_locator);
        unset($this->_session);
        unset($this->_sut);
    }
}
