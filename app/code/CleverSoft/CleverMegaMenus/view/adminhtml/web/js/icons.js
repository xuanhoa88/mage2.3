/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

define([
    'jquery',
    'cleverMenuJqueryUi',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'prototype'
], function(jQuery){
    window.Icons = {
        textareaElementId: null,
        iconsContent: null,
        dialogWindow: null,
        dialogWindowId: 'icons-chooser',
        overlayShowEffectOptions: null,
        iconWrapper:'.menu-icons-wrapper',
        overlayHideEffectOptions: null,
        fadeSpeed: 400,
        cleverSearchId:'clever-awesome-seach',
        insertFunction: 'Icons.insertIcon',
        htmlSelectorId:'',
        init: function(textareaElementId, insertFunction) {
            if ($(textareaElementId)) {
                this.textareaElementId = textareaElementId;
            }
            if (insertFunction) {
                this.insertFunction = insertFunction;
            }
        },

        resetData: function() {
            this.iconsContent = null;
            this.dialogWindow = null;
        },

        searchIcon: function($element) {
            var self = this;
            var val = jQuery($element).val();
            if (val == '') {
                jQuery('#'+ this.dialogWindowId +' '+self.iconWrapper+' .col-xs-2').show();
            } else {
                jQuery('#'+ self.dialogWindowId +' '+self.iconWrapper+' .col-xs-2 a').each(function(){
                    if(jQuery(this).text().indexOf(val) == -1) {
                        jQuery(this).closest('.col-xs-2').fadeOut( "fast",function(){
                            if(jQuery(this).closest('.row').find('.col-xs-2:visible').length == 0) {
                                jQuery(this).closest(self.iconWrapper).fadeOut( self.fadeSpeed );
                            }
                        } );
                    } else {
                        jQuery(this).closest(self.iconWrapper).fadeIn(self.fadeSpeed);
                        jQuery(this).closest('.col-xs-2').fadeIn(self.fadeSpeed);
                    }
                });

            }
        },

        getIconHtml: function(id,search){
            var $wrapper = jQuery('<div/>') ;
            jQuery(jQuery('#'+id).html()).appendTo($wrapper);
            if(search) {
                jQuery('<input style="width: 300px;height: 30px;padding-left: 10px;" type="text" id="' + this.cleverSearchId + '" name="clever-awesome-search" placeholder="Search icon by name" onkeypress="Icons.searchIcon(\'#clever-awesome-seach\')">').prependTo($wrapper);
            }
            return $wrapper.html();
        },

        openIconChooser: function(id,htmlId,title,search) {
            if (this.iconsContent == null || (this.htmlSelectorId != htmlId && htmlId)) {
                this.iconsContent = this.getIconHtml(htmlId,search);
                this.htmlSelectorId = id;
            }
            if (this.iconsContent) {
                this.openDialogWindow(this.iconsContent,title);
            }
            this.textareaElementId = id;
            this.addEventKeyupSearch();
        },
        addEventKeyupSearch: function() {
            var self = this;
            jQuery(document).keyup(function(event){
                if ( event.keyCode == 88 || event.keyCode == 86 || event.keyCode == 8  || event.keyCode == 46 ) {
                    event.stopPropagation();
                    self.searchIcon('#'+self.cleverSearchId);
                }
            });
        },
        openDialogWindow: function (iconsContent,title) {
            var windowId = this.dialogWindowId;
            jQuery('<div id="' + windowId + '">' + Icons.iconsContent + '</div>').modal({
                title: title,
                type: 'slide',
                buttons: [],
                closed: function (e, modal) {
                    modal.modal.remove();
                }
            });

            jQuery('#' + windowId).modal('openModal');

            iconsContent.evalScripts.bind(iconsContent).defer();
        },
        closeDialogWindow: function() {
            jQuery('#' + this.dialogWindowId).modal('closeModal');
        },
        prepareIconRow: function(varValue, varLabel) {
            var value = (varValue).replace(/"/g, '&quot;').replace(/'/g, '\\&#39;');
            var content = '<a href="#" onclick="'+this.insertFunction+'(\''+ value +'\');return false;">' + varLabel + '</a>';
            return content;
        },
        insertCleverIcon: function(value,classIcon) {
            var self = this;
            var windowId = this.dialogWindowId;
            jQuery('#' + windowId).modal('closeModal');
            var textareaElm = $(this.textareaElementId);

            if (textareaElm) {
                if(jQuery(textareaElm).is('textarea')){
                    var scrollPos = textareaElm.scrollTop;
                    updateElementAtCursor(textareaElm, value);
                    textareaElm.focus();
                    textareaElm.scrollTop = scrollPos;
                    textareaElm = null;
                }else{
                    textareaElm.value = value;
                    if(jQuery('#preview-'+this.textareaElementId).length){
                        var preview = jQuery('#preview-'+this.textareaElementId);
                        jQuery('i',preview).removeAttr('class').addClass(classIcon + value);
                    }
                }
                jQuery(textareaElm).trigger('change');
            }
            return;
        },
        insertSampleLayout: function(htmlId) {
            var windowId = this.dialogWindowId;
            jQuery('#' + windowId).modal('closeModal');
            var textareaElm = $(this.textareaElementId);
            if (textareaElm) {
                if(textareaElm.type == 'textarea'){
                    var scrollPos = textareaElm.scrollTop;
                    updateElementAtCursor(textareaElm, jQuery('#'+htmlId).html().trim());
                    textareaElm.focus();
                    textareaElm.scrollTop = scrollPos;
                    textareaElm = null;
                }
            }
            return;
        }
    };

    window.MagentoiconPlugin = {
        editor: null,
        icons: null,
        textareaId: null,
        setEditor: function(editor) {
            this.editor = editor;
        },
        loadChooser: function(url, textareaId) {
            this.textareaId = textareaId;
            if (this.icons == null) {
                new Ajax.Request(url, {
                    parameters: {},
                    onComplete: function (transport) {
                        if (transport.responseText.isJSON()) {
                            Icons.init(null, 'MagentoiconPlugin.insertIcon');
                            this.icons = transport.responseText.evalJSON();
                            this.openChooser(this.icons);
                        }
                    }.bind(this)
                });
            } else {
                this.openChooser(this.icons);
            }
            return;
        },
        openChooser: function(icons) {
            Icons.openIconChooser(icons);
        },
        insertIcon : function (value) {
            if (this.textareaId) {
                Icons.init(this.textareaId);
                Icons.insertIcon(value);
            } else {
                Icons.closeDialogWindow();
                this.editor.execCommand('mceInsertContent', false, value);
            }
            return;
        }
    };

});