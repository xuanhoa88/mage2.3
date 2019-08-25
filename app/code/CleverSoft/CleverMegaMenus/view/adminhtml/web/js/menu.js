/**
 * @category    CleverSoft
 * @package     CleverMegaMenus
 * @copyright   Copyright © 2017 CleverSoft., JSC. All Rights Reserved.
 * @author 		ZooExtension.com
 * @email       magento.cleversoft@gmail.com
 */

define([
    "jquery",
    'mage/translate',
    "cleverMenuJqueryUi",
    "Magento_Ui/js/modal/confirm",
    "createCategoryChooser"
], function($,translate, cleverUi,confirm){
    $.widget('mage.cleverMenuAdminhtml',{
        options:{
            tabs: {
                ids:'' ///can be multi id, separate by comma.
            },
            menuItemField:'.menu-item-field',
            itemFields: '.menu-item-fields',
            navBtn: '.item-edit',
            deleteBtn:'.item-delete',
            leftSide:'#type-items',
            globalMaxDepth:  11,
            menuFieldClass:'.menu-field',
            addBtn:'.add-to-menu',
            noPageSelected:'',
            transClass:'.menu-item-transport',
            formSubmit:'#edit_form',
            menuItemSettings: '.menu-item-settings',
            openClass:'menu-item-edit-active',
            closeClass: 'menu-item-edit-inactive',
            contentMenu: 'input[name="menucontent"]',
            categoryChser: {
                url:'',
                open: $.mage.__('Select Category...'),
                close: $.mage.__('Close'),
                selector: '[data-type="category"]'
            },
            menuItem:'.menu-item',
            noChildClass:'no-children',
            menuItemDepthPerLevel: 30,
            sortItemHanle:'.menu-item-handle',
            sortPlaceHolder:'sortable-placeholder',
            actionBtnWrap:'.menu-item-actions',
            animationSpeed: 350,
            deleteObject: {
                warmingMessage:'Are you sure ( All children will be removed ).? ',
                warmingTitle: $.mage.__('Confirm')
            },
            noImage:'',
            depthPartClass: 'menu-item-depth-',
            menuContainer: '.zoo-menu-content',
            textChangeEdit: {
                id:'#text_change_edit',
                text : $.mage.__('Drag each item into the order you prefer. Click the arrow on the right of the item to reveal additional configuration options.'),
                textEmpty: $.mage.__('Add menu items from the column on the left.')
            },
            mediaPath : '',
            loadingMask: '#clever-spinner',
            htmlZeroDepth:'#clever-addition-fields-for-depth-0'
        },
        _create: function(){
            var self = this;
            var options = this.options;
            if($(options.tabs.ids).length) $(options.tabs.ids).tabs();
            var element = this.element;
            window.filterImageUrl = function(value){
                if( (typeof value !== 'undefined') && (value != '')){
                    if( /(?:^|\s)url\=\"(.*?)\"\}/g.test(value)){
                        var match = /(?:^|\s)url\=\"(.*?)\"\}/g.exec(value);
                        return (self.options.mediaPath + match[1]);
                    }
                }
                return self.options.noImage;
            };
            window.uniqid = function(prefix){
                return prefix + Math.floor( Math.random() * 1000 ) + Date.now();
            };
            this.initSortable($(element),options);
            this.onBeforesubmitForm(self);
            this.eventClickAllBtn(options,$(options.menuContainer));
            this.loadCategoryChooser($(options.categoryChser.selector,$(options.menuContainer)));
            this.initPageType(options);
        },

        initPageType: function(options) {
            var page = $('[data-itemtype="page"]',$(options.leftSide));
            page.find('.field__label,.field__url').hide();
            $(options.navBtn,page).click();
        },

        initSortable :function($element,options){
            var self = this;
            var menuEdge = $element.offset().left, currentDepth = 0, originalDepth, minDepth, maxDepth,
                next, prevBottom, nextThreshold, helperHeight,transport, maxChildDepth, menuMaxDepth = initialMenuMaxDepth();
            $element.sortable({
                items: options.menuItem,
                handle: options.sortItemHanle,
                placeholder: options.sortPlaceHolder,
                start: function(event, ui){
                    var height, width, parent, children, tempHolder;

                    transport = ui.item.children(self.options.transClass);

                    // Set depths. currentDepth must be set before children are located.
                    originalDepth = menuItemDepth(ui.item);
                    updateCurrentDepth(ui, originalDepth);

                    // Attach child elements to parent
                    // Skip the placeholder
                    parent = ( ui.item.next()[0] == ui.placeholder[0] ) ? ui.item.next() : ui.item;
                    children = childMenuItems(parent);
                    transport.append( children );

                    // Update the height of the placeholder to match the moving item.
                    height = transport.outerHeight();
                    // If there are children, account for distance between top of children and parent
                    height += ( height > 0 ) ? (ui.placeholder.css('margin-top').slice(0, -2) * 1) : 0;
                    height += ui.helper.outerHeight();
                    helperHeight = height;
                    height -= 2; // Subtract 2 for borders
                    ui.placeholder.height(height);

                    // Update the width of the placeholder to match the moving item.
                    maxChildDepth = originalDepth;
                    children.each(function(){
                        var depth = menuItemDepth($(this));
                        maxChildDepth = (depth > maxChildDepth) ? depth : maxChildDepth;
                    });
                    width = ui.helper.find(self.options.sortItemHanle).outerWidth(); // Get original width
                    width += depthToPx(maxChildDepth - originalDepth); // Account for children
                    width -= 2; // Subtract 2 for borders
                    ui.placeholder.width(width);

                    // Update the list of menu items.
                    tempHolder = ui.placeholder.next( self.options.menuItem );
                    tempHolder.css( 'margin-top', helperHeight + 'px' ); // Set the margin to absorb the placeholder
                    ui.placeholder.detach(); // detach or jQuery UI will think the placeholder is a menu item
                    $(this).sortable( 'refresh' ); // The children aren't sortable. We should let jQ UI know.
                    ui.item.after( ui.placeholder ); // reattach the placeholder.
                    tempHolder.css('margin-top', 0); // reset the margin

                    // Now that the element is complete, we can update...
                    updateSharedVars(ui);
                },
                stop: function(event, ui){
                    var depthChange = currentDepth - originalDepth;
                    var $children = transport.children().insertAfter(ui.item);
                    ui.placeholder.remove();
                    if ( 0 !== depthChange ) {
                        updateDepthClass(ui.item, currentDepth );
                        shiftDepthClass( $children,depthChange );
                        updateMenuMaxDepth( depthChange );
                    }
                    //
                    if(currentDepth > 0) {
                        if($('.hide-on-trigger-parent',ui.item).length > 0) {
                            $('.hide-on-trigger-parent',ui.item).hide();
                        }
                    } else {
                        if($('.hide-on-trigger-parent',ui.item).length > 0) {
                            $('.hide-on-trigger-parent',ui.item).show();
                        } else {
                            if  (ui.item.data('itemtype') == 'link' || ui.item.data('itemtype') == 'page') var $find = '.menu-item-fields .field__class';
                            if  (ui.item.data('itemtype') == 'text') var $find = '.menu-item-fields .field__style';
                            if  (ui.item.data('itemtype') == 'category') var $find = '.menu-item-fields .field__display_type';
                            ui.item.find($find).after($(options.htmlZeroDepth).html());
                        }
                    }

                    //
                    ui.item[0].style.top = 0;
                    ui.item[0].setAttribute("data-depth", currentDepth);
                    self.updateChildrenClass();
                },
                change: function(e, ui){
                    if( ! ui.placeholder.parent().hasClass('menu') )
                        (prev.length) ? prev.after( ui.placeholder ) : $(this).prepend( ui.placeholder );

                    updateSharedVars(ui);
                },
                sort: function(e,ui){
                    if (menuEdge == 0) {
                        menuEdge = $(this).offset().left;
                    }
                    var offset = ui.helper.offset(),
                        edge = offset.left,
                        depth = pxToDepth( edge - menuEdge );

                    // Check and correct if depth is not within range.
                    // Also, if the dragged element is dragged upwards over
                    // an item, shift the placeholder to a child position.

                    if ( depth > maxDepth || offset.top < ( prevBottom ) ) {
                        depth = maxDepth;
                    } else if ( depth < minDepth ) {
                        depth = minDepth;
                    }
                    if( depth != currentDepth ){
                        updateCurrentDepth(ui, depth);
                        if (depth > 0 ) {
                            $(ui.item).find('.hide-on-trigger-parent').hide();
                        } else {
                            $(ui.item).find('.hide-on-trigger-parent').show();
                        }
                    }
                    if( nextThreshold && offset.top + helperHeight > nextThreshold ) {
                        next.after( ui.placeholder );
                        updateSharedVars( ui );
                        $(this).sortable( 'refreshPositions' );
                    }
                }
            });
            function initialMenuMaxDepth() {
                var maxDepth = 0;
                self.element.find(' > '+ options.menuItem).each(function(){
                    maxDepth = 	maxDepth < menuItemDepth($(this)) ? menuItemDepth($(this)) : maxDepth;
                });
                return maxDepth;
            }
            function shiftDepthClass($element,change){
                return $($element).each(function(){
                    var t = $(this),
                        depth = menuItemDepth(t),
                        newDepth = depth + change;

                    t.removeClass( self.options.depthPartClass + depth )
                        .addClass( self.options.depthPartClass + ( newDepth ) );
                    t.data('depth',newDepth);
                });
            }
            function updateDepthClass(element,current, prev) {
                return $(element).each(function(){
                    var t = $(this);
                    prev = prev || menuItemDepth(t);
                    $(this).removeClass(self.options.depthPartClass + prev )
                        .addClass(self.options.depthPartClass + current );
                });
            }
            function updateMenuMaxDepth(depthChange) {
                var depth, newDepth = menuMaxDepth;
                if ( depthChange === 0 ) {
                    return;
                } else if ( depthChange > 0 ) {
                    depth = maxChildDepth + depthChange;
                    if( depth > menuMaxDepth )
                        newDepth = depth;
                } else if ( depthChange < 0 && maxChildDepth == menuMaxDepth ) {
                    while( ! $('.' + self.options.depthPartClass  + newDepth, self.element).length && newDepth > 0 )
                        newDepth--;
                }
                menuMaxDepth = newDepth;
            }
            function childMenuItems($parent){
                var result = $();
                $($parent).each(function(){
                    var t = $(this), depth = menuItemDepth(t), next = t.next( self.options.menuItem);
                    while( next.length && menuItemDepth(next) > depth ) {
                        result = result.add( next );
                        next = next.next( self.options.menuItem );
                    }
                });
                return result;
            }
            function updateCurrentDepth(ui, depth) {
                updateDepthClass( ui.placeholder,depth, currentDepth );
                currentDepth = depth;
            }
            function updateSharedVars(ui) {
                var depth;

                prev = ui.placeholder.prev( self.options.menuItem );
                next = ui.placeholder.next( self.options.menuItem );

                // Make sure we don't select the moving item.
                if( prev[0] == ui.item[0] ) prev = prev.prev( self.options.menuItem );
                if( next[0] == ui.item[0] ) next = next.next( self.options.menuItem );

                prevBottom = (prev.length) ? prev.offset().top + prev.height() : 0;
                nextThreshold = (next.length) ? next.offset().top + next.height() / 3 : 0;
                minDepth = (next.length) ? menuItemDepth(next) : 0;

                if( prev.length )
                    maxDepth = ( (depth = menuItemDepth(prev) + 1) > options.globalMaxDepth ) ? options.globalMaxDepth : depth;
                else
                    maxDepth = 0;
            }
            function depthToPx(depth){
                return depth * options.menuItemDepthPerLevel;
            }
            function menuItemDepth($t) {
                var margin =  $t.eq(0).css('margin-left');
                return pxToDepth( margin && -1 != margin.indexOf('px') ? margin.slice(0, -2) : 0 );
            }
            function pxToDepth(px){
                return Math.floor(px / options.menuItemDepthPerLevel);
            }
        },

        onBeforesubmitForm: function(){
            var self = this;
            var form = $(this.options.formSubmit);
            form.on('beforeSubmit',function(){
                $(self.options.loadingMask).show();
                if( document.activeElement.id == 'duplicate' ){
                    $('<input type="hidden" name="duplicate" value="1" />').appendTo(form);
                }
                var a = $(self.options.contentMenu,form);
                $(self.options.contentMenu,form).val(self.menuItemsJson(self));
            });
        },

        menuItemsJson: function(self){
            var itemsArray = [];
            var options = self.options;
            $(options.menuItem,$(self.element)).each(function(){
                var $itemFields = $(options.itemFields,$(this)),
                    itemData = {};
                itemData.depth = $(this).data('depth');
                itemData.item_type = $(this).data('itemtype');
                itemData.content = {};
                $(options.menuItemField,$itemFields).each(function(){
                    var $itemField = $(options.menuFieldClass,$(this));
                    if($itemField.length > 1){
                        var fieldName = $itemField.first().data('name');
                        itemData.content[fieldName] = [];
                        $itemField.each(function(){
                            if ($(this).data('type') == 'checkbox' && $(this).is(':checked')) {
                                $(this).val("1");
                            }else {
                                if ($(this).data('type') == 'checkbox') {
                                    $(this).val("0");
                                }
                            }
                            itemData.content[fieldName].push($(this).val());
                        });
                    }else{
                        if ($itemField.data('type') == 'checkbox' && $itemField.is(':checked')) {
                            $itemField.val("1");
                        } else {
                            if ($itemField.data('type') == 'checkbox') {
                                $itemField.val("0");
                            }
                        }
                        if ($(this).hasClass('hide-on-trigger-parent') && $(this).css('display') == 'none' ) {
                            //do nothing
                        } else itemData.content[$itemField.data('name')] = $itemField.val();
                    }
                });
                itemsArray.push(itemData);
            });
            return JSON.stringify(itemsArray);
        },

        loadCategoryChooser: function($element){
            var self = this;
            if($element.length){
                $element.each(function(){
                    self.getCategoryChooser($(this).attr('id'));
                });
            }
        },

        eventClickAllBtn: function(options,element) {
            this.eventNavBtn(options,element);
            this.eventAddBtn(options,element);
            this.eventDelBtn(options,element);
        },

        eventNavBtn: function(options,element) {
            $(options.navBtn, element).on('click', function(){
                var $menuItem = $(this).closest(options.menuItem);
                var $settings = $menuItem.find(options.menuItemSettings);
                if($menuItem.hasClass(options.openClass)){
                    $settings.slideUp(options.animationSpeed);
                    $menuItem.removeClass(options.openClass).addClass(options.closeClass);
                }else{
                    var $id = $menuItem.parent().attr('id');
                    $("#"+$id+" "+options.menuItemSettings).not($settings).slideUp(options.animationSpeed);
                    $("#"+$id+" "+options.menuItem).removeClass(options.openClass).addClass(options.closeClass);
                    $settings.slideDown(options.animationSpeed);
                    $menuItem.addClass(options.openClass).removeClass(options.closeClass);
                }
            });
        },

        eventAddBtn: function(options,element) {
            var seft = this;
            $(options.addBtn, element).on('click', function(){
                var $menuItem = $(this).closest(options.menuItem);
                if ($menuItem.data('itemtype').trim() == 'page') {
                    if ($menuItem.find('input[data-name="page_selections"]:checked').length  > 0) {
                        $menuItem.find('input[data-name="page_selections"]:checked').each(function () {
                            var _cloneItem = $menuItem.clone();
                            _cloneItem.removeClass(options.openClass).addClass(options.closeClass).addClass(options.noChildClass);
                            $(_cloneItem).find('div[data-removeaddjs=1]').remove();
                            $(_cloneItem).data('depth',0);
                            $(options.addBtn,_cloneItem).remove();
                            $(seft.element).append(_cloneItem);
                            seft.eventClickAllBtn(options,_cloneItem);
                            seft.changeUniqueId(_cloneItem);
                            $field__label = $(this).closest(options.menuItemField).find('.label span').html().trim();
                            $field__url = $(this).attr('value').trim();
                            $('[data-name="label"]',_cloneItem).val($field__label).closest(options.menuItemField).show();
                            a = $('.link-title' ,_cloneItem);
                            $('.link-title' ,_cloneItem).text($field__label);
                            $('[data-name="url"]',_cloneItem).val($field__url).closest(options.menuItemField).show();
                            $(options.menuItemSettings,_cloneItem).hide();
                            _cloneItem.hide().fadeIn(options.animationSpeed);
                            var top = _cloneItem.offset().top;
                        });
                    } else {
                        alert(options.noPageSelected);
                    }
                } else {
                    var _cloneItem = $menuItem.clone();
                    _cloneItem.removeClass(options.openClass).addClass(options.closeClass).addClass(options.noChildClass);
                    $(_cloneItem).find('div[data-removeaddjs=1]').remove();
                    $(_cloneItem).data('depth',0);
                    $(options.addBtn,_cloneItem).remove();
                    $(seft.element).append(_cloneItem);
                    seft.eventClickAllBtn(options,_cloneItem);
                    seft.changeUniqueId(_cloneItem);
                    $(options.menuItemSettings,_cloneItem).hide();
                    _cloneItem.hide().fadeIn(options.animationSpeed);
                    var top = _cloneItem.offset().top;
                }

                if( ($(window).scrollTop() + $(window).height()) < top ){
                    $('html,body').animate({scrollTop: top - 10},options.animationSpeed);
                }
                seft.element.sortable('refresh');
                if ($(seft.options.menuItem ,$('#'+seft.element.id)).length > 0) {
                    $(options.textChangeEdit.id).html(options.textChangeEdit.text);
                } else {
                    $(options.textChangeEdit.id).html(options.textChangeEdit.textEmpty);
                }
            });
        },

        eventDelBtn: function(options,element) {
            var seft = this;
            $(options.deleteBtn, element).on('click', function(){
                var $menuItem = $(this).closest(options.menuItem);
                confirm({
                    content: options.deleteObject.warmingMessage,
                    title: options.deleteObject.warmingTitle,
                    actions: {
                        confirm: function () {
                            var $childs = seft.getAllChildren($menuItem);
                            $menuItem.fadeOut(options.animationSpeed, function(){
                                $childs.each(function(obj,idx){
                                    obj.fadeOut(options.animationSpeed,function(){
                                        obj.remove();
                                    });
                                });
                                $menuItem.remove();
                                seft.updateChildrenClass();
                            });
                        },
                        cancel: function () {
                            return false;
                        }
                    }
                });
            })
        },


        changeUniqueId: function(el) {
            var self = this;
            $(self.options.menuFieldClass, el).each(function() {
                var type = $(this).data('type'), _new = uniqid(type + '_'),$closest = $(this).closest(self.options.menuItemField), _oldId = $(this).attr('id');
                if(_oldId){
                    $(this).attr('id',_new);
                    $closest.find('.content-btn').each(function(){
                        var _onclick = $(this).attr('onclick');
                        if(_onclick){
                            $(this).attr('onclick',_onclick.replace(_oldId,_new));
                        }
                    });
                }
                if(type == 'category') self.getCategoryChooser(_new);
                if(type == 'icon') $closest.find('.preview-icon').attr('id','preview-'+_new);
            });
        },

        getCategoryChooser: function(uniqueId) {
            var self = this;
            window[uniqueId] = new WysiwygWidget.createCategoryChooser(
                uniqueId,
                self.options.categoryChser.url + uniqueId,
                {"buttons": {"open": self.options.categoryChser.open, "close": self.options.categoryChser.close}}
            );
            if ($(uniqueId + "value")) {
                $(uniqueId + "value").advaiceContainer = uniqueId + "advice-container";
            }
        },

        updateChildrenClass: function(){
            var self = this;
            $(self.options.menuItems,self.element).each(function(){
                var childs = self.getAllChildren($(this));
                if(childs.length == 0) {
                    $(this).addClass(self.options.noChildClass);
                } else {
                    $(this).removeClass(self.options.noChildClass);
                }
            })
        },

        getAllChildren: function(el){
            var _childs = [];
            var _next = $(el).next();
            while ((_next.length) && (this.getDepth(_next) > this.getDepth($(el)))) {
                _childs.push(_next);
                _next = _next.next();
            }
            return _childs;
        },

        getDepth: function(el){
            return el.data('depth');
        }

    });
    String.prototype.replaceAll = function(target, replacement) {
        return this.split(target).join(replacement);
    };
    return $.mage.cleverMenuAdminhtml;
});