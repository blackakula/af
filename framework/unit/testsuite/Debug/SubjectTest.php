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
use \Selenium\Locator;
class SubjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Session mock
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_session;

    /**
     * File mock
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_file;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_elementsFabric;

    protected function setUp()
    {
        $this->_session = $this->getMockBuilder('\\PHPUnit_Extensions_Selenium2TestCase')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_elementsFabric = $this->getMock('\\Selenium\\ElementsFabric');
        $this->_file = $this->getMock('\\Tools\\File', array(), array('some file name'));
    }

    protected function tearDown()
    {
        unset($this->_session);
        unset($this->_file);
        unset($this->_elementsFabric);
    }

    /**
     * @param bool $fileExists
     * @dataProvider fileExistsDataProvider
     */
    public function testPlaceholders($fileExists)
    {
        $this->_file->expects($this->once())
            ->method('exists')
            ->will($this->returnValue($fileExists));
        $key1 = 'key1';
        $placeholders = $fileExists ? array($key1 => 1, 'key2' => 1) : array();
        if ($fileExists) {
            $this->_file->expects($this->once())
                ->method('read')
                ->will($this->returnValue(serialize(array(array(), $placeholders))));
        } else {
            $this->_file->expects($this->never())->method('read');
        }
        $sut = new Subject($this->_session, $this->_file, $this->_elementsFabric);
        $this->assertEquals(array_keys($placeholders), $sut->getPlaceholders(), '', 0, 10, true);
        $addKey = 'key3';
        $sut->addPlaceholder($addKey);
        $placeholders[$addKey] = 1;
        $this->assertEquals(array_keys($placeholders), $sut->getPlaceholders(), '', 0, 10, true);
        if ($fileExists) {
            $sut->deletePlaceholder($key1);
            unset($placeholders[$key1]);
            $this->assertEquals(array_keys($placeholders), $sut->getPlaceholders(), '', 0, 10, true);
        }
        unset($sut);
    }

    /**
     * @param bool $fileExists
     * @dataProvider fileExistsDataProvider
     */
    public function testLocators($fileExists)
    {
        $this->_file->expects($this->once())
            ->method('exists')
            ->will($this->returnValue($fileExists));
        $key1 = 'key1';
        $locators = $fileExists
            ? array(
                $key1 => new Locator('some value 1'),
                'key2' => new Locator('some value 2')
            ) : array();
        if ($fileExists) {
            $this->_file->expects($this->once())
                ->method('read')
                ->will($this->returnValue(serialize(array($locators, array()))));
        } else {
            $this->_file->expects($this->never())->method('read');
        }
        $sut = new Subject($this->_session, $this->_file, $this->_elementsFabric);
        $this->assertEquals($locators, $sut->getLocators());
        if ($fileExists) {
            $this->assertEquals($locators[$key1], $sut->getLocator($key1));
        }
        $addKey = 'key3';
        $addLocator = new Locator('some value 3');
        $sut->addLocator($addKey, $addLocator);
        $locators[$addKey] = $addLocator;
        $this->assertEquals($locators, $sut->getLocators());
        if ($fileExists) {
            $sut->deleteLocator($key1);
            unset($locators[$key1]);
            $this->assertEquals($locators, $sut->getLocators());
        }
        unset($sut);
        unset($locators);
    }

    public function fileExistsDataProvider()
    {
        return array(
            array(true),
            array(false)
        );
    }

    public function testSave()
    {
        $this->_file->expects($this->once())
            ->method('write')
            ->with($this->equalTo(serialize(array(array(), array()))));
        $sut = new Subject($this->_session, $this->_file, $this->_elementsFabric);
        $sut->save();
        unset($sut);
    }

    /**
     * Replace placeholders in locator
     *
     * @param string $locatorValue
     * @param array $placeholders
     * @return string
     */
    private function _replacePlaceholders($locatorValue, $placeholders)
    {
        foreach ($placeholders as $key => $value) {
            $locatorValue = str_replace('%' . $key . '%', $value, $locatorValue);
        }
        return $locatorValue;
    }

    public function testGetElements()
    {
        $placeholders = array(
            'placeholder1' => 'value1',
            'placeholder2' => 'value2',
        );
        $key = 'locator_key1';
        $locators = array(
            'locator_key0' => new Locator('locator 0'),
            $key => new Locator('locator %placeholder1% 1'),
            'locator_key2' => new Locator('locator 2'),
            'locator_key3' => new Locator('locator %placeholder2% 3'),
        );
        $elements = array(
            'locator_key0' => 'element 0',
            $key => 'element 1',
            'locator_key2' => 'element 2',
            'locator_key3' => 'element 3'
        );
        $this->_file->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(true));
        $this->_file->expects($this->once())
            ->method('read')
            ->will($this->returnValue(serialize(array($locators, array_keys($placeholders)))));
        $i = 0;
        foreach ($locators as $locatorKey => $locator) {
            $this->_elementsFabric->expects($this->at($i++))
                ->method('create')
                ->with(
                    $this->equalTo($this->_session),
                    new Locator($this->_replacePlaceholders($locator['value'], $placeholders))
                )
                ->will($this->returnValue($elements[$locatorKey]));
        }
        $this->_elementsFabric->expects($this->at($i))
            ->method('create')
            ->with(
                $this->equalTo($this->_session),
                new Locator($this->_replacePlaceholders($locators[$key]['value'], $placeholders))
            )
            ->will($this->returnValue($elements[$key]));
        $sut = new Subject($this->_session, $this->_file, $this->_elementsFabric);
        $this->assertEquals($elements, $sut->getElements($placeholders));
        $this->assertEquals($elements[$key], $sut->getElement($key, $placeholders));
        unset($locators);
    }
}
