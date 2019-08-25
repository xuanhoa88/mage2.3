<?php
namespace CleverSoft\CleverShopByBrand\Block\Navigation;

class State extends \Magento\LayeredNavigation\Block\Navigation\State
{
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\CleverSoft\CleverShopByBrand\Model\Layer\Resolver $layerResolver,
		array $data = []
	) {
		parent::__construct($context, $layerResolver, $data);
	}
}
