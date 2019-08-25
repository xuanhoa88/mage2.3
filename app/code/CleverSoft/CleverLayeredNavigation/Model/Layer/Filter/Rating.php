<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Model\Layer\Filter;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Framework\View\Element\BlockFactory;

/**
 * Layer category filter
 */
class Rating extends AbstractFilter
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $attributeValue;

    protected $attributeCode = 'rating_summary';

    /** @var  BlockFactory */
    protected $blockFactory;

    protected $stars = array(
        1 => 20,
        2 => 40,
        3 => 60,
        4 => 80,
        5 => 100,
        //6 => -1
    );

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        BlockFactory $blockFactory,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->_requestVar = 'rating';
        $this->scopeConfig = $scopeConfig;
        $this->blockFactory = $blockFactory;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $filter = (int) $request->getParam($this->getRequestVar(), 0);
        if(!isset($this->stars[$filter])) {
            return $this;
        }

        $this->attributeValue = $filter;

        $condition = $this->stars[$filter];
        if($filter == 6) {
            $condition = new \Zend_Db_Expr("IS NULL");
        }


        $this->getLayer()->getProductCollection()->addFieldToFilter('rating_summary', $condition);
        if($filter < 5) {
            $name = __('%1 stars & up', $filter);
        } elseif($filter == 1) {
            $name = __('%1 star & up', $filter);
        } else {
            $name = __('%1 stars', $filter);
        }

        $item = $this->_createItem($name, $filter);

        $this->getLayer()->getState()->addFilter($item);
        return $this;
    }

    /**
     * Get filter name
     *
     * @return \Magento\Framework\Phrase
     */
    public function getName()
    {
        $label = $this->scopeConfig->getValue('clevershopby/rating_filter/label', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $label;
    }

    public function getPosition()
    {
        $position = (int) $this->scopeConfig->getValue('clevershopby/rating_filter/position', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $position;
    }

    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $productCollectionOrigin = $this->getLayer()
            ->getProductCollection();

        if($this->attributeValue  && $productCollectionOrigin->_memRequestBuilder){
            $productCollection = clone $productCollectionOrigin;
            $requestBuilder = clone $productCollection->_memRequestBuilder;
            $requestBuilder->removePlaceholder($this->attributeCode);
            $productCollection->setRequestData($requestBuilder);
            $productCollection->clear();
            $productCollection->loadWithFilter();
            $collection = $productCollection;
        }else{
            $collection = $productCollectionOrigin;
        }
        $optionsFacetedData = $collection->getFacetedData($this->attributeCode);

        $listData = [];

        $allCount = 0;
        for ($i = 5; $i >= 1; $i--) {
            $count = isset($optionsFacetedData[$i]) ? $optionsFacetedData[$i]['count'] : 0;

            $allCount += $count;

            $listData[] = array(
                'label' => $this->getLabelHtml($i),
                'value' => $i,
                'count' => $allCount,
                'real_count' => $count,
            );
        }

        /*$listData[] = array(
            'label' => $this->getLabelHtml(6),
            'value' => 6,
            'count' => $count[5],
            'real_count' => $count[5],
        );*/

        foreach ($listData as $data) {
            if($data['real_count'] < 1) {
                continue;
            }
            $this->itemDataBuilder->addItemData(
                $data['label'],
                $data['value'],
                $data['count']
            );
        }

        return $this->itemDataBuilder->build();
    }

    /**
     * @param int $countStars
     *
     * @return string
     */
    protected function getLabelHtml($countStars)
    {
        if($countStars == 6) {
            return __('Not Yet Rated');
        }
        /** @var \Magento\Framework\View\Element\Template $block */
        $block = $this->blockFactory->createBlock('\Magento\Framework\View\Element\Template');
        $block->setTemplate('CleverSoft_CleverLayeredNavigation::layer/filter/item/rating.phtml');
        $block->setData('star', $countStars);
        $html = $block->toHtml();
        return $html;
    }
}
