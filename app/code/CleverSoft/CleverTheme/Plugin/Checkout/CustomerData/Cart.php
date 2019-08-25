<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Plugin\Checkout\CustomerData;

class Cart
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var \Magento\Tax\Block\Item\Price\Renderer
     */
    protected $itemPriceRenderer;

    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    protected $quote = null;

    /**
     * @var array|null
     */
    protected $totals = null;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Tax\Block\Item\Price\Renderer $itemPriceRenderer
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Tax\Block\Item\Price\Renderer $itemPriceRenderer,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->checkoutHelper = $checkoutHelper;
        $this->itemPriceRenderer = $itemPriceRenderer;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->_objectManager = $objectmanager;
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * Add tax data to result
     *
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject,  $result )
    {
        $result['subtotal_header'] = '<div class="total-cart-header">' . $this->checkoutHelper->formatPrice($this->getSubtotalHeader()) . '</div>';
        $rule = $this->getSalesRulesFreeShip();
        if (!empty($rule)) {
        	$result['neededTotal'] =  $this->getNeededTotal();
            $result['neededTotalAmount'] =  $this->getNeededTotalAmount();
        	$result['currentPercent'] = $this->getCurrentPercent();
        	$result['ruleFreeShip'] = !empty($this->getSalesRulesFreeShip()) ? true : false;
        }

        return $result;
    }

    /**
     * Get subtotal, including tax
     *
     * @return float
     */
    protected function getSubtotalHeader()
    {
        $subtotal = 0;
        $totals = $this->getTotals();
        if (isset($totals['subtotal'])) {
            $subtotal = $totals['subtotal']->getValueInclTax() ?: $totals['subtotal']->getValue();
        }
        return $subtotal;
    }

    protected function getSalesRulesFreeShip() {
        $collection = $this->_collectionFactory->create();
        $rules = array();
        foreach ($collection as $rule) {
            if($rule->getData('is_active') && $rule->getSimpleFreeShipping() == 2 ) {
            	$condition = $rule->getConditionsSerialized();
            	if (isset($this->serializer->unserialize($condition)['conditions'])) {
                	$rules[$rule->getSortOrder()] = $rule;
                }
            }
        }
        if (count($rules)) {
            return reset($rules);
        }
        return false;
    }

    protected function getNeededTotal() {
        $rule = $this->getSalesRulesFreeShip();
        if (!empty($rule)) {
            $quote = $this->getQuote();
            $condition = $rule->getConditionsSerialized();
            $conditionSubtotal = $this->serializer->unserialize($condition)['conditions'][0]['value'];
            $currentSubtotal = $quote->getBaseSubtotal();
            if ($currentSubtotal < $conditionSubtotal) {
                $currency = $this->_objectManager->get('Magento\Directory\Model\Currency');
                return $currency->format($conditionSubtotal - $currentSubtotal, ['display'=>\Zend_Currency::USE_SYMBOL], true);
            }
        }
        return 0;
    }
    protected function getNeededTotalAmount() {
        $rule = $this->getSalesRulesFreeShip();
        if (!empty($rule)) {
            $quote = $this->getQuote();
            $condition = $rule->getConditionsSerialized();
            $conditionSubtotal = $this->serializer->unserialize($condition)['conditions'][0]['value'];
            $currentSubtotal = $quote->getBaseSubtotal();
            
            if ($currentSubtotal < $conditionSubtotal) {
                $currency = $this->_objectManager->get('Magento\Framework\Pricing\Helper\Data');
                return $currency->currency($conditionSubtotal - $currentSubtotal, true, false);
            } else {
                return 0;
            }
        }
        return 0;
    }
    protected function getCurrentPercent() {
        $rule = $this->getSalesRulesFreeShip();
        if (!empty($rule)) {
            $quote = $this->getQuote();
            $condition = $rule->getConditionsSerialized();
            $conditionSubtotal = $this->serializer->unserialize($condition)['conditions'][0]['value'];
            $currentSubtotal = $quote->getBaseSubtotal();

            if ($currentSubtotal > 0 && $currentSubtotal < $conditionSubtotal) {
                return ($currentSubtotal / $conditionSubtotal) * 100;
            } else {
                return 100;
            }
        } else {
            return 100;
        }
    }



    /**
     * Get totals
     *
     * @return array
     */
    public function getTotals()
    {
        // TODO: TODO: MAGETWO-34824 duplicate \Magento\Checkout\CustomerData\Cart::getSectionData
        if (empty($this->totals)) {
            $this->totals = $this->getQuote()->getTotals();
        }
        return $this->totals;
    }

    /**
     * Get active quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }

    /**
     * Find item by id in items haystack
     *
     * @param int $id
     * @param array $itemsHaystack
     * @return \Magento\Quote\Model\Quote\Item | bool
     */
    protected function findItemById($id, $itemsHaystack)
    {
        if (is_array($itemsHaystack)) {
            foreach ($itemsHaystack as $item) {
                /** @var $item \Magento\Quote\Model\Quote\Item */
                if ((int)$item->getItemId() == $id) {
                    return $item;
                }
            }
        }
        return false;
    }
}
