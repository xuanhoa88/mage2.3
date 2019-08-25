<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\Swatches\Ajax;

use Magento\Catalog\Helper\Image;
use CleverSoft\CleverTheme\Helper\Data as CoreHelper;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Media
{

    const DEFAULT_ZOOM_SIZE = 1.6;
    const EMPTY_IMAGE_VALUE = 'no_selection';

    protected $_resize_option = null;

    public function __construct(Image $imagehelper, CoreHelper $core_helper, ProductRepositoryInterface $productRepository)
    {
        $this->_imagehelper = $imagehelper;
        $this->_core_helper = $core_helper;
        $this->productRepository = $productRepository;
        $this->setDefaultImageResizeOptions();
    }

    private function setDefaultImageResizeOptions()
    {
        $this->_resize_option['keep_aspect_ratio'] = true;
        $this->_resize_option['keep_frame'] = false;
        $this->_resize_option['thump_image_width'] = $this->_core_helper->getCfg('product_page/thumb_image_width');
        $this->_resize_option['main_image_width'] = $this->_core_helper->getCfg('product_page/main_image_width');
        $this->_resize_option['main_image_height'] = null;
        $this->_resize_option['zoom_image_width'] = $this->_resize_option['main_image_width'] * self::DEFAULT_ZOOM_SIZE;
        $this->_resize_option['zoom_image_height'] = null;

        $this->_resize_option['keep_ratio'] = $this->_core_helper->getCfg('product_page/aspect_ratio');
        if (!$this->_resize_option['keep_aspect_ratio']){
            $this->_resize_option['keep_frame'] = true;
            $this->_resize_option['main_image_height'] = $this->_core_helper->getCfg('product_page/main_image_height');
            $this->_resize_option['zoom_image_height'] = $this->_resize_option['main_image_height'] * self::DEFAULT_ZOOM_SIZE;
        }
    }

//    public function aroundGetProductMediaGallery(\Magento\Swatches\Helper\Data $subject, callable $proceed, $product)
//    {
//        $proceed($product);
//
//        if (!in_array($product->getData('image'), [null, self::EMPTY_IMAGE_VALUE], true)) {
//            $baseImage = $product->getData('image');
//        } else {
//            $productMediaAttributes = array_filter($product->getMediaAttributeValues(), function ($value) {
//                return $value !== self::EMPTY_IMAGE_VALUE && $value !== null;
//            });
//            foreach ($productMediaAttributes as $attributeCode => $value) {
//                if ($attributeCode !== 'swatch_image') {
//                    $baseImage = (string)$value;
//                    break;
//                }
//            }
//        }
//
//        if (empty($baseImage)) {
//            return [];
//        }
//        $result = $this->getAllSizeImages($product, $baseImage);
//        $result['gallery'] = $this->getGalleryImages($product);
//        return $result;
//    }

    /**
     * Method getting full media gallery for current Product
     * Array structure: [
     *  ['image'] => 'http://url/pub/media/catalog/product/2/0/blabla.jpg',
     *  ['mediaGallery'] => [
     *      galleryImageId1 => simpleProductImage1.jpg,
     *      galleryImageId2 => simpleProductImage2.jpg,
     *      ...,
     *      ]
     * ]
     * @param ModelProduct $product
     * @return array
     */
    public function getProductMediaGallery(ModelProduct $product, $resize_option = null)
    {
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

        if (is_array($resize_option)) {
            $this->_resize_option = array_merge($this->_resize_option, $resize_option);
        }
        $resultGallery = $this->getAllSizeImages($product, $baseImage);
        $resultGallery['gallery'] = $this->getGalleryImages($product);

        return $resultGallery;
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
                $media->getData('file')
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
    private function getAllSizeImages(ModelProduct $product, $imageFile)
    {
        $return = array();

        $return['large'] = $this->_imagehelper->init($product, 'product_page_image_large_no_frame')
            ->setImageFile($imageFile)
            ->keepAspectRatio($this->_resize_option['keep_aspect_ratio'])
            ->keepFrame($this->_resize_option['keep_frame'])
            ->resize($this->_resize_option['zoom_image_width'], $this->_resize_option['zoom_image_height'])
            ->getUrl();
        $return['medium'] = $this->_imagehelper->init($product, 'product_page_image_medium_no_frame')
            ->setImageFile($imageFile)
            ->keepAspectRatio($this->_resize_option['keep_aspect_ratio'])
            ->keepFrame($this->_resize_option['keep_frame'])
            ->resize($this->_resize_option['main_image_width'], $this->_resize_option['main_image_height'])
            ->getUrl();
        $return['small'] = $this->_imagehelper->init($product, 'product_page_image_small')
            ->setImageFile($imageFile)
            ->keepAspectRatio(true)
            ->keepFrame(false)
            ->resize($this->_resize_option['thump_image_width'])
            ->getUrl();

        return $return;
    }
}
