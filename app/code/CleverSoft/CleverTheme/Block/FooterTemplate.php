<?php
/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverTheme\Block;


use Symfony\Component\Config\Definition\Exception\Exception;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Page\Config;

class FooterTemplate extends \Magento\Framework\View\Element\Template
{
    public $_coreRegistry;

    protected $_blockCollection;
    protected $_pageConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockCollection,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \CleverSoft\CleverTheme\Model\ResourceModel\FooterBlock\CollectionFactory $collectionFactory,
        \CleverSoft\CleverTheme\Helper\FooterData $footerData,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_blockCollection = $blockCollection;
        $this->_pageConfig = $context->getPageConfig();
        $this->_objectManager = $objectmanager;
        $this->_collectionFactory = $collectionFactory;
        $this->_footerData = $footerData;
        parent::__construct($context, $data);
        $this->addBodyClassLayout();
    }

    public function getConfig($config_path, $storeCode = null)
    {
        return $this->_scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }

    public function addBodyClassLayout() {
        $pageLayout = $this->getConfig('cleversofttheme/responsive/page_layout');
        $maxWidthpage = $this->getConfig('cleversofttheme/responsive/page_maxi_width');
        $rtl_language = $this->getConfig('cleversofttheme/responsive/rtl_language');
        if ($pageLayout) {
            $this->_pageConfig->addBodyClass($pageLayout);
        }
        if ($maxWidthpage) {
            $this->_pageConfig->addBodyClass('layout-'.$maxWidthpage);
        }

        if ($rtl_language) {
            $this->_pageConfig->addBodyClass('rtl');
        }
    }

    public function cssTemplate($content,$enable) {
        if (!$enable || empty($content)) return '';
        else $content = str_replace(PHP_EOL, '', $content);
        return <<<HTML
<style>
$content;
</style>
HTML;
    }

    public function getBlocks(){
        $blocks     = [];
        $layout     = $this->getLayout();
        $storeId    = $this->_storeManager->getStore()->getId();

        $classes    = [];
        $order      = [];


        foreach (array('xl', 'lg', 'md', 'sm') as $l){
            if (count(explode('|', $this->getConfig('cleversofttheme/footer/block_' . $l)) > 0) && $this->getConfig('cleversofttheme/footer/block_' . $l)) {
                foreach (explode('|', $this->getConfig('cleversofttheme/footer/block_' . $l)) as $block) {
                    list($blockId, $column, $cls) = explode(',', $block);

                    if (!isset($classes[$blockId])) {
                        $classes[$blockId] = "col-{$l}-{$column} ";
                    } else {
                        $classes[$blockId] .= "col-{$l}-{$column} ";
                    }
                    $classes[$blockId] .= "{$cls} ";

                    if (!in_array($blockId, $order)) {
                        $order[] = $blockId;
                    }
                }
            }
        }

        foreach ($order as $blockId){
            /* @var $collection Mage_Cms_Model_Resource_Block_Collection */
            $collection = $this->_blockCollection->create()->addFieldToFilter('block_id', array('eq' => $blockId));

            if ($collection->count()){
                /* @var $block Mage_Cms_Model_Block */
                $block = $collection->getFirstItem();
                $block->load($block->getId());
                $storeIds = $block->getStoreId();
                if ($block->getIsActive() && (in_array($storeId, $storeIds) || in_array(0, $storeIds))){
                    $blocks[] = array(
                        'class'     => isset($classes[$blockId]) ? $classes[$blockId] : '',
                        'content'   => $layout->createBlock('Magento\Cms\Block\Block')->setStoreId()->setBlockId($blockId)->toHtml()
                    );
                }
            }
        }

        return $blocks;
    }

    public function renderCollection($template='footer.phtml'){
        /* @var $block CleverProduct_Block_Widget_Collection */
        $this->setData(array(
            'full_footer_width'            => $this->getConfig('cleversofttheme/footer/full_footer_width'),
            'compare'            => $this->getConfig('cleversofttheme/footer/compare'),
            'wishlist'            => $this->getConfig('cleversofttheme/footer/wishlist')
        ));
        $this->setTemplate($template);

        return $this->toHtml();
    }

    public function isHomePage()
    {
        $currentUrl = $this->getUrl('', ['_current' => true]);
        $urlRewrite = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        return $currentUrl == $urlRewrite;
    }

    public function getMediaUrl(){
        return $this->_urlBuilder
            ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]);
    }

    public function getSettingData() {
        return $this->_footerData->getSettingData();
    }

    public function getFooterDesktop() {
        $settings = $this->getSettingData();
        $storeId = $this->_storeManager->getStore()->getId();
        if ($this->_collectionFactory->create()->addFieldToFilter('store_id',$storeId)->getFirstItem()->getId() && isset($settings->{'onoffswitch-footerbuilder'}) && $settings->{'onoffswitch-footerbuilder'}) {
            return $this->_collectionFactory->create()->addFieldToFilter('store_id',$storeId)->getFirstItem()->getFooterDesktopData();
        }
        return $this->_collectionFactory->create()->addFieldToFilter('store_id',0)->getFirstItem()->getFooterDesktopData();
    }

    public function getFooterMobile() {
        $settings = $this->getSettingData();
        $storeId = $this->_storeManager->getStore()->getId();
        if ($this->_collectionFactory->create()->addFieldToFilter('store_id',$storeId)->getFirstItem()->getId() && isset($settings->{'onoffswitch-footerbuilder'}) && $settings->{'onoffswitch-footerbuilder'}) {
            return $this->_collectionFactory->create()->addFieldToFilter('store_id',$storeId)->getFirstItem()->getFooterMobileData();
        }
        return $this->_collectionFactory->create()->addFieldToFilter('store_id',0)->getFirstItem()->getFooterMobileData();
    }

    public function isMobile() {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }

    public function renderRow($items, $id = '', $device = 'desktop') {
        $row_html    = '';
        $max_columns = 12;
        $last_item = false;
        $prev_item = false;
        $group_items = array();
        $gi          = 0;
        $n           = count( $items );
        $index       = 0;
        $space = $device == "desktop" ? "lg" : "sm";
        ob_start();

        while ( $index < $n ) {
            $item = $items[ $index ];
            if ( $gi < 0 ) {
                $gi = 0;
            }

            if ( $n > $index + 1 ) {
                $next_item = $items[ $index + 1 ];
            } else {
                $next_item = false;
            }

            $item_id    = $item->id;
            if ($device == "desktop") {
                $merge_key  = "_customize_footer_".$item_id."_merge_item";
            } else {
                $merge_key  = "_customize_footer_".$item_id."_merge_item_mobile";
            }

            $merge      = isset($this->_footerData->getSettingData()->{$merge_key}) && $this->_footerData->getSettingData()->{$merge_key} ? $this->_footerData->getSettingData()->{$merge_key} : "";

            $merge_next = false;
            $merge_prev = false;
            if ( $merge == 'no' || $merge == '0' ) {
                $merge = false;
            }

            if ( $next_item ) {
                if ($device == "desktop") {
                    $merge_key_next = "_customize_footer_".$next_item->id."_merge_item";
                } else {
                    $merge_key_next = "_customize_footer_".$next_item->id."_merge_item_mobile";
                }
                $merge_next     = isset($this->_footerData->getSettingData()->{$merge_key_next}) && $this->_footerData->getSettingData()->{$merge_key_next} ? $this->_footerData->getSettingData()->{$merge_key_next} : "";
            }

            if ( $merge_next == 'no' || $merge_next == '0' ) {
                $merge_next = false;
            }

            if ( $prev_item ) {
                $merge_prev = $prev_item->{'__merge'};
            }


            if (
                (!$merge_prev || $merge_prev == 'prev')
                && (!$merge || $merge == 'next')
                && (!$merge_next || $merge_next == 'next')
            ) {
                $gi++;
            } elseif (
                (!$merge_prev || $merge_prev == 'prev')
                && ($merge == 'next')
                && (!$merge_next || $merge_next == 'prev')
            ) {
                $gi++;
            }


            $prev_item            = $item;
            $prev_item->{'__merge'} = $merge;

            if ( ! isset( $group_items[ $gi ] ) ) {
                $group_items[ $gi ]          = $item;
                $group_items[ $gi ]->items   = array();
                $group_items[ $gi ]->items[] = $prev_item;
            } else {
                $group_items[ $gi ]->width   = ( $item->x + $item->width ) - $group_items[ $gi ]->x;
                $group_items[ $gi ]->items[] = $prev_item;
            }

            if ( $index == 0 && ( ! $merge || $merge == 'prev' ) && ( ! $merge_next || $merge_next == 'next' ) ) {
                $gi ++;
            }

            $index ++;
        }

        $index = 0;
        $number_group_item = count( $group_items );

        foreach ( $group_items as $item ) {
            if ( isset( $items[ $index + 1 ] ) ) {
                $next_item = $items[ $index + 1 ];
            } else {
                $next_item = false;
            }

            $first_id = $item->id;
            $x        = intval( $item->x );
            $width    = intval( $item->width );

            $classes = array();
            if ($id == "sidebar") {
                $classes[] = "mobile-sidebar__content";
            } else {
                if (($item->items)[0]->{'__merge'} == "next") {
                    $classes[] = "col-auto row-ml-auto";
                } elseif (($item->items)[count($item->items) - 1]->{'__merge'} == "prev") {
                    $classes[] = "col-auto";
                } else {
                    $classes[] = "col-{$space}-{$width}";
                }
            }
            
            if ($x > 0) {
                if (!$last_item) {
                    $classes[] = 'offset-' . $x;
                } else {
                    $o = intval($last_item->width) + intval($last_item->x);
                    if ($x - $o > 0) {
                        $classes[] = 'offset-' . ($x - $o);
                    }
                }
            }

            $classes = join( ' ', $classes );

            $row_items_html = '';
            $item_id = '';
            foreach ( $item->items as $_it ) {
                if(isset($_it->id)) {
                    if ($device == 'desktop') {
                        $item_id = $_it->id;
                    } else {
                        $item_id = $_it->id . "-" . $device;
                    }
                }
                $content     = $this->getChildHtml($item_id);
                if ( $content ) {
                    $row_items_html .= $content;
                }
            }
            if ( $row_items_html ) {
                echo '<div class="' . $classes . '">';
                echo $row_items_html;
                echo '</div>';
            }

            $last_item = $item;
            $index ++;

        }// end loop items

        // Get item output
        $row_html = ob_get_clean();
        return $row_html;
    }
}