<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */


namespace CleverSoft\CleverLayeredNavigation\Plugin;


use CleverSoft\CleverLayeredNavigation\Model\FilterSetting;
use CleverSoft\CleverLayeredNavigation\Model\FilterSettingFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Swatches\Model\Swatch;

class AttributePlugin
{
    /** @var  FilterSetting */
    protected $_setting;
    protected $swatchHelper;
    protected $eavAttribute;
    const DEFAULT_STORE_ID = 0;
    protected $isSwatchExists;
    protected $swatchCollectionFactory;
    protected $swatchFactory;
    protected $_version;
    /**
     * Base option title used for string operations to detect is option already exists or new
     */
    const BASE_OPTION_TITLE = 'option';
    /**
     * Array which contains links for new created attributes for swatches
     *
     * @var array
     */
    protected $dependencyArray = [];

    public function __construct(
        FilterSettingFactory $settingFactory,
        \Magento\Swatches\Helper\Data $swatchHelper,
        \Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory $collectionFactory,
        \Magento\Swatches\Model\SwatchFactory $swatchFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface,
        \Magento\Swatches\Model\Plugin\EavAttribute $eavAttribute
    )
    {
        $this->_setting = $settingFactory->create();
        $this->swatchCollectionFactory = $collectionFactory;
        $this->_version = $productMetadataInterface;
        $this->swatchFactory = $swatchFactory;
        $this->swatchHelper = $swatchHelper;
        $this->eavAttribute = $eavAttribute;
    }

