<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Model\Import;

use Magento\Store\Model\System\Store;

/**
 * Store Options for Cms Pages and Blocks
 */
class StoreViews extends \Magento\Store\Model\System\Store
{
    /**
     * All Store Views value
     */
    const ALL_STORE_VIEWS = '0';
    const SCOPE_DEFAULT		= 'default';
    const SCOPE_WEBSITES	= 'websites';
    const SCOPE_STORES		= 'stores';
    const SCOPE_DELIMITER	= '@';
    protected $_options;
    protected $_systemStore;

    public function __construct(Store $systemStore){
        $this->_systemStore = $systemStore;

    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getScopeSelectOptions(false, true);
    }

    /**
     * Retrieve scope values for form, compatible with form dropdown options
     *
     * @param bool
     * @param bool
     * @return array
     */
    public function getScopeSelectOptions($empty = false, $all = false)
    {
        if (!$this->_options)
        {
            $options = [];
            if ($empty)
            {
                $options[] = [
                    'label' => __('-- Please Select --'),
                    'value' => '',
                ];
            }
            if ($all)
            {
                $options[] = [
                    'label' => __('Default Config'),
                    'value' => self::SCOPE_DEFAULT . self::SCOPE_DELIMITER . '0', 'style' => 'color:#1EB5F0;',
                ];
            }

            $nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
            $storeModel = $this->_systemStore;
            /* @var $storeModel Store */

            foreach ($storeModel->getWebsiteCollection() as $website)
            {
                $websiteShow = false;
                foreach ($storeModel->getGroupCollection() as $group)
                {
                    if ($group->getWebsiteId() != $website->getId())
                    {
                        continue;
                    }
                    $groupShow = false;
                    foreach ($storeModel->getStoreCollection() as $store)
                    {
                        if ($store->getGroupId() != $group->getId())
                        {
                            continue;
                        }
                        if (!$websiteShow)
                        {
                            $options[] = [
                                'label' => $website->getName(),
                                'value' => self::SCOPE_WEBSITES . self::SCOPE_DELIMITER . $website->getId(),
                            ];
                            $websiteShow = true;
                        }
                        if (!$groupShow)
                        {
                            $groupShow = true;
                            $values    = [];
                        }
                        $values[] = [
                            'label' => str_repeat($nonEscapableNbspChar, 4) . $store->getName(),
                            'value' => self::SCOPE_STORES . self::SCOPE_DELIMITER . $store->getId(),
                        ];
                    } //end: foreach store
                    if ($groupShow)
                    {
                        $options[] = [
                            'label' => str_repeat($nonEscapableNbspChar, 4) . $group->getName(),
                            'value' => $values,
                        ];
                    }
                } //end: foreach group
            } //end: foreach website

            $this->_options = $options;
        }
        return $this->_options;
    }

}
