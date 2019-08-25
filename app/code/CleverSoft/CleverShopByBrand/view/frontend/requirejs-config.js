/**
 * Copyright © 2016 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            "cleverBrands": 'CleverSoft_CleverShopByBrand/js/brands',
            "owlCarousel": 'CleverSoft_CleverShopByBrand/js/owl.carousel.min',
            "jqueryAutocomplete" : 'CleverSoft_CleverShopByBrand/js/jquery-autocomplete'
        }
    },
	shim:{
        "cleverBrands": ["jquery"],
        "flexsliderJS": ["jquery"],
        "owlCarousel": ["jquery"]
	}
};
