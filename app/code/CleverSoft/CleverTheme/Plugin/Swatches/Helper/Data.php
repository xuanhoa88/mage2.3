<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Plugin\Swatches\Helper;

use Magento\Catalog\Helper\Image;
use CleverSoft\CleverTheme\Helper\Data as CoreHelper;
use Magento\Catalog\Api\Data\ProductInterface as Product;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Data
{

    const DEFAULT_ZOOM_SIZE = 1.6;
    const EMPTY_IMAGE_VALUE = 'no_selection';

    public function __construct(Image $imagehelper, CoreHelper $core_helper, ProductRepositoryInterface $productRepository)
    {
        $this->_imagehelper = $imagehelper;
        $this->_core_helper = $core_helper;
        $this->productRepository = $productRepository;
    }

    public function aroundGetProductMediaGallery(\Magento\Swatches\Helper\Data $subject, callable $proceed, $product)
    {
        $proceed($product);

        if (!in_array($product->getData('image'), [null, self::EMPTY_IMAGE_VALUE], true)) {
            $baseImage = $product->getData('image');
        } else {
            $productMediaAttributes = array_filter($product->getMediaAttributeValues(), function ($value) {
                return $value !== self::EMPTY_IMAGE_VALUE && $value !== null;
            });
            foreach ($productMediaAttributes as $attributeCode => $value) {
                if ($attributeCode !== 'swatch_image') {
                    $baseImage = (string)$value;
                    break;
                }
            }
        }

        if (empty($baseImage)) {
            return [];
        }
        $result = $this->getAllSizeImages($product, $baseImage);
        $result['gallery'] = $this->getGalleryImages($product);
        return $result;
    }

    /**
     * @param ModelProduct $product
     * @return array
     */
    private function getGalleryImages(ModelProduct $product)
    {
        //TODO: remove after fix MAGETWO-48040
        $product = $this->productRepository->getById($product->getId());

        $result = [];
        $mediaGallery = $product->getMediaGalleryImages();
        foreach ($mediaGallery as $media) {
            $result[$media->getData('value_id')] = $this->getAllSizeImages(
                $product,
                $media->getData('file'),
                $media
            );
        }
        return $result;
    }

    /**
     * @param ModelProduct $product
     * @param string $imageFile
     * @return array
     * @todo refactor to use imageBuilder which doesn't check file existence
     */
    private function getAllSizeImages(ModelProduct $product, $imageFile, $media = null)
    {
        $keepAspectRatio = true;
        $keepFrame = false;
        $thump_image_width = $this->_core_helper->getCfg('product_page/thumb_image_width');
        $main_image_width = $this->_core_helper->getCfg('product_page/main_image_width');
        $main_image_height = null;
        $zoom_image_width = $main_image_width * self::DEFAULT_ZOOM_SIZE;
        $zoom_image_height = null;

        $keep_ratio = $this->_core_helper->getCfg('product_page/aspect_ratio');
        if (!$keep_ratio){
            $keepFrame = true;
            $main_image_height = $this->_core_helper->getCfg('product_page/main_image_height');
            $zoom_image_height = $main_image_height * self::DEFAULT_ZOOM_SIZE;
        }

        $media_type = '';
        $video_url = '';
        if (isset($media) && $media != null) {
            $media_type = $media->getData('media_type');
            if ($media_type == 'external-video') {
                $video_url = $media->getData('video_url');
            }
        }


        $return = array();

        $return['media_type'] = $media_type;
        $return['large'] = $this->_imagehelper->init($product, 'product_page_image_large_no_frame')
            ->setImageFile($imageFile)
            ->keepAspectRatio($keepAspectRatio)
            ->keepFrame($keepFrame)
            ->resize($zoom_image_width, $zoom_image_height)
            ->getUrl();
        $return['medium'] = $this->_imagehelper->init($product, 'product_page_image_medium_no_frame')
            ->setImageFile($imageFile)
            ->keepAspectRatio($keepAspectRatio)
            ->keepFrame($keepFrame)
            ->resize($main_image_width, $main_image_height)
            ->getUrl();
        $return['small'] = $this->_imagehelper->init($product, 'product_page_image_small')
            ->setImageFile($imageFile)
            ->keepAspectRatio(true)
            ->keepFrame(false)
            ->resize($thump_image_width)
            ->getUrl();
        $return['video_url'] = $video_url;

        return $return;
    }
}
