<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\Export;

use Magento\Store\Model\System\Store;

/**
 * Store Options for Cms Pages and Blocks
 */
class StoreView extends \Magento\Store\Model\System\Store
{
    /**
     * All Store Views value
     */
    const ALL_STORE_VIEWS = '0';

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getStoreValuesForForm(false, true);
    }


}
