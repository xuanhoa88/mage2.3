/**
 * @category    CleverSoft
 * @package     CleverPinMarker
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

var config = {
    map: {
        '*': {
            pmScript: 'CleverSoft_CleverPinMarker/js/pm-script',
            owlCarousel: 'CleverSoft_CleverPinMarker/js/owl.carousel.min',
            masonryLib: 'CleverSoft_CleverPinMarker/js/masonry.pkgd.min'
        }
    },
    shim:{
        pmScript: ["jquery","jquery/ui"],
        owlCarousel: ["jquery"],
        masonryLib: ["jquery"]
    }
};
