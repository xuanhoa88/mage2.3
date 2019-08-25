<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Plugin;

use CleverSoft\CleverLayeredNavigation\Model\Layer\Filter\Attribute;
use CleverSoft\CleverLayeredNavigation\Helper\Content;

class AttributeFilterPlugin
{
    /** @var  Content */
    protected $contentHelper;

    public function __construct(Content $contentHelper)
    {
        $this->contentHelper = $contentHelper;
    }

    /**
     * @param Attribute $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsVisibleWhenSelected(Attribute $subject, $result)
    {
        if ($result) {
            if ($this->isBrandingBrand($subject)) {
                $result = false;
            }
        }

        return $result;
    }

    public function afterShouldAddState(Attribute $subject, $result)
    {
        if ($result) {
            if ($this->isBrandingBrand($subject)) {
                $result = false;
            }
        }

        return $result;
    }

    protected function isBrandingBrand(Attribute $subject)
    {
        $brand = $this->contentHelper->getCurrentBranding();
        return $brand && ('attr_' . $subject->getRequestVar() == $brand->getFilterCode());
    }
}
