<?php
/**
 * @category    CleverSoft
 * @package     CleverLayeredNavigation
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */
namespace CleverSoft\CleverBuilder\Helper\Ajax;

class AjaxBuilderContent extends \CleverSoft\CleverBuilder\Helper\Panels\Panels {

    public function getHtml(){
        $post = $this->_getRequest()->getParams();
        if ( empty( $post['page_id'] ) || empty( $post['panels_data'] ) ) {
            echo '';
            die();
        }

        // echo the content
        $old_panels_data        = $this->getDataHelper()->getPanelsData(); // get from database
        $panels_data            = json_decode( $post['panels_data'] , true );
        $panels_data['widgets'] = $this->getDataHelper()->process_raw_widgets(
            $panels_data['widgets'],
            ! empty( $old_panels_data['widgets'] ) ? $old_panels_data['widgets'] : false,
            false
        );
        $panels_data            = $this->sanitize_all( $panels_data );

        // Create a version of the builder data for post content
//        CleverSoft_Panels_Post_Content_Filters::add_filters();//will do in render function
        $GLOBALS[ 'CLEVERSOFT_PANELS_POST_CONTENT_RENDER' ] = true;
        echo $this->renderer()->render( intval( $post['page_id'] ), false, $panels_data );
//        CleverSoft_Panels_Post_Content_Filters::remove_filters();
        unset( $GLOBALS[ 'CLEVERSOFT_PANELS_POST_CONTENT_RENDER' ] );
        die();
    }
}