    public function aroundSave(Attribute $subject, \Closure $proceed)
    {
        if (!$subject->hasData('filter_code')) {
            return $proceed();
        }

        $filterCode = 'attr_' . $subject->getAttributeCode();
        $this->_setting->load($filterCode, 'filter_code');
        $this->_setting->addData($subject->getData());
        $currentFilterCode = $this->_setting->getFilterCode();
        if(empty($currentFilterCode)) {
            $this->_setting->setFilterCode($filterCode);
        }

        $connection = $this->_setting->getResource()->getConnection();
        try {
            $connection->beginTransaction();
            $this->_setting->save();
            $result = $proceed();
            $connection->commit();
        } catch(\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        return $result;
    }

    public function afterSave(Attribute $attribute) {
        if ($this->swatchHelper->isSwatchAttribute($attribute) &&  (version_compare($this->_version->getVersion(), '2.2.0', '>=')) ) {
            $this->processSwatchOptions($attribute);
            $this->saveDefaultSwatchOptionValue($attribute);
            $this->saveSwatchParams($attribute);
        }
    }

    /**
     * Save default swatch value using Swatch model instead of Eav model
     *
     * @param Attribute $attribute
     * @return void
     */
    protected function saveDefaultSwatchOptionValue(Attribute $attribute)
    {
        if (!$this->swatchHelper->isSwatchAttribute($attribute)) {
            return;
        }
        $defaultValue = $attribute->getData('default/0');
        if (!empty($defaultValue)) {
            /** @var \Magento\Swatches\Model\Swatch $swatch */
            $swatch = $this->swatchFactory->create();
            // created and removed on frontend option not exists in dependency array
            if (substr($defaultValue, 0, 6) == self::BASE_OPTION_TITLE &&
                isset($this->dependencyArray[$defaultValue])
            ) {
                $defaultValue = $this->dependencyArray[$defaultValue];
            }
            $swatch->getResource()->saveDefaultSwatchOption($attribute->getId(), $defaultValue);
        }
    }

    /**
     * Save all Swatches data
     *
     * @param Attribute $attribute
     * @return void
     */
    protected function saveSwatchParams(Attribute $attribute)
    {
        if ($this->swatchHelper->isVisualSwatch($attribute)) {
            $this->processVisualSwatch($attribute);
        }
    }

    /**
     * Create links for non existed swatch options
     *
     * @param array $optionsArray
     * @param array $attributeSavedOptions
     * @return void
     */
    protected function prepareOptionLinks(array $optionsArray, array $attributeSavedOptions)
    {
        $dependencyArray = [];
        if (is_array($optionsArray['value'])) {
            $optionCounter = 1;
            foreach (array_keys($optionsArray['value']) as $baseOptionId) {
                $dependencyArray[$baseOptionId] = $attributeSavedOptions[$optionCounter]['value'];
                $optionCounter++;
            }
        }

        $this->dependencyArray = $dependencyArray;
    }

    /**
     * Creates array which link new option ids
     *
     * @param Attribute $attribute
     * @return Attribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function processSwatchOptions(Attribute $attribute)
    {
        $optionsArray = $attribute->getData('option');

        if (!empty($optionsArray) && is_array($optionsArray)) {
            $optionsArray = $this->prepareOptionIds($optionsArray);
            $attributeSavedOptions = $attribute->getSource()->getAllOptions();
            $this->prepareOptionLinks($optionsArray, $attributeSavedOptions);
        }

        return $attribute;
    }

    /**
     * Get options array without deleted items
     *
     * @param array $optionsArray
     * @return array
     */
    protected function prepareOptionIds(array $optionsArray)
    {
        if (isset($optionsArray['value']) && is_array($optionsArray['value'])) {
            foreach (array_keys($optionsArray['value']) as $optionId) {
                if (isset($optionsArray['delete']) && isset($optionsArray['delete'][$optionId])
                    && $optionsArray['delete'][$optionId] == 1
                ) {
                    unset($optionsArray['value'][$optionId]);
                }
            }
        }
        return $optionsArray;
    }

    /**
     * Save Visual Swatch data
     *
     * @param Attribute $attribute
     * @return void
     */
    protected function processVisualSwatch(Attribute $attribute)
    {
        $swatchArray = $attribute->getData('swatch/value');
        if (isset($swatchArray) && is_array($swatchArray)) {
            foreach ($swatchArray as $optionId => $value) {
                $optionId = $this->getAttributeOptionId($optionId);
                $isOptionForDelete = $this->isOptionForDelete($attribute, $optionId);
                if ($optionId === null || $isOptionForDelete) {
                    //option was deleted by button with basket
                    continue;
                }

                $swatchType = $this->determineSwatchType($value);
                $store_labels = $attribute->getData('store_labels');
//                unset($store_labels[self::DEFAULT_STORE_ID]);
                foreach($store_labels as $store=>$strs) {
                    $swatch = $this->loadSwatchIfExists($optionId, $store);
                    if(!$swatch->getData('option_id')) {
                        continue;
                    }
                    $this->saveSwatchData($swatch, $optionId, $store, $swatchType, $value);
                }

                $this->isSwatchExists = null;
            }
        }
    }

    /**
     * Load swatch if it exists in database
     *
     * @param int $optionId
     * @param int $storeId
     * @return Swatch
     */
    protected function loadSwatchIfExists($optionId, $storeId)
    {
        $collection = $this->swatchCollectionFactory->create();
        $collection->addFieldToFilter('option_id', $optionId);
        $collection->addFieldToFilter('store_id', $storeId);
        $collection->setPageSize(1);

        $loadedSwatch = $collection->getFirstItem();
        if ($loadedSwatch->getId()) {
            $this->isSwatchExists = true;
        }
        return $loadedSwatch;
    }


    /**
     * Save operation
     *
     * @param Swatch $swatch
     * @param integer $optionId
     * @param integer $storeId
     * @param integer $type
     * @param string $value
     * @return void
     */
    protected function saveSwatchData($swatch, $optionId, $storeId, $type, $value)
    {
        if ($this->isSwatchExists) {
            $swatch->setData('type', $type);
            $swatch->setData('value', $value);
        } else {
            $swatch->setData('option_id', $optionId);
            $swatch->setData('store_id', $storeId);
            $swatch->setData('type', $type);
            $swatch->setData('value', $value);
        }
        $swatch->save();
    }

    /**
     * @param string $value
     * @return int
     */
    private function determineSwatchType($value)
    {
        $swatchType = Swatch::SWATCH_TYPE_EMPTY;
        if (!empty($value) && $value[0] == '#') {
            $swatchType = Swatch::SWATCH_TYPE_VISUAL_COLOR;
        } elseif (!empty($value) && $value[0] == '/') {
            $swatchType = Swatch::SWATCH_TYPE_VISUAL_IMAGE;
        }
        return $swatchType;
    }

    /**
     * Get option id. If it not exist get it from dependency link array
     *
     * @param integer $optionId
     * @return int
     */
    protected function getAttributeOptionId($optionId)
    {
        if (substr($optionId, 0, 6) == self::BASE_OPTION_TITLE) {
            $optionId = isset($this->dependencyArray[$optionId]) ? $this->dependencyArray[$optionId] : null;
        }
        return $optionId;
    }

    /**
     * Check if is option for delete
     *
     * @param Attribute $attribute
     * @param integer $optionId
     * @return bool
     */
    protected function isOptionForDelete(Attribute $attribute, $optionId)
    {
        $isOptionForDelete = $attribute->getData('option/delete/' . $optionId);
        return isset($isOptionForDelete) && $isOptionForDelete;
    }
}
