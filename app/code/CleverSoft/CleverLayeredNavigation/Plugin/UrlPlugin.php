<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Plugin;

use CleverSoft\CleverLayeredNavigation\Helper\Url;
use Magento\Framework\UrlInterface;

class UrlPlugin
{
    /** @var  Url */
    protected $helper;

    public function __construct(Url $helper)
    {
        $this->helper = $helper;
    }

    public function afterGetUrl(UrlInterface $subject, $native)
    {
        if ($this->helper->isSeoUrlEnabled()) {
            $result = $this->helper->seofyUrl($native);
            return $result;
        } else {
            return $native;
        }
    }
}
