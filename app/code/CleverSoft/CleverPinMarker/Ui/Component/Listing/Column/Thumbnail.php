<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverPinMarker\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'thumbnail';
    const ALT_FIELD = 'name';
	const URL_PATH_EDIT = 'pinmarker/index/edit';
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
		$this->_imageHelper = $this->_objectManager->get('CleverSoft\CleverPinMarker\Helper\Image');
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
                $model = $objectManager->create('CleverSoft\CleverPinMarker\Model\PinMarker');
                $pinmarker = $model->load($item['id']);
				if($pinmarker->getImage()){
					$pinmarkerThumbnail = $this->_imageHelper->init($pinmarker->getImage())->resize(100)->__toString();
					$pinmarkerOriginal = $mediaUrl.$pinmarker->getImage();
				}else{
					$pinmarkerThumbnail = $pinmarkerOriginal = $repository->getUrl('CleverSoft_CleverPinMarker/images/placeholder_thumbnail.jpg');
				}
                $item[$fieldName . '_src'] = $pinmarkerThumbnail;
                $item[$fieldName . '_alt'] = $pinmarker->getPinMarkerLabel();
                $item[$fieldName . '_link'] = $this->_urlBuilder->getUrl(self::URL_PATH_EDIT,['id' => $item['id']]);
                $item[$fieldName . '_orig_src'] = $pinmarkerOriginal;
            }
        }
        return $dataSource;
    }
}
