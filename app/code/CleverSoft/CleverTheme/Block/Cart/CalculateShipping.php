<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Block\Cart;

class CalculateShipping extends \Magento\Checkout\Block\Cart\AbstractCart
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory,
        array $layoutProcessors = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        array $data = []
    )
    {
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->_objectManager = $objectmanager;
        $this->_collectionFactory = $collectionFactory;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    public function getSalesRulesFreeShip() {
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

    public function getNeededTotal() {
        $rule = $this->getSalesRulesFreeShip();
        if (!empty($rule)) {
            $quote = $this->getQuote();
            $condition = $rule->getConditionsSerialized();
            $conditionSubtotal = $this->serializer->unserialize($condition)['conditions'][0]['value'];
            $currentSubtotal = $quote->getBaseSubtotal();
            
            if ($currentSubtotal < $conditionSubtotal) {
                $currency = $this->_objectManager->get('Magento\Directory\Model\Currency');
                return $currency->format($conditionSubtotal - $currentSubtotal, ['display'=>\Zend_Currency::USE_SYMBOL], true);
            } else {
                return 0;
            }
        }
        return 0;
    }
    public function getCurrentPercent() {
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

}