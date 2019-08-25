<?php
/**
 * @category    CleverSoft
 * @package     CleverShopByBrand
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverShopByBrand\Model\Attribute\Source;

use CleverSoft\CleverShopByBrand\Model\ResourceModel\Brand\CollectionFactory;

/**
 * Catalog category landing page attribute source
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Brand extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Block collection factory
     *
     * @var CollectionFactory
     */
    protected $_brandCollectionFactory;

    /**
     * Construct
     *
     * @param CollectionFactory $brandCollectionFactory
     */
    public function __construct(CollectionFactory $brandCollectionFactory)
    {
        $this->_brandCollectionFactory = $brandCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = $this->_brandCollectionFactory->create()->load()->toOptionArray();
            array_unshift($this->_options, ['value' => '', 'label' => __(' ')]);
        }
        return $this->_options;
    }
}
