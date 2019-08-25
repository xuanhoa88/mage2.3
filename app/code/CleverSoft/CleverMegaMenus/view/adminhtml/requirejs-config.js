/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author      ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

var config = {
    map: {
        '*': {
            cleverMenuAdminhtml: 'CleverSoft_CleverMegaMenus/js/menu',
            cleverButtonElement: 'CleverSoft_CleverMegaMenus/js/button-element',
            cleverMenuJqueryUi: "CleverSoft_CleverMegaMenus/js/jquery-ui.min",
            createCategoryChooser: "CleverSoft_CleverMegaMenus/js/category-chooser",
            icons: "CleverSoft_CleverMegaMenus/js/icons"
        }
    },
    shim:{
        "CleverSoft_CleverMegaMenus/js/menu": ["cleverMenuJqueryUi","createCategoryChooser"],
        "CleverSoft_CleverMegaMenus/js/button-element": ["jquery"],
        "CleverSoft_CleverMegaMenus/js/icons": ["jquery"],
        "CleverSoft_CleverMegaMenus/js/category-chooser": ["jquery"],
        "CleverSoft_CleverMegaMenus/js/jquery-ui.min": ["jquery/jquery-ui","jquery/ui"]
    }
};
