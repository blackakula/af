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
use Selenium\TestCase;
class GithubTest extends TestCase
{
    public function testGithubRecursively()
    {
        $originalPaths = explode(DIRECTORY_SEPARATOR, substr(__FILE__, strlen(ROOT_DIR) + 1));
        $startLine = __LINE__;
        $zeroLine = __LINE__;
        $path = $originalPaths;
        $this->url($this->_getUrl('baseUrl'));
        $page = new \Github\Tree($this);
        while (count($path) > 1) {
            $directory = array_shift($path);
            $page->clickDirectory($directory);
        }
        $page->clickFile(array_shift($path))
            ->clickRaw();
        $lines = explode("\n", htmlspecialchars_decode($this->source()));
        eval(implode("\n", array_slice($lines, $startLine, ($zeroLine > 1) ? __LINE__ - $startLine : __LINE__)));
    }
}
