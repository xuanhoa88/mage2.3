<?php
/**
 * @category    CleverSoft
 * @package     CleverSocialLogin
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverSocialLogin\Model\Config\Source;

class RedirectTo implements \Magento\Framework\Option\ArrayInterface
{

    protected $_options = null;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_getOptions();
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $options = [];
        foreach ($this->_getOptions() as $option) {
            $options[ $option['value'] ] = $option['label'];
        }

        return $options;
    }

    protected function _getOptions()
    {
        if(null === $this->_options) {
            $options = [
                ['value' => '__referer__',     'label' => __('Stay on the current page')],
                ['value' => '__custom__',      'label' => __('Redirect to Custom URL')],
                ['value' => '__none__',        'label' => __('---')],
                ['value' => '__dashboard__',   'label' => __('Customer -> Account Dashboard')],
            ];

            $items = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Cms\Model\Page')
                ->getCollection()
                ->getItems();

            foreach ($items as $item) {
                if($item->getId() == 1) continue;
                $options[] = ['value' => $item->getId(), 'label' => __($item->getTitle())];
            }

            $this->_options = $options;
        }

        return $this->_options;
    }

}