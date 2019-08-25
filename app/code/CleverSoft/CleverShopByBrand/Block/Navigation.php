<?php
namespace CleverSoft\CleverShopByBrand\Block;

class Navigation extends \Magento\LayeredNavigation\Block\Navigation
{
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\CleverSoft\CleverShopByBrand\Model\Layer\Resolver $layerResolver,
		\Magento\Catalog\Model\Layer\FilterList $filterList,
		\Magento\Catalog\Model\Layer\AvailabilityFlagInterface $visibilityFlag,
		array $data = []
	) {
		parent::__construct($context, $layerResolver, $filterList,
			$visibilityFlag);
	}
}
?>
