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
class DebuggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Calls of "execute" per debug cycle
     */
    const EXECUTE_CALLS = 7;

    /**
     * Subject
     *
     * @var Debugger
     */
    private $_sut;

    /**
     * Session
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_session;

    protected function setUp()
    {
        $this->_session = $this->getMockBuilder('\\PHPUnit_Extensions_Selenium2TestCase')
            ->setMethods(array('windowHandle', 'waitUntil', 'execute', 'window'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->_sut = new Debugger($this->_session);
    }

    protected function tearDown()
    {
        unset($this->_sut);
        unset($this->_session);
    }

    /**
     * @param bool $isPopupFocused
     * @dataProvider isPopupReadyDataProvider
     */
    public function testIsPopupReady($isPopupFocused)
    {
        $result = 'some value';
        $this->_session->expects($this->once())
            ->method('windowHandle')
            ->will($this->returnValue($isPopupFocused ? Debugger::WINDOW_NAME : ''));
        $constraint = new SessionExecuteConstraint(Debugger::WINDOW_NAME);
        if (!$isPopupFocused) {
            $constraint = new \PHPUnit_Framework_Constraint_Not($constraint);
        }
        $this->_session->expects($this->once())
            ->method('execute')
            ->with(new \PHPUnit_Framework_Constraint_And(array(
                $constraint,
                new SessionExecuteConstraint('debugIsReady')
            )))
            ->will($this->returnValue($result));
        $this->assertEquals($result, $this->_sut->isPopupReady());
    }

    public function isPopupReadyDataProvider()
    {
        return array(
            array(true),
            array(false)
        );
    }

    public function testDebug()
    {
        $originWindowHandle = 'some window handle';
        $subject = $this->getMock('\\Debug\\Debug');
        $highlightedElements = $this->getMockBuilder('\\Debug\\DebugElements')->disableOriginalConstructor()->getMock();
        $popupHtml = 'some popup html';
        $popup = $this->getMock('\\Debug\\Popup', array('getHtml'));
        $placeholders = array('some placeholders');
        $popup->expects($this->once())->method('getHtml')->will($this->returnValue($popupHtml));
        $this->_session->expects($this->any())
            ->method('windowHandle')
            ->will($this->returnValue($originWindowHandle));
        $this->_session->expects($this->_at(0))
            ->method('execute')
            ->with(new \PHPUnit_Framework_Constraint_And(array(
                new SessionExecuteConstraint(Debugger::WINDOW_VARIABLE),
                new SessionExecuteConstraint(json_encode(Debugger::WINDOW_NAME)),
                new SessionExecuteConstraint(json_encode($popupHtml)),
            )));
        $subject->expects($this->once())->method('getPlaceholders')->will($this->returnValue(array()));
        $subject->expects($this->once())->method('getLocators')->will($this->returnValue(array()));
        $this->_session->expects($this->once())
            ->method('waitUntil')
            ->with($this->anything(), $this->equalTo(Debugger::POPUP_TIMEOUT * 1000));
        $highlightedElements->expects($this->exactly(3))
            ->method('revertHighlighting');
        $this->_session->expects($this->exactly(2))
            ->method('window')
            ->with($this->equalTo($originWindowHandle));
        $highlightedElements->expects($this->exactly(2))
            ->method('highlightElements');
        $cycleNumber = 1;
        $this->_testActions($subject,
            $cycleNumber * self::EXECUTE_CALLS - 6, 'some key', 'new key', 'locator value', 'locator type');
        $this->_prepareDebugCycle($cycleNumber, $placeholders, $subject, $highlightedElements, true);
        $cycleNumber = 2;
        $this->_executeFunction($cycleNumber * self::EXECUTE_CALLS - 6, 'popActions()', array());
        $this->_prepareDebugCycle($cycleNumber, $placeholders, $subject, $highlightedElements, false);
        $cycleNumber = 3;
        $this->_executeFunction($cycleNumber * self::EXECUTE_CALLS - 6, 'popActions()', array());
        $this->_executeFunction($cycleNumber * self::EXECUTE_CALLS - 5, 'isCheckRequested()', false);
        $this->_executeFunction($cycleNumber * self::EXECUTE_CALLS - 4, Debugger::WINDOW_VARIABLE . '.name', false);

        $subject->expects($this->once())->method('save');
        $this->_sut->debug($subject, $highlightedElements, $popup);
        unset($subject);
        unset($highlightedElements);
        unset($popup);
    }

    /**
     * Prepare debug cycle
     *
     * @param int $cycleNumber
     * @param array $placeholders
     * @param \PHPUnit_Framework_MockObject_MockObject $subject
     * @param \PHPUnit_Framework_MockObject_MockObject $highlightedElements
     * @param bool $isSingleChecker
     * @return array
     */
    private function _prepareDebugCycle($cycleNumber, $placeholders, $subject, $highlightedElements, $isSingleChecker)
    {
        $invocations = $cycleNumber * self::EXECUTE_CALLS - 6;
        $this->_executeFunction($invocations + 1, 'isCheckRequested()', true);
        $this->_executeFunction($invocations + 2, 'unmarkCheckRequest()');
        $this->_executeFunction($invocations + 3, 'getPlaceholders()', $placeholders);
        $this->_executeFunction($invocations + 4, 'isSingleChecker()', $isSingleChecker);
        $missedLocators = $this->_testHighlighting($cycleNumber - 1,
            $subject, $highlightedElements, $placeholders, $isSingleChecker);
        $this->_session->expects($this->_at($invocations + 5))
            ->method('execute')
            ->with(new \PHPUnit_Framework_Constraint_And(array(
                new SessionExecuteConstraint('highlightLocators'),
                new SessionExecuteConstraint(json_encode($missedLocators))
            )));
        $this->_executeFunction($invocations + 6, Debugger::WINDOW_VARIABLE . '.name', true);
    }

    /**
     * Add expectation on 'execute' method
     *
     * @param int $invokeNumber
     * @param string $function
     * @param null|mixed $result
     */
    private function _executeFunction($invokeNumber, $function, $result = null)
    {
        $mocker = $this->_session->expects($this->_at($invokeNumber))
            ->method('execute')
            ->with(new SessionExecuteConstraint($function));
        if (isset($result)) {
            $mocker->will($this->returnValue($result));
        }
    }

    /**
     * Test actions callback handling
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $subject
     * @param int $invokeNumber
     * @param string $key
     * @param string $newKey
     * @param string $locatorValue
     * @param string $locatorType
     */
    private function _testActions($subject, $invokeNumber, $key, $newKey, $locatorValue, $locatorType)
    {
        $this->_executeFunction($invokeNumber, 'popActions()', array(array(
            'action' => 'deletePlaceholder',
            'key' => $key
        ), array(
            'action' => 'addPlaceholder',
            'key' => $key
        ), array(
            'action' => 'deleteLocator',
            'key' => $key
        ), array(
            'action' => 'setLocator',
            'oldKey' => $key,
            'key' => $newKey,
            'locatorValue' => $locatorValue,
            'locatorType' => $locatorType
        )));
        $subject->expects($this->once())->method('deletePlaceholder')->with($this->equalTo($key));
        $subject->expects($this->once())->method('addPlaceholder')->with($this->equalTo($key));
        $subject->expects($this->exactly(2))->method('deleteLocator')->with($this->equalTo($key));
        $subject->expects($this->once())
            ->method('addLocator')
            ->with($this->equalTo($newKey), $this->equalTo(new \Selenium\Locator($locatorValue, $locatorType)));
    }

    /**
     * Test highlighting elements
     *
     * @param int $invocation
     * @param \PHPUnit_Framework_MockObject_MockObject $subject
     * @param \PHPUnit_Framework_MockObject_MockObject $highlightedElements
     * @param array $placeholders
     * @param bool $isSingleChecker
     * @return array
     */
    private function _testHighlighting($invocation, $subject, $highlightedElements, $placeholders, $isSingleChecker)
    {
        $key1 = 'key1';
        $key2 = 'key2';
        $key3 = 'key3';
        $id2 = 'id2';
        $locator = new \Selenium\Locator('some locator');
        $elements = array(
            $key1 => $this->_getElementMock($this->returnValue(false), 'TestElement1', null, $isSingleChecker),
            $key2 => $this->_getElementMock($this->returnValue(true), 'TestElement2', $id2, $isSingleChecker),
            $key3 => $this->_getElementMock($this->throwException(
                new \PHPUnit_Extensions_Selenium2TestCase_WebDriverException()),
                'TestElement3', null, $isSingleChecker),
        );
        $subject->expects($this->_at($invocation))
            ->method('getElements')
            ->with($this->equalTo($placeholders))
            ->will($this->returnValue($elements));
        $subject->expects($this->_at($invocation))
            ->method('getLocator')
            ->with($this->equalTo($key2))
            ->will($this->returnValue($locator));
        $highlightedElements->expects($this->_at($invocation))
            ->method('addElement')
            ->with($this->equalTo($id2), $this->equalTo($key2), $this->equalTo($locator));
        return array($key1, $key3);
    }

    /**
     * Get element mock
     *
     * @param \PHPUnit_Framework_MockObject_Stub $isPresentWill
     * @param string $mockClassName
     * @param string|null $id
     * @param bool $isSingleChecker
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function _getElementMock($isPresentWill, $mockClassName, $id, $isSingleChecker)
    {
        $mockMethod = $isSingleChecker ? 'element' : 'elements';
        $element = $this->getMockBuilder('\\Selenium\\Element')
            ->disableOriginalConstructor()
            ->setMethods(array('isPresent', $mockMethod))
            ->setMockClassName($mockClassName . (int)$isSingleChecker)
            ->getMock();
        $element->expects($this->once())
            ->method('isPresent')
            ->will($isPresentWill);
        if (isset($id)) {
            $seleniumElementMockClass = 'TestSelenium' . $mockClassName . (int)$isSingleChecker;
            $seleniumElement = $this->_getSeleniumElementMock($seleniumElementMockClass, $id);
            $element->expects($this->once())
                ->method($mockMethod)
                ->will($this->returnValue($isSingleChecker ? $seleniumElement : array($seleniumElement)));
        } else {
            $element->expects($this->never())->method($mockMethod);
        }
        return $element;
    }

    /**
     * Get selenium element mock
     *
     * @param string $mockClassName
     * @param string $id
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function _getSeleniumElementMock($mockClassName, $id)
    {
        $element = $this->getMockBuilder('\\PHPUnit_Extensions_Selenium2TestCase_Element')
            ->setMethods(array('getId'))
            ->disableOriginalConstructor()
            ->setMockClassName($mockClassName)
            ->getMock();
        $element->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($id));
        return $element;
    }

    /**
     * Returns a matcher that matches when the method it is evaluated for
     * is invoked at the given $index.
     *
     * @param int $invocation
     * @return InvokedAtIndex
     */
    private function _at($invocation)
    {
        return new InvokedAtIndex($invocation);
    }
}

function usleep ($micro_seconds) {}
class SessionExecuteConstraint extends \PHPUnit_Framework_Constraint_StringContains
{
    protected function matches($other)
    {
        return parent::matches($other['script']);
    }
}
class InvokedAtIndex extends \PHPUnit_Framework_MockObject_Matcher_InvokedAtIndex
{
    /**
     * Indexes for invocation methods
     *
     * @var array
     */
    private $_indexes = array();

    /**
     * Check invocations per method
     *
     * @param \PHPUnit_Framework_MockObject_Invocation $invocation
     * @return bool
     */
    public function matches(\PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        if (!isset($this->_indexes[$invocation->methodName])) {
            $this->_indexes[$invocation->methodName] = 0;
        } else {
            $this->_indexes[$invocation->methodName]++;
        }
        ++$this->currentIndex;
        return $this->_indexes[$invocation->methodName] == $this->sequenceIndex;
    }
}
