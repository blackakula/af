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
namespace Tools;
class File
{
    /**
     * Name of file
     *
     * @var string
     */
    private $_fileName;

    /**
     * @param string $fileName
     */
    public function __construct($fileName)
    {
        $this->_fileName = $fileName;
    }

    /**
     * Write data to file
     *
     * @param string $data
     */
    public function write($data)
    {
        file_put_contents($this->_fileName, $data);
    }

    /**
     * Get data from file
     *
     * @return string
     */
    public function read()
    {
        return file_get_contents($this->_fileName);
    }

    /**
     * Check whether file exists
     *
     * @return bool
     */
    public function exists()
    {
        return is_file($this->_fileName);
    }
}
