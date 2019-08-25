define([
    'jquery',
    'jquery/ui',
    'headerbuilder-scripts'
], function($){


    (function( $, cleverbuilder ) {
        var $document = $( document );

        var Header_Layout_Builder = {
            "builders": {
                "header": {
                    "id": "header",
                    "title": "Header Builder",
                    "control_id": "header_builder_panel",
                    "panel": "header_settings",
                    "section": "header_builder_panel",
                    "devices": {"desktop": "Desktop", "mobile": "Mobile\/Tablet"},
                    "items": {
                        "html-1": {
                            "name": "HTML 1",
                            "id": "html-1",
                            "col": 0,
                            "width": "3"
                        },
                        "html-2": {
                            "name": "HTML 2",
                            "id": "html-2",
                            "col": 0,
                            "width": "3"
                        },
                        "html-3": {
                            "name": "HTML 3",
                            "id": "html-3",
                            "col": 0,
                            "width": "3"
                        },
                        "html-4": {
                            "name": "HTML 4",
                            "id": "html-4",
                            "col": 0,
                            "width": "3"
                        },
                        "html-5": {
                            "name": "HTML 5",
                            "id": "html-5",
                            "col": 0,
                            "width": "3"
                        },
                        "primary_menu": {
                            "name": "Primary Menu",
                            "id": "primary_menu",
                            "col": 0,
                            "width": "4",
                            "devices": "desktop"
                        },
                        "top_menu": {
                            "name": "Top Menu",
                            "id": "top_menu",
                            "col": 0,
                            "width": "3"
                        },
                        "menu": {
                            "name": "Menu Mobile",
                            "id": "menu",
                            "col": 0,
                            "width": "4",
                            "devices": "mobile"
                        },
                        "sidebar_icon": {
                            "name": "Canvas",
                            "id": "sidebar_icon",
                            "width": "1"
                        },
                        "logo": {
                            "name": "Logo & Site Identity",
                            "id": "logo",
                            "width": "2"
                        },
                        "top.search": {
                            "name": "Search Box",
                            "id": "top.search",
                            "col": 0,
                            "width": "3"
                        },
                        "compare_link": {
                            "name": "Compare",
                            "id": "compare_link",
                            "col": 0,
                            "width": "1"
                        },
                        "minicart": {
                            "name": "Mini Cart",
                            "id": "minicart",
                            "col": 0,
                            "width": "1"
                        },
                        "user_area": {
                            "name": "My Account",
                            "id": "user_area",
                            "col": 0,
                            "width": "1"
                        },
                        "wishlist": {
                            "name": "Wishlist",
                            "id": "wishlist",
                            "col": 0,
                            "width": "1"
                        },
                        "store_language": {
                            "name": "Language",
                            "id": "store_language",
                            "col": 0,
                            "width": "1"
                        },
                        "currency": {
                            "name": "Currency",
                            "id": "currency",
                            "col": 0,
                            "width": "1"
                        }
                    },
                    "rows": {
                        "top": "Header Top",
                        "main": "Header Main",
                        "bottom": "Header Bottom",
                        "sidebar": "Menu Sidebar"
                    }
                }
            },
            "is_rtl": ""
        };

        var is_rtl = Header_Layout_Builder.is_rtl;

        var HeaderBuilder = function( options, id ){

            var Builder = {
                id: id,
                controlId: '',
                cols: 12,
                cellHeight: 45,
                items: [],
                container: null,
                ready: false,
                devices: {'desktop': 'Desktop', 'mobile': 'Mobile/Tablet' },
                activePanel: 'desktop',
                panels: {},
                activeRow: 'main',
                draggingItem: null,
                getTemplate: _.memoize(function () {
                    var control = this;
                    var compiled,
                        options = {
                            evaluate: /<#([\s\S]+?)#>/g,
                            interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                            escape: /\{\{([^\}]+?)\}\}(?!\})/g,
                            variable: 'data'
                        };

                    return function (data, id, data_variable_name ) {
                        if (_.isUndefined(id)) {
                            id = 'tmpl-customize-control-' + control.type;
                        }
                        if ( ! _.isUndefined( data_variable_name ) && _.isString( data_variable_name ) ) {
                            options.variable = data_variable_name;
                        } else {
                            options.variable = 'data';
                        }
                        compiled = _.template($('#' + id).html(), null, options);
                        return compiled(data);
                    };

                }),
                drag_drop: function(){
                    var that = this;

                    $( '.cleversoft--device-panel', that.container ).each( function(){
                        var panel = $( this );
                        var device = panel.data( 'device' );
                        var sortable_ids= [];
                        that.panels[ device ] = {};
                        $( '.cleversoft--cb-items', panel ).each( function( index ){
                            var data_name = $( this ).attr( 'data-id' ) || '';
                            var id;
                            if ( ! data_name ) {
                                id = '_sid_'+device+index;
                            } else {
                                id = '_sid_'+device+'-'+data_name;
                            }
                            $( this ).attr( 'id', id );
                            sortable_ids[ index ] = '#'+id;
                        });
                        $( '.grid-stack', panel ).each( function(){
                            var _id = $( this ).attr( 'data-id' ) || '';
                            that.panels[ device ][ _id ] = $( this );
                            $( this ).droppable( {
                                drop: function( event, ui ) {
                                    var $wrapper = $( this );
                                    that.gridster( $wrapper, ui, event );
                                    that.save();
                                }
                            } );

                        } );

                        var sidebar = $( '.cleversoft--sidebar-items', panel );
                        var sidebar_id = sidebar.attr( 'id' ) || false;

                        $( '.cleversoft-available-items .grid-stack-item', panel ).draggable({
                            revert: 'invalid',
                            connectToSortable: ( sidebar_id ) ? '#'+sidebar_id : false,
                            start: function( event, ui ){
                                $( 'body' ).addClass( 'builder-item-moving' );
                                $( '.cleversoft--cb-items', panel ).css( 'z-index', '' );
                                ui.helper.parent().css( 'z-index', 9999 );
                            },
                            stop: function(  event, ui ){
                                $( 'body' ).removeClass( 'builder-item-moving' );
                                $( '.cleversoft--cb-items', panel ).css( 'z-index', '' );
                                ui.helper.parent().css( 'z-index', '' );
                                if (device == 'desktop') {
                                    that.updateGridData($("#_sid_desktop-sidebar"));
                                } else {
                                    that.updateGridData($("#_sid_mobile-sidebar"));
                                }
                                
                            }

                        });

                        if ( sidebar.length > 0 ) {
                            sidebar.sortable({
                                revert: true,
                                change: function( event, ui ) {
                                    that.save();
                                },
                                receive: function( event, ui ) {
                                    $( this ).find( '.grid-stack-item' ).removeAttr('style').attr( 'data-gs-width', 1 );
                                    that.save();
                                }
                            });

                            that.panels[ device ][ 'sidebar' ] = sidebar;
                        }


                        $( '.cleversoft-available-items .grid-stack-item', panel ).resizable({
                            handles: 'w, e',
                            stop: function( event, ui ){
                                that.setGridWidth( ui.element.parent(), ui );
                                that.save();
                            }
                        });


                    } );
                },
                sortGrid: function( $wrapper ){
                    $(".grid-stack-item", $wrapper ).each( function( ){
                        var el = $( this );
                        var x = el.attr( 'data-gs-x' ) || 0;
                        x = parseInt( x );
                        var next = el.next();
                        if ( next.length > 0 ) {
                            var nx = next.attr( 'data-gs-x' ) || 0;
                            nx = parseInt( nx );
                            if ( x > nx ) {
                                el.insertAfter( next );
                            }
                        }
                    } );

                },
                getX: function( $item ){
                    var x = $item.attr( 'data-gs-x' ) || 0;
                    return parseInt( x );
                },
                getW: function( $item, df ){
                    if ( _.isUndefined( df ) ) {
                        df = false;
                    }
                    var w;
                    if ( df ) {
                        w = $item.attr( 'data-df-width' ) || 1;
                    } else {
                        w = $item.attr( 'data-gs-width' ) || 1;
                    }
                    return parseInt( w );
                },
                gridGetItemInfo: function( $item, flag, $wrapper ) {
                    var that = this;
                    var x = that.getX( $item );
                    var w = that.getW( $item );
                    var slot_before = 0;
                    var slot_after = 0;
                    var i;

                    var br = false;

                    i = x-1;
                    while ( i >= 0 && ! br ) {
                        if ( flag[ i ] === 0 ) {
                            slot_before++;
                        } else {
                            br = true;
                        }
                        i--;
                    }

                    br = false;
                    i = x + w ;
                    while ( i < that.cols && ! br ) {
                        if ( flag[ i ] === 0 ) {
                            slot_after++;
                        } else {
                            br = true;
                        }
                        i++;
                    }

                    return {
                        flag: flag,
                        x: x,
                        w: w,
                        item: $item,
                        before: slot_before,
                        after: slot_after,
                        id: $item.attr( 'data-id' ) || '',
                        wrapper: $wrapper
                    }
                },
                updateItemsPositions: function( flag ){
                    var maxCol = this.cols;
                    for( var i = 0; i <= maxCol; i++ ) {
                        if( typeof  flag[i] === 'object' || typeof flag[i] === 'function'  ) {
                            flag[i].attr( 'data-gs-x', i );
                        }
                    }
                },
                gridster: function( $wrapper, ui, event ){
                    var flag = [], backupFlag = [], that = this;
                    var maxCol = this.cols;

                    var addItemToFlag = function( node ){
                        var x = node.x, w = node.w;
                        var el = node.el;

                        for ( var i = x; i < x+w ; i++ ) {
                            if( i === x ) {
                                flag[ i ] = el;
                            } else {
                                flag[ i ] = 1;
                            }
                        }
                    };

                    var removeNode = function( node ){
                        var x = node.x, w = node.w;
                        var el = node.el;
                        for ( var i = x; i < x+w ; i++ ) {
                            flag[ i ] = 0;
                        }
                    };

                    var  getEmptySlots = function ( ) {
                        var emptySlots = 0;
                        for( var i = 0; i< maxCol; i++ ) {
                            if ( flag[ i ] === 0 ) {
                                emptySlots ++;
                            }
                        }

                        return emptySlots;
                    };

                    var getRightEmptySlotFromX = function (x, stopWhenNotEmpty){
                        var emptySlots = 0;
                        for( var i = x; i < maxCol; i++ ) {
                            if ( flag[ i ] === 0 ) {
                                emptySlots ++;
                            } else {
                                if ( stopWhenNotEmpty ) {
                                    return emptySlots;
                                }
                            }
                        }
                        return emptySlots;
                    };

                    var getLeftEmptySlotFromX = function (x, stopWhenNotEmpty ){
                        var emptySlots = 0;
                        if ( typeof stopWhenNotEmpty === "undefined" ) {
                            stopWhenNotEmpty = false;
                        }
                        for( var i = x; i >= 0; i-- ) {
                            if ( flag[ i ] === 0 ) {
                                emptySlots ++;
                            } else {
                                if ( stopWhenNotEmpty ) {
                                    return emptySlots;
                                }
                            }
                        }
                        return emptySlots;
                    };

                    var isEmptyX = function ( x ){
                        if ( flag[ x ] === 0 ) {
                            return true;
                        }
                        return false;
                    };

                    var checkEnoughSpaceFromX = function (x, w){
                        var check = true;
                        var i = x;
                        var j;
                        while ( i < x + w && check ) {
                            if ( flag[ i ] !== 0 ) {
                                return false;
                            }
                            i++;
                        }
                        return check;
                    };

                    var getPrevBlock = function( x ){
                        if ( x < 0 ) {
                            return {
                                x: -1,
                                w: 1
                            }
                        }

                        var i, _x = -1, _xw, found;

                        if ( flag[x] <= 1  ) {
                            i= x;
                            found = false;
                            while ( i >= 0 && ! found ) {
                                if ( flag[i] !== 1 && flag[i] !== 0 ) {
                                    _x = i;
                                    found = true;
                                }
                                i--;
                            }
                        } else {
                            _x = x;
                        }

                        i = _x + 1;
                        _xw = _x;

                        while( flag[ i ] === 1 ) {
                            _xw ++ ;
                            i++;
                        }
                        return {
                            x: _x,
                            w: ( _xw + 1 ) - _x
                        }
                    };

                    var getNextBlock = function( x ){
                        var i, _x = -1, _xw, found;

                        if ( flag[x] < maxCol  ) {
                            i = x;
                            found = false;
                            while ( i < maxCol && ! found ) {
                                if ( flag[i] !== 1 && flag[i] !== 0 ) {
                                    _x = i;
                                    found = true;
                                }
                                i++;
                            }
                        } else {
                            _x = x;
                        }

                        i = _x + 1;
                        _xw = _x;

                        while( flag[ i ] === 1 ) {
                            _xw ++ ;
                            i++;
                        }
                        return {
                            x: _x,
                            w: ( _xw + 1 ) - _x
                        }
                    };

                    var moveAllItemsFromXToLeft = function( x, number ){
                        var backupFlag = flag.slice();
                        var maxNumber = getLeftEmptySlotFromX( x );

                        if ( maxNumber === 0 ) {
                            return number;
                        }
                        var prev =  getPrevBlock( x );
                        var newX = prev.x >= 0 ? prev.x + prev.w - 1 : x;
                        var nMove = number;
                        if ( number > maxNumber ) {
                            nMove = maxNumber;
                        } else {
                            nMove = number;
                        }

                        var xE = 0, c = 0, i = newX;
                        while ( c <= nMove && i >= 0 ) {
                            if ( flag[i] === 0 ) {
                                c++;
                                xE = i;
                            }
                            i--;
                        }

                        var flagNoEmpty = [], j = 0;
                        for ( i =  xE; i <= newX; i++ ) {
                            flag[i] =0;
                            if ( backupFlag[ i ] !== 0 ) {
                                flagNoEmpty[j] = backupFlag[ i ];
                                j++;
                            }
                        }

                        j = 0;
                        for ( i = xE; i<= newX; i++ ){
                            if ( typeof flagNoEmpty[ j ] !== "undefined" ) {
                                flag[ i ] = flagNoEmpty[ j ];
                            } else {
                                flag[ i ] = 0;
                            }
                            j++;
                        }


                        var left = number - nMove;
                        return left;

                    };

                    var moveAllItemsFromXToRight = function ( x, number ){
                        var backupFlag = flag.slice();
                        var maxNumber = getRightEmptySlotFromX( x );
                        if ( maxNumber === 0 ) {
                            return number;
                        }

                        var prev = getPrevBlock( x );
                        var newX = prev.x >= 0 ? prev.x : x;
                        var nMove = number;
                        if ( number <= maxNumber ) {
                            nMove = number;
                        } else {
                            nMove = maxNumber;
                        }

                        var xE = x, c = 0, i = newX;
                        while ( c < nMove && i < maxCol ) {
                            if ( flag[i] === 0 ) {
                                c++;
                                xE = i;
                            }
                            i++;
                        }

                        var flagNoEmpty = [], j = 0;

                        for ( i = newX ; i <= xE; i++ ) {
                            flag[i] =0;
                            if ( backupFlag[ i ] !== 0 ) {
                                flagNoEmpty[j] = backupFlag[ i ];
                                j++;
                            }
                        }

                        j = flagNoEmpty.length - 1;
                        for ( i = xE; i >= newX; i-- ){
                            if ( typeof flagNoEmpty[ j ] !== "undefined" ) {
                                flag[ i ] = flagNoEmpty[ j ];
                            } else {
                                flag[ i ] = 0;
                            }
                            j--;
                        }

                        var left = number - nMove ;
                        return left;

                    };

                    var updateItemsPositions = function(){
                        that.updateItemsPositions( flag );
                    };

                    var insertToFlag = function( node, swap ){
                        var x = node.x, w = node.w;

                        var emptySlots = getEmptySlots();

                        if( emptySlots <= 0 ) {
                            return false;
                        }

                        if ( _.isUndefined( swap ) ) {
                            swap = false;
                        }

                        var _x;
                        var _re;
                        var _le;
                        var _w;

                        if ( ! swap ) {
                            if (isEmptyX(x)) {
                                _w = w;

                                if ( checkEnoughSpaceFromX(x, _w)) {
                                    addItemToFlag(node);
                                    node.el.attr('data-gs-x', x);
                                    node.el.attr('data-gs-width', _w);
                                    return true;
                                }

                                _re = getRightEmptySlotFromX(x, true);
                                _le = getLeftEmptySlotFromX(x-1, true);

                                if ( _re + _le >= w && ( w - _re ) <= _le ) {
                                    _x =  x - ( w - _re );
                                } else {
                                    _x = x - _le;
                                }

                                if ( _x < 0 ) {
                                    _x = 0;
                                }

                                while (_w >= 1) {
                                    if (checkEnoughSpaceFromX(_x, _w)) {
                                        node.x = _x;
                                        node.w = _w;
                                        addItemToFlag(node);
                                        node.el.attr('data-gs-x', _x );
                                        node.el.attr('data-gs-width', _w);
                                        return true;
                                    }
                                    _w--;
                                }

                            }

                            if (flag[x] === 1) {
                                var prev = getPrevBlock(x);
                                if (prev.x >= 0) {

                                    if (x > prev.x + Math.floor(prev.w / 2) && x > prev.x) {
                                        _x = prev.x + prev.w;
                                        _re = getRightEmptySlotFromX(_x, true);
                                        if (_re >= w) {
                                            addItemToFlag({el: node.el, x: _x, w: w});
                                            node.el.attr('data-gs-x', _x);
                                            node.el.attr('data-gs-width', w);
                                            return true;
                                        }
                                    }

                                }
                            }
                        }


                        var remain = 0;

                        var _move_to_swap = function( node, _x ){

                            var _block_prev;
                            var _block_next;
                            var _empty_slots = 0;
                            var found = false;
                            var  i, el, er;

                            if ( isEmptyX( _x ) ) {
                                _block_prev = getPrevBlock( _x );
                                _block_next = getNextBlock( _x );
                                if ( _block_prev.x > -1 ) {
                                    _empty_slots = getRightEmptySlotFromX( _block_prev.x );
                                    if( _empty_slots >= node.w ) {
                                        if ( checkEnoughSpaceFromX( _x, node.w ) ) {
                                            x = _x;
                                            found = true;
                                        } else if ( node.ox > _x ) {
                                            i = _block_prev.x + _block_prev.w ;
                                            el = getLeftEmptySlotFromX( i );
                                            if ( el <= node.w ) {
                                                el =  node.w - el;
                                            } else{
                                                el = node.w;
                                            }
                                            moveAllItemsFromXToRight( i + 1, el );
                                            _empty_slots = getRightEmptySlotFromX( i );
                                            found = false;
                                            while ( i > _block_prev.x + _block_prev.w && ! found ) {
                                                if ( checkEnoughSpaceFromX( i, node.w ) ) {
                                                    x = i;
                                                    found = true;
                                                }
                                                i--;
                                            }
                                        }
                                    }

                                    if ( ! found  && node.ox < _x ) {
                                        i = _block_prev.x + _block_prev.w - 1;
                                        el = getLeftEmptySlotFromX( _block_prev.x );
                                        if ( el > node.w ) {
                                            el =  node.w;
                                        }
                                        el -= 2;
                                        moveAllItemsFromXToLeft( _block_prev.x, el );
                                        _empty_slots = getRightEmptySlotFromX( i );
                                        i -= _empty_slots;
                                        _block_next = getNextBlock( _x );
                                        var max = _block_prev.x + _block_prev.w;
                                        if ( _block_next.x > -1 ) {
                                            max = _block_next.x;
                                        }
                                        while ( i < max && ! found ) {
                                            if ( checkEnoughSpaceFromX( i, node.w ) ) {
                                                x = i;
                                                found = true;
                                            }
                                            i++;
                                        }


                                    }


                                    if ( ! found ) {
                                        x = _block_prev.x + _block_prev.w;
                                        node.w = _empty_slots;
                                        node.x = x;
                                    }

                                } else if( _block_next.x > -1 ) {
                                    _block_next = getNextBlock( _x );
                                    _empty_slots = getRightEmptySlotFromX( _x, false );
                                    var n_move = _empty_slots >= node.w ? node.w : _empty_slots;
                                    moveAllItemsFromXToRight( _x, n_move );
                                    i = _block_next.x;
                                    while ( i >= 0 && ! found ) {
                                        if ( checkEnoughSpaceFromX( i, node.w ) ) {
                                            x = i;
                                            node.x = x;
                                            found = true;
                                        }
                                        i--;
                                    }

                                    if ( ! found ) {
                                        x = _x;
                                        node.w = _empty_slots;
                                        node.x = x;
                                    }

                                }
                            } else {
                                _block_prev = getPrevBlock( _x );
                                if (_block_prev.w == 1 && node.w == 1) {
                                    x = _x;
                                } else {
                                    if ( node.ox < _block_prev.x ) {
                                        moveAllItemsFromXToLeft( _x, node.w );
                                        if ( isEmptyX( _x ) ) {
                                            x = _x;
                                        } else {
                                            while ( ! isEmptyX( _x ) && _x <= that.cols - 1 ) {
                                                _x++;
                                            }
                                            x = _x;
                                        }
                                    } else {
                                        moveAllItemsFromXToRight( _x, node.w );
                                        if ( isEmptyX( _x ) ) {
                                            x = _x;
                                        } else {
                                            while ( ! isEmptyX( _x ) && _x >=0 ) {
                                                _x--;
                                            }
                                            x = _x;
                                        }
                                    }
                                }
                            }

                            if ( x > that.cols ) {
                                x = that.cols - 1;
                            }
                            node.x = x;

                        };

                        _move_to_swap( node, _.clone( x ) );

                        var newX = x;
                        var i;
                        var found = false;
                        var le = 0 ;

                        var _block_prev = getPrevBlock( x );

                        if (_block_prev.w > 1) {
                            if ( x + w > that.cols - 1 ) {
                                le = getLeftEmptySlotFromX(x, true);
                            }
                        }

                        updateItemsPositions();

                        if (_block_prev.w == 1 && w == 1) {
                            var node_exist = node.el.parent().find('.grid-stack-item[data-gs-x="' + node.x + '"]');
                            var node_exist_count = node_exist.length;
                            w = node.w;
                            addItemToFlag( {el: node.el, x: node.x, w: w});
                            node.el.attr( 'data-gs-x', node.x );
                            node.el.attr( 'data-gs-width', w );

                            node.el.attr('data-gs-y', ((node_exist_count + 1)));
                        } else {
                            node.el.attr('data-gs-y', 1);
                            le = 0;
                            while( w >= 1 ) {
                                if ( emptySlots >= w ) {
                                    if (checkEnoughSpaceFromX(x, w)) {
                                        node.w = w;
                                        addItemToFlag(node);
                                        node.el.attr( 'data-gs-x', x );
                                        node.el.attr( 'data-gs-width', w );
                                        return true;
                                    }

                                    found = false;
                                    le = getLeftEmptySlotFromX(x, true);
                                    newX = x - le;
                                    i = newX;
                                    while (i < maxCol && !found) {
                                        if ( checkEnoughSpaceFromX(i, w) ) {
                                            node.w = w;
                                            addItemToFlag( {el: node.el, x: i, w: w});
                                            node.el.attr( 'data-gs-x', i );
                                            node.el.attr( 'data-gs-width', w );
                                            found = true;
                                            return true;
                                        }
                                        i++;
                                    }
                                }
                                w --;
                            }


                            w = node.w;
                            found = false;
                            while( w >= 1 ) {
                                i = 0;
                                while (i < maxCol && !found) {
                                    if ( checkEnoughSpaceFromX(i, w) ) {
                                        addItemToFlag( {el: node.el, x: i, w: w});
                                        node.el.attr( 'data-gs-x', i );
                                        node.el.attr( 'data-gs-width', w );
                                        found = true;
                                        return true;
                                    }
                                    i++;
                                }
                                w --;
                            }

                        }

                        return false;
                    };

                    var swap = function( node, newX ){
                        var x = node.x;
                        var w = node.w;

                        removeNode( node );

                        var block2 = getPrevBlock( newX );


                        var block2_right = 0;
                        if ( block2.x > -1 ) {
                            block2_right =  ( block2.x + block2.w );
                        }
                        if ( checkEnoughSpaceFromX( newX , w ) ) {
                            addItemToFlag( { el: node.el, x: newX, w: w } );
                            return true;
                        } else if ( block2_right > 0 && checkEnoughSpaceFromX( block2_right , w ) && newX >= block2_right ) {
                            var block3 = getNextBlock( newX );
                            if ( block3.x > -1 ) {
                                if ( node.w + newX >= block3.x ) {
                                    var _newX = _.clone( newX );
                                    while ( _newX > block2_right ) {
                                        if ( checkEnoughSpaceFromX( _newX , w ) ) {
                                            addItemToFlag( { el: node.el, x: _newX, w: w } );
                                            return  true;
                                        }
                                        _newX --;
                                    }
                                }
                            }

                            if ( newX + w > that.cols ){
                                var _x = that.cols - w;
                                if ( checkEnoughSpaceFromX( _x , w ) ) {
                                    addItemToFlag( { el: node.el, x: _x, w: w } );
                                    return true;
                                }
                            }
                            addItemToFlag( { el: node.el, x: block2_right, w: w } );
                            return true;
                        }

                        node.x = newX;

                        insertToFlag( node, true );
                    };

                    var mergeItem = function( node , newX, el){
                        var row_id = $(".header-full-overlay" + " " + "#"+ el.parent().attr('id') +" .grid-stack-item");
                        var max_y = 1;
                        row_id.each(function (it) {
                            if (row_id.eq(it).attr('data-gs-y') > max_y) {
                                max_y = row_id.eq(it).attr('data-gs-y');
                            }
                        });

                        if ( _.isUndefined(window.header_max_y)) {
                            window.header_max_y = max_y;
                        }

                        var node_items = node.el.parent().find('.grid-stack-item[data-gs-x="' + node.x + '"]');
                        var node_exist_count = node_items.length;
                        var index = 1;

                        node_items.each(function (it) {
                            if (node.x !== newX) {
                                node_items.eq(it).attr('data-gs-y',index);
                                index++;
                                node.el.attr('data-gs-y', 1);
                            } else {
                                node_items.eq(it).attr('data-gs-y',it+1);
                            }
                        });
                        max_y = 1;
                        row_id.each(function (it) {
                            if (row_id.eq(it).attr('data-gs-y') > max_y) {
                                max_y = row_id.eq(it).attr('data-gs-y');
                            }
                        });

                        if ( _.isUndefined(window.header_min_height)) {
                            window.header_min_height = parseInt($(".header-full-overlay" + " " + "#"+ el.parent().attr('id')).css('min-height'))/window.header_max_y;
                        }
                        $(".header-full-overlay" + " " + "#"+ el.parent().attr('id')).css('min-height',max_y*window.header_min_height+'px');
                    }

                    var that = this;
                    flag = that.getFlag( $wrapper );
                    backupFlag = flag.slice();
                    var wOffset =  $wrapper.offset();
                    that.draggingItem = ui.draggable;
                    var width  = $wrapper.width();
                    var colWidth = width/that.cols;
                    var x = 0;
                    var iOffset = ui.offset;
                    var w, cw, itemWidth, in_this_row;
                    cw = that.getW( ui.draggable, false );
                    w = that.getW( ui.draggable, true );
                    itemWidth = ui.draggable.width();

                    var ox = that.getX( ui.draggable );
                    if ( is_rtl ) {
                        removeNode({
                            el: ui.draggable,
                            x: ox,
                            w: w
                        });
                    }

                    var xc = 0, xi = 0, found = false;

                    if ( ! ui.draggable.parent().is( $wrapper ) ) {
                        in_this_row = false;
                        if ( w < cw ) {
                            w = cw;
                        }
                    } else {
                        in_this_row = true;
                        w = cw;
                    }

                    if ( ! is_rtl ) {
                        xc = Math.round(( event.clientX - wOffset.left ) / colWidth);

                        xi = Math.round(( iOffset.left - wOffset.left - 10 ) / colWidth);
                        if (xi < 0) {
                            xi = 0;
                        }
                    } else {
                        xc = Math.round(( ( wOffset.left + width + 10 ) - event.clientX ) / colWidth);

                        xi = Math.round( ( ( wOffset.left + width  )  - ( iOffset.left + itemWidth + 10 )  ) / colWidth );
                        if (xi < 0) {
                            xi = 0;
                        }

                    }
                    if ( xc > that.cols ) {
                        xc = that.cols;
                    }
                    if (getPrevBlock(xi).w == 1 && cw == 1) {
                        in_this_row = true;
                        w = cw;
                    }
                    x = xi;
                    var _i;
                    _i = xi;

                    if( is_rtl ) {
                        if (!isEmptyX(_i)) {
                            while (_i < that.cols && !found) {
                                if (isEmptyX(_i)) {
                                    found = true;
                                } else {
                                    _i++;
                                }
                            }
                        } else {
                            x = xi;
                            found = true;
                        }
                    } else {

                        if (!isEmptyX(x)) {
                            while (x <= xc && !found) {
                                if (isEmptyX(x)) {
                                    found = true;
                                } else {
                                    x++;
                                }
                            }
                            if (x > xc) {
                                x = xc;
                            }
                        } else {
                            x = xi;
                            found = true;
                        }

                    }

                    if (!found) {
                        if (in_this_row) {
                            x = xi;
                        } else {
                            x = xc;
                        }
                    }

                    if ( x < 0 ) {
                        x = 0;
                    }

                    if ( x + w >= that.cols ) {
                        found = true;
                        _i = x;
                        while ( _i + w > that.cols && found ) {
                            if ( ! isEmptyX( _i ) ) {
                                _i++;
                                found = false;
                            } else {
                                _i--;
                            }

                        }

                        x = _i;
                    }

                    delete found;

                    var node = {
                        el: ui.draggable,
                        x: x,
                        w: w,
                        ox: ox,
                        ow: cw
                    };

                    if ( node.x <= 0 ) {
                        node.x = 0;
                    }

                    var did = false;
                    if ( in_this_row ) {
                        node.x = parseInt( ui.draggable.attr( 'data-gs-x' ) || 0 );
                        node.w = parseInt( ui.draggable.attr( 'data-gs-width' ) || 1 );
                        swap( node, x );
                        did = true;
                    } else {
                        did = insertToFlag( node );
                    }



                    if ( ! did ) {
                        if (getPrevBlock( x ).w == 1 && cw == 1) {
                            $wrapper.append(ui.draggable);
                            ui.draggable.removeAttr( 'style' );
                            that.draggingItem = null;
                        } else {
                            ui.draggable.removeAttr('style');
                            flag = backupFlag;
                        }
                    } else {
                        ui.draggable.removeClass( 'item-from-list' );

                        $wrapper.append(ui.draggable);
                        ui.draggable.removeAttr( 'style' );
                        that.draggingItem = null;
                        mergeItem(node, x, ui.draggable);
                    }

                    updateItemsPositions();
                    that.updateAllGrids();

                },
                updateAllGrids: function(){
                    var that = this;
                    _.each( that.panels[ that.activePanel ], function( row, row_id ) {
                        that.updateGridFlag( row );
                    });
                    if (!$(".header-full-overlay .cleversoft--panel-desktop .cleversoft--cp-rows .grid-stack-item").length) {
                        $('#headerbuilder-desktop-data').val('{}');
                    }
                    if (!$(".header-full-overlay .cleversoft--panel-mobile .cleversoft--cp-rows .grid-stack-item").length) {
                        $('#headerbuilder-mobile-data').val('{}');
                    }
                },
                setGridWidth: function( $wrapper, ui ){
                    var that = this;
                    var $item = ui.element;
                    var width  = $wrapper.width();
                    var itemWidth = ui.size.width;
                    var originalElementWidth = ui.originalSize.width;
                    var colWidth = Math.ceil( width/that.cols ) - 1;
                    var isShiftLeft, isShiftRight;

                    if ( ! is_rtl ) {
                        isShiftLeft = ui.originalPosition.left > ui.position.left;
                        isShiftRight = ui.originalPosition.left < ui.position.left;
                    } else {
                        isShiftLeft = ui.originalPosition.left > ui.position.left;
                        isShiftRight = originalElementWidth !== itemWidth;
                    }

                    var ow =  ui.originalElement.attr( 'data-gs-width' ) || 1;
                    var ox =  ui.originalElement.attr( 'data-gs-x' ) || 0;
                    ow = parseInt( ow );
                    ox = parseInt( ox );

                    var addW;
                    var newX;
                    var newW;
                    var flag = that.getFlag( $wrapper );
                    var itemInfo = that.gridGetItemInfo( ui.originalElement, flag, $wrapper );
                    var diffLeft, diffRight;

                    if ( isShiftLeft ) {

                        if ( ! is_rtl ) {
                            newX = Math.floor( ( ui.position.left - 1 ) / colWidth );
                            addW = ox - newX ;
                            if (addW > itemInfo.before) {
                                addW = itemInfo.before;
                            }

                            newX = ox - addW;
                            newW = ow + addW;
                            $item.attr('data-gs-x', newX).removeAttr('style');
                            $item.attr('data-gs-width', newW).removeAttr('style');
                        } else {
                            newX = Math.floor( ( ui.position.left - 1 ) / colWidth );
                            newX = that.cols - newX;
                            addW = ( newX - ox ) - ow;
                            if (addW > itemInfo.after) {
                                addW = itemInfo.after;
                            }
                            newW = ow + addW;
                            $item.attr('data-gs-x', ox ).removeAttr('style');
                            $item.attr('data-gs-width', newW).removeAttr('style');
                        }

                        that.updateGridFlag( $wrapper );
                        return ;

                    } else if( isShiftRight ) {

                        if ( ! is_rtl ) {
                            newX = Math.round( ( ui.position.left - 1 ) / colWidth );
                            addW = newX - ox ;
                            newW = ow - addW;
                            if (newW <= 0) {
                                newW = 1;
                                addW = 0;
                            }
                            newX = ox + addW;
                            $item.attr('data-gs-x', newX).removeAttr('style');
                            $item.attr('data-gs-width', newW).removeAttr('style');

                        } else {

                            if ( ui.originalPosition.left !== ui.position.left ) {
                                newX = Math.floor( ( ui.position.left - 1 ) / colWidth );
                                newX = that.cols - newX;
                                addW = ( ow + ox ) - newX;
                                if ( addW > ow ) {
                                    addW = 0;
                                }
                                newX = ox;
                                newW = ow - addW;
                                if ( newX <= 0 ) {
                                    newX = 0;
                                }

                            } else {
                                newX = Math.ceil( ( ui.position.left + ui.size.width - 11 ) / colWidth );
                                newX = that.cols - newX;
                                addW = ox - newX;
                                if (addW > itemInfo.before ) {
                                    addW = itemInfo.before;
                                }
                                newX = ox - addW;
                                newW = ow + addW;
                            }
                            $item.attr('data-gs-x', newX).removeAttr('style');
                            $item.attr('data-gs-width', newW).removeAttr('style');
                        }

                        that.updateGridFlag( $wrapper );
                        return ;
                    }

                    var w ;
                    var x = itemInfo.x;
                    var x_c;

                    if ( itemWidth <  ui.originalSize.width ) {
                        x_c = Math.round( ( ui.position.left + ui.size.width - 11 ) / colWidth );
                        if ( x_c <= x ) {
                            x_c = x + 1;
                        }
                        w =  itemInfo.w - ( ( x + itemInfo.w ) - x_c );
                    } else {
                        x_c = Math.ceil( ( ui.position.left + ui.size.width - 11 ) / colWidth );
                        w = itemInfo.w + ( x_c - ( x + itemInfo.w ) );
                        if ( itemInfo.x + w > itemInfo.x + itemInfo.w + itemInfo.after ) {
                            w = itemInfo.w + itemInfo.after;
                        }
                    }

                    if ( w <= 0 ) {
                        w = 1;
                    }

                    $item.attr('data-gs-width', w ).removeAttr('style');
                    that.updateGridFlag( $wrapper );

                },
                getFlag: function( $row ){
                    var that = this;
                    var flag = $row.data( 'gridRowFlag' ) || [];
                    var i;
                    if ( _.isEmpty( flag ) ) {
                        for ( i =0; i< that.cols; i++ ) {
                            flag[ i ] = 0;
                        }
                        $row.data( 'gridRowFlag', flag );
                    }

                    return flag;
                },
                updateGridFlag: function( $row ){
                    var that = this;
                    var rowFlag = [];
                    var i;
                    for ( i = 0; i < that.cols; i++ ) {
                        rowFlag[ i ] = 0;
                    }
                    var items;
                    items =  $( '.grid-stack-item', $row );
                    items.each( function( index ){
                        $( this ).removeAttr( 'style' );
                        var x = that.getX( $( this ) );
                        var w = that.getW( $( this ) );

                        for ( i = x; i < x + w; i ++  ) {
                            if ( i === x ) {
                                rowFlag[ i ] = $( this );
                            } else {
                                rowFlag[ i ] = 1;
                            }
                        }

                        that.updateGridData( $( this ) );

                    } );
                    $row.data( 'gridRowFlag', rowFlag );
                    that.updateItemsPositions( rowFlag );
                    that.sortGrid( $row );
                    return rowFlag;
                },
                updateGridData: function($row) {
                    data_desktop = {
                        "top": [],
                        "main": [],
                        "bottom": [],
                        "sidebar":[]
                    }
                    data_mobile = {
                        "top": [],
                        "main": [],
                        "bottom": [],
                        "sidebar":[]
                    }
                    if ($row.closest('.header-full-overlay .cleversoft--device-panel').attr('data-device') == 'desktop') {
                        $(".cleversoft--cb-row", $row.closest('.header-full-overlay .cleversoft--device-panel')).each(function(e) {
                            var i = $(this);
                            $(".grid-stack-item", i).each(function(e) {
                                var pos = $(this).parent().attr('data-id');
                                if($(this).attr('data-id')) {
                                    if (pos == 'top' ) {
                                    v = {"x": $(this).attr('data-gs-x'), "y": $(this).attr('data-gs-y'), "width": $(this).attr('data-gs-width'), "height": "1", "id": $(this).attr('data-id')}
                                    data_desktop.top.push(v);
                                    data_desktop.top.sort(function(a,b) {
                                        return a.x - b.x;
                                    });
                                    } else if (pos == 'main') {
                                        v = {"x": $(this).attr('data-gs-x'), "y": $(this).attr('data-gs-y'), "width": $(this).attr('data-gs-width'), "height": "1", "id": $(this).attr('data-id')}
                                        data_desktop.main.push(v);
                                        data_desktop.main.sort(function(a,b) {
                                            return a.x - b.x;
                                        });
                                    } else if (pos == 'bottom') {
                                        v = {"x": $(this).attr('data-gs-x'), "y": $(this).attr('data-gs-y'), "width": $(this).attr('data-gs-width'), "height": "1", "id": $(this).attr('data-id')}
                                        data_desktop.bottom.push(v);
                                        data_desktop.bottom.sort(function(a,b) {
                                            return a.x - b.x;
                                        });
                                    } else if (pos == 'sidebar') {
                                        v = {"x": "0", "y": $(this).attr('data-gs-y'), "width": "1", "height": "1", "id": $(this).attr('data-id')}
                                        data_desktop.sidebar.push(v);
                                        data_desktop.sidebar.sort(function(a,b) {
                                            return a.x - b.x;
                                        });
                                    }   
                                }
                            });
                        });

                        $('#headerbuilder-desktop-data').val(JSON.stringify(data_desktop));
                    } else if($row.closest('.header-full-overlay .cleversoft--device-panel').attr('data-device') == 'mobile') {
                        $(".cleversoft--cb-row", $row.closest('.header-full-overlay .cleversoft--device-panel')).each(function(e) {
                            var i = $(this);
                            $(".grid-stack-item", i).each(function(e) {
                                var pos = $(this).parent().attr('data-id');
                                if($(this).attr('data-id')) {
                                    if (pos == 'top' ) {
                                        v = {"x": $(this).attr('data-gs-x'), "y": $(this).attr('data-gs-y'), "width": $(this).attr('data-gs-width'), "height": "1", "id": $(this).attr('data-id')}
                                        data_mobile.top.push(v);
                                        data_mobile.top.sort(function(a,b) {
                                            return a.x - b.x;
                                        });
                                    } else if (pos == 'main') {
                                        v = {"x": $(this).attr('data-gs-x'), "y": $(this).attr('data-gs-y'), "width": $(this).attr('data-gs-width'), "height": "1", "id": $(this).attr('data-id')}
                                        data_mobile.main.push(v);
                                        data_mobile.main.sort(function(a,b) {
                                            return a.x - b.x;
                                        });
                                    } else if (pos == 'bottom') {
                                        v = {"x": $(this).attr('data-gs-x'), "y": $(this).attr('data-gs-y'), "width": $(this).attr('data-gs-width'), "height": "1", "id": $(this).attr('data-id')}
                                        data_mobile.bottom.push(v);
                                        data_mobile.bottom.sort(function(a,b) {
                                            return a.x - b.x;
                                        });
                                    } else if (pos == 'sidebar') {
                                        v = {"x": "0", "y": $(this).attr('data-gs-y'), "width": "1", "height": "1", "id": $(this).attr('data-id')}
                                        data_mobile.sidebar.push(v);
                                        data_mobile.sidebar.sort(function(a,b) {
                                            return a.x - b.x;
                                        });
                                    }
                                }
                            });
                        });
                        $('#headerbuilder-mobile-data').val(JSON.stringify(data_mobile));
                    }

                },
                addNewWidget: function ( $item, row ) {

                    var that = this;
                    var panel = that.container.find('.cleversoft--device-panel.cleversoft--panel-'+that.activePanel );
                    var el = row;
                    if ( ! _.isObject( el ) ) {
                        el =  panel.find( '.cleversoft--cb-items' ).first();
                    }

                    var elItem = $item;
                    elItem.draggable({
                        revert: "invalid",
                        appendTo: panel,
                        scroll: false,
                        zIndex: 99999,
                        handle: '.grid-stack-item-content',
                        start: function( event, ui ){
                            $( 'body' ).addClass( 'builder-item-moving' );
                            $( '.cleversoft--cb-items', panel ).css( 'z-index', '' );
                            ui.helper.parent().css( 'z-index', 9999 );
                        },
                        stop: function(  event, ui ){
                            $( 'body' ).removeClass( 'builder-item-moving' );
                            $( '.cleversoft--cb-items', panel ).css( 'z-index', '' );
                            that.save();
                        }
                    }).resizable({
                        handles: 'w, e',
                        start: function( event, ui ) {
                            ui.originalElement.css( { 'right' : 'auto', left: ui.position.left } );
                        },
                        stop: function( event, ui ){
                            that.setGridWidth( ui.element.parent(), ui );
                            that.save();
                        }
                    });

                    el.append( elItem );
                    that.updateGridFlag( el );

                },
                addPanel: function( device ){
                    var that = this;
                    var template = that.getTemplate();
                    var template_id =  'tmpl-cleversoft--cb-panel';
                    if (  $( '#'+template_id ).length == 0 ) {
                        return ;
                    }
                    if ( ! _.isObject( options.rows ) ) {
                        options.rows = {};
                    }
                    var html = template( {
                        device: device,
                        id: options.id,
                        rows: options.rows
                    }, template_id );
                    return '<div class="cleversoft--device-panel cleversoft-vertical-panel cleversoft--panel-'+device+'" data-device="'+device+'">'+html+'</div>';
                },
                addDevicePanels: function(){
                    var that = this;
                    _.each( that.devices, function( device_name, device ) {
                        var panelHTML = that.addPanel( device );
                        $( '.cleversoft--cb-devices-switcher', that.container ).append( '<a href="#" class="switch-to switch-to-'+device+'" data-device="'+device+'">'+device_name+'</a>' );
                        $( '.cleversoft--cb-body', that.container ).append( panelHTML );
                    } );

                    if ( $( '#cleversoft-upsell-tmpl' ).length ) {
                        $( $( '#cleversoft-upsell-tmpl' ).html() ).insertAfter(  $( '.cleversoft--cb-devices-switcher', that.container ) );
                    }

                },
                addItem: function( node ){
                    var that = this;
                    var template = that.getTemplate();
                    var template_id =  'tmpl-cleversoft--cb-item';
                    if (  $( '#'+template_id ).length == 0 ) {
                        return ;
                    }
                    var html = template( node, template_id );
                    return $( html );
                },
                addAvailableItems: function(){
                    var that = this;

                    _.each( that.devices, function(device_name, device ){
                        var $itemWrapper = $( '<div class="cleversoft-available-items" data-device="'+device+'"></div>' );
                        $( '.cleversoft--panel-'+device, that.container ).append( $itemWrapper );


                        _.each( that.items, function( node ) {
                            var _d = true;
                            if ( ! _.isUndefined( node.devices ) && ! _.isEmpty( node.devices ) ) {
                                if ( _.isString( node.devices ) ) {
                                    if ( node.devices != device ) {
                                        _d = false;
                                    }
                                } else {
                                    var _has_d = false;
                                    _.each( node.devices, function( _v ){
                                        if ( device == _v ){
                                            _has_d = true;
                                        }} );
                                    if ( ! _has_d ) {
                                        _d = false;
                                    }
                                }
                            }

                            if ( _d ) {
                                var item = that.addItem( node );
                                $itemWrapper.append( item );
                            }

                        } );
                    } );

                },
                switchToDevice: function( device, toggle_button ){
                    var that = this;
                    var numberDevices = _.size( that.devices );
                    if( numberDevices > 1 ) {
                        $('.cleversoft--cb-devices-switcher a', that.container).removeClass('cleversoft--tab-active');
                        $('.cleversoft--cb-devices-switcher .switch-to-' + device, that.container).addClass('cleversoft--tab-active');
                        $('.cleversoft--device-panel', that.container).addClass('cleversoft--panel-hide');
                        $('.cleversoft--device-panel.cleversoft--panel-' + device, that.container).removeClass('cleversoft--panel-hide');
                        that.activePanel = device;
                    } else {
                        $('.cleversoft--cb-devices-switcher a', that.container).addClass('cleversoft--tab-active');
                    }
                },
                clearData: function() {
                    var that = this;
                    $("#header_clear_section").click(function() {
                        var device = $(".header-full-overlay .cleversoft--tab-active").attr("data-device");
                        var items = $(".header-full-overlay .cleversoft--panel-"+device+" .cleversoft--cp-rows .grid-stack-item, .cleversoft--panel-"+device+" .cleversoft--cp-sidebar .grid-stack-item");
                        var panel = $(".header-full-overlay .cleversoft--device-panel[data-device='"+device+"']");
                        items.attr( 'data-gs-width', 1 );
                        items.attr( 'data-gs-x', 0 );
                        items.attr( 'data-gs-y', '' );
                        items.removeAttr( 'style' );
                        $(".header-full-overlay .cleversoft--panel-"+device+" .cleversoft--cb-items").removeAttr( 'style' );
                        $( '.cleversoft-available-items', panel ).append( items );
                        that.updateAllGrids();
                        that.save();

                        $(".headerbuilder-modal").css("display","none");
                    });
                },
                addExistingRowsItems: function(){
                    var that  = this;

                    var data = cleverbuilder.control( that.controlId ).params.value;
                    if ( ! _.isObject( data ) ) {
                        data = {};
                    }
                    _.each( that.panels, function( $rows,  device ) {
                        var device_data = {};
                        if ( _.isObject( data[ device ] ) ) {
                            device_data = data[ device ];
                        }
                        _.each( device_data, function( items, row_id ) {
                            if( ! _.isUndefined( items ) ) {
                                var max_y = 1;
                                _.each( items, function (node, index) {
                                    var item = $('.cleversoft-available-items[data-device="' + device + '"] .grid-stack-item[data-id="' + node.id + '"]').first();
                                    item.attr('data-gs-width', node.width);
                                    item.attr('data-gs-x', node.x);
                                    item.attr('data-gs-y', node.y);
                                    item.removeClass( 'item-from-list' );
                                    that.addNewWidget( item,  $rows[ row_id  ] );
                                    if (node.y > max_y) {
                                        max_y = node.y;
                                    }
                                });
                                if (max_y > 1) {
                                    $("#_sid_"+ device +"-" + row_id).css('min-height',max_y*parseInt($("#_sid_"+ device +"-" + row_id).css('min-height'))+'px');
                                }
                            }
                        });
                    });

                    that.ready = true;
                },
                focus: function(){
                    this.container.on("click", ".cleversoft--cb-item-setting, .cleversoft--cb-item-name, .item-tooltip", function(e) {
                        e.preventDefault();
                        var p = "headerbuilder-popup-"+$(this).data("id");
                        $(".headerbuilder-modal").css("display","none");
                        var modal = document.getElementById(p);

                        if (modal) {
                            $(".headerbuilder-modal-body .modal-inner").css("display","none");
                            var modal_content = document.getElementById("header_" + $(this).closest(".cleversoft--device-panel").attr("data-device")+"_"+$(this).data("id"));
                            if (modal_content) {
                                modal_content.style.display = "block";
                            }
                            modal.style.display = "block";
                            window.onclick = function(event) {
                                if (event.target == modal) {
                                    modal.style.display = "none";
                                }
                            }
                        }

                    }), this.container.on("click", ".cleversoft--cb-row-settings", function(e) {
                        e.preventDefault();
                        var p = "headerbuilder-popup-"+$(this).data("id");

                        $(".headerbuilder-modal").css("display","none");
                        var modal = document.getElementById(p);
                        if (modal) {
                            $(".headerbuilder-modal-body .modal-inner").css("display","none");
                            var modal_content = document.getElementById("header_" + $(this).closest(".cleversoft--device-panel").attr("data-device")+"_"+$(this).data("id"));
                            if (modal_content) {
                                modal_content.style.display = "block";
                            }
                            modal.style.display = "block";
                            window.onclick = function(event) {
                                if (event.target == modal) {
                                    modal.style.display = "none";
                                }
                            }
                        }
                    }),$(".headerbuilder-modal-close").click(function() {
                        $(".headerbuilder-modal").css("display","none");
                    })

                },
                remove: function(){
                    var that = this;
                    $document.on( 'click', '.cleversoft--device-panel .cleversoft--cb-item-remove', function ( e ) {
                        e.preventDefault();
                        var item = $( this ).closest('.grid-stack-item');

                        var max_y = 1;
                        var row_id = $("#"+ item.parent().attr('id') +" .grid-stack-item");
                        row_id.each(function (it) {
                            if (row_id.eq(it).attr('data-gs-y') > max_y) {
                                max_y = row_id.eq(it).attr('data-gs-y');
                            }
                        });
                        if ( _.isUndefined(window.header_min_height)) {
                            window.header_min_height = parseInt($("#"+ item.parent().attr('id')).css('min-height'))/max_y;
                        }
                        if (max_y > 1) {
                            $("#"+ item.parent().attr('id')).css('min-height',(max_y-1)*window.header_min_height+'px');
                        }

                        var panel = item.closest( '.cleversoft--device-panel' );
                        item.attr( 'data-gs-width', 1 );
                        item.attr( 'data-gs-x', 0 );
                        item.attr( 'data-gs-y', '' );
                        item.removeAttr( 'style' );
                        $( '.cleversoft-available-items', panel ).append( item );
                        that.updateAllGrids();
                        that.save();

                        $(".headerbuilder-modal").css("display","none");
                    } );

                },
                encodeValue: function( value ){
                    return encodeURI( JSON.stringify( value ) )
                },
                decodeValue: function( value ){
                    return JSON.parse( decodeURI( value ) );
                },
                save: function(){
                    var that = this;
                    if ( ! that.ready  ) {
                        return ;
                    }

                    var data = {};
                    _.each( that.panels, function( $rows,  device ) {
                        data[device] = {};
                        _.each( $rows, function( row, row_id ) {
                            var rowData = _.map( $( ' > .grid-stack-item', row ), function (el) {
                                el = $(el);
                                return {
                                    x: that.getX( el ),
                                    y: 1,
                                    width: that.getW( el ),
                                    height: 1,
                                    id: el.data('id') || ''
                                };

                            });
                            data[device][row_id] = rowData;
                        });
                    });

                },
                init: function( controlId, items, devices ){
                    var that = this;


                    var template = that.getTemplate();
                    var template_id =  'tmpl-cleversoft--builder-panel';
                    var html = template( options , template_id );
                    that.container = $( html );
                    $( 'body .header-full-overlay' ).append( that.container );
                    that.controlId = controlId;
                    that.items = items;
                    that.devices = devices;

                    if ( options.section ) {
                        cleverbuilder.section( options.section ).container.addClass( 'cleversoft--hide' );
                    }

                    that.addDevicePanels();
                    that.switchToDevice( that.activePanel );
                    that.addAvailableItems();
                    that.switchToDevice( that.activePanel );
                    that.drag_drop();
                    that.focus();
                    that.remove();
                    that.addExistingRowsItems();
                    that.clearData();

                    that.container.on( 'click', '.cleversoft--cb-devices-switcher a.switch-to', function(e){
                        e.preventDefault();
                        var device = $( this ).data('device');
                        that.switchToDevice( device );
                        $(".headerbuilder-modal").hide();
                    } );

                    $document.trigger( 'cleversoft_builder_panel_loaded', [ id, that ] );

                }
            };

            Builder.init( options.control_id, options.items, options.devices );
            return Builder;
        };

        $(document).ready(function(e,b) {
            _.each( Header_Layout_Builder.builders, function( opts, id ){
                new HeaderBuilder( opts, id );
            } );

        });

        var encodeValue = function( value ){
            return encodeURI( JSON.stringify( value ) )
        };


        $document.on( 'mouseover', '.cleversoft--cb-row .grid-stack-item', function( e ) {
            var item = $( this );
            var nameW = $( '.cleversoft--cb-item-remove',item ).outerWidth() + $( '.cleversoft--cb-item-setting',item ).outerWidth();
            var itemW = $( '.grid-stack-item-content', item ).innerWidth();
            if ( nameW > itemW - 50 ) {
                item.addClass('show-tooltip');
            }
        });

        $document.on( 'mouseleave', '.cleversoft--cb-row .grid-stack-item', function( e ) {
            $( this ).removeClass('show-tooltip');
        });

    })( jQuery, clever.builder || null );
});