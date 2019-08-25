<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CleverSoft\CleverBuilder\Model\Config\Source\Wysiwyg;

/**
 * Configuration source model for Wysiwyg toggling
 */
class Enabled implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => \CleverSoft\CleverBuilder\Model\Wysiwyg\Config::WYSIWYG_ENABLED, 'label' => __('Enabled by Default')],
            ['value' => \CleverSoft\CleverBuilder\Model\Wysiwyg\Config::WYSIWYG_HIDDEN, 'label' => __('Disabled by Default')],
            ['value' => \CleverSoft\CleverBuilder\Model\Wysiwyg\Config::WYSIWYG_DISABLED, 'label' => __('Disabled Completely')]
        ];
    }
}
