<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverTheme\Controller\Swatches\Ajax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Model\Product;
use CleverSoft\CleverTheme\Model\Swatches\Ajax\Media as ModelMedia;

/**
 * Class Media
 *
 * @package Magento\Swatches\Controller\Ajax
 */
class Media extends \Magento\Swatches\Controller\Ajax\Media
{
    /**
     * @var \Magento\Swatches\Helper\Data
     */
    protected $swatchHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productModelFactory;

    /**
     * @param $param
     * @param \CleverSoft\CleverTheme\Model\Swatches\Ajax\Media $model_media
     */
    public function __construct(
        $param,
        ModelMedia $model_media
    ) {
        foreach ($param as $object) {
            if (is_a($object, 'Magento\Catalog\Model\ProductFactory')) {
                $this->productModelFactory = $object;
            } else if (is_a($object, 'Magento\Swatches\Helper\Data')) {
                $this->swatchHelper = $object;
            }
        }
        $this->_model_media = $model_media;

        parent::__construct($param[0],$param[1],$param[2]);
    }

    /**
     * Get product media by fallback:
     * 1stly by default attribute values
     * 2ndly by getting base image from configurable product
     *
     * @return string
     */
    public function execute()
    {
        $productMedia = [];
        if ($productId = 550) {
            $currentConfigurable = $this->productModelFactory->create()->load($productId);
            $attributes = array('color'=>7);
            if (!empty($attributes)) {
                $product = $this->getProductVariationWithMedia($currentConfigurable, $attributes);
            }
            if ((empty($product) || (!$product->getImage() || $product->getImage() == 'no_selection'))
                && isset($currentConfigurable)
            ) {
                $product = $currentConfigurable;
            }
            $resize_option = array();
            $productMedia = $this->_model_media->getProductMediaGallery($product, $resize_option);
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($productMedia);
        return $resultJson;
    }

}
