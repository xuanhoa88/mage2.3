/**
 * @category    CleverSoft
 * @package     CleverTheme
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

var config = {
    map: {
        '*': {
            'headerbuilder': 'CleverSoft_CleverTheme/js/header/headerbuilder',
            'footerbuilder': 'CleverSoft_CleverTheme/js/footer/footerbuilder',
            'headerbuilder-scripts': 'CleverSoft_CleverTheme/js/header/headerbuilder-scripts',
            'footerbuilder-scripts': 'CleverSoft_CleverTheme/js/footer/footerbuilder-scripts'
        }
    },
    shim:{
        "headerbuilder": ["jquery"],
        "footerbuilder": ["jquery"],
        "headerbuilder-scripts": ["jquery"],
        "footerbuilder-scripts": ["jquery"]
    }
    
};
