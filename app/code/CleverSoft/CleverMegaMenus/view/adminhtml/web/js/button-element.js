
define([
    "jquery",
    "cleverMenuAdminhtml",
    "Magento_Ui/js/modal/modal",
    'mage/translate'
], function($){
    window.cleverButtonElement = {
        layout:{
            preview: 'preview-layout',
            dataType:'data-type="layout"'
        },
        menuItemField: '.menu-item-field',
        menuBarTitle:'.menu-item-bar .menu-item-title',
        linkTitle : '.link-title',
        menuHandleTitle: '.menu-item-handle',
        menuItem:'.menu-item',
        leftSide:'#type-items',
        iconPreview: '.preview-icon',
        columnClass:'.content-col',
        rowClass:'.content-row',
        modalTitle: $.mage.__('Insert Your Html Then Click on SAVE button'),

        eventChangeIconChooser : function($select){
            var self = this;
            var params = window.iconSwitchChooserParam;
            if(params.constructor !== Array) {
                params = JSON.parse(params);
            }
            params.each(function(index,$value){
                $(self.menuItemField + '.' + window.prefixClass + index).hide();
            });
            $(self.menuItemField + '.' + window.prefixClass + params[$($select).val()]).fadeIn('fast');
        },

        changeLabelOnKeyUp: function($input){
            if( $($input).closest(this.leftSide).length) {
                var _html = ' ( ' + $($input).val() + ' )';
            } else {
                var _html = $($input).val();
            }
            $($input).val() == '' ? $(this.linkTitle, $($input).closest(this.menuItem)).html('') : $(this.linkTitle, $($input).closest(this.menuItem)).html(_html);
        },

        fullImage: function($image) {
            var $imageModal = $('<div class="img-modal"><img src="'+$($image).data('href')+'" /></div>').modal({
                title: '',
                modalClass: '_image-box',
                clickableOverlay: true,
                buttons: []
            });
            $imageModal.modal('openModal');
        },

        removePreviewIcon: function($element,$type){
            $('[data-type="'+$type+'"]',$($element).closest(this.menuItemField)).val('').trigger('change');
            $(this.iconPreview +' i',$($element).closest(this.menuItemField)).removeAttr('class');
        },

        changePreviewIcon: function(element,value, type) {
            var $menuItem = $(element).closest(this.menuItem);
            var $menuItemField = $(element).closest(this.menuItemField);
            var _iconClass = type == 0 ? "fa fa-"+value : "cs-font clever-icon-"+value;
            switch (type) {
                case 0 :
                case 2:
                    $(this.iconPreview + ' i' ,$menuItemField).removeAttr('class').addClass(_iconClass);
                    $(this.menuHandleTitle + ' ' + this.iconPreview, $menuItem).html('<i class="'+_iconClass+'" ></i>');
                    break;
                case 1 :
                    var src = filterImageUrl(value);
                    if($(element).data('name') == 'icon_img'){
                        $(this.menuHandleTitle + ' ' + this.iconPreview, $menuItem).html('<a class="preview-image" onclick="cleverButtonElement.fullImage(this)" href="javascript:void(0)" data-href="'+src+'"><img src="'+src+'" /></a>');
                    }
                    $(element).parent().find('.preview-image').data('href',src);
                    $('img',$(element).parent().find('.preview-image')).attr('src',src);
                    break;
            }
        },

        removePreviewImage: function(element){
            $('[data-type="image"]',$(element).closest(this.menuItemField)).val('').trigger('change');
        },

        toggleSampleColumns: function(element) {
            $(element).closest('.content-layout-wrap').toggleClass('open');
            $('.content-layout-chooser',$(element).closest('.content-layout-wrap')).fadeToggle(350);
        },

        changeSampleColumnsTemplate: function(element,cols, $layout){
            var $element = $(element);
            var $menuItemField = $element.closest(this.menuItemField);
            var $layoutMIF = $menuItemField.next();
            var $row = $(this.rowClass,$layoutMIF);
            var $newCols = cols.length;
            var currentUniqid = $('[data-type="editor"]',$(this.columnClass,$row).first()).attr('id');
            var $html = $(this.columnClass,$row).first().clone();
            $html.find('[data-type="editor"]').html('');
            var $currentNumCols = $(this.columnClass,$row).length;
            if($newCols > $currentNumCols) {
                var $needM =$newCols - $currentNumCols;
                for (var i = 0 ; i <  $needM ; i++) {
                    var uniqueId = uniqid('editor_');
                    var $newHtml = $html.html().replaceAll(currentUniqid,uniqueId);
                    $row.append('<div class="content-col active">'+$newHtml+'</div>');
                }
            } else if($newCols < $currentNumCols) {
                var $remove = $currentNumCols - $newCols;
                for(var i=0; i<$remove; i++){
                    $(this.columnClass,$row).last().remove();
                }
            }
            this.changeColumnsWidth(cols,$(this.columnClass,$layoutMIF));
            this.toggleSampleColumns(element);
            $('['+this.layout.dataType+']',$menuItemField).val($layout);
            $('.'+this.layout.preview,$menuItemField).html($(element).html());
            $('.'+this.layout.preview,$menuItemField).attr('class',this.layout.preview+' layout-'+$layout)
        },

        changeColumnsWidth: function(cols, $colObject){
            cols.each(function(val,idx){
                $colObject.eq(idx).css('width',val*100+'%');
            });
        },

        modalOpenWysiwyg: function(element) {
            var self = this;
            var unique = uniqid('modal_element_id_');
            $(element).closest(this.rowClass).attr('id',unique);
            var _oldCol = $(element).closest(this.columnClass);
            var $oldId    = $('[data-type="editor"]',_oldCol).attr('id');
            var $newId  = uniqid('editor_');
            var _modalClone = _oldCol.clone();
            _modalClone.removeAttr('style');
            var _modalHtml = _modalClone.html();
            _modalHtml = _modalHtml.replaceAll($oldId,$newId);
            _modalClone.html(_modalHtml);
            _modalClone.css({display:'block',float:'none'});
            _modalClone.find('[data-type="editor"]').attr('style','width:100%;min-height:200px;');
            var a = _modalClone.find('.content-col-wysiwyg');
            _modalClone.find('.content-col-wysiwyg').remove();
            var $modal = $(_modalClone).modal({
                title: self.modalTitle,
                modalClass: '_content-box',
                clickableOverlay: true,
                buttons: [{
                    text: $.mage.__('SAVE'),
                    class: 'btn',
                    click: function () {
                        $('#'+$oldId,'body').html(this.element.find('[data-type="editor"]').val());
                        $('#'+$oldId,'body').val(this.element.find('[data-type="editor"]').val());
                        $modal.modal('closeModal');
                    }
                }]
            });
            $modal.modal('openModal');
        },

        insertColumnSampleHtml: function() {

        }

    }
});