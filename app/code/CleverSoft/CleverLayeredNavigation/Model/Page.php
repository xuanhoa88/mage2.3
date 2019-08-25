<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverLayeredNavigation\Model;

use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class Page
 *
 * @author Artem Brunevski
 */

class Page extends AbstractExtensibleModel
{
    /**
     * Position of placing meta data in category
     */
    const POSITION_REPLACE = 'replace';
    const POSITION_AFTER = 'after';
    const POSITION_BEFORE = 'before';

    const CATEGORY_FORCE_MIXED_MODE = 'clevershopby_force_mixed_mode';
    const CATEGORY_FORCE_USE_CANONICAL = 'clevershopby_force_use_canonical';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('CleverSoft\CleverLayeredNavigation\Model\ResourceModel\Page');
    }

    /**
     * @return array|mixed
     */
    public function getConditionsUnserialized()
    {
        try {
            $ret = unserialize($this->getConditions());
        } catch (\Exception $e) {
            $ret = [];
        }
        if (!is_array($ret)){
            $ret = [];
        }
        
        $ret = array_values($ret); //rewrite array keys to ordinal

        return $ret;
    }

    /**
     * @return mixed
     */
    public function saveStores()
    {
        return $this->getResource()->saveStores($this);
    }
}
