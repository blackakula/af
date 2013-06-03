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
class DebugElementsTest extends \PHPUnit_Framework_TestCase
{
    public function testHighlightElement()
    {
        $style1 = 'some style key 1';
        $style2 = 'some style key 2';
        $callback1 = 'some callback 1';
        $callback2 = 'some callback 2';
        $originalStyle1 = 'original style 1';
        $originalStyle2 = 'original style 2';
        $key1 = 'key1';
        $key2 = 'key2';
        $locator1 = new \Selenium\Locator('value1');
        $locator2 = new \Selenium\Locator('value2');
        $session = $this->getMockBuilder('\\PHPUnit_Extensions_Selenium2TestCase')
            ->setMethods(array('execute'))
            ->disableOriginalConstructor()
            ->getMock();
        $sut = new DebugElements($session);
        $sut->deleteStyle('style.border')
            ->deleteStyle('title')
            ->setStyle($style1, function($key, $locator) use ($callback1) {
                return $key . $locator['value'] . ' ' . $callback1;
            })
            ->setStyle($style2, function($key, $locator) use ($callback2) {
                return $key . $locator['value'] . ' ' . $callback2;
            });
        $elements = array(
            $key1 => array('id1', $locator1),
            $key2 => array('id2', $locator2),
        );
        foreach ($elements as $key => $value) {
            $elementId = $value[0];
            $locator = $value[1];
            $sut->addElement($elementId, $key, $locator);
        }
        $expectedAgruments = array(
            array('ELEMENT' => $elements[$key1][0]),
            array('ELEMENT' => $elements[$key2][0]),
        );
        $session->expects($this->at(0))
            ->method('execute')
            ->with(array(
                'script' => 'return {0:{' . json_encode($style1) . ':arguments[0].' . $style1 . ','
                    . json_encode($style2) . ':arguments[0].' . $style2 . '},1:{'
                    . json_encode($style1) . ':arguments[1].' . $style1 . ','
                    . json_encode($style2) . ':arguments[1].' . $style2 . '}}',
                'args' => $expectedAgruments
            ))
            ->will($this->returnValue(array(
                0 => array(
                    $style1 => $elements[$key1][0] . $originalStyle1,
                    $style2 => $elements[$key1][0] . $originalStyle2
                ),
                1 => array(
                    $style1 => $elements[$key2][0] . $originalStyle1,
                    $style2 => $elements[$key2][0] . $originalStyle2
                )
            )));
        $session->expects($this->at(1))
            ->method('execute')
            ->with(array(
                'script' => 'arguments[0].' . $style1 . '=' . json_encode($key1 . $locator1['value'] . ' ' . $callback1)
                    . ';arguments[0].' . $style2 . '=' . json_encode($key1 . $locator1['value'] . ' ' . $callback2)
                    . ';arguments[1].' . $style1 . '=' . json_encode($key2 . $locator2['value'] . ' ' . $callback1)
                    . ';arguments[1].' . $style2 . '=' . json_encode($key2 . $locator2['value'] . ' ' . $callback2),
                'args' => $expectedAgruments
            ));
        $session->expects($this->at(2))
            ->method('execute')
            ->with(array(
                'script' => 'arguments[0].' . $style1 . '=' . json_encode($elements[$key1][0] . $originalStyle1)
                    . ';arguments[0].' . $style2 . '=' . json_encode($elements[$key1][0] . $originalStyle2)
                    . ';arguments[1].' . $style1 . '=' . json_encode($elements[$key2][0] . $originalStyle1)
                    . ';arguments[1].' . $style2 . '=' . json_encode($elements[$key2][0] . $originalStyle2),
                'args' => $expectedAgruments
            ));
        $sut->highlightElements();
        $sut->revertHighlighting();
        unset($sut);
        unset($session);
        unset($locator1);
        unset($locator2);
    }
}
