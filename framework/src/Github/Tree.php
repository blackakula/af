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
class Tree extends Base
{
    /**
     * Click directory
     *
     * @param string $directory
     * @return Tree
     */
    public function clickDirectory($directory)
    {
        $this->_clickElement('item', $directory);
        return $this;
    }

    /**
     * Click file
     *
     * @param string $file
     * @return File
     */
    public function clickFile($file)
    {
        $this->_clickElement('item', $file);
        return new File($this->_session);
    }
}
