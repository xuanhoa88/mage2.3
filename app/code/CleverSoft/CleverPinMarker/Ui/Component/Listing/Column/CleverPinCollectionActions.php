<?php
/**
 * Copyright © 2017 CleverSoft, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CleverSoft\CleverPinMarker\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class CleverPinCollectionActions extends Column
{
	/** Url path */
	const BRAND_URL_PATH_EDIT = 'pinmarker/collection/edit';
	const BRAND_URL_PATH_DELETE = 'pinmarker/collection/delete';
	/** @var UrlInterface */
    protected $urlBuilder;
	 /**
     * @var string
     */
    private $editUrl;
	/**
	* @param ContextInterface $context
	* @param UiComponentFactory $uiComponentFactory
	* @param UrlInterface $urlBuilder
	* @param array $components
	* @param array $data
	* @param string $editUrl
	*/
	public function __construct(
		ContextInterface $context,
		UiComponentFactory $uiComponentFactory,
		UrlInterface $urlBuilder,
		array $components = [],
		array $data = [],
		$editUrl = self::BRAND_URL_PATH_EDIT
	) {
		$this->urlBuilder = $urlBuilder;
		$this->editUrl = $editUrl;
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
				if (isset($item['id'])) {
					$item[$name]['edit'] = [
						'href' => $this->urlBuilder->getUrl($this->editUrl, ['id' => $item['id']]),
						'label' => __('Edit')
					];
				}
			}
		}
		return $dataSource;
	}
}