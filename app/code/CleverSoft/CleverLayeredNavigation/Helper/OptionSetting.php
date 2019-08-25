<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Helper;

use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\App\Helper\Context;
use CleverSoft\CleverLayeredNavigation;
use CleverSoft\CleverLayeredNavigation\Model\ResourceModel\OptionSetting\Collection;
use CleverSoft\CleverLayeredNavigation\Model\ResourceModel\OptionSetting\CollectionFactory;

class OptionSetting extends \Magento\Framework\App\Helper\AbstractHelper
{
    /** @var  Collection */
    protected $collection;

    /** @var  CleverLayeredNavigation\Model\OptionSettingFactory */
    protected $settingFactory;

    /** @var  Repository */
    protected $repository;

    public function __construct(Context $context, CollectionFactory $optionCollectionFactory, CleverLayeredNavigation\Model\OptionSettingFactory $settingFactory, Repository $repository)
    {
        parent::__construct($context);
        $this->collection = $optionCollectionFactory->create();
        $this->settingFactory = $settingFactory;
        $this->repository = $repository;
    }

    /**
     * @param string $value
     * @param string $filterCode
     * @param int $storeId
     * @return CleverLayeredNavigation\Api\Data\OptionSettingInterface
     */
    public function getSettingByValue($value, $filterCode, $storeId)
    {
        /** @var CleverLayeredNavigation\Model\OptionSetting $setting */
        $setting = $this->settingFactory->create();
        $setting = $setting->getByParams($filterCode, $value, $storeId);

        if (!$setting->getId()) {
            $setting->setFilterCode($filterCode);
            $attributeCode = substr($filterCode, 5);
            $attribute = $this->repository->get($attributeCode);
            foreach ($attribute->getOptions() as $option)
            {
                if ($option->getValue() == $value) {
                    $this->initiateSettingByOption($setting, $option);
                    break;
                }
            }
        }

        return $setting;
    }

    protected function initiateSettingByOption(LayeredNavigation\Api\Data\OptionSettingInterface $setting, AttributeOptionInterface $option)
    {
        $setting->setValue($option->getValue());

        $setting->setTitle($option->getLabel());
        $setting->setMetaTitle($option->getLabel());
    }
}
