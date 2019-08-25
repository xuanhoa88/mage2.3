<?php
/**
 * Copyright © 2017 CleverSoft, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CleverSoft\CleverShopByBrand\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use CleverSoft\CleverShopByBrand\Helper\Data as CleverShopByBrandHelper;

class Featured extends Column
{

    /**
     * Column name
     */
	const NAME = 'column.is_featured';
	/**
	* @param ContextInterface $context
	* @param UiComponentFactory $uiComponentFactory
	* @param UrlInterface $urlBuilder
	* @param array $components
	* @param array $data
	* @param CleverShopByBrandHelper $helper
	*/
	public function __construct(
		ContextInterface $context,
		UiComponentFactory $uiComponentFactory,
		array $components = [],
		array $data = []
	) {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		parent::__construct($context, $uiComponentFactory, $components, $data);
	}
	/**
	* Prepare Data Source
	*
	* @param array $dataSource
	* @return array
	*/
	public function prepareDataSource(array $dataSource)
	{
		if (isset($dataSource['data']['items'])) {
			foreach ($dataSource['data']['items'] as & $item) {
				$name = $this->getData('name');
				if (isset($item['option_id'])) {
                    $model = $this->_objectManager->create('CleverSoft\CleverShopByBrand\Model\Brand');
                    $brand = $model->load($item['option_id']);
					$item[$name] = $brand->getData('is_featured');
				}
			}
		}
		return $dataSource;
	}
}