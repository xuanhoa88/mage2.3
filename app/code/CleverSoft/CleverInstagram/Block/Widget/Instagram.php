<?php
/**
 * @category    CleverSoft
 * @package     CleverInstagram
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

namespace CleverSoft\CleverInstagram\Block\Widget;
use \Magento\Catalog\Model\Product\Attribute\Repository;

class Instagram extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('widget/instagram.phtml');
    }

    //get all option of image
    public function getAllOptionImage(){
        $option = array();
        $option['mode'] = $this->getData('modetakeimg');
        switch($option['mode']){
            case 'userid': $option['userid'] = $this->getData('userid'); break;
            case 'hashtag': $option['hash_tag'] = $this->getData('hash_tag'); break;
            case 'location':
                $option['lat'] = $this->getData('latitude');
                $option['long'] = $this->getData('longitude');
                break;
            case 'liked': $option['liked'] = $this->getData('liked'); break;
        }
        $option['accessToken'] = $this->getData('accessToken') ? $this->getData('accessToken') : '';
        $option['openimg'] = $this->getData('openimg');
        $option['layoutwg'] = $this->getData('layoutwg');
        $option['enable_fullwidth'] = $this->getData('enable_fullwidth');
        $option['numberimage'] = $this->getData('numberimage') ? $this->getData('numberimage') : 10;
        $option['img_resolution'] = $this->getData('img_resolution');
        $option['navigation_prev'] = $this->getData('navigation_prev') ? $this->getData('navigation_prev') : 'prev';
        $option['navigation_next'] = $this->getData('navigation_next') ? $this->getData('navigation_next') : 'next';
        $option['sortby'] = $this->getData('sortby');
        $option['autoplay'] = $this->getData('autoplay') == '1' ? 'true' : 'false';
        $option['hoverpause'] = $this->getData('hoverpause') == '1' ? 'true' : 'false';
        $option['autoplaytime'] = $this->getData('autoplaytime') ? (int)$this->getData('autoplaytime')*1000 : 1000;
        $option['bullet'] = $this->getData('bullet') == '1' ? 'true' : 'false';
        $option['nbi480'] = $this->getData('nbi480') ? (int)$this->getData('nbi480') : 1;
        $option['nbi768'] = $this->getData('nbi768') ? (int)$this->getData('nbi768') : 1;
        $option['nbi990'] = $this->getData('nbi990') ? (int)$this->getData('nbi990') : 2;
        $option['nbi1200'] = $this->getData('nbi1200') ? (int)$this->getData('nbi1200') : 3;
        $option['imgwidth'] = $this->getData('imgwidth') ? (int)$this->getData('imgwidth') : 150;
        $option['customclass'] = $this->getData('customclass') ? $this->getData('customclass') : 'owlcarousel';

        return $option;
    }
}
