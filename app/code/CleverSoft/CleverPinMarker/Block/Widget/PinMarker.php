<?php 

namespace CleverSoft\CleverPinMarker\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface; 
use Magento\Framework\App\ActionInterface;
 
class PinMarker extends \Magento\Catalog\Block\Product\AbstractProduct implements BlockInterface
{
    protected $_template = "widget/pinmarker.phtml";
    
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->urlHelper = $urlHelper;
        parent::__construct(
            $context,
            $data
        );
    }

    public function getPinInfo($id) {
        return json_decode($this->getPinMarker($id)->getData('wpa_pin'));
    }

    public function getPinMarker($id) {
        return $this->_objectManager->create('CleverSoft\CleverPinMarker\Model\PinMarker')->load($id);
    }

    public function getPinIds() {
        return explode(",", $this->_objectManager->create('CleverSoft\CleverPinMarker\Model\PinCollection')->load($this->getData('collection_pin'))->getPinIds());
    }

    public function getProductById($id) {
        return $this->_objectManager->create('Magento\Catalog\Model\Product')->load($id);
    }

        /**
     * Get post parameters
     *
     * @param Product $product
     * @return string
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }

    /**
     * @return string
     */
    public function getColorSwatchDetailsHtml($product)
    {
        if($product->getTypeId() != 'configurable') return '';
        $block = $this->getLayout()->createBlock('CleverSoft\CleverTheme\Block\Product\Renderer\Configurable');
        $block->setProduct($product);

        return $block->toHtml();
    }

    public function getCollectionData($data) {
        $collectionData = $this->_objectManager->create('CleverSoft\CleverPinMarker\Model\PinCollection')->load($this->getData('collection_pin'));
        return $collectionData->getData($data);
    }
}