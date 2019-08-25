define([
    'jquery',
    'mage/translate',
    'underscore',
    'jquery/ui',
    'jquery/jquery.parsequery',
    "fotoramaVideoEvents",
    "Magento_Swatches/js/swatch-renderer",
    'jQueryLibMin'
], function ($,$t) {
    'use strict';
    
    /**
     //      * @private
     //      */
    function parseHref(href) {
        var a = document.createElement('a');

        a.href = href;

        return a;
    }

    /**
     * @private
     */
    function parseURL(href, forceVideo) {
        var id,
            type,
            ampersandPosition,
            vimeoRegex;

        /**
         * Get youtube ID
         * @param {String} srcid
         * @returns {{}}
         */
        function _getYoutubeId(srcid) {
            if (srcid) {
                ampersandPosition = srcid.indexOf('&');

                if (ampersandPosition === -1) {
                    return srcid;
                }

                srcid = srcid.substring(0, ampersandPosition);
            }

            return srcid;
        }

        if (typeof href !== 'string') {
            return href;
        }

        href = parseHref(href);

        if (href.host.match(/youtube\.com/) && href.search) {
            id = href.search.split('v=')[1];

            if (id) {
                id = _getYoutubeId(id);
                type = 'youtube';
            }
        } else if (href.host.match(/youtube\.com|youtu\.be/)) {
            id = href.pathname.replace(/^\/(embed\/|v\/)?/, '').replace(/\/.*/, '');
            type = 'youtube';
        } else if (href.host.match(/vimeo\.com/)) {
            type = 'vimeo';
            vimeoRegex = new RegExp(['https?:\\/\\/(?:www\\.|player\\.)?vimeo.com\\/(?:channels\\/(?:\\w+\\/)',
                '?|groups\\/([^\\/]*)\\/videos\\/|album\\/(\\d+)\\/video\\/|video\\/|)(\\d+)(?:$|\\/|\\?)'
            ].join(''));
            id = href.href.match(vimeoRegex)[3];
        }

        if ((!id || !type) && forceVideo) {
            id = href.href;
            type = 'custom';
        }

        return id ? {
            id: id, type: type, s: href.search.replace(/^\?/, '')
        } : false;
    }

    /**
     * Use to update image and video for product page with sticky_2 layout
     */
    $.widget('cleversoft.cleverBaseApllyImageAndVideoEvent', {
        options: {
            class: {
                sticky_gallery_content: '.gallery-sticky2',
                thumb_content: '.gallery-sticky2-image-thumb-col',
                image_content: '.gallery-sticky2-image-col',
                image_wrapper: '.gallery-sticky2-image-wrapper',
                video_wrapper: '.gallery-sticky2-video-wrapper'
            }
        },
        _create: function(){
            console.log('run cleverBaseApllyImageAndVideoEvent');
            var gallery_content = $(this.options.class.sticky_gallery_content);
            var thumb_content = gallery_content.find(this.options.class.thumb_content);
            var image_content = gallery_content.find(this.options.class.image_content);

            // image_content.find(this.options.class.image_wrapper).cleverInnerZoom();

            image_content.find(this.options.class.video_wrapper + " a").click(function(event){
                event.preventDefault();
            });

            image_content.find(this.options.class.video_wrapper).click(function () {
                var url = $(this).data('media-url');
                var video_data =  parseURL(url);
                $(this).data('related', '0')
                    .data('loop', '0')
                    .data('responsive', false)
                    .data('type', video_data.type)
                    .data('code', video_data.id)
                    .data('width', $(this).width())
                    .data('height', $(this).height());

                $(this).productVideoLoader();
            });
        }

    });

    $.widget('cleversoft.cleverBaseUpdateImageAndVideo', {
        options: {
            images: {},
            class: {
                sticky_gallery_content: '.gallery-sticky2',
                thumb_content: '.gallery-sticky2-image-thumb-col',
                image_content: '.gallery-sticky2-image-col',
                image_wrapper: '.gallery-sticky2-image-wrapper',
                video_wrapper: '.gallery-sticky2-video-wrapper',
            }
        },
        _create: function(){
            var galerry_data = this.options.images;

            var gallery_content = $(this.options.class.sticky_gallery_content);
            var thumb_content = gallery_content.find(this.options.class.thumb_content);
            var image_content = gallery_content.find(this.options.class.image_content);

            var html = '';
            $.each( galerry_data, function( key, item ) {

                if ( item.media_type == 'external-video') {
                    html += '<div class="gallery-sticky2-video-wrapper" data-media-type="external-video" data-media-url="' + item.video_url + '">';
                } else {
                    html += '<div class="gallery-sticky2-image-wrapper">';
                }

                html += '<a href="' + item.full + '">';
                html += '<img src="' + item.img + '">';
                html += '</a>';
                html += '</div>';
            });
            image_content.html(html);

            $.cleversoft.cleverBaseApllyImageAndVideoEvent(this.options);
        }
    });
    /**
     * Render tooltips by attributes (only to up).
     * Required element attributes:
     *  - option-type (integer, 0-3)
     *  - option-label (string)
     *  - option-tooltip-thumb
     *  - option-tooltip-value
     */
    $.widget('mage.cleverSwatchRendererTooltip', $.mage.SwatchRendererTooltip, {

    });

    /**
     * Render swatch controls with options and use tooltips.
     * Required two json:
     *  - jsonConfig (magento's option config)
     *  - jsonSwatchConfig (swatch's option config)
     *
     *  Tuning:
     *  - numberToShow (show "more" button if options are more)
     *  - onlySwatches (hide selectboxes)
     *  - moreButtonText (text for "more" button)
     *  - selectorProduct (selector for product container)
     *  - selectorProductPrice (selector for change price)
     */
    $.widget('mage.cleverSwatchRenderer', $.mage.SwatchRenderer, {
        options: {
            stickyClass:false,
            //
            onlyMainImg: false,
            equalHeightConfig: false
        },
        /*
         * call init function from parent and make some changes
         */
        _init: function () {
            var $widget = this;
            var $_init = this._super();
            $.when.apply( null, $_init ).done(function() {
                if($('.gallery-placeholder').size() == 0) $widget.element.find("." + $widget.options.classes.attributeClass).slice(1).css({'height':0,'overflow':'hidden'});
                if( $widget.options.equalHeightConfig) {
                    if($widget.element.closest('.trigger-equal-height').length > 0) {
                        $widget._updateHeightOnCatalogListing($widget.element.closest('.trigger-equal-height'));
                    }
                }
            });
            if ($widget.element.find("." + $widget.options.classes.attributeClass).length > 0) {
                $widget.element.closest('.product-item-info').find('.action.tocart span').text($t('Select Options'));
            }
        },

        /*
         * update for all product items if "equal height" option enabled
         */
        _updateHeightOnCatalogListing: function ($selector) {
            if($($selector).length > 0 ) {
                $($selector).attr('data-mage-init', JSON.stringify({'equalHeight': {'target': ' .product-item-info'}})).trigger('contentUpdated');
            }
        },

        _RenderFormInput: function (config) {
            return '<input class="' + this.options.classes.attributeInput + ' super-attribute-select" ' +
                'name="super_attribute[' + config.id + ']" ' +
                'type="text" ' +
                'value="" ' +
                'data-selector="super_attribute[' + config.id + ']" ' +
                'data-validate="{required:true}" ' +
                'aria-required="true" ' +
                'aria-invalid="true" ' +
                'style="visibility: hidden; position:absolute; left:-1000px">';
        },

        /**
         * Update total price
         *
         * @private
         */
        _UpdatePrice: function () {
            var $widget = this,
                $product = $widget.element.parents($widget.options.selectorProduct),
                $productPrice = $product.find(this.options.selectorProductPrice),
                options = _.object(_.keys($widget.optionsMap), {}),
                result;

            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var attributeId = $(this).attr('attribute-id');

                options[attributeId] = $(this).attr('option-selected');
            });
            $.each(options,function(key,val){
                if(!val){
                    delete options[key];
                }
            });
            result = $widget.options.jsonConfig.optionPrices[_.findKey($widget.options.jsonConfig.index, options)];

            $productPrice.trigger(
                'updatePrice',
                {
                    'prices': $widget._getPrices(result, $productPrice.priceBox('option').prices)
                }
            );

            if (typeof result != 'undefined' && result.oldPrice.amount !== result.finalPrice.amount) {
                $(this.options.slyOldPriceSelector).show();
            } else {
                $(this.options.slyOldPriceSelector).hide();
            }

            if($widget.options.stickyClass){
                var $_contaniner = $('.' + $widget.options.stickyClass);
                if($_contaniner.length > 0) {
                    $_contaniner.find('div[data-role="priceBox"]').html($productPrice.html());
                }
            }
        },
        /**
         * Enable loader
         *
         * @param {Object} $this
         * @private
         */
        _EnableProductMediaLoader: function ($this) {
            var $widget = this;

            if ($widget.element.closest('.product-view-bg').find('.gallery-placeholder').length) {
                $widget.element.closest('.product-view-bg').find('.gallery-placeholder')
                    .addClass($widget.options.classes.loader);
            } else if ($widget.element.closest('.product-item-info').find('.zoo-product-image').length) {
                //Category View
                $widget.element.closest('.product-item-info').find('.zoo-product-image')
                    .addClass($widget.options.classes.loader);
            }
        },

        /**
         * Disable loader
         *
         * @param {Object} $this
         * @private
         */
        _DisableProductMediaLoader: function ($this) {
            var $widget = this;

            if ($widget.element.closest('.product-view-bg').find('.gallery-placeholder').length) {
                $widget.element.closest('.product-view-bg').find('.gallery-placeholder')
                    .removeClass($widget.options.classes.loader);
            } else if ($widget.element.closest('.product-item-info').find('.zoo-product-image').length) {
                //Category View
                $widget.element.closest('.product-item-info').find('.zoo-product-image')
                    .removeClass($widget.options.classes.loader);
            }
        },

        /**
         * Callback for product media
         *
         * @param {Object} $this
         * @param {String} response
         * @private
         */
        _ProductMediaCallback: function ($this, response) {
            var isProductViewExist = $('.gallery-placeholder').size() > 0,
                $main = isProductViewExist ? $this.parents('.column.main') : $this.parents('.product-item-info'),
                $widget = this,
                images = [],

                /**
                 * Check whether object supported or not
                 *
                 * @param {Object} e
                 * @returns {*|Boolean}
                 */
                support = function (e) {
                    return e.hasOwnProperty('large') && e.hasOwnProperty('medium') && e.hasOwnProperty('small');
                };
            if ($main.length <= 0){
                $main = $this.closest('.product-view-bg');
            }

            if (_.size($widget) < 1 || !support(response)) {
                var $promise = this.updateBaseImage(this.options.mediaGalleryInitial, $main, isProductViewExist);
                $.when.apply(null, $promise).done($widget.changeButtonCartText());
                return;
            }

            images.push({
                media_type: response.media_type,
                full: response.large,
                img: response.medium,
                thumb: response.small,
                isMain: true,
                video_url:  response.video_url
            });

            if (response.hasOwnProperty('gallery')) {
                $.each(response.gallery, function () {
                    if (!support(this) || response.large === this.large) {
                        return;
                    }
                    images.push({
                        media_type: this.media_type,
                        full: this.large,
                        img: this.medium,
                        thumb: this.small,
                        video_url:  this.video_url
                    });
                });
            }

            var $promise = this.updateBaseImage(images, $main, isProductViewExist);
            $.when.apply(null, $promise).done($widget.changeButtonCartText());
        },

        /*
         * change the text from select options to add to cart if every option is selected.
         */
        changeButtonCartText: function() {
            var $widget = this;
            var $options = $widget.element.find("." + $widget.options.classes.attributeClass);
            var $show = true ;
            $options.each(function(){
                if($(this).find('.' + $widget.options.classes.optionClass + '.selected').length == 0) {
                    $show = false;
                    return false;
                }
            })
            if($show) $widget.element.closest('.product-item-info').find('.action.tocart span').text($t('Add To Cart'));
        },

        /**
         * Update [gallery-placeholder] or [product-image-photo]
         * @param {Array} images
         * @param {jQuery} context
         * @param {Boolean} isProductViewExist
         */
        updateBaseImage: function (images, context, isProductViewExist) {
            var justAnImage = images[0],
                updateImg,
                imagesToUpdate,
                gallery = context.find(this.options.mediaGallerySelector).data('gallery'),
                item;
            if (typeof(gallery) === 'undefined') {
               gallery = context.find('.quickview-gallery-placeholder').data('gallery');
            }
            if (isProductViewExist) {
                if (typeof(gallery) != 'undefined' && gallery != null) {
                    imagesToUpdate = images.length ? this._setImageType($.extend(true, [], images)) : [];

                    if (this.options.onlyMainImg) {
                        updateImg = imagesToUpdate.filter(function (img) {
                            return img.isMain;
                        });
                        item = updateImg.length ? updateImg[0] : imagesToUpdate[0];
                        gallery.updateDataByIndex(0, item);

                        gallery.seek(1);
                    } else {
                        gallery.updateData(imagesToUpdate);
                        if (typeof AddFotoramaVideoEvents !== 'undefined' && $.isFunction(typeof AddFotoramaVideoEvents)) {
                            $(this.options.mediaGallerySelector).AddFotoramaVideoEvents();
                        }
                    }


                }
            } else if (justAnImage && justAnImage.img) {
                var $img = justAnImage.img;
                $.each( justAnImage, function( i, val ) {
                    if ($.type(val) === "string") {
                        var indexOfImg = val.indexOf(context.find('.product-image-photo').data('size'));
                        if(indexOfImg > 0) {
                            var $img = val;
                            justAnImage.finalImg = val;
                            return $img;
                        }
                    }
                });
                if (justAnImage.finalImg === undefined || justAnImage.finalImg === null) {
                    justAnImage.finalImg = justAnImage.img;
                }
                if (justAnImage.finalImg.replace(/^.*[\\\/]/, '') === 'transparent.gif') {
                    justAnImage.finalImg = context.find('.product-image-photo').attr('data-img');
                }
                context.find('.product-image-photo').attr('srcset',justAnImage.finalImg).attr('src',justAnImage.finalImg).attr('data-src',justAnImage.finalImg).waitForImages({
                    finished: function() {
                        // ...
                    },
                    each: function() {
                        // ...
                    },
                    waitForAll: true
                }).done(function() {
                    $(this).closest('.trigger-equal-height').attr('data-mage-init', JSON.stringify({'equalHeight': {'target': ' .product-item-info'}}));
                    $(this).closest('.trigger-equal-height').trigger('contentUpdated');
                });
            }

            if (this.options.isStickyLayout) {
                var options = {
                    images: images,
                    class: {
                        sticky_gallery_content: '.gallery-sticky2',
                        thumb_content: '.gallery-sticky2-image-thumb-col',
                        image_content: '.gallery-sticky2-image-col',
                        image_wrapper: '.gallery-sticky2-image-wrapper',
                        video_wrapper: '.gallery-sticky2-video-wrapper'
                    }
                };

                $.cleversoft.cleverBaseUpdateImageAndVideo(options);
            }
            if(this.options.equalHeightConfig) this._updateHeightOnCatalogListing('#zoo-product-listing');
        }
    });

    $.widget('mage.cleverSwatchRendererProductDetails',$.mage.cleverSwatchRenderer, {
        _RenderControls: function () {
            var $widget = this,
                container = this.element,
                classes = this.options.classes,
                chooseText = this.options.jsonConfig.chooseText;

            $widget.optionsMap = {};

            $.each(this.options.jsonConfig.attributes, function () {
                var item = this,
                    options = $widget._RenderSwatchOptions(item),
                    select = $widget._RenderSwatchSelect(item, chooseText),
                    input = $widget._RenderFormInput(item),
                    label = '';

                // Show only swatch controls
                if ($widget.options.onlySwatches && !$widget.options.jsonSwatchConfig.hasOwnProperty(item.id)) {
                    return;
                }

                if ($widget.options.enableControlLabel) {
                    label +=
                        '<span class="' + classes.attributeLabelClass + '">' + item.label + '</span>' +
                        '<span class="' + classes.attributeSelectedOptionLabelClass + '"></span>';
                }

                // Create new control
                container.append(
                    '<div class="' + classes.attributeClass + ' ' + item.code +
                    '" attribute-code="' + item.code +
                    '" attribute-id="' + item.id + '">' +
                    label +
                    '<div class="' + classes.attributeOptionsWrapper + ' clearfix">' +
                    options + select + input +
                    '</div>' +
                    '</div>'
                );

                $widget.optionsMap[item.id] = {};

                // Aggregate options array to hash (key => value)
                $.each(item.options, function () {
                    if (this.products.length > 0) {
                        $widget.optionsMap[item.id][this.id] = {
                            price: parseInt(
                                $widget.options.jsonConfig.optionPrices[this.products[0]].finalPrice.amount,
                                10
                            ),
                            products: this.products
                        };
                    }
                });
            });

            // Connect Tooltip
            container
                .find('[option-type="1"], [option-type="2"], [option-type="0"], [option-type="3"]')
                .cleverSwatchRendererTooltip();

            // Hide all elements below more button
            $('.' + classes.moreButton).nextAll().hide();

            // Handle events like click or change
            $widget._EventListener();

            // Rewind options
            $widget._Rewind(container);

            //Emulate click on all swatches from Request
            $widget._EmulateSelected($.parseQuery());
            $widget._EmulateSelected($widget._getSelectedAttributes());
        }
    });

});
