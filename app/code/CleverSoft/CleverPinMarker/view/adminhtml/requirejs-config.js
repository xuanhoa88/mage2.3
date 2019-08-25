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
            pmLoadScripts: 'CleverSoft_CleverPinMarker/js/load-scripts',
            pmColorPicker: 'CleverSoft_CleverPinMarker/js/color-picker.min',
            pmIris: "CleverSoft_CleverPinMarker/js/iris.min",
            pmAdmin: 'CleverSoft_CleverPinMarker/js/pm-admin',
            pmWpColorPicker: 'CleverSoft_CleverPinMarker/js/wp-color-picker.min'
        }
    },
    shim:{
        pmLoadScripts: ["jquery","jquery/ui"],
        pmColorPicker: ["jquery","jquery/ui"],
        pmIris: ["jquery","jquery/ui"],
        pmAdmin: ["jquery","jquery/ui"],
        pmWpColorPicker: ["jquery","jquery/ui","pmColorPicker"]
    }
};
