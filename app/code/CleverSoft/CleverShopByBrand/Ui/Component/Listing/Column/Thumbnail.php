<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverShopByBrand\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'thumbnail';
    const ALT_FIELD = 'name';
	const BRAND_URL_PATH_EDIT = 'shopbybrand/index/edit';
	const BRAND_URL_PATH_DELETE = 'shopbybrand/index/delete';
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->_imageHelper = $this->_objectManager->get('CleverSoft\CleverShopByBrand\Helper\Image');
        $this->_urlBuilder = $urlBuilder;
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
			$objectManager = $this->_objectManager;
			$mediaUrl = $objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
			$repository = $objectManager->get('Magento\Framework\View\Asset\Repository');
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $model = $objectManager->create('CleverSoft\CleverShopByBrand\Model\Brand');
                $brand = $model->load($item['option_id']);
				if($brand->getLogo()){
					$brandThumbnail = $this->_imageHelper->init($brand->getLogo())->resize(100)->__toString();
					$brandOriginal = $mediaUrl.$brand->getLogo();
				}else{
					$brandThumbnail = $brandOriginal = $repository->getUrl('CleverSoft_CleverShopByBrand/images/placeholder_thumbnail.jpg');
				}
                $item[$fieldName . '_src'] = $brandThumbnail;
                $item[$fieldName . '_alt'] = $brand->getBrandLabel();
                $item[$fieldName . '_link'] = $this->_urlBuilder->getUrl(self::BRAND_URL_PATH_EDIT);
                $item[$fieldName . '_orig_src'] = $brandOriginal;
            }
        }
        return $dataSource;
    }
}
