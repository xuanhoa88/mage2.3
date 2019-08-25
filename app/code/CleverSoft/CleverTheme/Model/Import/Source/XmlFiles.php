<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\Import\Source;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir\Reader;

class XmlFiles implements \Magento\Framework\Option\ArrayInterface {

	protected $_options;
    protected $_presetFileTmpBaseDir;
    protected $_frameworkFilesystem;
    protected $_dirReader;
    protected $_exportPath;
    protected $_modules = [
        'CleverSoft_CleverTheme', 'CleverSoft_CleverTheme'
    ];

    public function __construct(Context $context,
                                Filesystem $frameworkFilesystem,
                                Reader $dirReader)
    {
        $this->_frameworkFilesystem = $frameworkFilesystem;
        $this->_dirReader = $dirReader;
        $this->_exportPath = BP . '/app/code/CleverSoft/CleverTheme/etc/import/config/';

    }

	public function toOptionArray($package = NULL)
	{
		if (!$this->_options)
		{
			$this->_options = [];
			$this->_options[] = ['value' => '', 'label' => __('-- Please Select --')]; //First option is empty

			if (is_dir($this->_exportPath))
			{
				$files = scandir($this->_exportPath);
				foreach ($files as $file)
				{
					if (!is_dir($this->_exportPath . $file))
					{
						$path = pathinfo($file);
                        if ($path['extension'] == 'xml') $this->_options[] = ['value' => $path['filename'], 'label' => ucfirst(str_replace("_"," ",$path['filename']))];
					}
				}
			}
		}
		return $this->_options;
	}

}
