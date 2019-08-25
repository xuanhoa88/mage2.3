<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Helper;


use CleverSoft\CleverLayeredNavigation\Api\Data\FilterSettingInterface;
use Magento\Catalog\Model\Layer;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use CleverSoft\CleverLayeredNavigation\Model\ResourceModel\FilterSetting\CollectionFactory;

class Dataseo extends AbstractHelper
{
    /** @var CollectionFactory */
    protected $settingCollectionFactory;
    /** @var Option\CollectionFactory */
    protected $optionCollectionFactory;

    /** @var  \Magento\Catalog\Model\Product\Url */
    protected $productUrl;

    protected $seoSignificantUrlParameters;
    protected $optionsSeoData;

    public function __construct(
        Context $context,
        CollectionFactory $settingCollectionFactory,
        Option\CollectionFactory $optionCollectionFactory,
        \Magento\Catalog\Model\Product\Url $productUrl,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    )
    {
        parent::__construct($context);
        $this->settingCollectionFactory = $settingCollectionFactory;
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->productUrl = $productUrl;
        $this->_objectManager = $objectManager;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
    }

    public function getSeoSignificantUrlParameters()
    {
        if (is_null($this->seoSignificantUrlParameters)) {
            $this->seoSignificantUrlParameters = $this->getSeoSignificantAttributeCodes();
        }
        return $this->seoSignificantUrlParameters;
    }

    public function getOptionsSeoData()
    {
        if (is_null($this->optionsSeoData)) {
            $seoAttributeCodes = $this->getSeoSignificantAttributeCodes();

            $collection = $this->optionCollectionFactory->create();
            $collection->join(['a' => 'eav_attribute'], 'a.attribute_id = main_table.attribute_id', ['attribute_code']);
            $collection->addFieldToFilter('attribute_code', ['in' => $seoAttributeCodes]);
            $collection->setStoreFilter();
            $select = $collection->getSelect();

            $statement = $select->query();
            $rows = $statement->fetchAll();
            $this->optionsSeoData = [];
            $existOption = [];
            $aliasHash = [];
            foreach ($rows as $row) {
                $alias = $this->buildUniqueAlias($row['value'], $aliasHash);
                $optionId = $row['option_id'];
                $this->optionsSeoData[$optionId] = [
                    'alias' => $alias,
                    'attribute_code' => $row['attribute_code'],
                ];
                $aliasHash[$alias] = $optionId;
                $existOption[] = $optionId;
            }

            if(in_array('category_ids', $seoAttributeCodes)) {
                $categories = $this->_categoryCollectionFactory->create();
                if(count($categories)) {
                    foreach($categories as $category) {
                        $model = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($category->getEntityId());
                        $alias = $this->buildUniqueAlias($model->getName(), $aliasHash);
                        if (!in_array($model->getEntityId(), $existOption)) {
                            $this->optionsSeoData[$model->getEntityId()] = [
                                'alias' => $alias,
                                'attribute_code' => 'cat',
                            ];
                            $aliasHash[$alias] = $model->getEntityId();
                        }
                    }
                }
            }
        }
        
        return $this->optionsSeoData;
    }

    protected function getSeoSignificantAttributeCodes()
    {
        $collection = $this->settingCollectionFactory->create();
        $collection->addFieldToFilter(FilterSettingInterface::IS_SEO_SIGNIFICANT, 1);
        $filterCodes = $collection->getColumnValues(FilterSettingInterface::FILTER_CODE);
        array_walk($filterCodes, function (&$code) {
            if (substr($code, 0, 5) == 'attr_') {
                $code = substr($code, 5);
            }
        });
        return $filterCodes;
    }

    protected function buildUniqueAlias($value, $hash)
    {
        if (preg_match('@^[\d\.]+$@s', $value)) {
            $format = $value;
        } else {
            $format = $this->productUrl->formatUrlKey($value);
        }
        if ($format == '') {
            // Magento formats '-' as ''
            $format = '-';
        }

        $unique = $format;
        for ($i=1; array_key_exists($unique, $hash); $i++) {
            $unique = $format . '-' . $i;
        }
        return $unique;
    }
}
