<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
/**
 * Renderer for sub-heading in fieldset
 */

namespace CleverSoft\CleverTheme\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\AbstractBlock;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class Heading
    extends AbstractBlock implements RendererInterface
{
    /**
     * Render element html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        return sprintf(
            '<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5" style="max-width:580px;"><h4 id="%s">%s</h4><div class="subheading-note" style="font-size:12px;font-style:italic;color:#a5a5a5;padding-left:2.7rem;padding-bottom:5px;"><span>%s</span></div></td></tr>',
            $element->getHtmlId(), $element->getHtmlId(), $element->getLabel(), $element->getComment()
        );
    }
}
