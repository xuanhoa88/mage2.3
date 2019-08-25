;(function ( $, window, document, undefined ) {
    define([
        'jquery',
        'jquery/ui',
        'pmLoadScripts',
        'pmIris',
        'pmColorPicker',
        'pmWpColorPicker'
    ], function($){

        $.PM = $.PM || {};

        // ======================================================
        // Init Tab
        // ------------------------------------------------------
        $.PM.Tab = function() {
            $( document ).on( 'click', '.box__nav--item', function( e ) {
                // Switch active tab index.
                $( this ).addClass( 'box__nav--active' ).siblings().removeClass( 'box__nav--active' );

                // Show target tab.
                var tab = $( this ).parent().next( '.box__content' ).children( '[data-tab="' + $( this ).attr( 'data-nav' ) + '"]' );

                if ( tab.length ) {
                    tab.removeClass( 'hide' ).siblings().addClass( 'hide' );
                }
            } );

            // Disnable tab Popup Settings if popup-type is link
            $( document ).on( 'click', '.item-styled input', function( e ) {
                var _this = $( this );
                var value = _this.val();
                var parent = _this.closest( '.pin__settings' );

                if( value == 'link' ) {
                    parent.find( '.nav li[data-nav="popup-settings"]' ).hide();
                } else {
                    parent.find( '.nav li[data-nav="popup-settings"]' ).show();
                }
            } );
        };

        // ======================================================
        // Init App
        // ------------------------------------------------------
        $.PM.Pin_Maker_Init = function() {

            // Define Backbone model for pin.
            var Pin = Backbone.Model.extend( {
                // Default attributes for the pin item.
                defaults: function() {
                    return {
                        top: 0,
                        left: 0,
                        settings: {},
                    };
                },
            } );
            // Define Backbone collection for pin list.
            var PinList = Backbone.Collection.extend( {
                // Reference to this collection’s model.
                model: Pin,

                // Disable fetching from remote server.
                url: '#',

                // Override default method for fetching data.
                fetch: function() {
                    if ( window.wpa_pin && window.wpa_pin.length ) {
                        this.add( window.wpa_pin );
                    }
                },
            } );

            // Create the global collection of pins.
            var Pins = new PinList, index = 0;

            // Define Backbone view for pin.
            var PinView = Backbone.View.extend( {
                tagName: 'div',
                className: 'pin',

                // Cache the template function for a single item.
                template: _.template( $( '#wpa_pin_maker_render_editor' ).text() ),

                // The DOM events specific to an item.
                events: {
                    'click .pin__action--edit': 'edit',
                    'click .box__bar--close': 'close',

                    'click .pin-action--delete': 'remove',
                    'click .pin-action--clone': 'clone',
                },

                // The PinView listens for changes to its model, re-rendering.
                // Since there’s a one-to-one correspondence between a Pin and a
                // PinView in this app, we set a direct reference on the model for
                // convenience.
                initialize: function() {
                    // Update index.
                    this.index = index++;
                },

                // Re-render the titles of the pin item.
                render: function() {
                    var self = this, settings = this.model.get( 'settings' );

                    this.$el.addClass( this.className ).html( this.template( this.model.toJSON() ) );

                    // Position the pin relatively to the image.
                    var top = this.model.get( 'top' ), left = this.model.get( 'left' );

                    if ( typeof top == 'number' || '%' != top.substr( -1 ) ) {
                        top = ( top / $( '.edit__wrap' ).height() ) * 100 + '%';

                        this.model.set( 'top', top );
                    }

                    if ( typeof left == 'number' || '%' != left.substr( -1 ) ) {
                        left = ( left / $( '.edit__wrap' ).width() ) * 100 + '%';

                        this.model.set( 'left', left );
                    }

                    this.$el.css( {
                        top: top,
                        left: left,
                    } );

                    // Make the pin draggable.
                    this.$el.draggable( {
                        stop: function( event, ui ) {
                            // Update model.
                            var top = ( ui.position.top / $( '.edit__wrap' ).height() ) * 100 + '%';
                            var left = ( ui.position.left / $( '.edit__wrap' ).width() ) * 100 + '%';

                            self.model.set( 'top', top );
                            self.model.set( 'left', left );

                            // Update form fields.
                            self.$el.find( '[data-option="top"]' ).val( top );
                            self.$el.find( '[data-option="left"]' ).val( left );

                            // Position the pin relatively to the image.
                            self.$el.css( {
                                top: top,
                                left: left,
                            } );

                            // Set an attribute to prevent edit form from
                            // displaying.
                            self.just_dragged = true;

                            setTimeout( function() {
                                self.just_dragged = false;
                            }, 200 );
                        },
                    } ).css( 'position', 'absolute' );

                    // Init tooltip.
                    if ( ! settings[ 'popup-type' ] || settings[ 'popup-type' ] == 'product' ) {
                        this.$el.find( '.tooltip' ).addClass( 'hide' );
                    }

                    // Bind pin settings to edit form.
                    this.$el.find( '[data-option]' ).each( function() {
                        // Get option name.
                        var option = $( this ).attr( 'data-option' ).match( /([^\[]+)(\[([^\[]+)\])*/ );
                        var upload_image = $( this ).parent().find('.upload__image');
                        // Update field name first.
                        if ( $( this ).attr( 'name' ) != option[ 0 ] ) {
                            if ( option[ 3 ] !== undefined ) {
                                $( this ).attr( 'name', 'wpa_pin[' + self.index + '][' + option[ 1 ] + '][' + option[ 3 ] + ']' );
                                $( this ).attr( 'id', 'target_element_id_' + self.index);
                                if ($( this ).attr('data-option') == 'settings[id]') {
                                    $( this ).val('id_'+ window.model_id + '_' + self.index);
                                }
                            } else {
                                $( this ).attr( 'name', 'wpa_pin[' + self.index + '][' + option[ 1 ] + ']' );
                                $( this ).attr( 'id', 'target_element_id_' + self.index);
                                if ($( this ).attr('data-option') == 'settings[id]') {
                                    $( this ).val('id_'+ window.model_id + '_' + self.index);
                                }
                            }
                            $onClick = upload_image.attr('onclick');
                            if (typeof $onClick != 'undefined') {
                                upload_image.attr('onclick',$onClick.replace("default_target_element_id",$( this ).attr( 'id')));
                            }
                        }

                        // Then set field value.
                        var value;

                        if ( option[ 3 ] !== undefined ) {
                            if ( settings[ option[ 3 ] ] ) {
                                value = settings[ option[ 3 ] ];
                            }
                        } else {
                            value = self.model.get( option[ 1 ] );
                        }

                        if ( value ) {
                            if ( $( this ).prop( 'nodeName' ) == 'INPUT' ) {
                                if ( $( this ).attr( 'type' ) == 'radio' ) {
                                    if ( $( this ).attr( 'value' ) == value ) {
                                        $( this ).attr( 'checked', 'checked' );
                                    } else {
                                        $( this ).removeAttr( 'checked' );
                                    }
                                } else {
                                    $( this ).val( value );

                                    if ( $( this ).attr( 'type' ) == 'hidden' && $( this ).next().attr( 'type' ) == 'checkbox' ) {
                                        if ( parseInt( value ) ) {
                                            $( this ).next().attr( 'checked', 'checked' );
                                        } else {
                                            $( this ).next().removeAttr( 'checked' );
                                        }
                                    }
                                }
                            } else if ( $( this ).prop( 'nodeName' ) == 'TEXTAREA' ) {
                                $( this ).val( value.replace( /<br>/g, "\n" ) );
                            } else {
                                $( this ).val( value );
                            }
                        }

                        // Live preview.
                        switch ( $( this ).attr( 'data-option' ) ) {
                            // Pin type
                            case 'settings[pin-type]' :
                                $( this ).change( function() {
                                    if ( $( this ).attr( 'checked' ) ) {
                                        var pin_action = self.$el.find( '.pin__action' ),
                                            pin_icon   = pin_action.children( 'i' ),
                                            pin_area   = pin_action.children( 'div' ),
                                            pin_image  = pin_action.children( 'img' ),
                                            field_wrappers = self.$el.find( '[data-pin-type="' + $( this ).val() + '"]' );

                                        if ( $( this ).val() == 'pin-icon' ) {
                                            pin_icon.removeClass( 'hide' );
                                            pin_area.addClass( 'hide' );
                                            pin_image.addClass( 'hide' );
                                        } else if ( $( this ).val() == 'pin-area' ) {
                                            pin_icon.addClass( 'hide' );
                                            pin_area.removeClass( 'hide' );
                                            pin_image.addClass( 'hide' );
                                        } else {
                                            pin_icon.addClass( 'hide' );
                                            pin_area.addClass( 'hide' );
                                            pin_image.removeClass( 'hide' );
                                        }

                                        field_wrappers.find( 'input, select, textarea' ).trigger( 'change' ).trigger( 'keyup' );
                                    }
                                } ).trigger( 'change' );
                                break;

                            // Live preview for pin icon
                            case 'settings[icon-size]' :
                                $( this ).change( function() {
                                    if ( $( this ).val() != '' && 'pin-icon' == self.$el.find( '[data-option*="pin-type"]:checked' ).val() ) {
                                        var pin_icon = self.$el.find( '.pin__icon--add' );

                                        pin_icon.removeClass( 'pin__size--small pin__size--medium pin__size--large' ).addClass( $( this ).val() );
                                    }
                                } ).trigger( 'change' );
                                break;

                            case 'settings[icon-border-width]' :
                                $( this ).keyup( function() {
                                    if ( $( this ).val() != '' && 'pin-icon' == self.$el.find( '[data-option*="pin-type"]:checked' ).val() ) {
                                        self.$el.find( '.pin__icon--add' ).css({
                                            'border-width': $( this ).val() + 'px',
                                            'border-style': 'solid'
                                        });
                                    }
                                } ).trigger( 'keyup' );
                                break;

                            case 'settings[icon-border-radius]' :
                                $( this ).keyup( function() {
                                    if ( $( this ).val() != '' && 'pin-icon' == self.$el.find( '[data-option*="pin-type"]:checked' ).val() ) {
                                        self.$el.find( '.pin__icon--add' ).css( 'border-radius', $( this ).val() + 'px' );
                                    }
                                } ).trigger( 'keyup' );
                                break;

                            case 'settings[icon-color]' :
                                $( this ).change( function() {
                                    if ( $( this ).val() != '' && 'pin-icon' == self.$el.find( '[data-option*="pin-type"]:checked' ).val() ) {
                                        self.$el.find( '.pin__icon--add' ).css( 'color', $( this ).val() );
                                    }
                                } ).trigger( 'change' );
                                break;

                            case 'settings[icon-border-color]' :
                                $( this ).change( function() {
                                    if ( $( this ).val() != '' && 'pin-icon' == self.$el.find( '[data-option*="pin-type"]:checked' ).val() ) {
                                        self.$el.find( '.pin__icon--add' ).css( 'border-color', $( this ).val() );
                                    }
                                } ).trigger( 'change' );
                                break;

                            case 'settings[icon-bg-color]' :
                                $( this ).change( function() {
                                    if ( $( this ).val() != '' && 'pin-icon' == self.$el.find( '[data-option*="pin-type"]:checked' ).val() ) {
                                        self.$el.find( '.pin__icon--add' ).css( 'background-color', $( this ).val() );
                                    }
                                } ).trigger( 'change' );
                                break;
                            // End live preview for pin icon

                            // Live preview for pin area
                            case 'settings[area-text]':
                                $( this ).keyup( function() {
                                    if ( $( this ).val().length == 0 ) {
                                        self.$el.find( '.pin__area' ).empty();
                                    }
                                    if ( $( this ).val() != '' && 'pin-area' == self.$el.find( '[data-option*="pin-type"]:checked' ).val() ) {
                                        self.$el.find( '.pin__area' ).html( $( this ).val() );
                                    }
                                } ).trigger( 'keyup' );
                                break;
                            case 'settings[area-text-size]':
                                $( this ).keyup( function() {
                                    if ( $( this ).val() != '' && 'pin-area' == self.$el.find( '[data-option*="pin-type"]:checked' ).val() ) {
                                        self.$el.find( '.pin__area' ).css( 'font-size', $( this ).val() + 'px' );
                                    }
                                } ).trigger( 'keyup' );
                                break;
                            case 'settings[area-text-color]':
                                $( this ).change( function() {
                                    if ( $( this ).val() != '' && 'pin-area' == self.$el.find( '[data-option*="pin-type"]:checked' ).val() ) {
                                        self.$el.find( '.pin__area' ).css( 'color', $( this ).val() + 'px' );
                                    }
                                } ).trigger( 'change' );
                                break;
                            case 'settings[area-width]':
                                $( this ).keyup( function() {
                                    if ( $( this ).val() != '' && 'pin-area' == self.$el.find( '[data-option*="pin-type"]:checked' ).val() ) {
                                        self.$el.find( '.pin__area' ).css( 'width', $( this ).val() + 'px' );
                                    }
                                } ).trigger( 'keyup' );
                                break;
                            case 'settings[area-height]':
                                $( this ).keyup( function() {
                                    if ( $( this ).val() != '' && 'pin-area' == self.$el.find( '[data-option*="pin-type"]:checked' ).val() ) {
                                        self.$el.find( '.pin__area' ).css( 'height', $( this ).val() + 'px' );
                                    }
                                } ).trigger( 'keyup' );
                                break;
                            case 'settings[area-border-width]':
                                $( this ).keyup( function() {
                                    if ( $( this ).val() != '' && 'pin-area' == self.$el.find( '[data-option*="pin-type"]:checked' ).val() ) {
                                        self.$el.find( '.pin__area' ).css({
                                            'border-width': $( this ).val() + 'px',
                                            'border-style': 'solid'
                                        });
                                    }
                                } ).trigger( 'keyup' );
                                break;
                            case 'settings[area-border-radius]':
                                $( this ).keyup( function() {
                                    if ( $( this ).val() != '' && 'pin-area' == self.$el.find( '[data-option*="pin-type"]:checked' ).val() ) {
                                        self.$el.find( '.pin__area' ).css( 'border-radius', $( this ).val() + 'px' );
                                    }
                                } ).trigger( 'keyup' );
                                break;
                            case 'settings[area-bg-color]' :
                                $( this ).change( function() {
                                    if ( $( this ).val() != '' && 'pin-area' == self.$el.find( '[data-option*="pin-type"]:checked' ).val() ) {
                                        self.$el.find( '.pin__area' ).css( 'background', $( this ).val() );
                                    }
                                } ).trigger( 'change' );
                                break;
                            case 'settings[area-border-color]' :
                                $( this ).change( function() {
                                    if ( $( this ).val() != '' && 'pin-area' == self.$el.find( '[data-option*="pin-type"]:checked' ).val() ) {
                                        self.$el.find( '.pin__area' ).css( 'border-color', $( this ).val() );
                                    }
                                } ).trigger( 'change' );
                                break;
                            // End live preview for pin area

                            // Live preview for pin image
                            case 'settings[image-file]':
                                $( this ).change( function() {
                                    var pin_image = self.$el.find( '.pin__image' );
                                    if ( $( this ).val() != '' && 'pin-image' == self.$el.find( '[data-option*="pin-type"]:checked' ).val() ) {
                                        if ( pin_image.length > 0 ) {
                                            pin_image.attr( 'src', window.mediaUrl + $( this ).val() );
                                        }
                                    }
                                } ).trigger( 'change' );
                                break;
                            case 'settings[image-width]':
                                $( this ).keyup( function() {
                                    if ( $( this ).val() != '' && 'pin-image' == self.$el.find( '[data-option*="pin-type"]:checked' ).val() ) {
                                        self.$el.find( '.pin__image' ).css( 'width', $( this ).val() + 'px' );
                                    }
                                } ).trigger( 'keyup' );
                                break;
                            case 'settings[image-height]':
                                $( this ).keyup( function() {
                                    if ( $( this ).val() != '' && 'pin-image' == self.$el.find( '[data-option*="pin-type"]:checked' ).val() ) {
                                        self.$el.find( '.pin__image' ).css( 'height', $( this ).val() + 'px' );
                                    }
                                } ).trigger( 'keyup' );
                                break;
                            case 'settings[image-border-radius]':
                                $( this ).keyup( function() {
                                    if ( $( this ).val() != '' && 'pin-image' == self.$el.find( '[data-option*="pin-type"]:checked' ).val() ) {
                                        self.$el.find( '.pin__image' ).css( 'border-radius', $( this ).val() + 'px' );
                                    }
                                } ).trigger( 'keyup' );
                                break;
                            // End live preview for pin image
                        }

                        // Init color picker if needed.
                        if ( $( this ).hasClass( 'color-picker' ) ) {
                            $( this ).cs_wpColorPicker();
                        }

                        // Init product selector if needed.
                        else if ( $( this ).hasClass( 'product__selector' ) ) {
                            console.log('select product');
                        }
                    } );

                    return this;
                },

                // Display the settings form.
                edit: function( event ) {
                    if ( ! $( event.target ).parent().parent().hasClass( 'pin' ) || this.just_dragged ) {
                        return;
                    }

                    if ( this.$el.hasClass( 'opened' ) ) {
                        this.close( event );
                    } else {
                        if ( !this.$el.data( 'wpa_pin_maker_settings_initialized' ) ) {
                            var self = this;

                            this.$el.on( 'change', 'input[type="radio"], select', function() {
                                if ( $( this ).attr( 'data-option' ) ) {
                                    var option = $( this ).attr( 'data-option' ).match( /([^\[]+)(\[([^\[]+)\])*/ ), value = $( this ).val();

                                    if ( option[ 3 ] ) {
                                        self.$el.find( '[data-' + option[ 3 ] + ']' ).each( function() {
                                            if ( $( this ).attr( 'data-' + option[ 3 ] ).indexOf( value ) > -1 ) {
                                                $( this ).removeClass( 'hide' );
                                            } else {
                                                $( this ).addClass( 'hide' );
                                            }
                                        } );
                                    }
                                }
                            } ).find( 'input[type="radio"]:checked, select' ).trigger( 'change' );

                            this.$el.data( 'wpa_pin_maker_settings_initialized', true );
                        }

                        // Disable draggable on the pin.
                        this.$el.draggable( 'option', 'disabled', true );

                        // Show edit form.
                        this.$el.addClass( 'opened' );

                        // Make sure the edit form does not go off-screen.
                        var form = this.$el.children( '.pin__settings' );

                        if ( 'auto' == form.css( 'top' ) ) {
                            var offset_top = this.$el.height();

                            if ( form.offset().top + form.height() > $( window ).height() ) {
                                offset_top += $( window ).height() - ( form.offset().top + form.height() );
                                offset_top -= ( parseInt( form.css( 'border-top-width' ) ) + parseInt( form.css( 'border-bottom-width' ) ) );
                            }

                            form.css( 'top', offset_top + 'px' );
                        }

                        if ( 'auto' == form.css( 'left' ) ) {
                            var offset_left = 0;

                            if ( form.offset().left + form.width() > $( window ).width() ) {
                                offset_left += $( window ).width() - ( form.offset().left + form.width() );
                                offset_left -= ( parseInt( form.css( 'border-left-width' ) ) + parseInt( form.css( 'border-right-width' ) ) );
                            }

                            form.css( 'left', offset_left + 'px' );
                        }

                        // Init draggable on the edit form.
                        form.draggable();
                    }
                },

                // Close the settings form, saving changes to the pin.
                close: function( event ) {
                    // Destroy draggable on the edit form.
                    this.$el.children( '.pin__settings' ).draggable( 'destroy' );

                    // Hide edit form.
                    this.$el.removeClass( 'opened' );

                    // Update tooltip.
                    var pin_type = this.$el.find( 'input[data-option*="popup-type"]:checked' ).val();

                    if ( pin_type == 'product' ) {
                        this.$el.find( '.tooltip' ).addClass( 'hide' );
                    } else {
                        var title = this.$el.find( 'input[data-option*="popup-title"]' ).val();

                        this.$el.find( '.tooltip' ).text( title ? title : wpa_pin_maker.text.please_input_a_title ).removeClass( 'hide' );
                    }

                    // Enable draggable on the pin.
                    this.$el.draggable( 'option', 'disabled', false );
                },

                // Remove the item, destroy the model.
                remove: function( event ) {
                    event.preventDefault();

                    if ( confirm( wpa_pin_maker.text.confirm_removing_pin ) ) {
                        this.$el.remove();
                        this.model.destroy();

                        // State that data have changed.
                        $( '#post #save' ).attr( 'data-changed', 'yes' );
                    }
                },

                // Clone the item.
                clone: function( event ) {
                    event.preventDefault();

                    // Prevent cloning continously.
                    if ( !this.just_cloned ) {
                        // Prepare settings for new pin.
                        var settings = {};

                        this.$el.children( '.pin__settings' ).find( 'input, select, textarea' ).each( function( i, e ) {
                            if ( $( e ).attr( 'data-option' ) ) {
                                var option = $( e ).attr( 'data-option' ).match( /([^\[]+)(\[([^\[]+)\])*/ );

                                if ( option[3] !== undefined ) {
                                    if ( e.nodeName == 'INPUT' ) {
                                        if ( e.type == 'checkbox' || e.type == 'radio' ) {
                                            if ( e.checked ) {
                                                settings[ option[3] ] = $( e ).val();
                                            }
                                        } else {
                                            settings[ option[3] ] = $( e ).val();
                                        }
                                    } else {
                                        settings[ option[3] ] = $( e ).val();
                                    }
                                }
                            }
                        })

                        settings.id = '';

                        // Add new pin.
                        Pins.add( [ {
                            top: ( parseInt( this.model.get( 'top' ) ) + 1 ) + '%',
                            left: ( parseInt( this.model.get( 'left' ) ) + 1 ) + '%',
                            settings: settings,
                        } ] );

                        // State that cloning just occurred.
                        var self = this;

                        self.just_cloned = true;

                        setTimeout( function() {
                            self.just_cloned = false;
                        }, 200 );
                    }
                },
            } );

            // Define Backbone view for pin list.
            var PinListView = Backbone.View.extend( {
                // Instead of generating a new element, bind to the existing
                // skeleton of the pin list view already present in the HTML.
                el: '.edit__wrap',

                // At initialization we bind to the relevant events on the Pins
                // collection, when items are added or changed. Kick things off by
                // loading any preexisting pins that might be defined before.
                initialize: function() {
                    this.listenTo( Pins, 'add', this.addOne );

                    // Setup event for creating new pins.
                    $( document ).on( 'click', '.edit__wrap > img', this.create );

                    // Fetch any preexisting pins that might be defined before.
                    Pins.fetch();
                },

                // Add a single pin item to the list by creating a view for it, and
                // appending its element to the wrapper.
                addOne: function( pin ) {
                    var view = new PinView( {
                        model: pin
                    } ), el = view.render().el;

                    this.$el.append( el );
                },

                // Create new pin item.
                create: function( event ) {
                    if ( !$( '.pin.opened' ).length ) {
                        Pins.add( [ {
                            top: ( event.clientY - $( event.target ).offset().top ) + $( window ).scrollTop() - 12,
                            left: ( event.clientX - $( event.target ).offset().left ) + $( window ).scrollLeft() - 12,
                        } ] );

                        // State that data have changed.
                        $( '#post #save' ).attr( 'data-changed', 'yes' );
                    }
                },
            } );

            // Register event to initialize application.
            // $( document ).on( 'wpa_pin_maker_init', function( event ) {
            if ( !$( document ).data( 'wpa_pin_maker_settings_init' ) ) {

                // Init general settings.
                $( '.pm-general-setting-trigger' ).click( function() {
                    // Show settings form.
                    $( this ).parent().toggleClass( 'opened' );

                    // Make sure the edit form does not go off-screen.
                    var form = $( this ).next();

                    if ( 'auto' == form.css( 'top' ) || 0 === parseInt( form.css( 'top' ) ) ) {
                        var offset_top = $( this ).parent().height();

                        if ( form.offset().top + form.height() > $( window ).height() ) {
                            offset_top += $( window ).height() - ( form.offset().top + form.height() );
                            offset_top -= ( parseInt( form.css( 'border-top-width' ) ) + parseInt( form.css( 'border-bottom-width' ) ) );
                        }

                        form.css( 'top', offset_top + 'px' );
                    }

                    if ( 'auto' == form.css( 'left' ) || 0 === parseInt( form.css( 'left' ) ) ) {
                        var offset_left = 0;
                        if ( form.offset().left + form.width() > $( window ).width() ) {
                            offset_left += $( window ).width() - ( form.offset().left + form.width() );
                            offset_left -= ( parseInt( form.css( 'border-left-width' ) ) + parseInt( form.css( 'border-right-width' ) ) );
                        }

                        form.css( 'left', offset_left + 'px' );
                    }
                } );



                // Track click to hide popup / modal.
                $( document ).click( function( event ) {
                    // Check if there is any media modal visible.
                    if ( $( event.target ).closest( '.media-modal' ).length ) {
                        return;
                    }

                    // Hide all color picker popup if not being focused.
                    $( '.pin-maker .wp-picker-holder .iris-picker' ).each( function() {
                        if ( $( this ).css( 'display' ) != 'none' && !$.contains( this, event.target ) ) {
                            $( this ).parent().children().hide();
                        }
                    } );

                    // Hide all icon selector popup if not being focused.
                    $( '.pin-maker .icon-selector' ).each( function() {
                        if ( $( this ).children( '.icon-wrap' ).css( 'display' ) != 'none' && !$.contains( this, event.target ) ) {
                            $( this ).children( '.icon-wrap' ).hide();
                        }
                    } );


                } );

                $( document ).data( 'wpa_pin_maker_settings_init', true );
            }
            if ( ! window.wpa_pin_app && $( '.edit__wrap' ).length ) {
                // Init pin list view.
                window.wpa_pin_app = new PinListView;
            }
            // } );
        };

        $( document ).ready( function() {
            $.PM.Tab();
            $.PM.Pin_Maker_Init();

        } );
    });
})( jQuery, window, document );
