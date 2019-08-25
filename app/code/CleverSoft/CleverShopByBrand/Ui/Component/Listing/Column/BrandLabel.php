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

class BrandLabel extends Column
{

     /**
     * Column name
     */
	const NAME = 'column.brand_label';
	
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

                    $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
				    $connection = $resource->getConnection();
				    $tableName = $resource->getTableName('eav_attribute_option_value');

				    $option_id = $item['option_id'];
				    $sql = "select * FROM " . $tableName . " where option_id=".$option_id;
				    $result = $connection->fetchAll($sql);

					$item[$name] = $result[0]['value'];
				}
			}
		}
		return $dataSource;
	}
}