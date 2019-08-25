<?php

namespace CleverSoft\CleverCookieLaw\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Configuration provider for GiftMessage rendering on "Shipping Method" step of checkout.
 */
class IntegratesConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfiguration;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration
     * @param \Magento\Framework\Escaper $escaper
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration,
        \CleverSoft\CleverCookieLaw\Helper\Data $cookieHelper,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->scopeConfiguration = $scopeConfiguration;
        $this->_cookieHelper = $cookieHelper;
        $this->escaper = $escaper;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $integrates = [];
        $integrates['checkoutIntegrates'] = $this->getIntegratesConfig();
        return $integrates;
    }

    /**
     * Returns agreements config
     *
     * @return array
     */
    protected function getIntegratesConfig()
    {
        $integrateConfiguration = [];
        
        if ($this->_cookieHelper->getSystemConfig('integration', 'checkout') == 1) {
            $integrateConfiguration['isEnabled'] = true;
        } else {
            $integrateConfiguration['isEnabled'] = false;
        }

        $integrateConfiguration['integrates'][] = [
            'checkboxText' => $this->_cookieHelper->getSystemConfig('integration', 'checkout_text'),
            'integrateId' => '1'
        ];

        return $integrateConfiguration;
    }
}
