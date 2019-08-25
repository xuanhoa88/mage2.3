var config = {
    "map": {
        "*": {
            CleverBuilderPanelButtons:"CleverSoft_CleverBuilder/js/script",
            backbone: 'CleverSoft_CleverBuilder/js/lib/backbone.min',
            jQueryScrollTo: 'CleverSoft_CleverBuilder/js/lib/jquery.scrollTo',
            liveEditor: 'CleverSoft_CleverBuilder/js/lib/live-editor-front',
            imageBrowser: 'CleverSoft_CleverBuilder/js/imgbrowser',
            folderTree: 'CleverSoft_CleverBuilder/js/folder-tree',
            mediabrowser: 'CleverSoft_CleverBuilder/js/jquery.jstree',
            colorPickerLib: 'CleverSoft_CleverBuilder/js/lib/mcolorpicker/mcolorpicker',
            sitePanels:"CleverSoft_CleverBuilder/js/site-panels",
            styling:"CleverSoft_CleverBuilder/js/styling",
            catTree:"CleverSoft_CleverBuilder/js/categories-tree",
            dependency:"CleverSoft_CleverBuilder/js/dependency",
            tinyMceSetup:"CleverSoft_CleverBuilder/js/tiny_mce/setup",
            mbYTPlayer:"CleverSoft_CleverBuilder/js/lib/jquery.mb.YTPlayer",
            flexsliderJS: 'CleverSoft_CleverBuilder/js/slider/jquery.flexslider',
            jqueryDrag: 'CleverSoft_CleverBuilder/js/slider/jquery.event.drag',
            bgLoaderJS: 'CleverSoft_CleverBuilder/js/slider/bgLoader',
            froogaLoop: 'CleverSoft_CleverBuilder/js/slider/froogaloop2.min',
            jarallax: 'CleverSoft_CleverBuilder/js/slider/jarallax',
            jarallaxVideo: 'CleverSoft_CleverBuilder/js/slider/jarallax-video',
            jarallaxElement: 'CleverSoft_CleverBuilder/js/slider/jarallax-element',
            cleverProductMage: 'CleverSoft_CleverBuilder/js/product/frontend',
            cleverProductLoader: 'CleverSoft_CleverBuilder/js/product/ajaxloader',
            cleverElementLoader: 'CleverSoft_CleverBuilder/js/element/ajaxloader',
            cleverElementAnimate: 'CleverSoft_CleverBuilder/js/animate',
            cleverSelect2: 'CleverSoft_CleverBuilder/js//lib/select2.full.min'
        }
    },
    "shim": {
        CleverBuilderPanelButtons : ["jquery"],
        jQueryScrollTo : ["jquery"],
        catTree : ["jquery","jquery/ui"],
        liveEditor : ["jquery"],
        colorPickerLib : ['jquery',"jquery/ui",'underscore'],
        imageBrowser : ["jquery"],
        folderTree : ["jquery"],
        mediabrowser : ["jquery"],
        dependency : ["jquery"],
        backbone: {
            deps: ['jquery', 'underscore'],
            exports: 'Backbone'
        },
        sitePanels : ["jquery","jquery/ui","underscore","backbone","liveEditor","jQueryScrollTo"],
        styling : ["jquery"],
        jarallax : ["jquery"],
        mbYTPlayer : ["jquery"],
        "flexsliderJS": ["jquery"],
        "jqueryDrag": ["jquery"],
        "bgLoaderJS": ["jquery"],
        "froogaLoop": ["jquery"],
        "jarallax": ["jquery"],
        "jarallaxVideo": ["jquery"],
        "jarallaxElement": ["jquery"],
        cleverProductMage : ["jquery"],
        cleverProductLoader : ["jquery"],
        cleverElementLoader : ["jquery"]
    },
    paths: {
        backbone: 'CleverSoft_CleverBuilder/js/lib/backbone.min'
    }
};