/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

var config = {
    map: {
        '*': {
            cleverSwatchRenderer : 'CleverSoft_CleverTheme/js/clever-product-renderer',
            cleverSwatchRendererProductDetails : 'CleverSoft_CleverTheme/js/clever-product-renderer',
            stickyCustom : 'CleverSoft_CleverTheme/js/clever-product-renderer',
            cleverJsTheme : 'CleverSoft_CleverTheme/js/theme',
            'magnifier/magnifier' : 'CleverSoft_CleverTheme/js/magnifier'
        },
        shim:{
            "cleverJsTheme": ["jquery"]
        }
    }

};