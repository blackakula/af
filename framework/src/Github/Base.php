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
namespace Github;
abstract class Base extends \Browser\Page
{
    /**
     * Max number of tries
     */
    const MAX_TRIES = 3;

    /**
     * Click element, do several tries
     *
     * @param string $key
     * @param string|null $value
     * @throws \PHPUnit_Extensions_Selenium2TestCase_WebDriverException
     */
    protected function _clickElement($key, $value = null)
    {
        $tries = 0;
        $placeholders = isset($value) ? array($key => $value) : array();
        while (true) {
            $element = $this->_getElement($key, $placeholders);
            try {
                $this->_session->waitUntil(function() use ($element) {
                    return $element->isPresent() ? 1 : null;
                }, $this->_loadTime * 1000);
            } catch (\PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
                ++$tries;
                if ($tries >= self::MAX_TRIES) {
                    throw $e;
                }
                $this->_session->refresh();
                continue;
            }
            if (!isset($value)) {
                $url = $this->_session->url();
                $value = substr($url, strrpos($url, '/') + 1);
            }
            try {
                $element->element()->click();
                $this->_session->waitUntil(function(\PHPUnit_Extensions_Selenium2TestCase $session) use ($value) {
                    return substr($session->url(), -strlen($value)) == $value ?  1 : null;
                }, $this->_loadTime * 1000);
                break;
            } catch (\PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
                ++$tries;
                if ($tries >= self::MAX_TRIES) {
                    throw $e;
                }
            }
        }
    }
}
