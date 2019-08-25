<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Block\Product\View;

use Magento\Framework\Data\Collection;
use Magento\Framework\Json\EncoderInterface;
use Magento\Catalog\Helper\Image;

class Gallery extends \Magento\Catalog\Block\Product\View\Gallery
{
    protected $_matoHelper;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        \CleverSoft\CleverTheme\Helper\Data $matoHelper,
        array $data = []
    ) {
        $this->_matoHelper = $matoHelper;
        parent::__construct($context, $arrayUtils, $jsonEncoder, $data);
    }


    public function getLabel(){
        return $this->_matoHelper->getLabel($this->getProduct());
    }
}
