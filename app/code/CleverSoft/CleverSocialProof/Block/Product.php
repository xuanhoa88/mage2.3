<?php
/**
 * @category    CleverSoft
 * @package     CleverSocialProof
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverSocialProof\Block;

class Product extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \CleverSoft\CleverSocialProof\Helper\Data $helper,
        \Magento\Catalog\Helper\Image $helperImage,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_helper = $helper;
        $this->_helperImage = $helperImage;
        $this->_jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    public function getProducts() {
        $categoryIds = explode(",", $this->_helper->getConfig("socialproof/general/list_category"));
        $limit = $this->_helper->getConfig("socialproof/general/limit_product");
        $collection = $this->_productCollectionFactory->create();
        $collection->addUrlRewrite();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('status', array('eq'=>'1'))
            ->addAttributeToFilter('visibility', array('neq' => '1'));
        $collection->addCategoriesFilter(['in' => $categoryIds]);
        $collection->setPageSize($limit);
        $collection->getSelect()->orderRand();
        $name = $this->getName();
        $country = $this->getCountry();
        $time = $this->getTime();
        $product = array();
        $index = 0;
        if (count($collection)) {
            foreach ($collection as $item) {
                $image = $this->_helperImage->init($item, 'product_page_image_small')->setImageFile($item->getFile())->resize(100, 100)->getUrl();
                $productName = $item->getName();
                $productUrl = $item->getProductUrl();
                $product[$index]["image"] = $image;
                $product[$index]["product_name"] = $productName;
                $product[$index]["product_url"] = $productUrl;
                $product[$index]["name"] = $name[array_rand($name, 1)] . " " . "in" . " " . $country[array_rand($country, 1)] . " " . "just bought";
                $product[$index]["time"] = $time[array_rand($time, 1)];
                $index++;
            }
        }
        return $this->_jsonHelper->jsonEncode($product);
    }

    public function getDisplayTime() {
        return $this->_helper->getConfig("socialproof/general/display_time");
    }

    public function getDelayTime() {
        return $this->_helper->getConfig("socialproof/general/delay_time");
    }

    public function getName() {
        return explode("|", $this->_helper->getConfig("socialproof/general/name"));
    }

    public function getCountry() {
        return explode("|", $this->_helper->getConfig("socialproof/general/country"));
    }

    public function getTime() {
        return explode("|", $this->_helper->getConfig("socialproof/general/time"));
    }
}