define([
    "jquery",
    "jquery/ui",
    "liveEditor",
    'mage/translate'
], function(){
    "use strict";

    (function e(t, n, r) {
        function s(o, u) {
            if (!n[o]) {
                if (!t[o]) {
                    var a = typeof require == "function" && require;
                    if (!u && a)return a(o, !0);
                    if (i)return i(o, !0);
                    var f = new Error("Cannot find module '" + o + "'");
                    throw f.code = "MODULE_NOT_FOUND", f
                }
                var l = n[o] = {exports: {}};
                t[o][0].call(l.exports, function (e) {
                    var n = t[o][1][e];
                    return s(n ? n : e)
                }, l, l.exports, e, t, n, r)
            }
            return n[o].exports
        }

        var i = typeof require == "function" && require;
        for (var o = 0; o < r.length; o++)s(r[o]);
        return s
    })({
        1: [function (require, module, exports) {
            var panels = window.panels;

            module.exports = Backbone.Collection.extend({
                model: panels.model.cell,

                initialize: function () {
                },

                /**
                 * Get the total weight for the cells in this collection.
                 * @returns {number}
                 */
                totalWeight: function () {
                    var totalWeight = 0;
                    this.each(function (cell) {
                        totalWeight += cell.get('weight');
                    });

                    return totalWeight;
                }

            });

        }, {}],
        2: [function (require, module, exports) {
            var panels = window.panels;

            module.exports = Backbone.Collection.extend({
                model: panels.model.historyEntry,

                /**
                 * The builder model
                 */
                builder: null,

                /**
                 * The maximum number of items in the history
                 */
                maxSize: 12,

                initialize: function () {
                    this.on('add', this.onAddEntry, this);
                },

                /**
                 * Add an entry to the collection.
                 *
                 * @param text The text that defines the action taken to get to this
                 * @param data
                 */
                addEntry: function (text, data) {

                    if (_.isEmpty(data)) {
                        data = this.builder.getPanelsData();
                    }

                    var entry = new panels.model.historyEntry({
                        text: text,
                        data: JSON.stringify(data),
                        time: parseInt(new Date().getTime() / 1000),
                        collection: this
                    });

                    this.add(entry);
                },

                /**
                 * Resize the collection so it's not bigger than this.maxSize
                 */
                onAddEntry: function (entry) {

                    if (this.models.length > 1) {
                        var lastEntry = this.at(this.models.length - 2);

                        if (
                            (
                                entry.get('text') === lastEntry.get('text') && entry.get('time') - lastEntry.get('time') < 15
                            ) ||
                            (
                                entry.get('data') === lastEntry.get('data')
                            )
                        ) {
                            // If both entries have the same text and are within 20 seconds of each other, or have the same data, then remove most recent
                            this.remove(entry);
                            lastEntry.set('count', lastEntry.get('count') + 1);
                        }
                    }

                    // Make sure that there are not to many entries in this collection
                    while (this.models.length > this.maxSize) {
                        this.shift();
                    }
                }
            });

        }, {}],
        3: [function (require, module, exports) {
            var panels = window.panels;

            module.exports = Backbone.Collection.extend({
                model: panels.model.row,

                /**
                 * Destroy all the rows in this collection
                 */
                empty: function () {
                    var model;
                    do {
                        model = this.collection.first();
                        if (!model) {
                            break;
                        }

                        model.destroy();
                    } while (true);
                }

            });

        }, {}],
        4: [function (require, module, exports) {
            var panels = window.panels;

            module.exports = Backbone.Collection.extend({
                model: panels.model.widget,

                initialize: function () {

                }

            });

        }, {}],
        5: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = panels.view.dialog.extend({
                dialogClass: 'cs-panels-dialog-add-builder',

                render: function () {
                    // Render the dialog and attach it to the builder interface
                    this.renderDialog(this.parseDialogContent($('#cleversoft-panels-dialog-builder').html(), {}));
                    this.$('.cs-content .cleversoft-panels-builder').append(this.builder.$el);
                },

                initializeDialog: function () {
                    var thisView = this;
                    this.once('open_dialog_complete', function () {
                        thisView.builder.initSortable();
                    });

                    this.on('open_dialog_complete', function () {
                        thisView.builder.trigger('builder_resize');
                    });
                }
            });

        }, {}],
        6: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = panels.view.dialog.extend({

                historyEntryTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-dialog-history-entry').html())),

                entries: {},
                currentEntry: null,
                revertEntry: null,
                selectedEntry: null,

                previewScrollTop: null,

                dialogClass: 'cs-panels-dialog-history',
                dialogIcon: 'history',

                events: {
                    'click .cs-update': 'updateDialog',
                    'click .cs-close': 'updateDialog',
                    'click .cs-restore': 'restoreSelectedEntry'
                },

                initializeDialog: function () {
                    this.entries = new panels.collection.historyEntries();

                    this.on('open_dialog', this.setCurrentEntry, this);
                    this.on('open_dialog', this.renderHistoryEntries, this);
                },

                render: function () {
                    var thisView = this;

                    // Render the dialog and attach it to the builder interface
                    this.renderDialog(this.parseDialogContent($('#cleversoft-panels-dialog-history').html(), {}));

                    this.$('iframe.cleversoft-panels-history-iframe').load(function () {
                        var $$ = $(this);
                        $$.show();

                        $$.contents().scrollTop(thisView.previewScrollTop);
                    });
                },

                /**
                 * Set the original entry. This should be set when creating the dialog.
                 *
                 * @param {panels.model.builder} builder
                 */
                setRevertEntry: function (builder) {
                    this.revertEntry = new panels.model.historyEntry({
                        data: JSON.stringify(builder.getPanelsData()),
                        time: parseInt(new Date().getTime() / 1000)
                    });
                },

                /**
                 * This is triggered when the dialog is opened.
                 */
                setCurrentEntry: function () {
                    this.currentEntry = new panels.model.historyEntry({
                        data: JSON.stringify(this.builder.model.getPanelsData()),
                        time: parseInt(new Date().getTime() / 1000)
                    });

                    this.selectedEntry = this.currentEntry;
                    this.previewEntry(this.currentEntry);
                    this.$('.cs-buttons .cs-restore').addClass('disabled');
                },

                /**
                 * Render the history entries in the sidebar
                 */
                renderHistoryEntries: function () {
                    // Set up an interval that will display the time since every 10 seconds
                    var thisView = this;

                    var c = this.$('.history-entries').empty();

                    if (this.currentEntry.get('data') !== this.revertEntry.get('data') || !_.isEmpty(this.entries.models)) {
                        $(this.historyEntryTemplate({title: panelsOptions.loc.history.revert, count: 1}))
                            .data('historyEntry', this.revertEntry)
                            .prependTo(c);
                    }

                    // Now load all the entries in this.entries
                    this.entries.each(function (entry) {

                        var html = thisView.historyEntryTemplate({
                            title: panelsOptions.loc.history[entry.get('text')],
                            count: entry.get('count')
                        });

                        $(html)
                            .data('historyEntry', entry)
                            .prependTo(c);
                    });


                    $(this.historyEntryTemplate({title: panelsOptions.loc.history['current'], count: 1}))
                        .data('historyEntry', this.currentEntry)
                        .addClass('cs-selected')
                        .prependTo(c);

                    // Handle loading and selecting
                    c.find('.history-entry').click(function () {
                        var $$ = jQuery(this);
                        c.find('.history-entry').not($$).removeClass('cs-selected');
                        $$.addClass('cs-selected');

                        var entry = $$.data('historyEntry');

                        thisView.selectedEntry = entry;

                        if (thisView.selectedEntry.cid !== thisView.currentEntry.cid) {
                            thisView.$('.cs-buttons .cs-restore').removeClass('disabled');
                        } else {
                            thisView.$('.cs-buttons .cs-restore').addClass('disabled');
                        }

                        thisView.previewEntry(entry);
                    });

                    this.updateEntryTimes();
                },

                /**
                 * Preview an entry
                 *
                 * @param entry
                 */
                previewEntry: function (entry) {
                    var iframe = this.$('iframe.cleversoft-panels-history-iframe');
                    iframe.hide();
                    this.previewScrollTop = iframe.contents().scrollTop();

                    this.$('form.history-form input[name="live_editor_panels_data"]').val(entry.get('data'));
                    this.$('form.history-form input[name="live_editor_post_ID"]').val(this.builder.config.postId);
                    this.$('form.history-form').submit();
                },

                /**
                 * Restore the current entry
                 */
                restoreSelectedEntry: function () {

                    if (this.$('.cs-buttons .cs-restore').hasClass('disabled')) {
                        return false;
                    }

                    if (this.currentEntry.get('data') === this.selectedEntry.get('data')) {
                        this.updateDialog();
                        return false;
                    }

                    // Add an entry for this restore event
                    if (this.selectedEntry.get('text') !== 'restore') {
                        this.builder.addHistoryEntry('restore', this.builder.model.getPanelsData());
                    }

                    this.builder.model.loadPanelsData(JSON.parse(this.selectedEntry.get('data')));

                    this.updateDialog();

                    return false;
                },

                /**
                 * Update the entry times for the list of entries down the side
                 */
                updateEntryTimes: function () {
                    var thisView = this;

                    this.$('.history-entries .history-entry').each(function () {
                        var $$ = jQuery(this);

                        var time = $$.find('.timesince');
                        var entry = $$.data('historyEntry');

                        time.html(thisView.timeSince(entry.get('time')));
                    });
                },

                /**
                 * Gets the time since as a nice string.
                 *
                 * @param date
                 */
                timeSince: function (time) {
                    var diff = parseInt(new Date().getTime() / 1000) - time;

                    var parts = [];
                    var interval;

                    // There are 3600 seconds in an hour
                    if (diff > 3600) {
                        interval = Math.floor(diff / 3600);
                        if (interval === 1) {
                            parts.push(panelsOptions.loc.time.hour.replace('%d', interval));
                        } else {
                            parts.push(panelsOptions.loc.time.hours.replace('%d', interval));
                        }
                        diff -= interval * 3600;
                    }

                    // There are 60 seconds in a minute
                    if (diff > 60) {
                        interval = Math.floor(diff / 60);
                        if (interval === 1) {
                            parts.push(panelsOptions.loc.time.minute.replace('%d', interval));
                        } else {
                            parts.push(panelsOptions.loc.time.minutes.replace('%d', interval));
                        }
                        diff -= interval * 60;
                    }

                    if (diff > 0) {
                        if (diff === 1) {
                            parts.push(panelsOptions.loc.time.second.replace('%d', diff));
                        } else {
                            parts.push(panelsOptions.loc.time.seconds.replace('%d', diff));
                        }
                    }

                    // Return the amount of time ago
                    return _.isEmpty(parts) ? panelsOptions.loc.time.now : panelsOptions.loc.time.ago.replace('%s', parts.slice(0, 2).join(', '));

                }

            });

        }, {}],
        7: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = panels.view.dialog.extend({

                // directoryTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-directory-items').html())),

                builder: null,
                dialogClass: 'cs-panels-dialog-prebuilt-layouts',
                dialogIcon: 'layouts',

                layoutCache: {},
                currentTab: false,
                directoryPage: 1,

                events: {
                    'click .cs-toolbar .cs-import-layout': 'applyLayoutHandle',
                    'click .cs-toolbar .cs-remove-layout': 'removeLayoutHandle',
                    'click .cs-close': 'closeDialog',
                    'click .cs-content .layout-group li': 'layoutClickHandler'
                },

                /**
                 * Initialize the prebuilt dialog.
                 */
                initializeDialog: function () {
                    var thisView = this;

                    this.on('open_dialog', function () {
                        thisView.$('.cs-sidebar-tabs li a').first().click();
                        thisView.$('.cs-status').removeClass('cs-panels-loading');
                    });

                    this.on('button_click', this.toolbarButtonClick, this);
                },

                /**
                 * Render the prebuilt layouts dialog
                 */
                render: function () {
                    this.renderDialog(this.parseDialogContent($('#cleversoft-panels-dialog-prebuilt').html(), {}));
                    this.initToolbar();
                },

                layoutClickHandler: function (e) {
                    var thisView = this;
                    thisView.$('.cs-content .layout-group li').removeClass('active');
                    var $element = $(e.target).closest('li');
                    $element.addClass('active');
                },

                removeLayoutHandle: function (e) {
                    var thisView = this;
                    if (confirm('Are you sure you want to remove this template?')) {
                        $('.cs-preview-overlay').show();
                        $('.cs-panels-live-editor').addClass('cs-toolbar-loading');
                        var layout = thisView.$('.cs-content .layout-group li.active .layout-id').attr('data-layout');
                        var template = thisView.$('.cs-content .layout-group li.active .layout-id').attr('data-template') ? 1 : 0;
                        var data = {
                            action: 'so_panels_remove_template',
                            layout: layout,
                            template: template,
                            showLoader: true,
                            done_url: panelsOptions.doneurl
                        };
                        $.post(
                            panelsOptions.ajaxurl,
                            data,
                            function (result) {
                                thisView.$('.cs-content .layout-group li.active').remove();
                                window.location = panelsOptions.doneurl
                            })
                    }
                },

                applyLayoutHandle: function () {
                    this.updateDialog();
                    this.builder.model.refreshPanelsData();
                },

                updateDialog: function (options) {
                    options = _.extend({
                        silent: false
                    }, options);

                    if (!options.silent) {
                        this.trigger('close_dialog');
                    }

                    this.dialogOpen = false;
                    this.$el.hide();
                    panels.helpers.pageScroll.unlock();

                    // Stop listen for keyboard keypresses.
                    $(window).off('keyup', this.keyboardListen);

                    if (!options.silent) {
                        // This triggers once everything is hidden
                        this.trigger('close_dialog_complete');
                        this.builder.trigger('close_dialog', this);
                    }
                    this.updatePrebuiltPanelsData();
                    $('.cs-panels-live-editor').removeClass('cs-toolbar-loading');
                },
                /*
                 * update database
                 */
                updatePrebuiltPanelsData: function() {
                    var thisView = this;
                    this.$('.cs-preview-overlay').show();
                    $('.cs-panels-live-editor').addClass('cs-toolbar-loading');
                    var layout = thisView.$('.cs-content .layout-group li.active .layout-id').attr('data-layout');
                    var template = thisView.$('.cs-content .layout-group li.active .layout-id').attr('data-template') ? 1 : 0;
                    var data = {
                        action: 'so_panels_prebuilt_database',
                        layout: layout,
                        template: template,
                        page_id: this.builder.config.postId,
                        showLoader: true,
                        done_url: panelsOptions.doneurl
                    };
                    $.post(
                        panelsOptions.ajaxurl,
                        data,
                        function (result) {
                            window.location = panelsOptions.doneurl
                        })
                },

                closeDialog: function () {
                    this.dialogOpen = false;
                    this.$el.hide();
                }
            });

        }, {}],
        8: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = panels.view.dialog.extend({

                cellPreviewTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-dialog-row-cell-preview').html())),

                editableLabel: true,

                events: {
                    'click .cs-update': 'updateDialog',
                    'click .cs-close': 'saveHandler',

                    // Toolbar buttons
                    'click .cs-toolbar .cs-save': 'saveHandler',
                    'click .cs-toolbar .cs-insert': 'insertHandler',
                    'click .cs-toolbar .cs-delete': 'deleteHandler',
                    'click .cs-toolbar .cs-duplicate': 'duplicateHandler',

                    // Changing the row
                    'change .row-set-form > *': 'setCellsFromForm',
                    'click .row-set-form button.set-row': 'setCellsFromForm'
                },

                dialogIcon: 'add-row',
                dialogClass: 'cs-panels-dialog-row-edit',
                styleType: 'row',

                dialogType: 'edit',

                /**
                 * The current settings, not yet saved to the model
                 */
                row: {
                    // This will be a clone of cells collection.
                    cells: null,
                    // The style settings of the row
                    style: {}
                },

                cellStylesCache: [],

                initializeDialog: function () {
                    this.on('open_dialog', function () {
                        if (!_.isUndefined(this.model) && !_.isEmpty(this.model.get('cells'))) {
                            this.setRowModel(this.model);
                        } else {
                            this.setRowModel(null);
                        }

                        this.regenerateRowPreview();
                    }, this);

                    // This is the default row layout
                    this.row = {
                        cells: new panels.collection.cells([{weight: 0.5}, {weight: 0.5}]),
                        style: {}
                    };

                    // Refresh panels data after both dialog form components are loaded
                    this.dialogFormsLoaded = 0;
                    var thisView = this;
                    this.on('form_loaded styles_loaded', function () {
                        this.dialogFormsLoaded++;
                        if (this.dialogFormsLoaded === 2) {
                            thisView.updateModel({
                                refreshArgs: {
                                    silent: true
                                }
                            });
                        }
                    });

                    this.on('close_dialog', this.closeHandler);

                    this.on('edit_label', function (text) {
                        // If text is set to default values, just clear it.
                        if (text === panelsOptions.loc.row.add || text === panelsOptions.loc.row.edit) {
                            text = '';
                        }
                        this.model.set('label', text);
                        if (_.isEmpty(text)) {
                            var title = this.dialogType === 'create' ? panelsOptions.loc.row.add : panelsOptions.loc.row.edit;
                            this.$('.cs-title').text(title);
                        }
                    }.bind(this));
                },

                /**
                 *
                 * @param dialogType Either "edit" or "create"
                 */
                setRowDialogType: function (dialogType) {
                    this.dialogType = dialogType;
                },

                /**
                 * Render the new row dialog
                 */
                render: function () {
                    var title = this.dialogType === 'create' ? panelsOptions.loc.row.add : panelsOptions.loc.row.edit;
                    this.renderDialog(this.parseDialogContent($('#cleversoft-panels-dialog-row').html(), {
                        title: title,
                        dialogType: this.dialogType
                    }));

                    var titleElt = this.$('.cs-title');

                    if (this.model.has('label') && !_.isEmpty(this.model.get('label'))) {
                        titleElt.text(this.model.get('label'));
                    }
                    this.$('.cs-edit-title').val(titleElt.text());

                    // Now we need to attach the style window
                    this.styles = new panels.view.styles();
                    this.styles.model = this.model;
                    this.styles.render('row', this.builder.config.postId, {
                        builderType: this.builder.config.builderType,
                        dialog: this
                    });

                    if (!this.builder.supports('addRow')) {
                        this.$('.cs-buttons .cs-duplicate').remove();
                    }
                    if (!this.builder.supports('deleteRow')) {
                        this.$('.cs-buttons .cs-delete').remove();
                    }

                    var $rightSidebar = this.$('.cs-sidebar.cs-right-sidebar');
                    this.styles.attach($rightSidebar);

                    // Handle the loading class
                    this.styles.on('styles_loaded', function (hasStyles) {
                        // If we have styles remove the loading spinner, else remove the whole empty sidebar.
                        if (hasStyles) {
                            $rightSidebar.removeClass('cs-panels-loading');
                        } else {
                            $rightSidebar.closest('.cs-panels-dialog').removeClass('cs-panels-dialog-has-right-sidebar');
                            $rightSidebar.remove();
                        }
                    }, this);
                    $rightSidebar.addClass('cs-panels-loading');

                    if (!_.isUndefined(this.model)) {
                        // Set the initial value of the
                        this.$('input[name="cells"].cs-row-field').val(this.model.get('cells').length);
                        if (this.model.has('ratio')) {
                            this.$('select[name="ratio"].cs-row-field').val(this.model.get('ratio'));
                        }
                        if (this.model.has('fullpage')) {
                            this.$('select[name="fullpage"].cs-row-field').val(this.model.get('fullpage'));
                        }
                        if (this.model.has('ratio_direction')) {
                            this.$('select[name="ratio_direction"].cs-row-field').val(this.model.get('ratio_direction'));
                        }
                    }

                    this.$('input.cs-row-field').keyup(function () {
                        $(this).trigger('change');
                    });

                    return this;
                },

                /**
                 * Set the row model we'll be using for this dialog.
                 *
                 * @param model
                 */
                setRowModel: function (model) {
                    this.model = model;

                    if (_.isEmpty(this.model)) {
                        return this;
                    }

                    // Set the rows to be a copy of the model
                    this.row = {
                        cells: this.model.get('cells').clone(),
                        style: {},
                        ratio: this.model.get('ratio'),
                        fullpage: this.model.get('fullpage'),
                        ratio_direction: this.model.get('ratio_direction')
                    };

                    // Set the initial value of the cell field.
                    this.$('input[name="cells"].cs-row-field').val(this.model.get('cells').length);
                    if (this.model.has('ratio')) {
                        this.$('select[name="ratio"].cs-row-field').val(this.model.get('ratio'));
                    }
                    if (this.model.has('fullpage')) {
                        this.$('select[name="fullpage"].cs-row-field').val(this.model.get('fullpage'));
                    }
                    if (this.model.has('ratio_direction')) {
                        this.$('select[name="ratio_direction"].cs-row-field').val(this.model.get('ratio_direction'));
                    }

                    this.clearCellStylesCache();

                    return this;
                },

                /**
                 * Regenerate the row preview and resizing interface.
                 */
                regenerateRowPreview: function () {
                    var thisDialog = this;
                    var rowPreview = this.$('.row-preview');

                    // If no selected cell, select the first cell.
                    var selectedIndex = this.getSelectedCellIndex();

                    rowPreview.empty();

                    var timeout;

                    // Represent the cells
                    this.row.cells.each(function (cellModel, i) {
                        var newCell = $(this.cellPreviewTemplate({weight: cellModel.get('weight')}));
                        rowPreview.append(newCell);

                        if (i == selectedIndex) {
                            newCell.find('.preview-cell-in').addClass('cell-selected');
                        }

                        var prevCell = newCell.prev();
                        var handle;

                        if (prevCell.length) {
                            handle = $('<div class="resize-handle"></div>');
                            handle
                                .appendTo(newCell)
                                .dblclick(function () {
                                    var prevCellModel = thisDialog.row.cells.at(i - 1);
                                    var t = cellModel.get('weight') + prevCellModel.get('weight');
                                    cellModel.set('weight', t / 2);
                                    prevCellModel.set('weight', t / 2);
                                    thisDialog.scaleRowWidths();
                                });

                            handle.draggable({
                                axis: 'x',
                                containment: rowPreview,
                                start: function (e, ui) {

                                    // Create the clone for the current cell
                                    var newCellClone = newCell.clone().appendTo(ui.helper).css({
                                        position: 'absolute',
                                        top: '0',
                                        width: newCell.outerWidth(),
                                        left: 6,
                                        height: newCell.outerHeight()
                                    });
                                    newCellClone.find('.resize-handle').remove();

                                    // Create the clone for the previous cell
                                    var prevCellClone = prevCell.clone().appendTo(ui.helper).css({
                                        position: 'absolute',
                                        top: '0',
                                        width: prevCell.outerWidth(),
                                        right: 6,
                                        height: prevCell.outerHeight()
                                    });
                                    prevCellClone.find('.resize-handle').remove();

                                    $(this).data({
                                        'newCellClone': newCellClone,
                                        'prevCellClone': prevCellClone
                                    });

                                    // Hide the
                                    newCell.find('> .preview-cell-in').css('visibility', 'hidden');
                                    prevCell.find('> .preview-cell-in').css('visibility', 'hidden');
                                },
                                drag: function (e, ui) {
                                    // Calculate the new cell and previous cell widths as a percent
                                    var cellWeight = thisDialog.row.cells.at(i).get('weight');
                                    var prevCellWeight = thisDialog.row.cells.at(i - 1).get('weight');
                                    var ncw = cellWeight - (
                                        (
                                            ui.position.left + 6
                                        ) / rowPreview.width()
                                    );
                                    var pcw = prevCellWeight + (
                                        (
                                            ui.position.left + 6
                                        ) / rowPreview.width()
                                    );

                                    var helperLeft = ui.helper.offset().left - rowPreview.offset().left - 6;

                                    $(this).data('newCellClone').css('width', rowPreview.width() * ncw)
                                        .find('.preview-cell-weight').html(Math.round(ncw * 1000) / 10);

                                    $(this).data('prevCellClone').css('width', rowPreview.width() * pcw)
                                        .find('.preview-cell-weight').html(Math.round(pcw * 1000) / 10);
                                },
                                stop: function (e, ui) {
                                    // Remove the clones
                                    $(this).data('newCellClone').remove();
                                    $(this).data('prevCellClone').remove();

                                    // Reshow the main cells
                                    newCell.find('.preview-cell-in').css('visibility', 'visible');
                                    prevCell.find('.preview-cell-in').css('visibility', 'visible');

                                    // Calculate the new cell weights
                                    var offset = ui.position.left + 6;
                                    var percent = offset / rowPreview.width();

                                    // Ignore this if any of the cells are below 2% in width.
                                    var cellModel = thisDialog.row.cells.at(i);
                                    var prevCellModel = thisDialog.row.cells.at(i - 1);
                                    if (cellModel.get('weight') - percent > 0.02 && prevCellModel.get('weight') + percent > 0.02) {
                                        cellModel.set('weight', cellModel.get('weight') - percent);
                                        prevCellModel.set('weight', prevCellModel.get('weight') + percent);
                                    }

                                    thisDialog.scaleRowWidths();
                                    ui.helper.css('left', -6);
                                }
                            });
                        }

                        newCell.click(function (event) {

                            if (!( $(event.target).is('.preview-cell') || $(event.target).is('.preview-cell-in') )) {
                                return;
                            }

                            var cell = $(event.target);
                            cell.closest('.row-preview').find('.preview-cell .preview-cell-in').removeClass('cell-selected');
                            cell.addClass('cell-selected');

                            this.openSelectedCellStyles();

                        }.bind(this));

                        // Make this row weight click editable
                        newCell.find('.preview-cell-weight').click(function (ci) {

                            // Disable the draggable while entering values
                            thisDialog.$('.resize-handle').css('pointer-event', 'none').draggable('disable');

                            rowPreview.find('.preview-cell-weight').each(function () {
                                var $$ = jQuery(this).hide();
                                $('<input type="text" class="preview-cell-weight-input no-user-interacted" />')
                                    .val(parseFloat($$.html())).insertAfter($$)
                                    .focus(function () {
                                        clearTimeout(timeout);
                                    })
                                    .keyup(function (e) {
                                        if (e.keyCode !== 9) {
                                            // Only register the interaction if the user didn't press tab
                                            $(this).removeClass('no-user-interacted');
                                        }

                                        // Enter is clicked
                                        if (e.keyCode === 13) {
                                            e.preventDefault();
                                            $(this).blur();
                                        }
                                    })
                                    .keydown(function (e) {
                                        if (e.keyCode === 9) {
                                            e.preventDefault();

                                            // Tab will always cycle around the row inputs
                                            var inputs = rowPreview.find('.preview-cell-weight-input');
                                            var i = inputs.index($(this));
                                            if (i === inputs.length - 1) {
                                                inputs.eq(0).focus().select();
                                            } else {
                                                inputs.eq(i + 1).focus().select();
                                            }
                                        }
                                    })
                                    .blur(function () {
                                        rowPreview.find('.preview-cell-weight-input').each(function (i, el) {
                                            if (isNaN(parseFloat($(el).val()))) {
                                                $(el).val(Math.floor(thisDialog.row.cells.at(i).get('weight') * 1000) / 10);
                                            }
                                        });

                                        timeout = setTimeout(function () {
                                            // If there are no weight inputs, then skip this
                                            if (rowPreview.find('.preview-cell-weight-input').length === 0) {
                                                return false;
                                            }

                                            // Go through all the inputs
                                            var rowWeights = [],
                                                rowChanged = [],
                                                changedSum = 0,
                                                unchangedSum = 0;

                                            rowPreview.find('.preview-cell-weight-input').each(function (i, el) {
                                                var val = parseFloat($(el).val());
                                                if (isNaN(val)) {
                                                    val = 1 / thisDialog.row.cells.length;
                                                } else {
                                                    val = Math.round(val * 10) / 1000;
                                                }

                                                // Check within 3 decimal points
                                                var changed = !$(el).hasClass('no-user-interacted');

                                                rowWeights.push(val);
                                                rowChanged.push(changed);

                                                if (changed) {
                                                    changedSum += val;
                                                } else {
                                                    unchangedSum += val;
                                                }
                                            });

                                            if (changedSum > 0 && unchangedSum > 0 && (
                                                1 - changedSum
                                            ) > 0) {
                                                // Balance out the unchanged rows to occupy the weight left over by the changed sum
                                                for (var i = 0; i < rowWeights.length; i++) {
                                                    if (!rowChanged[i]) {
                                                        rowWeights[i] = (
                                                            rowWeights[i] / unchangedSum
                                                        ) * (
                                                            1 - changedSum
                                                        );
                                                    }
                                                }
                                            }

                                            // Last check to ensure total weight is 1
                                            var sum = _.reduce(rowWeights, function (memo, num) {
                                                return memo + num;
                                            });
                                            rowWeights = rowWeights.map(function (w) {
                                                return w / sum;
                                            });

                                            // Set the new cell weights and regenerate the preview.
                                            if (Math.min.apply(Math, rowWeights) > 0.01) {
                                                thisDialog.row.cells.each(function (cell, i) {
                                                    cell.set('weight', rowWeights[i]);
                                                });
                                            }

                                            // Now lets animate the cells into their new widths
                                            rowPreview.find('.preview-cell').each(function (i, el) {
                                                var cellWeight = thisDialog.row.cells.at(i).get('weight');
                                                $(el).animate({'width': Math.round(cellWeight * 1000) / 10 + "%"}, 250);
                                                $(el).find('.preview-cell-weight-input').val(Math.round(cellWeight * 1000) / 10);
                                            });

                                            // So the draggable handle is not hidden.
                                            rowPreview.find('.preview-cell').css('overflow', 'visible');

                                            setTimeout(function () {
                                                thisDialog.regenerateRowPreview();
                                            }, 260);

                                        }, 100);
                                    })
                                    .click(function () {
                                        $(this).select();
                                    });
                            });

                            $(this).siblings('.preview-cell-weight-input').select();

                        });

                    }, this);

                    this.openSelectedCellStyles();

                    this.trigger('form_loaded', this);
                },

                getSelectedCellIndex: function () {
                    var selectedIndex = -1;
                    this.$('.preview-cell .preview-cell-in').each(function (index, el) {
                        if ($(el).is('.cell-selected')) {
                            selectedIndex = index;
                        }
                    });
                    return selectedIndex;
                },

                openSelectedCellStyles: function () {
                    if (!_.isUndefined(this.cellStyles)) {
                        if (this.cellStyles.stylesLoaded) {
                            var style = {};
                            try {
                                style = this.getFormValues('.cs-sidebar .cs-visual-styles.cs-cell-styles').style;
                            }
                            catch (err) {
                                console.log('Error retrieving cell styles - ' + err.message);
                            }

                            this.cellStyles.model.set('style', style);
                        }
                        this.cellStyles.detach();
                    }

                    this.cellStyles = this.getSelectedCellStyles();

                    if (this.cellStyles) {
                        var $rightSidebar = this.$('.cs-sidebar.cs-right-sidebar');
                        this.cellStyles.attach($rightSidebar);

                        if (!this.cellStyles.stylesLoaded) {
                            this.cellStyles.on('styles_loaded', function () {
                                $rightSidebar.removeClass('cs-panels-loading');
                            }, this);
                            $rightSidebar.addClass('cs-panels-loading');
                        }
                    }
                },

                getSelectedCellStyles: function () {
                    var cellIndex = this.getSelectedCellIndex();
                    if (cellIndex > -1) {
                        var cellStyles = this.cellStylesCache[cellIndex];
                        if (!cellStyles) {
                            cellStyles = new panels.view.styles();
                            cellStyles.model = this.row.cells.at(cellIndex);
                            cellStyles.render('cell', this.builder.config.postId, {
                                builderType: this.builder.config.builderType,
                                dialog: this,
                                index: cellIndex
                            });
                            this.cellStylesCache[cellIndex] = cellStyles;
                        }
                    }

                    return cellStyles;
                },

                clearCellStylesCache: function () {
                    // Call remove() on all cell styles to remove data, event listeners etc.
                    this.cellStylesCache.forEach(function (cellStyles) {
                        cellStyles.remove();
                    });
                    this.cellStylesCache = [];
                },

                /**
                 * Visually scale the row widths based on the cell weights
                 */
                scaleRowWidths: function () {
                    var thisDialog = this;
                    this.$('.row-preview .preview-cell').each(function (i, el) {
                        var cell = thisDialog.row.cells.at(i);
                        $(el)
                            .css('width', cell.get('weight') * 100 + "%")
                            .find('.preview-cell-weight').html(Math.round(cell.get('weight') * 1000) / 10);
                    });
                },

                /**
                 * Get the weights from the
                 */
                setCellsFromForm: function () {

                    try {
                        var f = {
                            'cells': parseInt(this.$('.row-set-form input[name="cells"]').val()),
                            'ratio': parseFloat(this.$('.row-set-form select[name="ratio"]').val()),
                            'fullpage': parseFloat(this.$('.row-set-form select[name="fullpage"]').val()),
                            'direction': this.$('.row-set-form select[name="ratio_direction"]').val()
                        };

                        if (_.isNaN(f.cells)) {
                            f.cells = 1;
                        }
                        if (isNaN(f.ratio)) {
                            f.ratio = 1;
                        }
                        if (isNaN(f.fullpage)) {
                            f.fullpage = 0;
                        }
                        if (f.cells < 1) {
                            f.cells = 1;
                            this.$('.row-set-form input[name="cells"]').val(f.cells);
                        }
                        else if (f.cells > 12) {
                            f.cells = 12;
                            this.$('.row-set-form input[name="cells"]').val(f.cells);
                        }

                        this.$('.row-set-form select[name="ratio"]').val(f.ratio);
                        this.$('.row-set-form select[name="fullpage"]').val(f.fullpage);

                        var cells = [];
                        var cellCountChanged = (
                            this.row.cells.length !== f.cells
                        );

                        // Now, lets create some cells
                        var currentWeight = 1;
                        for (var i = 0; i < f.cells; i++) {
                            cells.push(currentWeight);
                            currentWeight *= f.ratio;
                        }

                        // Now lets make sure that the row weights add up to 1

                        var totalRowWeight = _.reduce(cells, function (memo, weight) {
                            return memo + weight;
                        });
                        cells = _.map(cells, function (cell) {
                            return cell / totalRowWeight;
                        });

                        // Don't return cells that are too small
                        cells = _.filter(cells, function (cell) {
                            return cell > 0.01;
                        });

                        if (f.direction === 'left') {
                            cells = cells.reverse();
                        }

                        // Discard deleted cells.
                        this.row.cells = new panels.collection.cells(this.row.cells.first(cells.length));

                        _.each(cells, function (cellWeight, index) {
                            var cell = this.row.cells.at(index);
                            if (!cell) {
                                cell = new panels.model.cell({weight: cellWeight, row: this.model});
                                this.row.cells.add(cell);
                            } else {
                                cell.set('weight', cellWeight);
                            }
                        }.bind(this));

                        this.row.ratio = f.ratio;
                        this.row.fullpage = f.fullpage;
                        this.row.ratio_direction = f.direction;

                        if (cellCountChanged) {
                            this.regenerateRowPreview();
                        } else {
                            var thisDialog = this;

                            // Now lets animate the cells into their new widths
                            this.$('.preview-cell').each(function (i, el) {
                                var cellWeight = thisDialog.row.cells.at(i).get('weight');
                                $(el).animate({'width': Math.round(cellWeight * 1000) / 10 + "%"}, 250);
                                $(el).find('.preview-cell-weight').html(Math.round(cellWeight * 1000) / 10);
                            });

                            // So the draggable handle is not hidden.
                            this.$('.preview-cell').css('overflow', 'visible');

                            setTimeout(function () {
                                thisDialog.regenerateRowPreview();
                            }, 260);
                        }
                    }
                    catch (err) {
                        console.log('Error setting cells - ' + err.message);
                    }


                    // Remove the button primary class
                    this.$('.row-set-form .cs-button-row-set').removeClass('button-primary');
                },

                /**
                 * Handle a click on the dialog left bar tab
                 */
                tabClickHandler: function ($t) {
                    if ($t.attr('href') === '#row-layout') {
                        this.$('.cs-panels-dialog').addClass('cs-panels-dialog-has-right-sidebar');
                    } else {
                        this.$('.cs-panels-dialog').removeClass('cs-panels-dialog-has-right-sidebar');
                    }
                },

                /**
                 * Update the current model with what we have in the dialog
                 */
                updateModel: function (args) {
                    args = _.extend({
                        refresh: true,
                        refreshArgs: null
                    }, args);

                    // Set the cells
                    if (!_.isEmpty(this.model)) {
                        this.model.setCells(this.row.cells);
                        this.model.set('ratio', this.row.ratio);
                        this.model.set('fullpage', this.row.fullpage);
                        this.model.set('ratio_direction', this.row.ratio_direction);
                    }

                    // Update the row styles if they've loaded
                    if (!_.isUndefined(this.styles) && this.styles.stylesLoaded) {
                        // This is an edit dialog, so there are styles
                        var style = {};
                        try {
                            style = this.getFormValues('.cs-sidebar .cs-visual-styles.cs-row-styles').style;
                        }
                        catch (err) {
                            console.log('Error retrieving row styles - ' + err.message);
                        }

                        this.model.set('style', style);
                    }

                    // Update the cell styles if any are showing.
                    if (!_.isUndefined(this.cellStyles) && this.cellStyles.stylesLoaded) {

                        var style = {};
                        try {
                            style = this.getFormValues('.cs-sidebar .cs-visual-styles.cs-cell-styles').style;
                        }
                        catch (err) {
                            console.log('Error retrieving cell styles - ' + err.message);
                        }

                        this.cellStyles.model.set('style', style);
                    }

                    if (args.refresh) {
                        this.builder.model.refreshPanelsData(args.refreshArgs);
                    }
                },

                /**
                 * Insert the new row
                 */
                insertHandler: function () {
                    this.builder.addHistoryEntry('row_added');

                    this.updateModel();

                    var activeCell = this.builder.getActiveCell({
                        createCell: false
                    });

                    var options = {};
                    if (activeCell !== null) {
                        options.at = this.builder.model.get('rows').indexOf(activeCell.row) + 1;
                    }

                    // Set up the model and add it to the builder
                    this.model.collection = this.builder.model.get('rows');
                    this.builder.model.get('rows').add(this.model, options);

                    this.updateDialog();

                    this.builder.model.refreshPanelsData();

                    return false;
                },

                /**
                 * We'll just save this model and close the dialog
                 */
                saveHandler: function () {
                    this.builder.addHistoryEntry('row_edited');
                    this.updateModel();
                    this.updateDialog();

                    this.builder.model.refreshPanelsData();

                    return false;
                },

                /**
                 * The user clicks delete, so trigger deletion on the row model
                 */
                deleteHandler: function () {
                    // Trigger a destroy on the model that will happen with a visual indication to the user
                    this.model.trigger('visual_destroy');
                    this.updateDialog({silent: true});

                    return false;
                },

                /**
                 * Duplicate this row
                 */
                duplicateHandler: function () {
                    this.builder.addHistoryEntry('row_duplicated');

                    var duplicateRow = this.model.clone(this.builder.model);

                    this.builder.model.get('rows').add(duplicateRow, {
                        at: this.builder.model.get('rows').indexOf(this.model) + 1
                    });

                    this.updateDialog({silent: true});

                    return false;
                },

                closeHandler: function () {
                    this.clearCellStylesCache();
                    if (!_.isUndefined(this.cellStyles)) {
                        this.cellStyles = undefined;
                    }
                }

            });

        }, {}],
        9: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;
            var jsWidget = require('../view/widgets/js-widget');

            module.exports = panels.view.dialog.extend({

                builder: null,
                sidebarWidgetTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-dialog-widget-sidebar-widget').html())),

                dialogClass: 'cs-panels-dialog-edit-widget',
                dialogIcon: 'add-widget',

                widgetView: false,
                savingWidget: false,
                editableLabel: true,

                events: {
                    'click .cs-update': 'saveHandler',
                    'click .cs-close': 'closeDialog',
                    'click .cs-nav.cs-previous': 'navToPrevious',
                    'click .cs-nav.cs-next': 'navToNext',

                    // Action handlers
                    'click .cs-toolbar .cs-delete': 'deleteHandler',
                    'click .cs-toolbar .cs-duplicate': 'duplicateHandler',
                    'click .clever-innerrow .with-thumbnail' : 'editInnerrowHandle',
                    'click .update-value-input-hidden-clicked': 'updateValueHidden',
                    'change .select-add-class-into-widget-view-content' : 'addClassIntoWidgetViewContent',
                    'click .clever-banner .with-thumbnail' : 'editBannerHandle'
                },

                initializeDialog: function () {
                    var thisView = this;
                    this.model.on('change:values', this.handleChangeValues, this);
                    this.model.on('destroy', this.remove, this);

                    // Refresh panels data after both dialog form components are loaded
                    this.dialogFormsLoaded = 0;
                    this.on('form_loaded styles_loaded', function () {
                        this.dialogFormsLoaded++;
                        if (this.dialogFormsLoaded === 2) {
                            thisView.updateModel({
                                refreshArgs: {
                                    silent: true
                                }
                            });
                        }
                    });


                    this.on('edit_label', function (text) {
                        // If text is set to default value, just clear it.
                        var temp = panelsOptions.widgets[this.model.get('class')] ? panelsOptions.widgets[this.model.get('class')].title : panelsOptions.widgets[this.model.get('type')].title;
                        if (text === temp) {
                            text = '';
                        }
                        this.model.set('label', text);
                        if (_.isEmpty(text)) {
                            this.$('.cs-title').text(this.model.getWidgetField('title'));
                        }
                    }.bind(this));
                },

                /**
                 * Render the widget dialog.
                 */
                render: function () {
                    if (!_.isUndefined(window.formContentLoaded) && !_.isUndefined(window.formContentLoaded[this.model.cid])) {
                        window.formContentLoaded[this.model.cid].show();
                        return false;
                    }

                    // Render the dialog and attach it to the builder interface
                    this.renderDialog(this.parseDialogContent($('#cleversoft-panels-dialog-widget').html(), {}));
                    this.loadForm();

                    var title = this.model.getWidgetField('title');
                    this.$('.cs-title .widget-name').html(title);
                    this.$('.cs-edit-title').val(title);

                    if (!this.builder.supports('addWidget')) {
                        this.$('.cs-buttons .cs-duplicate').remove();
                    }
                    if (!this.builder.supports('deleteWidget')) {
                        this.$('.cs-buttons .cs-delete').remove();
                    }

                    // Now we need to attach the style window
                    this.styles = new panels.view.styles();
                    this.styles.model = this.model;
                    this.styles.render('widget', this.builder.config.postId, {
                        builderType: this.builder.config.builderType,
                        dialog: this
                    });

                    var $rightSidebar = this.$('.cs-sidebar.cs-right-sidebar');
                    this.styles.attach($rightSidebar);

                    // Handle the loading class
                    this.styles.on('styles_loaded', function (hasStyles) {
                        // If we have styles remove the loading spinner, else remove the whole empty sidebar.
                        if (hasStyles) {
                            $rightSidebar.removeClass('cs-panels-loading');
                        } else {
                            $rightSidebar.closest('.cs-panels-dialog').removeClass('cs-panels-dialog-has-right-sidebar');
                            $rightSidebar.remove();
                        }
                    }, this);
                    $rightSidebar.addClass('cs-panels-loading');
                },
                /*
                 * add or change class in iframe once sider got changed
                 */
                addClassIntoWidgetViewContent: function(e) {
                    var target = $(e.target);
                    var $widget_form = target.closest('.cleversoft-widget-form ');
                    var $widget_id = $widget_form.attr('data-widget-id');
                    var iframe_content = $('.cs-preview iframe').contents().find('div[data-id="'+$widget_id+'"]');
                    var $class = iframe_content.attr('class');
                    var options = target.find('option');
                    var values = $.map(options ,function(option) {
                        return option.value;
                    });
                    var $class_array = $class.split(' ');
                    values.each(function(el){
                        var indexOf = $.inArray(el, $class_array);
                        if(indexOf != -1) $class = $class.replace(el, '');
                    });
                    $class = $class + ' ' + target.val();
                    iframe_content.attr('class', $class);
                },
                /*
                 * update value for hidden element
                 */
                updateValueHidden: function(e) {
                    var target = $(e.target);
                    $(target.attr('data-id')).val(target.attr('value'));
                },
                /*
                 * Handle this Innerrow being clicked on
                 */
                editInnerrowHandle : function(e) {
                    var target = $(e.target);
                    target.closest('.clever-innerrow').find('li.active').removeClass('active');
                    target.closest('li').addClass('active');
                    //re-build panel data
                    var layout = target.closest('.with-thumbnail');
                    $(layout.attr('data-value-id')).val(layout.attr('data-layout'));
                    this.model.set('totalInnerrowpanels', layout.attr('data-layout'));
                    this.updateModel();
                },

                /*
                 * Handle this Banner being clicked on
                 */
                editBannerHandle : function(e) {
                    var target = $(e.target);
                    target.closest('.clever-banner').find('li.active').removeClass('active');
                    target.closest('li').addClass('active');
                    //re-build panel data
                    var layout = target.closest('.with-thumbnail');
                    $(layout.attr('data-value-id')).val(layout.attr('data-layout'));
                    this.model.set('totalBannerpanels', layout.attr('data-layout'));
                    this.updateModel();
                },

                /**
                 * Get the previous widget editing dialog by looking at the dom.
                 * @returns {*}
                 */
                getPrevDialog: function () {
                    var widgets = this.builder.$('.cs-cells .cell .cs-widget');
                    if (widgets.length <= 1) {
                        return false;
                    }
                    var currentIndex = widgets.index(this.widgetView.$el);

                    if (currentIndex === 0) {
                        return false;
                    } else {
                        do {
                            var widgetView = widgets.eq(--currentIndex).data('view');
                            if (!_.isUndefined(widgetView) && !widgetView.model.get('read_only')) {
                                return widgetView.getEditDialog();
                            }
                        } while (!_.isUndefined(widgetView) && currentIndex > 0);
                    }

                    return false;
                },

                /**
                 * Get the next widget editing dialog by looking at the dom.
                 * @returns {*}
                 */
                getNextDialog: function () {
                    var widgets = this.builder.$('.cs-cells .cell .cs-widget');
                    if (widgets.length <= 1) {
                        return false;
                    }

                    var currentIndex = widgets.index(this.widgetView.$el), widgetView;

                    if (currentIndex === widgets.length - 1) {
                        return false;
                    } else {
                        do {
                            widgetView = widgets.eq(++currentIndex).data('view');
                            if (!_.isUndefined(widgetView) && !widgetView.model.get('read_only')) {
                                return widgetView.getEditDialog();
                            }
                        } while (!_.isUndefined(widgetView));
                    }

                    return false;
                },

                /**
                 * Load the widget form from the server.
                 * This is called when rendering the dialog for the first time.
                 */
                loadForm: function () {
                    // don't load the form if this dialog hasn't been rendered yet
                    if (!this.$('> *').length) {
                        return;
                    }

                    this.$('.cs-content').addClass('cs-panels-loading');
                    this.$('.cs-content').append('<span class="zoo-loading" style="opacity: 1; visibility: visible; top: 50%;"></span>');

                    var data = {
                        'action': 'so_panels_widget_form',
                        'widget': this.model.get('class'),
                        'widget_id' : this.model.get('widget_id'),
                        'type' : this.model.get('type'),
                        'instance': JSON.stringify(this.model.get('values')),
                        'raw': this.model.get('raw')
                    };

                    $.post(
                        panelsOptions.ajaxurl,
                        data,
                        function (result) {
                            // Add in the CID of the widget model
                            var html = result.replace(/{\$id}/g, this.model.cid);

                            // Load this content into the form
                            var $soContent = this.$('.cs-content');
                            $soContent
                                .removeClass('cs-panels-loading')
                                .append(html);

                            this.$('.cs-content .zoo-loading').remove();  
                               
                            // Trigger all the necessary events
                            this.trigger('form_loaded', this);

                            //run mage init
                            if($('.clever-trigger-content-update').length > 0) $('.clever-trigger-content-update').trigger('contentUpdated');
                            $('.mage-init-dependency').trigger('contentUpdated');

                            // For legacy compatibility, trigger a panelsopen event
                            this.$('.panel-dialog').trigger('panelsopen');

                            // If the main dialog is closed from this point on, save the widget content
                            this.on('close_dialog', this.updateModel, this);
                            /*
                             * add event click to slider input field
                             *
                             */
                             
                            var $builder = this;
                            $(document).on('changeSliderUi', function(event){
                                $builder.updateModel();
                            });

                            var widgetContent = $soContent.find('> .widget-content');
                            // If there's a widget content wrapper, this is one of the new widgets in WP 4.8 which need some special
                            // handling in JS.
                            if (widgetContent.length > 0) {
                                jsWidget.addWidget($soContent, this.model.widget_id);
                            }
                            if (_.isUndefined(window.formContentLoaded)) {
                                window.formContentLoaded = [];
                            }
                            window.formContentLoaded[this.model.cid] = $soContent.closest('.cs-panels-dialog-wrapper');
                        }.bind(this),
                        'html'
                    );
                },

                /**
                 * Save the widget from the form to the model
                 */
                updateModel: function (args) {
                    args = _.extend({
                        refresh: true,
                        refreshArgs: null
                    }, args);

                    // Get the values from the form and assign the new values to the model
                    this.savingWidget = true;

                    if (!this.model.get('missing')) {
                        // Only get the values for non missing widgets.
                        var values = this.getFormValues();
                        if (_.isUndefined(values.widgets)) {
                            values = {};
                        } else {
                            values = values.widgets;
                            values = values[Object.keys(values)[0]];
                        }

                        this.model.setValues(values);
                        this.model.set('raw', true); // We've saved from the widget form, so this is now raw
                    }

                    if (this.styles.stylesLoaded) {
                        // If the styles view has loaded
                        var style = {};
                        try {
                            style = this.getFormValues('.cs-sidebar .cs-visual-styles').style;
                        }
                        catch (e) {
                        }
                        this.model.set('style', style);
                    }

                    this.savingWidget = false;

                    if (args.refresh) {
                        this.builder.model.refreshPanelsData(args.refreshArgs);
                    }
                },

                /**
                 *
                 */
                handleChangeValues: function () {
                    if (!this.savingWidget) {
                        // Reload the form when we've changed the model and we're not currently saving from the form
                        this.loadForm();
                    }
                },

                /**
                 * Save a history entry for this widget. Called when the dialog is closed.
                 */
                saveHandler: function () {
                    this.builder.addHistoryEntry('widget_edited');
                    this.updateDialog();
                },

                /**
                 * When the user clicks delete.
                 *
                 * @returns {boolean}
                 */
                deleteHandler: function () {

                    this.model.trigger('visual_destroy');
                    this.updateDialog({silent: true});
                    this.builder.model.refreshPanelsData();

                    return false;
                },

                duplicateHandler: function () {
                    this.model.trigger('user_duplicate');

                    this.updateDialog({silent: true});
                    this.builder.model.refreshPanelsData();

                    return false;
                }

            });

        }, {"../view/widgets/js-widget": 31}],
        10: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = panels.view.dialog.extend({

                builder: null,
                widgetTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-dialog-widgets-widget').html())),
                filter: {},

                dialogClass: 'cs-panels-dialog-add-widget',
                dialogIcon: 'add-widget',
                remove_widget: 'clever-innerrow',

                events: {
                    'click .cs-update': 'updateDialog',
                    'click .cs-close': 'updateDialog',
                    'click .widget-type': 'widgetClickHandler',
                    'keyup .cs-sidebar-search': 'searchHandler'
                },

                /**
                 * Initialize the widget adding dialog
                 */
                initializeDialog: function () {

                    this.on('open_dialog', function () {
                        this.filter.search = '';
                        this.filterWidgets(this.filter);
                    }, this);

                    this.on('open_dialog_complete', function () {
                        // Clear the search and re-filter the widgets when we open the dialog
                        this.$('.cs-sidebar-search').val('').focus();
                        this.balanceWidgetHeights();
                    });

                    // We'll implement a custom tab click handler
                    this.on('tab_click', this.tabClickHandler, this);
                },

                render: function () {
                    // Render the dialog and attach it to the builder interface
                    this.renderDialog(this.parseDialogContent($('#cleversoft-panels-dialog-widgets').html(), {}));

                    // Add all the widgets
                    _.each(panelsOptions.widgets, function (widget) {
                        if(widget.code === this.remove_widget) return;
                        var data = {
                            title: widget.title,
                            description: widget.description,
                            type: widget.type,
                            code: widget.code
                        };

                        //if(widget.area == 'widget') {
                        var $w = $(this.widgetTemplate(data));
                        //} else if( widget.area == 'element' ) {
                        //    var $w = $(this.elementTemplate(data));
                        //}

                        if (_.isUndefined(widget.icon) || widget.icon.trim() == '' ) {
                            widget.icon = 'dashicons dashicons-admin-generic';
                        }

                        $('<span class="'+widget.area+'-icon" />').addClass(widget.icon).prependTo($w.find('.widget-type-wrapper'));

                        var $data = {
                            'class': widget.class,
                            'type' : widget.type,
                            'items': !_.isUndefined(widget.has_child) ? true : false,
                            'has_child' : !_.isUndefined(widget.has_child) ? widget.has_child : false,
                            'layouts' : !_.isUndefined(widget.layouts) ? widget.layouts : false,
                            'default_items' : !_.isUndefined(widget.default_items) ? widget.default_items : false
                        };

                        $w.data($data).appendTo(this.$('.'+widget.area+'-type-list'));
                    }, this);


                    // Add the sidebar tabs
                    var tabs = this.$('.cs-sidebar-tabs');
                    _.each(panelsOptions.widget_dialog_tabs, function (tab) {
                        $(this.dialogTabTemplate({'title': tab.title})).data({
                            'message': tab.message,
                            'filter': tab.filter
                        }).appendTo(tabs);
                    }, this);

                    // We'll be using tabs, so initialize them
                    this.initTabs();

                    var thisDialog = this;
                    $(window).resize(function () {
                        thisDialog.balanceWidgetHeights();
                    });
                },
                /*
                 *
                 */
                getChildWidgets: function() {},

                /**
                 * Handle a tab being clicked
                 */
                tabClickHandler: function ($t) {
                    // Get the filter from the tab, and filter the widgets
                    this.filter = $t.parent().data('filter');
                    this.filter.search = this.$('.cs-sidebar-search').val();

                    var message = $t.parent().data('message');
                    if (_.isEmpty(message)) {
                        message = '';
                    }

                    this.$('.cs-toolbar .cs-status').html(message);

                    this.filterWidgets(this.filter);

                    return false;
                },

                /**
                 * Handle changes to the search value
                 */
                searchHandler: function (e) {
                    if (e.which === 13) {
                        var visibleWidgets = this.$('.widget-type-list .widget-type:visible, .element-type-list .widget-type:visible, .layout-type-list .widget-type:visible');
                        if (visibleWidgets.length === 1) {
                            visibleWidgets.click();
                        }
                    }
                    else {
                        this.filter.search = $(e.target).val().trim();
                        this.filterWidgets(this.filter);
                    }
                },

                /**
                 * Filter the widgets that we're displaying
                 * @param filter
                 */
                filterWidgets: function (filter) {
                    if (_.isUndefined(filter)) {
                        filter = {};
                    }

                    if (_.isUndefined(filter.groups)) {
                        filter.groups = '';
                    }

                    this.$('.widget-type-list .widget-type, .element-type-list .widget-type, .layout-type-list .widget-type').each(function () {
                        var $$ = $(this), showWidget;
                        var widgetClass = $$.data('class');

                        var widgetData = (
                            !_.isUndefined(panelsOptions.widgets[widgetClass])
                        ) ? panelsOptions.widgets[widgetClass] : (!_.isUndefined(panelsOptions.widgets[$$.data('type')]) ? panelsOptions.widgets[$$.data('type')] : null );

                        if (_.isEmpty(filter.groups)) {
                            // This filter doesn't specify groups, so show all
                            showWidget = true;
                        } else if (widgetData !== null && !_.isEmpty(_.intersection(filter.groups, panelsOptions.widgets[widgetClass].groups))) {
                            // This widget is in the filter group
                            showWidget = true;
                        } else {
                            // This widget is not in the filter group
                            showWidget = false;
                        }

                        // This can probably be done with a more intelligent operator
                        if (showWidget) {

                            if (!_.isUndefined(filter.search) && filter.search !== '') {
                                // Check if the widget title contains the search term
                                if (widgetData.title.toLowerCase().indexOf(filter.search.toLowerCase()) === -1) {
                                    showWidget = false;
                                }
                            }

                        }

                        if (showWidget) {
                            $$.show();
                        } else {
                            $$.hide();
                        }
                    });

                    // Balance the tags after filtering
                    this.balanceWidgetHeights();
                },

                /**
                 * Add the widget to the current builder
                 *
                 * @param e
                 */
                widgetClickHandler: function (e) {
                    // Add the history entry
                    this.builder.addHistoryEntry('widget_added');

                    var $w = $(e.currentTarget);
                    if ($w.data('type') == "CleverSoft\\CleverBuilder\\Block\\Builder\\Widget\\Row") {
                        $('.cs-builder-toolbar .cs-row-add').trigger('click');
                        this.updateDialog();
                    } else {
                        var a = $w.data('default_items');
                        var b = panelsOptions.widgets[$w.data('type')].default_items;
                        var widget = new panels.model.widget({
                            class: $w.data('class'),
                            type : $w.data('type'),
                            items: $w.data('items') ?  new panels.collection.childWidgets() : false,
                            has_child: $w.data('has_child') ? $w.data('has_child') : false,
                            layouts: $w.data('layouts') ? $w.data('layouts') : false,
                            default_items: $w.data('default_items') ? $w.data('default_items') : (!_.isUndefined(panelsOptions.widgets[$w.data('type')].default_items)?panelsOptions.widgets[$w.data('type')].default_items:false),
                            widget_id: panels.helpers.utils.generateUUID()
                        });

                        // Add the widget to the cell model
                        widget.cell = this.builder.getActiveCell();
                        if($w.data('has_child') === 'innerrow') {
                            var dialog = new panels.dialog.innerrowLayoutsItem();
                            dialog.setBuilder(this.builder);
                            dialog.setWidget(widget);
                            this.updateDialog();
                            dialog.openDialog();
                        } else if($w.data('has_child') === 'row') {
                            var dialog = new panels.dialog.rowLayoutsItem();
                            dialog.setBuilder(this.builder);
                            dialog.setWidget(widget);
                            this.updateDialog();
                            dialog.openDialog();
                        } else if($w.data('has_child') === 'banner') {
                            var dialog = new panels.dialog.bannerItem();
                            dialog.setBuilder(this.builder);
                            dialog.setWidget(widget);
                            this.updateDialog();
                            dialog.openDialog();
                        } else {
                            widget.cell.get('widgets').add(widget);
                            this.updateDialog();
                            this.builder.model.refreshPanelsData();
                        }
                    }

                },

                /**
                 * Balance widgets in a given row so they have enqual height.
                 * @param e
                 */
                balanceWidgetHeights: function (e) {
                    this.balanceWidgetHeightsArea('widget-type-list');
                    this.balanceWidgetHeightsArea('element-type-list');
                    this.balanceWidgetHeightsArea('layout-type-list');

                },
                /*
                 * Balance widgets in a given row so they have enqual height for each area.
                 * @param area
                 */
                balanceWidgetHeightsArea: function(area) {
                    var widgetRows = [[]];
                    var previousWidget = null;

                    // Work out how many widgets there are per row
                    var perRow = Math.round(this.$('.'+ area + ' .widget-type').parent().width() / this.$('.'+ area + ' .widget-type').width());

                    // Add clears to create balanced rows
                    this.$('.'+ area + ' .widget-type')
                        .css('clear', 'none')
                        .filter(':visible')
                        .each(function (i, el) {
                            if (i % perRow === 0 && i !== 0) {
                                $(el).css('clear', 'both');
                            }
                        });

                    // Group the widgets into rows
                    this.$('.widget-type-wrapper')
                        .css('height', 'auto')
                        .filter(':visible')
                        .each(function (i, el) {
                            var $el = $(el);
                            if (previousWidget !== null && previousWidget.position().top !== $el.position().top) {
                                widgetRows[widgetRows.length] = [];
                            }
                            previousWidget = $el;
                            widgetRows[widgetRows.length - 1].push($el);
                        });

                    // Balance the height of the widgets within the row.
                    _.each(widgetRows, function (row, i) {
                        var maxHeight = _.max(row.map(function (el) {
                            return el.height();
                        }));
                        // Set the height of each widget in the row
                        _.each(row, function (el) {
                            el.height(maxHeight);
                        });

                    });
                }
            });

        }, {}],
        11: [function (require, module, exports) {
            module.exports = {
                /**
                 * Check if we have copy paste available.
                 * @returns {boolean|*}
                 */
                canCopyPaste: function () {
                    return typeof(Storage) !== "undefined" && panelsOptions.user;
                },

                /**
                 * Set the model that we're going to store in the clipboard
                 */
                setModel: function (model) {
                    if (!this.canCopyPaste()) {
                        return false;
                    }

                    var serial = panels.helpers.serialize.serialize(model);
                    if (model instanceof  panels.model.row) {
                        serial.thingType = 'row-model';
                    } else if (model instanceof  panels.model.widget) {
                        serial.thingType = 'widget-model';
                    }

                    // Store this in local storage
                    localStorage['panels_clipboard_' + panelsOptions.user] = JSON.stringify(serial);
                    return true;
                },

                /**
                 * Check if the current model stored in the clipboard is the expected type
                 */
                isModel: function (expected) {
                    if (!this.canCopyPaste()) {
                        return false;
                    }

                    var clipboardObject = localStorage['panels_clipboard_' + panelsOptions.user];
                    if (clipboardObject !== undefined) {
                        clipboardObject = JSON.parse(clipboardObject);
                        return clipboardObject.thingType && clipboardObject.thingType === expected;
                    }

                    return false;
                },

                /**
                 * Get the model currently stored in the clipboard
                 */
                getModel: function (expected) {
                    if (!this.canCopyPaste()) {
                        return null;
                    }

                    var clipboardObject = localStorage['panels_clipboard_' + panelsOptions.user];
                    if (clipboardObject !== undefined) {
                        clipboardObject = JSON.parse(clipboardObject);
                        if (clipboardObject.thingType && clipboardObject.thingType === expected) {
                            return panels.helpers.serialize.unserialize(clipboardObject, clipboardObject.thingType, null);
                        }
                    }

                    return null;
                }
            };

        }, {}],
        12: [function (require, module, exports) {
            module.exports = {
                /**
                 * Lock window scrolling for the main overlay
                 */
                lock: function () {
                    if (jQuery('body').css('overflow') === 'hidden') {
                        return;
                    }

                    // lock scroll position, but retain settings for later
                    var scrollPosition = [
                        self.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft,
                        self.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop
                    ];

                    jQuery('body')
                        .data({
                            'scroll-position': scrollPosition
                        })
                        .css('overflow', 'hidden');

                    if (!_.isUndefined(scrollPosition)) {
                        window.scrollTo(scrollPosition[0], scrollPosition[1]);
                    }
                },

                /**
                 * Unlock window scrolling
                 */
                unlock: function () {
                    if (jQuery('body').css('overflow') !== 'hidden') {
                        return;
                    }

                    // Check that there are no more dialogs or a live editor
                    if (!jQuery('.cs-panels-dialog-wrapper').is(':visible') && !jQuery('.cs-panels-live-editor').is(':visible')) {
                        jQuery('body').css('overflow', 'visible');
                        var scrollPosition = jQuery('body').data('scroll-position');

                        if (!_.isUndefined(scrollPosition)) {
                            window.scrollTo(scrollPosition[0], scrollPosition[1]);
                        }
                    }
                }
            };

        }, {}],
        13: [function (require, module, exports) {
            /*
             This is a modified version of https://github.com/underdogio/backbone-serialize/
             */

            /* global Backbone, module, panels */

            module.exports = {
                serialize: function (thing) {
                    var val;

                    if (thing instanceof Backbone.Model) {
                        var retObj = {};
                        for (var key in thing.attributes) {
                            if (thing.attributes.hasOwnProperty(key)) {
                                // Skip these to avoid recursion
                                if (key === 'builder' || key === 'collection') {
                                    continue;
                                }

                                // If the value is a Model or a Collection, then serialize them as well
                                val = thing.attributes[key];
                                if (val instanceof Backbone.Model || val instanceof Backbone.Collection) {
                                    retObj[key] = this.serialize(val);
                                } else {
                                    // Otherwise, save the original value
                                    retObj[key] = val;
                                }
                            }
                        }
                        return retObj;
                    }
                    else if (thing instanceof Backbone.Collection) {
                        // Walk over all of our models
                        var retArr = [];

                        for (var i = 0; i < thing.models.length; i++) {
                            // If the model is serializable, then serialize it
                            val = thing.models[i];

                            if (val instanceof Backbone.Model || val instanceof Backbone.Collection) {
                                retArr.push(this.serialize(val));
                            } else {
                                // Otherwise (it is an object), return it in its current form
                                retArr.push(val);
                            }
                        }

                        // Return the serialized models
                        return retArr;
                    }
                },

                unserialize: function (thing, thingType, parent) {
                    var retObj;

                    switch (thingType) {
                        case 'row-model' :
                            retObj = new panels.model.row();
                            retObj.builder = parent;
                            retObj.set('style', thing.style);
                            retObj.setCells(this.unserialize(thing.cells, 'cell-collection', retObj));
                            break;

                        case 'cell-model' :
                            retObj = new panels.model.cell();
                            retObj.row = parent;
                            retObj.set('weight', thing.weight);
                            retObj.set('style', thing.style);
                            retObj.set('widgets', this.unserialize(thing.widgets, 'widget-collection', retObj));
                            break;

                        case 'widget-model' :
                            retObj = new panels.model.widget();
                            retObj.cell = parent;
                            for (var key in thing) {
                                if (thing.hasOwnProperty(key)) {
                                    retObj.set(key, thing[key]);
                                }
                            }
                            retObj.set('widget_id', panels.helpers.utils.generateUUID());
                            break;

                        case 'cell-collection':
                            retObj = new panels.collection.cells();
                            for (var i = 0; i < thing.length; i++) {
                                retObj.push(this.unserialize(thing[i], 'cell-model', parent));
                            }
                            break;

                        case 'widget-collection':
                            retObj = new panels.collection.widgets();
                            for (var i = 0; i < thing.length; i++) {
                                retObj.push(this.unserialize(thing[i], 'widget-model', parent));
                            }
                            break;

                        default:
                            console.log('Unknown Thing - ' + thingType);
                            break;
                    }

                    return retObj;
                }
            };

        }, {}],
        14: [function (require, module, exports) {
            module.exports = {

                generateUUID: function () {
                    var d = new Date().getTime();
                    if (window.performance && typeof window.performance.now === "function") {
                        d += performance.now(); //use high-precision timer if available
                    }
                    var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                        var r = (d + Math.random() * 16) % 16 | 0;
                        d = Math.floor(d / 16);
                        return ( c == 'x' ? r : (r & 0x3 | 0x8) ).toString(16);
                    });
                    return uuid;
                },

                processTemplate: function (s) {
                    if (_.isUndefined(s) || _.isNull(s)) {
                        return '';
                    }
                    s = s.replace(/{{%/g, '<%');
                    s = s.replace(/%}}/g, '%>');
                    s = s.trim();
                    return s;
                },

                // From this SO post: http://stackoverflow.com/questions/6139107/programmatically-select-text-in-a-contenteditable-html-element
                selectElementContents: function (element) {
                    var range = document.createRange();
                    range.selectNodeContents(element);
                    var sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(range);
                }

            }

        }, {}],
        15: [function (require, module, exports) {
            /* global _, jQuery, panels */

            var panels = window.panels, $ = jQuery;

            module.exports = function (config) {

                return this.each(function () {
                    var $$ = jQuery(this);
                    var widgetId = $$.closest('form').find('.widget-id').val();

                    // Create a config for this specific widget
                    var thisConfig = $.extend(true, {}, config);

                    // Exit if this isn't a real widget
                    if (!_.isUndefined(widgetId) && widgetId.indexOf('__i__') > -1) {
                        return;
                    }

                    // Create the main builder model
                    var builderModel = new panels.model.builder();

                    // Now for the view to display the builder
                    var builderView = new panels.view.builder({
                        model: builderModel,
                        config: thisConfig
                    });

                    // Save panels data when we close the dialog, if we're in a dialog
                    var dialog = $$.closest('.cs-panels-dialog-wrapper').data('view');
                    if (!_.isUndefined(dialog)) {
                        dialog.on('close_dialog', function () {
                            builderModel.refreshPanelsData();
                        });

                        dialog.on('open_dialog_complete', function () {
                            // Make sure the new layout widget is always properly setup
                            builderView.trigger('builder_resize');
                        });

                        dialog.model.on('destroy', function () {
                            // Destroy the builder
                            builderModel.emptyRows().destroy();
                        });

                        // Set the parent for all the sub dialogs
                        builderView.setDialogParents(panelsOptions.loc.layout_widget, dialog);
                    }

                    // Basic setup for the builder
                    var isWidget = Boolean($$.closest('.widget-content').length);
                    builderView
                        .render()
                        .attach({
                            container: $$,
                            dialog: isWidget || $$.data('mode') === 'dialog',
                            type: $$.data('type')
                        })
                        .setDataField($$.find('input.panels-data'));

                    if (isWidget || $$.data('mode') === 'dialog') {
                        // Set up the dialog opening
                        builderView.setDialogParents(panelsOptions.loc.layout_widget, builderView.dialog);
                        $$.find('.cleversoft-panels-display-builder').click(function (e) {
                            e.preventDefault();
                            builderView.dialog.openDialog();
                        });
                    } else {
                        // Remove the dialog opener button, this is already being displayed in a page builder dialog.
                        $$.find('.cleversoft-panels-display-builder').parent().remove();
                    }

                    // Trigger a global jQuery event after we've setup the builder view
                    $(document).trigger('panels_setup', builderView);
                });
            };

        }, {}],
        16: [function (require, module, exports) {
            /**
             * Everything we need for CleverSoft Page Builder.
             * @license GPL 3.0 http://www.gnu.org/licenses/gpl.html
             */

            /* global Backbone, _, jQuery, tinyMCE, panelsOptions, plupload, confirm, console, require */

            var panels = {};

// Store everything globally
            window.panels = panels;
            window.cleversoftPanels = panels;

// Helpers
            panels.helpers = {};
            panels.helpers.clipboard = require('./helpers/clipboard');
            panels.helpers.utils = require('./helpers/utils');
            panels.helpers.serialize = require('./helpers/serialize');
            panels.helpers.pageScroll = require('./helpers/page-scroll');

// The models
            panels.model = {};
            panels.model.widget = require('./model/widget');
            panels.model.childWidget = require('./model/child-widget');
            panels.model.cell = require('./model/cell');
            panels.model.row = require('./model/row');
            panels.model.builder = require('./model/builder');
            panels.model.historyEntry = require('./model/history-entry');

// The collections
            panels.collection = {};
            panels.collection.widgets = require('./collection/widgets');
            panels.collection.childWidgets = require('./collection/child-widget');
            panels.collection.cells = require('./collection/cells');
            panels.collection.rows = require('./collection/rows');
            panels.collection.historyEntries = require('./collection/history-entries');

// The views
            panels.view = {};
            panels.view.widget = require('./view/widget');
            panels.view.childWidget = require('./view/child-widget');
            panels.view.cell = require('./view/cell');
            panels.view.row = require('./view/row');
            panels.view.builder = require('./view/builder');
            panels.view.dialog = require('./view/dialog');
            panels.view.styles = require('./view/styles');
            panels.view.liveEditor = require('./view/live-editor');

// The dialogs
            panels.dialog = {};
            panels.dialog.builder = require('./dialog/builder');
            panels.dialog.widgets = require('./dialog/widgets');
            panels.dialog.childWidgets = require('./dialog/child-widgets');
            panels.dialog.widget = require('./dialog/widget');
            panels.dialog.childWidget = require('./dialog/child-widget');
            panels.dialog.prebuilt = require('./dialog/prebuilt');
            panels.dialog.tabItemEditor = require('./dialog/tab-item-editor');
            panels.dialog.scrolltoItemEditor = require('./dialog/scrollto-item-editor');
            panels.dialog.textboxItemEditor = require('./dialog/textbox-item-editor');
            panels.dialog.innerrowItemEditor = require('./dialog/innerrow-item-editor');
            panels.dialog.innerrowLayoutsItem = require('./dialog/innerrow-layout-item-editor');
            panels.dialog.rowItemEditor = require('./dialog/row-item-editor');
            panels.dialog.rowLayoutsItem = require('./dialog/row-layout-item-editor');
            panels.dialog.bannerItem = require('./dialog/banner-item-editor');
            panels.dialog.row = require('./dialog/row');
            panels.dialog.history = require('./dialog/history');

// The utils
            panels.utils = {};
            panels.utils.menu = require('./utils/menu');

// jQuery Plugins
            jQuery.fn.soPanelsSetupBuilderWidget = require('./jquery/setup-builder-widget');


// Set up Page Builder if we're on the main interface
            jQuery(function ($) {

                var container,
                    field,
                    //form,
                    builderConfig;

                var $panelsMetabox = $('#cleversoft-panels-metabox');
                //form = $('form#post');
                //if ($panelsMetabox.length && form.length) {
                if ($panelsMetabox.length) {
                    // This is usually the case when we're in the post edit interface
                    container = $panelsMetabox;
                    field = $panelsMetabox.find('.cleversoft-panels-data-field');

                    builderConfig = {
                        editorType: 'tinyMCE',
                        postId: $('#post_ID').val(),
                        editorId: '#content',
                        builderType: $panelsMetabox.data('builder-type'),
                        builderSupports: $panelsMetabox.data('builder-supports'),
                        loadOnAttach: panelsOptions.loadOnAttach && $('#auto_draft').val() == 1,
                        loadLiveEditor: $panelsMetabox.data('live-editor') == 1,
                        liveEditorPreview: container.data('preview-url')
                    };
                } else if ($('.cleversoft-panels-builder-form').length) {
                    // We're dealing with another interface like the custom home page interface
                    var $$ = $('.cleversoft-panels-builder-form');

                    container = $$.find('.cleversoft-panels-builder-container');
                    field = $$.find('input[name="panels_data"]');
                    //form = $$;

                    builderConfig = {
                        editorType: 'standalone',
                        postId: $$.data('post-id'),
                        editorId: '#post_content',
                        builderType: $$.data('type'),
                        builderSupports: $$.data('builder-supports'),
                        loadLiveEditor: false,
                        liveEditorPreview: $$.data('preview-url')
                    };
                }

                if (!_.isUndefined(container)) {
                    // If we have a container, then set up the main builder
                    var panels = window.cleversoftPanels;

                    // Create the main builder model
                    var builderModel = new panels.model.builder();

                    // Now for the view to display the builder
                    var builderView = new panels.view.builder({
                        model: builderModel,
                        config: builderConfig
                    });

                    // Set up the builder view
                    builderView
                        .render()
                        .attach({
                            container: container
                        })
                        .setDataField(field)
                        .attachToEditor()
                        .displayLiveEditor()
                    ;

                    // When the form is submitted, update the panels data
                    //form.submit(function () {
                    //    // Refresh the data
                    //    builderModel.refreshPanelsData();
                    //});

                    container.removeClass('cs-panels-loading');

                    // Trigger a global jQuery event after we've setup the builder view. Everything is accessible form there
                    $(document).trigger('panels_setup', builderView, window.panels);
                }

                // Setup new widgets when they're added in the standard widget interface
                $(document).on('widget-added', function (e, widget) {
                    $(widget).find('.cleversoft-page-builder-widget').soPanelsSetupBuilderWidget();
                });

                // Setup existing widgets on the page (for the widgets interface)
                //if (!$('body').hasClass('wp-customizer')) {
                $(function () {
                    $('.cleversoft-page-builder-widget').soPanelsSetupBuilderWidget();
                });
                //}
            });

        }, {
            "./collection/cells": 1,
            "./collection/history-entries": 2,
            "./collection/rows": 3,
            "./collection/widgets": 4,
            "./collection/child-widget": 35,
            "./dialog/builder": 5,
            "./dialog/history": 6,
            "./dialog/prebuilt": 7,
            "./dialog/tab-item-editor": 34,
            "./dialog/scrollto-item-editor": 44,
            "./dialog/textbox-item-editor": 42,
            "./dialog/innerrow-item-editor": 40,
            "./dialog/innerrow-layout-item-editor": 41,
            "./dialog/banner-item-editor": 43,
            "./dialog/row-item-editor": 45,
            "./dialog/row-layout-item-editor": 46,
            "./dialog/row": 8,
            "./dialog/widget": 9,
            "./dialog/child-widget": 39,
            "./dialog/widgets": 10,
            "./dialog/child-widgets": 38,
            "./helpers/clipboard": 11,
            "./helpers/page-scroll": 12,
            "./helpers/serialize": 13,
            "./helpers/utils": 14,
            "./jquery/setup-builder-widget": 15,
            "./model/builder": 17,
            "./model/cell": 18,
            "./model/history-entry": 19,
            "./model/row": 20,
            "./model/widget": 21,
            "./model/child-widget": 36,
            "./utils/menu": 22,
            "./view/builder": 23,
            "./view/cell": 24,
            "./view/dialog": 25,
            "./view/live-editor": 26,
            "./view/row": 27,
            "./view/styles": 28,
            "./view/widget": 29,
            "./view/child-widget": 37
        }],
        17: [function (require, module, exports) {
            module.exports = Backbone.Model.extend({
                layoutPosition: {
                    BEFORE: 'before',
                    AFTER: 'after',
                    REPLACE: 'replace'
                },

                rows: {},

                defaults: {
                    'data': {
                        'widgets': [],
                        'grids': [],
                        'grid_cells': []
                    }
                },

                initialize: function () {
                    // These are the main rows in the interface
                    this.set('rows', new panels.collection.rows());
                },

                /**
                 * Add a new row to this builder.
                 *
                 * @param attrs
                 * @param cells
                 * @param options
                 */
                addRow: function (attrs, cells, options) {
                    options = _.extend({
                        noAnimate: false
                    }, options);

                    var cellCollection = new panels.collection.cells(cells);

                    attrs = _.extend({
                        collection: this.get('rows'),
                        cells: cellCollection
                    }, attrs);

                    // Create the actual row
                    var row = new panels.model.row(attrs);
                    row.builder = this;

                    this.get('rows').add(row, options);

                    return row;
                },

                /**
                 * Load the panels data into the builder
                 *
                 * @param data Object the layout and widgets data to load.
                 * @param position string Where to place the new layout. Allowed options are 'before', 'after'. Anything else will
                 *                          cause the new layout to replace the old one.
                 */
                loadPanelsData: function (data, position) {
                    try {
                        if (position === this.layoutPosition.BEFORE) {
                            data = this.concatPanelsData(data, this.getPanelsData());
                        } else if (position === this.layoutPosition.AFTER) {
                            data = this.concatPanelsData(this.getPanelsData(), data);
                        }
                        // Start by destroying any rows that currently exist. This will in turn destroy cells, widgets and all the associated views
                        this.emptyRows();

                        // This will empty out the current rows and reload the builder data.
                        this.set('data', JSON.parse(JSON.stringify(data)), {silent: true});

                        var cit = 0;
                        var rows = [];

                        if (_.isUndefined(data.grid_cells)) {
                            this.trigger('load_panels_data');
                            return;
                        }

                        var gi;
                        for (var ci = 0; ci < data.grid_cells.length; ci++) {
                            gi = parseInt(data.grid_cells[ci].grid);
                            if (_.isUndefined(rows[gi])) {
                                rows[gi] = [];
                            }

                            rows[gi].push(data.grid_cells[ci]);
                        }

                        var builderModel = this;
                        _.each(rows, function (row, i) {
                            var rowAttrs = {};

                            if (!_.isUndefined(data.grids[i].style)) {
                                rowAttrs.style = data.grids[i].style;
                            }

                            if (!_.isUndefined(data.grids[i].ratio)) {
                                rowAttrs.ratio = data.grids[i].ratio;
                            }

                            if (!_.isUndefined(data.grids[i].fullpage)) {
                                rowAttrs.fullpage = data.grids[i].fullpage;
                            }

                            if (!_.isUndefined(data.grids[i].ratio_direction)) {
                                rowAttrs.ratio_direction = data.grids[i].ratio_direction
                            }

                            if (!_.isUndefined(data.grids[i].color_label)) {
                                rowAttrs.color_label = data.grids[i].color_label;
                            }

                            if (!_.isUndefined(data.grids[i].label)) {
                                rowAttrs.label = data.grids[i].label;
                            }
                            // This will create and add the row model and its cells
                            builderModel.addRow(rowAttrs, row, {noAnimate: true});
                        });


                        if (_.isUndefined(data.widgets)) {
                            return;
                        }

                        // Add the widgets
                        _.each(data.widgets, function (widgetData) {
                            var panels_info = null;
                            if (!_.isUndefined(widgetData.panels_info)) {
                                panels_info = widgetData.panels_info;
                                delete widgetData.panels_info;
                            } else {
                                panels_info = widgetData.info;
                                delete widgetData.info;
                            }
                            var row = builderModel.get('rows').at(parseInt(panels_info.grid));
                            var cell = row.get('cells').at(parseInt(panels_info.cell));
                            var newWidget = new panels.model.widget({
                                class: panels_info.class,
                                type : panels_info.type,
                                default_items: !_.isUndefined(panels_info.default_items) ? panels_info.default_items : (!_.isUndefined(panelsOptions.widgets[panels_info.type].default_items)?panelsOptions.widgets[panels_info.type].default_items:false),
                                layouts: !_.isUndefined(widgetData.layouts) ? widgetData.layouts : false,
                                has_child: !_.isUndefined(panels_info.has_child) ? panels_info.has_child : false,
                                panelsItems: !_.isUndefined(panels_info.panelsItems) ? panels_info.panelsItems : false,
                                items : new panels.collection.childWidgets() ,
                                widget_id: !_.isUndefined(panels_info.widget_id) ? panels_info.widget_id : panels.helpers.utils.generateUUID(),
                                values: widgetData
                            });

                            switch (panels_info.has_child) {
                                case 'tab':
                                    newWidget.set('totalTabpanels', !_.isUndefined(panels_info.totalTabpanels) ? panels_info.totalTabpanels : panels_info.default_items.length);
                                    break;
                                case 'scrollto':
                                    newWidget.set('totalScrollTopanels', !_.isUndefined(panels_info.totalScrollTopanels) ? panels_info.totalScrollTopanels : panels_info.default_items.length);
                                    break;
                                case 'slider':
                                    newWidget.set('totalSliderpanels', !_.isUndefined(panels_info.totalSliderpanels) ? panels_info.totalSliderpanels : 1);
                                    break;
                                case 'textbox':
                                    newWidget.set('totalTextBoxpanels', !_.isUndefined(panels_info.totalTextBoxpanels) ? panels_info.totalTextBoxpanels : panels_info.default_items.length);
                                    break;
                                case 'banner':
                                    newWidget.set('totalBannerpanels', !_.isUndefined(panels_info.totalBannerpanels) ? panels_info.totalBannerpanels : 1);
                                    newWidget.set('layout', !_.isUndefined(widgetData.layout) ? widgetData.layout : 0);
                                    newWidget.set('layout_name', !_.isUndefined(widgetData.layout_name) ? widgetData.layout_name : 'blank');
                                    break;
                                case 'innerrow':
                                    newWidget.set('totalInnerrowpanels', !_.isUndefined(panels_info.totalInnerrowpanels) ? panels_info.totalInnerrowpanels : panels_info.default_items.length);
                                    break;
                                case 'row':
                                    newWidget.set('totalRowpanels', !_.isUndefined(panels_info.totalRowpanels) ? panels_info.totalRowpanels : panels_info.default_items.length);
                                    break;
                            }
                            if (!_.isUndefined(panels_info.style)) {
                                newWidget.set('style', panels_info.style);
                            }

                            if (!_.isUndefined(panels_info.read_only)) {
                                newWidget.set('read_only', panels_info.read_only);
                            }
                            if (!_.isUndefined(panels_info.widget_id)) {
                                newWidget.set('widget_id', panels_info.widget_id);
                            }
                            if (!_.isUndefined(panels_info.has_child)) {
                                newWidget.set('has_child', panels_info.has_child);
                            }
                            else {
                                newWidget.set('widget_id', panels.helpers.utils.generateUUID());
                            }

                            if (!_.isUndefined(panels_info.label)) {
                                newWidget.set('label', panels_info.label);
                            }
                            // add child widgets
                            if(panels_info.items.length > 0) {
                                builderModel.addChildWidgets(panels_info.items, newWidget, builderModel, false);
                            }

                            newWidget.cell = cell;
                            cell.get('widgets').add(newWidget, {noAnimate: true});

                        });
                        this.trigger('load_panels_data');
                    }
                    catch (err) {
                        console.log('Error loading data: ' + err.message);

                    }
                },
                //remove empty item from builder
                removeEmptyItem: function( $items) {
                    //$items = $items.filter(function($item) {return _.size($item) > 0} );
                    _.each($items, function($temps, $key) {
                        if(_.size($temps) > 0) {
                            if(jQuery.isArray($temps)) {
                                $temps = $temps.filter(function ($temp) {
                                    return _.size($temp) > 0
                                });
                            }
                            $items[$key] = $temps;
                        }
                    });
                    return $items;
                },
                /*
                 * add the child widgets
                 */
                addChildWidgets: function($items, widget , builderModel, loopChild, parentWIdx = false) {
                    var thisView = this;
                    $items = this.removeEmptyItem($items);
                    _.each($items, function (widgetData, key ) {
                        if(_.size(widgetData) === 0) {
                            return false;
                        } else {
                            if(!jQuery.isArray(widgetData)) widgetData = [widgetData];
                            _.each(widgetData, function(item, k) {
                                var temp = [];
                                var tempIt = item;
                                if(item.length === 0 || (_.isUndefined(item.panels_info) && _.isUndefined(item.info))) {
                                    return false;
                                }
                                var panels_info = null;
                                if (!_.isUndefined(item.panels_info)) {
                                    panels_info = item.panels_info;
                                    delete item.panels_info;
                                } else {
                                    panels_info = item.info;
                                    delete item.info;
                                }

                                var row = builderModel.get('rows').at(parseInt(panels_info.grid));
                                var cell = row.get('cells').at(parseInt(panels_info.cell));

                                var childWidget = new panels.model.childWidget({
                                    class: panels_info.class,
                                    type : panels_info.type,
                                    idx : loopChild ? widget.get('idx')  : key,
                                    // idx : key,
                                    has_button : !_.isUndefined(panels_info.has_button) ? panels_info.has_button : false,
                                    layout: !_.isUndefined(item.layout) ? item.layout : false,
                                    items : !_.isUndefined(item.layout) ? item.layout : false ,
                                    default_items: !_.isUndefined(panels_info.default_items) ? panels_info.default_items : (!_.isUndefined(panelsOptions.widgets[panels_info.type].default_items)?panelsOptions.widgets[panels_info.type].default_items:false),
                                    tabTitle : !_.isUndefined(item.tabTitle) ? item.tabTitle : false ,
                                    rowTitle : !_.isUndefined(item.rowTitle) ? item.rowTitle : false ,
                                    innerrowTitle : !_.isUndefined(item.innerrowTitle) ? item.innerrowTitle : false ,
                                    values: item,
                                    parentWIdx: !_.isUndefined(parentWIdx) ? parentWIdx : false ,
                                    widget_id: !_.isUndefined(panels_info.widget_id) ? panels_info.widget_id : panels.helpers.utils.generateUUID()
                                });

                                ///set is_inner
                                var widgetInfo = panelsOptions.widgets[panels_info.type];
                                if ( !_.isUndefined(widgetInfo.has_child)) {
                                    childWidget.is_inner = true;
                                    childWidget.set('has_child',widgetInfo.has_child);
                                }
                                //

                                if (!_.isUndefined(panels_info.style)) {
                                    childWidget.set('style', panels_info.style);
                                }

                                if (!_.isUndefined(panels_info.read_only)) {
                                    childWidget.set('read_only', panels_info.read_only);
                                }
                                if (!_.isUndefined(panels_info.widget_id)) {
                                    childWidget.set('child_widget_id', panels_info.widget_id);
                                }
                                if (!_.isUndefined(panels_info.has_child)) {
                                    childWidget.set('has_child', panels_info.has_child);
                                }
                                else {
                                    childWidget.set('child_widget_id', panels.helpers.utils.generateUUID());
                                }

                                if (!_.isUndefined(panels_info.label)) {
                                    childWidget.set('label', panels_info.label);
                                }

                                if (_.size(panels_info.items) > 0) {
                                    thisView.addChildWidgets(panels_info.items, childWidget , builderModel , true, panels_info.widget_id);
                                }

                                if (!_.isUndefined(item.layouts)) {
                                    childWidget.set('layouts', item.layouts);
                                }

                                if (!_.isUndefined(item.innerrow_layout)) {
                                    childWidget.set('innerrow_layout', item.innerrow_layout);
                                }

                                if (!_.isUndefined(item.totalInnerrowpanels)) {
                                    childWidget.set('totalInnerrowpanels', item.totalInnerrowpanels);
                                }

                                if (!_.isUndefined(item.row_layout)) {
                                    childWidget.set('row_layout', item.row_layout);
                                }

                                if (!_.isUndefined(item.totalRowpanels)) {
                                    childWidget.set('totalRowpanels', item.totalRowpanels);
                                }


                                if (!_.isUndefined(item.panelsItems)) {
                                    childWidget.set('panelsItems', item.panelsItems);
                                }

                                if (_.isUndefined(item.widget)) {
                                    childWidget.set('idx', key);
                                }
                                childWidget.widget = widget;

                                widget.get('items').add(childWidget, {noAnimate: true});

                            });
                        }
                    });
                },

                /**
                 * Concatenate the second set of Page Builder data to the first. There is some validation of input, but for the most
                 * part it's up to the caller to ensure the Page Builder data is well formed.
                 */
                concatPanelsData: function (panelsDataA, panelsDataB) {

                    if (_.isUndefined(panelsDataB) || _.isUndefined(panelsDataB.grids) || _.isEmpty(panelsDataB.grids) ||
                        _.isUndefined(panelsDataB.grid_cells) || _.isEmpty(panelsDataB.grid_cells)) {
                        return panelsDataA;
                    }

                    if (_.isUndefined(panelsDataA) || _.isUndefined(panelsDataA.grids) || _.isEmpty(panelsDataA.grids)) {
                        return panelsDataB;
                    }

                    var gridsBOffset = panelsDataA.grids.length;
                    var widgetsBOffset = !_.isUndefined(panelsDataA.widgets) ? panelsDataA.widgets.length : 0;
                    var newPanelsData = {grids: [], 'grid_cells': [], 'widgets': []};

                    // Concatenate grids (rows)
                    newPanelsData.grids = panelsDataA.grids.concat(panelsDataB.grids);

                    // Create a copy of panelsDataA grid_cells and widgets
                    if (!_.isUndefined(panelsDataA.grid_cells)) {
                        newPanelsData.grid_cells = panelsDataA.grid_cells.slice();
                    }
                    if (!_.isUndefined(panelsDataA.widgets)) {
                        newPanelsData.widgets = panelsDataA.widgets.slice();
                    }

                    var i;
                    // Concatenate grid cells (row columns)
                    for (i = 0; i < panelsDataB.grid_cells.length; i++) {
                        var gridCellB = panelsDataB.grid_cells[i];
                        gridCellB.grid = parseInt(gridCellB.grid) + gridsBOffset;
                        newPanelsData.grid_cells.push(gridCellB);
                    }

                    // Concatenate widgets
                    if (!_.isUndefined(panelsDataB.widgets)) {
                        for (i = 0; i < panelsDataB.widgets.length; i++) {
                            var widgetB = panelsDataB.widgets[i];
                            widgetB.panels_info.grid = parseInt(widgetB.panels_info.grid) + gridsBOffset;
                            widgetB.panels_info.id = parseInt(widgetB.panels_info.id) + widgetsBOffset;
                            newPanelsData.widgets.push(widgetB);
                        }
                    }

                    return newPanelsData;
                },
                /*
                 * convert child items into an array
                 */
                getChildItems: function(tempItems, panels_info , widget, ri, ci, childWidgetId, loopChild) {
                    var thisView = this;
                    var i = 0;
                    //var tempItemsInside = [];
					let tab_widgetids = [];
                    widget.get('items').each(function(item, it) {
                        var $items = [];
                        var item_panels_info = {
                            class: item.get('class'),
                            type: item.get('type') ? item.get('type') : item.get('class'),
                            raw: item.get('raw'),
                            grid: ri,
                            cell: ci,
                            has_button : !_.isUndefined(item.get('has_button')) ? item.get('has_button') : false,
                            items: item.get('items'),
                            // Strictly this should be an index
                            id: childWidgetId++,
                            widget_id: item.get('widget_id'),
                            child_widget_id: item.get('child_widget_id'),
                            style: item.get('style'),
                            label: item.get('label')

                        };
                        switch (item.get('has_child')) {
                            case 'innerrow':
                                item_panels_info.panelsItems = item.get('panelsItems');
                                item_panels_info.totalInnerrowpanels = item.get('totalInnerrowpanels');
                                break;
                            case 'row':
                                item_panels_info.panelsItems = item.get('panelsItems');
                                item_panels_info.totalRowpanels = item.get('totalRowpanels');
                                break;
                            case 'tab':
                                item_panels_info.totalTabpanels = item.get('totalTabpanels');
                                break;
                        }

                        if (_.isEmpty(item_panels_info.child_widget_id)) {
                            item_panels_info.child_widget_id = panels.helpers.utils.generateUUID();
                        }
                        var item_values = _.extend(_.clone(item.get('values')), {
                            panels_info: item_panels_info
                        });
                        switch (panels_info.has_child) {
                            case 'innerrow':
                                var innerrow_layout = !_.isUndefined(widget.get('innerrow_layout')) ? widget.get('innerrow_layout') : widget.get('values').innerrow_layout;
                                item_values.layout = widget.get('layouts')[innerrow_layout][item.get('idx')];

                                switch (item.get('has_child')) {
                                    case 'tab':
                                        item_values.is_inner = 1;
                                        if (item_values.panels_info.items.models.length) {
                                            jQuery.each(item_values.panels_info.items.models, function(idx, it) {
                                                item_values.panels_info.items.models[idx].get('values').tabTitle = thisView.get('tabTitle'+item.get('widget_id')+idx) ? thisView.get('tabTitle'+item.get('widget_id')+idx) : (item_values.panels_info.items.models[idx].tabTitle ? item_values.panels_info.items.models[idx].tabTitle  : (jQuery.mage.__('Tab') + ' ' + (parseInt(idx) + 1) + ' ' + jQuery.mage.__('Title')  ) ) ;
                                            });
                                        }
                                   default :
                                        item_values.tabTitle = thisView.get('tabTitle'+item.get('parent_widget_id')+item.get('idx')) ? thisView.get('tabTitle'+item.get('parent_widget_id')+item.get('idx')) : (item.get('tabTitle') ? item.get('tabTitle')  : (jQuery.mage.__('Tab') + ' ' + (parseInt(item.get('idx')) + 1) + ' ' + jQuery.mage.__('Title')  ) ) ;
                                        break;
                                }
                                break;
                            case 'row':
                                var row_layout = !_.isUndefined(widget.get('row_layout')) ? widget.get('row_layout') : widget.get('values').row_layout;
                                item_values.layout = widget.get('layouts')[row_layout][item.get('idx')];

                                switch (item.get('has_child')) {
                                    case 'tab':
										tab_widgetids.push(item.get('widget_id'));
                                        item_values.is_inner = 1;
                                        if (item_values.panels_info.items.models.length) {
                                            jQuery.each(item_values.panels_info.items.models, function(idx, it) {
                                                item_values.panels_info.items.models[idx].get('values').tabTitle = thisView.get('tabTitle'+item.get('widget_id')+idx) ? thisView.get('tabTitle'+item.get('widget_id')+idx) : (item_values.panels_info.items.models[idx].tabTitle ? item_values.panels_info.items.models[idx].tabTitle  : (jQuery.mage.__('Tab') + ' ' + (parseInt(idx) + 1) + ' ' + jQuery.mage.__('Title')  ) ) ;
                                            });
                                        }
                                    case 'innerrow':
                                        item_values.layouts = item.get('layouts');
                                        item_values.innerrow_layout = item.get('innerrow_layout');
                                        item_values.totalInnerrowpanels = item.get('totalInnerrowpanels');
                                        item_values.panelsItems = item.get('panelsItems');
                                        item_values.is_inner = 1;
                                        //break;
									default:
										if (typeof item.get('parent_widget_id') !== 'undefined' && typeof item.get('inner_lv') !== 'undefined' && item.get('inner_lv') != 1 && tab_widgetids.indexOf(item.get('parent_widget_id')) !== -1) {
											item_values.tabTitle = thisView.get('tabTitle'+item.get('parent_widget_id')+item.get('idx')) ? thisView.get('tabTitle'+item.get('parent_widget_id')+item.get('idx')) : (item.get('tabTitle') ? item.get('tabTitle')  : (jQuery.mage.__('Tab') + ' ' + (parseInt(item.get('idx')) + 1) + ' ' + jQuery.mage.__('Title')  ) ) ;
										}
                                }
                                break;
                            case 'banner':
                                var banner_layout = !_.isUndefined(widget.get('banner_layout')) ? widget.get('banner_layout') : widget.get('values').banner_layout;
                                item_values.layout = widget.get('layouts')[banner_layout];
                                break;
                            case 'tab':
                                item_values.tabTitle = thisView.get('tabTitle'+panels_info.widget_id+item.get('idx')) ? thisView.get('tabTitle'+panels_info.widget_id+item.get('idx')) : (item.get('tabTitle') ? item.get('tabTitle')  : (jQuery.mage.__('Tab') + ' ' + (parseInt(item.get('idx')) + 1) + ' ' + jQuery.mage.__('Title')  ) ) ;
                                switch (item.get('has_child')) {
                                    case 'innerrow':
                                        item_values.layouts = item.get('layouts');
                                        item_values.innerrow_layout = item.get('innerrow_layout');
                                        item_values.totalInnerrowpanels = item.get('totalInnerrowpanels');
                                        item_values.panelsItems = item.get('panelsItems');
                                        item_values.is_inner = 1;
                                        break;
                                }
                                break;
                            case 'row':
                                item_values.rowTitle = thisView.get('rowTitle'+panels_info.widget_id+item.get('idx')) ? thisView.get('rowTitle'+panels_info.widget_id+item.get('idx')) : (item.get('rowTitle') ? item.get('rowTitle')  : (jQuery.mage.__('Row') + ' ' + (parseInt(item.get('idx')) + 1) + ' ' + jQuery.mage.__('Title')  ) ) ;
                                break;
                        }

                        //loop for inner childs
                        if(_.size(item_panels_info.items) > 0 ) {
                            item_values.panels_info.items = thisView.getChildItems([], item_values.panels_info, item, ri, ci, 0, true);
                        }
                        if (!_.isUndefined(item_values.is_inner)) {
                            if (item_values.panels_info.items.length) {

                                _.each(item_values.panels_info.items, function (item, idx) {
                                    
                                    if (_.isArray(item) || _.isObject(item)) {
                                        if (_.isArray(item)) {
                                            item_values.panels_info.items[idx] = item;
                                        } else if(_.isObject(item)) {
                                            item_values.panels_info.items[idx] = [item];
                                        }
                                    } else {
                                        item_values.panels_info.items[idx] = [item];
                                    }
									
                                });
                            }
                        }
                        if (loopChild && typeof widget.get('has_child') !== 'undefined' && widget.get('has_child') == 'tab' && typeof item.get('tabTitle') !== 'undefined') {
                            item_values.tabTitle = item.get('tabTitle');
                        }
						$items.push(item_values);
                        if(!loopChild) {
                            if (!_.isUndefined(item.get('parent_widget_id')) && item.get('inner_lv') != 1) {
                                var hasTempItems = false;
                                for (var key in tempItems) {
                                    if (jQuery.isEmptyObject(tempItems[key]) == false && typeof (tempItems[key]) == "object") {
                                        hasTempItems = true;
										break;
                                    }
                                }
                                if (hasTempItems == true) {
                                    var parentWIdx = item.get('parent_widget_id');
                                    tempItems.each(function(it) {
                                        if (it.length) {
                                            it.each(function(i){
                                                if(!_.isUndefined(i) && !_.isUndefined(i.panels_info)) {
                                                    if(i.panels_info.widget_id == parentWIdx) {
                                                        if(!_.isUndefined(i.is_inner)) {
                                                            if (i.panels_info.items.length) {
                                                                if (i.panels_info.widget_id == item.get('parent_widget_id')) {
                                                                    for (var $idx = 0; $idx < i.panels_info.items.length; $idx++) {
                                                                        if ($idx == item.get('idx')) {
                                                                            if (!_.isUndefined(i.panels_info.items[$idx])) {
                                                                                i.panels_info.items[$idx].push(item_values);
                                                                            } else {
                                                                                i.panels_info.items.push([item_values]);
                                                                            }
                                                                        }
                                                                    }
                                                                } else {
                                                                    for (var $idx = 0; $idx < i.panels_info.items.length; $idx++) {
                                                                        if ($idx == item.get('idx')) {
                                                                            if (!_.isUndefined(i.panels_info.items[$idx])) {
                                                                                i.panels_info.items[$idx].push([item_values]);
                                                                            } else {
                                                                                i.panels_info.items.push([item_values]);
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            } else {
																let current_id = typeof item.get('idx') !== 'undefined' ? Number(item.get('idx')) : item.get('idx');
																let numElement = current_id + 1;
																if ((typeof i.panels_info.totalInnerrowpanels !== 'undefined' && Number(i.panels_info.totalInnerrowpanels) > current_id) || (typeof i.panels_info.totalTabpanels !== 'undefined' && Number(i.panels_info.totalTabpanels) > current_id)) {
																	numElement = typeof i.panels_info.totalInnerrowpanels !== 'undefined' ? Number(i.panels_info.totalInnerrowpanels) : (typeof i.panels_info.totalTabpanels !== 'undefined' ? Number(i.panels_info.totalTabpanels) : 0);
																}
																for (var $idx = 0; $idx < numElement; $idx++) {
																	if ($idx == current_id) {
																		i.panels_info.items.push([item_values]);
																	} else {
																		i.panels_info.items.push([]);
																	}
																}
																
                                                            }
                                                            
                                                        } else {
                                                            i.panels_info.items.push(item_values);
                                                        }
                                                    } else {
                                                        thisView.loopChildItemsData(i,parentWIdx,item_values, item);
                                                    }
                                                }
                                            });
                                        }
                                    });
                                } else {
                                    if(_.size(tempItems[item.get('idx')]) > 0) {
                                        tempItems[item.get('idx')] = tempItems[item.get('idx')].concat($items);
                                    } else {
                                        tempItems[item.get('idx')] = $items.concat(tempItems[item.get('idx')]);
                                    }
                                }
                            } else {
                                if(_.size(tempItems[item.get('idx')]) > 0) {
                                    tempItems[item.get('idx')] = tempItems[item.get('idx')].concat($items);
                                } else {
									jQuery.each(tempItems, function(index, it) {
                                        if (index == item.get('idx')) {
                                            tempItems[index] = $items.concat(tempItems[index]);
                                        } else {
                                            tempItems[index] = [].concat(tempItems[index]);
                                        }
                                    });
                                }
                            }
                        } else {
                            if(tempItems.length > 0) {
                                const idx = item.get('idx');
								let hasUpdate = false;
								let itemIsArray = false;
                                jQuery.each(tempItems, function(key, val) {
                                    if (_.isArray(val)) {
										if (key == idx) {
											if (_.size(tempItems[idx]) > 0) {
												tempItems[key] = tempItems[key].concat($items);
												hasUpdate = true;
											} else {
												tempItems[key] = $items.concat(tempItems[key]);
												hasUpdate = true;
											}
										}
										itemIsArray = true;
                                    }
                                });
								if (!hasUpdate) {
									if (itemIsArray) {
										tempItems = tempItems.concat([$items]);
									} else {
										tempItems = tempItems.concat($items);
									}
								}
								

                            } else {
								
                                const idx = item.get('idx');
                                const lengthArr = _.size(tempItems);
                                let total_innerrowpanels = 1;
                                let passCheck = false;
                                if ((typeof item.get('totalInnerrowpanels') === 'undefined' && widget.get('totalInnerrowpanels')) || (typeof item.get('totalTabpanels') === 'undefined' && widget.get('totalTabpanels'))) {
                                    total_innerrowpanels = typeof widget.get('totalInnerrowpanels') !== 'undefined' ? widget.get('totalInnerrowpanels') : (typeof widget.get('totalTabpanels') !== 'undefined' ? widget.get('totalTabpanels') : 0);
                                    passCheck = true;
                                }

                                if (total_innerrowpanels && total_innerrowpanels > lengthArr && idx < total_innerrowpanels &&  _.isArray(tempItems) && passCheck && typeof idx !== 'undefined') {
                                    tempItems = Array(total_innerrowpanels).fill([]);
                                    jQuery.each(tempItems, function(key, val) {
                                        if (key == idx) {
                                            tempItems[key] = $items.concat(tempItems[key]);                            
                                        } else {
                                            tempItems[key] = [].concat(tempItems[key]);
                                        }
                                    });
                                } else {
                                    tempItems = $items.concat(tempItems);
                                }
                                								
                                window.innerIdx = item.get('idx');
                            }
                        }
                        var skipParent = false;
                        if (tempItems.length) {
                            _.each(tempItems, function (it) {
                                if (!_.isUndefined(it) && it.length) {
                                    _.each(it, function (i) {
                                        if(!_.isUndefined(i) && !_.isUndefined(i.panels_info)) {
                                            if (i.panels_info.widget_id == window.innerParentWIdx && !_.isUndefined(window.innerItemValues)) {
                                                if(!_.isUndefined(i.panels_info.items[window.inner.get('idx')])) {
                                                    if (jQuery.inArray(window.innerItemValues, i.panels_info.items[window.inner.get('idx')]) < 0) {
                                                        i.panels_info.items[window.inner.get('idx')].push(window.innerItemValues);
                                                        skipParent = true;
                                                        return false;
                                                    }
                                                }
                                            }
                                        }
                                    });
                                }
                            });
                        }
                        if(skipParent)
                        {
                            return true;
                        }
                    });
                    for (var key in tempItems) {
                        if ((jQuery.isEmptyObject(tempItems[key]) == true && typeof (tempItems[key]) != "object") || (jQuery.isEmptyObject(tempItems[key]) == true && panels_info.has_child == "banner") || (!_.isUndefined(tempItems[key].panels_info) && tempItems[key].panels_info.class == null)) {
                            delete tempItems[key];
                        }
                    }
                    var newTempItems = [];
                    _.each(tempItems, function (item, it) {
                        if (jQuery.isEmptyObject(tempItems[it]) == false) {
                            newTempItems[i] = tempItems[it];
                            i++;
                        }
                    });
                    return newTempItems;
                },

                loopChildItemsData: function(i,parentWIdx,item_values, $item) {
                    $item = $item || {};
					let idx = null;
					var thisView = this;					
					if (!jQuery.isEmptyObject($item) && typeof $item.get('idx') !== 'undefined') {
						idx = Number($item.get('idx'));
					}
                    i.panels_info.items.each(function(ii){
                        if(!_.isUndefined(ii.panels_info)) {
                            if(ii.panels_info.widget_id == parentWIdx) {
								if (idx !== null && ((typeof ii.panels_info.totalInnerrowpanels !== 'undefined' && Number(ii.panels_info.totalInnerrowpanels) > idx) || (typeof ii.panels_info.totalTabpanels !== 'undefined' && Number(ii.panels_info.totalTabpanels) > idx))) {
									if (ii.panels_info.items.length) {
										for (var $idx = 0; $idx < ii.panels_info.items.length; $idx++) {
											if ($idx == idx) {
												if (!_.isUndefined(ii.panels_info.items[$idx])) {
													ii.panels_info.items[$idx].push(item_values);
												} else {
													ii.panels_info.items.push([item_values]);
												}
											}
										}
									} else {
										let numElement = typeof ii.panels_info.totalInnerrowpanels !== 'undefined' ? Number(ii.panels_info.totalInnerrowpanels) : (typeof ii.panels_info.totalTabpanels !== 'undefined' ? Number(ii.panels_info.totalTabpanels) : 0);
										for (var $idx = 0; $idx < numElement; $idx++) {
											if ($idx == idx) {
												ii.panels_info.items.push([item_values]);
											} else {
												ii.panels_info.items.push([]);
											}
										}
									}
								} else {
									ii.panels_info.items.push(item_values);
								}
                            } else {
                                thisView.loopChildItemsData(ii,parentWIdx,item_values, $item);
                            }
                        } else {
                            if (ii.length) {
                                ii.each(function(iii){
                                    if(!_.isUndefined(iii.panels_info)) {
                                        if(iii.panels_info.widget_id == parentWIdx) {
											if (idx !== null && ((typeof iii.panels_info.totalInnerrowpanels !== 'undefined' && Number(iii.panels_info.totalInnerrowpanels) > idx) || (typeof iii.panels_info.totalTabpanels !== 'undefined' && Number(iii.panels_info.totalTabpanels) > idx))) {
												if (iii.panels_info.items.length) {
													for (var $idx = 0; $idx < iii.panels_info.items.length; $idx++) {
														if ($idx == idx) {
															if (!_.isUndefined(iii.panels_info.items[$idx])) {
																iii.panels_info.items[$idx].push(item_values);
															} else {
																iii.panels_info.items.push([item_values]);
															}
														}
													}
												} else {
													let numElement = typeof iii.panels_info.totalInnerrowpanels !== 'undefined' ? Number(iii.panels_info.totalInnerrowpanels) : (typeof iii.panels_info.totalTabpanels !== 'undefined' ? Number(iii.panels_info.totalTabpanels) : 0);
													for (var $idx = 0; $idx < numElement; $idx++) {
														if ($idx == idx) {
															iii.panels_info.items.push([item_values]);
														} else {
															iii.panels_info.items.push([]);
														}
													}
												}
											} else {
												iii.panels_info.items.push(item_values);	
											}
                                        } else {
                                            thisView.loopChildItemsData(iii,parentWIdx,item_values, $item);
                                        }
                                    }
                                });
                            }
                        }
                    });
					
                },

                /**
                 * Convert the content of the builder into a object that represents the page builder data
                 */
                getPanelsData: function () {

                    var builder = this;

                    var data = {
                        'widgets': [],
                        'grids': [],
                        'grid_cells': []
                    };
                    var widgetId = 0;
                    var childWidgetId = 0;

                    this.get('rows').each(function (row, ri) {

                        row.get('cells').each(function (cell, ci) {

                            cell.get('widgets').each(function (widget, wi) {
                                // Add the data for the widget, including the panels_info field.
                                var panels_info = {
                                    class: widget.get('class'),
                                    has_child: !_.isUndefined(widget.get('has_child')) ? widget.get('has_child') : false,
                                    type: widget.get('type') ? widget.get('type') : widget.get('class'),
                                    raw: widget.get('raw'),
                                    grid: ri,
                                    cell: ci,
                                    // Strictly this should be an index
                                    panelsItems: !_.isUndefined(widget.get('panelsItems')) ? widget.get('panelsItems') : false,
                                    id: widgetId++,
                                    widget_id: widget.get('widget_id'),
                                    style: widget.get('style'),
                                    label: widget.get('label')
                                };
                                if(panels_info.has_child) {
                                    panels_info.default_items = !_.isUndefined(widget.get('default_items')) ? widget.get('default_items') : false;

                                    //
                                    switch (panels_info.has_child) {
                                        case 'innerrow':
                                            panels_info.totalInnerrowpanels =  !_.isUndefined(widget.get('totalInnerrowpanels')) ? widget.get('totalInnerrowpanels') : widget.get('default_items').length;
                                            var values = widget.get('values');
                                            values.layouts = !_.isUndefined(widget.get('layouts')) ? widget.get('layouts') : false;
                                            if(_.isEmpty(values.innerrow_layout)) {
                                                values.innerrow_layout =  widget.get('innerrow_layout') ? widget.get('innerrow_layout') : 0;
                                                widget.set('values', values);
                                            }
                                            panels_info.items = (widget.get('items').length > 0) ? (widget.get('items')) : ((widget.get('layouts')[values.innerrow_layout]) ? widget.get('layouts')[values.innerrow_layout] : [] );
                                            break;
                                        case 'row':
                                            panels_info.totalRowpanels =  !_.isUndefined(widget.get('totalRowpanels')) ? widget.get('totalRowpanels') : widget.get('default_items').length;
                                            var values = widget.get('values');
                                            values.layouts = !_.isUndefined(widget.get('layouts')) ? widget.get('layouts') : false;
                                            if(_.isEmpty(values.row_layout)) {
                                                values.row_layout =  widget.get('row_layout') ? widget.get('row_layout') : 0;
                                                widget.set('values', values);
                                            }
                                            panels_info.items = (widget.get('items').length > 0) ? (widget.get('items')) : ((widget.get('layouts')[values.row_layout]) ? widget.get('layouts')[values.row_layout] : [] );
                                            break;
                                        case 'tab':
                                            panels_info.items = (widget.get('items').length > 0) ? (widget.get('items')) : ((widget.get('default_items')) ? widget.get('default_items') : [] );
                                            panels_info.totalTabpanels =  !_.isUndefined(widget.get('totalTabpanels')) ? widget.get('totalTabpanels') : widget.get('default_items').length;
                                            break;
                                        case 'row':
                                        case 'scrollto':
                                            panels_info.items = (widget.get('items').length > 0) ? (widget.get('items')) : ((widget.get('default_items')) ? widget.get('default_items') : [] );
                                            panels_info.totalScrollTopanels =  !_.isUndefined(widget.get('totalScrollTopanels')) ? widget.get('totalScrollTopanels') : widget.get('default_items').length;
                                            break;
                                        case 'banner':
                                            panels_info.items = (widget.get('items').length > 0) ? (widget.get('items')) : [] ;
                                            //panels_info.layout = !_.isUndefined(widget.get('banner_layout')) ? widget.get('banner_layout') : 0;
                                            //panels_info.layout_name = !_.isUndefined(widget.get('layout_name')) ? widget.get('layout_name') : 'blank';
                                            panels_info.totalBannerpanels =  !_.isUndefined(widget.get('totalBannerpanels')) ? widget.get('totalBannerpanels') : 1;
                                            break;
                                        case 'slider':
                                            panels_info.items = panels_info.items = (widget.get('items').length > 0) ? (widget.get('items')) : [] ;
                                            panels_info.totalSliderpanels =  !_.isUndefined(widget.get('totalSliderpanels')) ? widget.get('totalSliderpanels') : 1;
                                            break;
                                        case 'textbox':
                                            panels_info.items = panels_info.items = (widget.get('items').length > 0) ? (widget.get('items')) : [] ;
                                            panels_info.totalTextBoxpanels =  !_.isUndefined(widget.get('totalTextBoxpanels')) ? widget.get('totalTextBoxpanels') : 1;
                                            break;
                                    }

                                }

                                
                                var tempItems = [];
                                switch (panels_info.has_child) {
                                    case 'innerrow':
                                        var child = panels_info.totalInnerrowpanels;
                                        break;
                                    case 'row':
                                        var child = panels_info.totalRowpanels;
                                        break;
                                    case 'tab':
                                        var child = panels_info.totalTabpanels;
                                        break;
                                    case 'scrollto':
                                        var child = panels_info.totalScrollTopanels;
                                        break;
                                    case 'banner':
                                        var child = panels_info.totalBannerpanels;
                                        break;
                                    case 'slider':
                                        var child = panels_info.totalSliderpanels;
                                        break;
                                    case 'textbox':
                                        var child = panels_info.totalTextBoxpanels;
                                        break;
                                }

                                if(widget.get('default_items') && widget.get('items').length > 0 ) {
                                    if(child) {
                                        var n = child;
                                    }else if (panels_info.items.length < child) {
                                        var n = child;
                                    } else {
                                        var n = panels_info.items.length;
                                    }
                                    tempItems = Array(n).fill({});
                                    //set layout for innerrow element
                                } else {
                                    tempItems = Array(child).fill({});
                                }

                                if (_.isEmpty(panels_info.widget_id)) {
                                    panels_info.widget_id = panels.helpers.utils.generateUUID();
                                }
                                if (widget.get('items')) {
                                    tempItems = builder.getChildItems(tempItems, panels_info , widget, ri, ci, childWidgetId, false);
                                }else {

                                }
                                
                                //set defaulf items
                                if(panels_info.has_child ==='innerrow') {
                                    var tempInnerrow = tempItems.map(function(num, idx) {
                                        if(_.isEmpty(num)) {
                                            if(widget.get('layouts')) {
                                                //item_panels_info.layout = widget.get('values').layouts[parseInt(widget.get('values').innerrow_layout)-1][item.get('idx')];
                                                var innerrow_layout = !_.isUndefined(widget.get('innerrow_layout')) ? widget.get('innerrow_layout') : widget.get('values').innerrow_layout;
                                                if(!_.isUndefined(widget.get('layouts')[innerrow_layout][idx])) {
                                                    return [widget.get('layouts')[innerrow_layout][idx]];
                                                } else {
                                                    if (!_.isUndefined(panels_info['panelsItems'][idx])) {
                                                        return [panels_info['panelsItems'][idx]];
                                                    }
                                                }

                                            } else {
                                                return [panels_info.default_items[idx]];
                                                
                                            }

                                        } else {
                                            return num;
                                        }
                                    });
                                    tempItems = tempInnerrow;
                                    if(tempItems.length > 0) tempItems = builder.removeEmptyItem(tempItems);
                                } else if (panels_info.has_child ==='row') {
                                    var tempRow = tempItems.map(function(num, idx) {
                                        if(_.isEmpty(num)) {
                                            if(widget.get('layouts')) {
                                                var row_layout = !_.isUndefined(widget.get('row_layout')) ? widget.get('row_layout') : widget.get('values').row_layout;
                                                if(!_.isUndefined(widget.get('layouts')[row_layout][idx])) {
                                                    return [widget.get('layouts')[row_layout][idx]];
                                                } else {
                                                    if (!_.isUndefined(panels_info['panelsItems'][idx])) {
                                                        return [panels_info['panelsItems'][idx]];
                                                    }
                                                }

                                                //item_panels_info.layout
                                            } else {
                                                return [panels_info.default_items[idx]];                                    
                                            }

                                        } else {
                                            return num;
                                        }
                                    });
                                    tempItems = tempRow;
                                    if(tempItems.length > 0) tempItems = builder.removeEmptyItem(tempItems);
                                } else {
                                    tempItems = builder.removeEmptyItem(tempItems);
                                }

                                ///put panel info items
                                panels_info.items = tempItems;
                                ///put all together
                                var values = _.extend(_.clone(widget.get('values')), {
                                    panels_info: panels_info
                                });
                                if(panels_info.has_child === 'banner') {
                                    values.layout = !_.isUndefined(widget.get('banner_layout')) ? widget.get('banner_layout') : 0;
                                    values.layout_name = !_.isUndefined(widget.get('layout_name')) ? widget.get('layout_name') : 'blank';
                                }
                                data.widgets.push(values);
                            });

                            // Add the cell info
                            data.grid_cells.push({
                                grid: ri,
                                index: ci,
                                weight: cell.get('weight'),
                                style: cell.get('style')
                            });

                        });

                        data.grids.push({
                            cells: row.get('cells').length,
                            style: row.get('style'),
                            ratio: row.get('ratio'),
                            fullpage: row.get('fullpage'),
                            ratio_direction: row.get('ratio_direction'),
                            color_label: row.get('color_label'),
                            label: row.get('label')
                        });

                    });
                    return data;
                },

                /**
                 * This will check all the current entries and refresh the panels data
                 */
                refreshPanelsData: function (args) {
                    args = _.extend({
                        silent: false
                    }, args);

                    var oldData = this.get('data');
                    var newData = this.getPanelsData();
                    this.set('data', newData, {silent: true});

                    if (!args.silent && JSON.stringify(newData) !== JSON.stringify(oldData)) {
                        // The default change event doesn't trigger on deep changes, so we'll trigger our own
                        this.trigger('change');
                        this.trigger('change:data');
                        this.trigger('refresh_panels_data', newData, args);
                    }
                },

                /**
                 * Empty all the rows and the cells/widgets they contain.
                 */
                emptyRows: function () {
                    _.invoke(this.get('rows').toArray(), 'destroy');
                    this.get('rows').reset();

                    return this;
                },

                isValidLayoutPosition: function (position) {
                    return position === this.layoutPosition.BEFORE ||
                        position === this.layoutPosition.AFTER ||
                        position === this.layoutPosition.REPLACE;
                },

                /**
                 * Convert HTML into Panels Data
                 * @param html
                 */
                getPanelsDataFromHtml: function (html, editorClass) {
                    var thisModel = this;
                    var $html = jQuery('<div id="wrapper">' + html + '</div>');

                    if ($html.find('.panel-layout .panel-grid').length) {
                        // This looks like Page Builder html, lets try parse it
                        var panels_data = {
                            grids: [],
                            grid_cells: [],
                            widgets: []
                        };

                        // The Regex object that'll match CleverSoft widgets
                        var re = new RegExp(panelsOptions.cleversoftWidgetRegex, "i");
                        var decodeEntities = (function () {
                            // this prevents any overhead from creating the object each time
                            var element = document.createElement('div');

                            function decodeHTMLEntities(str) {
                                if (str && typeof str === 'string') {
                                    // strip script/html tags
                                    str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
                                    str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
                                    element.innerHTML = str;
                                    str = element.textContent;
                                    element.textContent = '';
                                }

                                return str;
                            }

                            return decodeHTMLEntities;
                        })();

                        // Remove all wrapping divs from a widget to get its html
                        var getTextWidgetContents = function ($el) {
                            var $divs = $el.find('div');
                            if (!$divs.length) {
                                return $el.html();
                            }

                            var i;
                            for (i = 0; i < $divs.length - 1; i++) {
                                if (jQuery.trim($divs.eq(i).text()) != jQuery.trim($divs.eq(i + 1).text())) {
                                    break;
                                }
                            }

                            var title = $divs.eq(i).find('.widget-title:header'),
                                titleText = '';

                            if (title.length) {
                                titleText = title.html();
                                title.remove();
                            }

                            return {
                                title: titleText,
                                text: $divs.eq(i).html()
                            };
                        };

                        var $layout = $html.find('.panel-layout').eq(0);
                        var filterNestedLayout = function (i, el) {
                            return jQuery(el).closest('.panel-layout').is($layout);
                        };

                        $html.find('> .panel-layout > .panel-grid').filter(filterNestedLayout).each(function (ri, el) {
                            var $row = jQuery(el),
                                $cells = $row.find('.panel-grid-cell').filter(filterNestedLayout);

                            panels_data.grids.push({
                                cells: $cells.length,
                                style: $row.data('style'),
                                ratio: $row.data('ratio'),
                                fullpage: $row.data('fullpage'),
                                ratio_direction: $row.data('ratio-direction'),
                                color_label: $row.data('color-label'),
                                label: $row.data('label')
                            });

                            $cells.each(function (ci, el) {
                                var $cell = jQuery(el),
                                    $widgets = $cell.find('.cs-panel').filter(filterNestedLayout);

                                panels_data.grid_cells.push({
                                    grid: ri,
                                    weight: !_.isUndefined($cell.data('weight')) ? parseFloat($cell.data('weight')) : 1,
                                    style: $cell.data('style')
                                });

                                $widgets.each(function (wi, el) {
                                    var $widget = jQuery(el),
                                        widgetContent = $widget.find('.panel-widget-style').length ? $widget.find('.panel-widget-style').html() : $widget.html(),
                                        panels_info = {
                                            grid: ri,
                                            cell: ci,
                                            style: $widget.data('style'),
                                            raw: false,
                                            label: $widget.data('label')
                                        };

                                    widgetContent = widgetContent.trim();

                                    // Check if this is a CleverSoft Widget
                                    var match = re.exec(widgetContent);
                                    if (!_.isNull(match) && widgetContent.replace(re, '').trim() === '') {
                                        try {
                                            var classMatch = /class="(.*?)"/.exec(match[3]),
                                                dataInput = jQuery(match[5]),
                                                data = JSON.parse(decodeEntities(dataInput.val())),
                                                newWidget = data.instance;

                                            panels_info.class = classMatch[1].replace(/\\\\+/g, '\\');
                                            panels_info.raw = false;

                                            newWidget.panels_info = panels_info;
                                            panels_data.widgets.push(newWidget);
                                        }
                                        catch (err) {
                                            // There was a problem, so treat this as a standard editor widget
                                            panels_info.class = editorClass;
                                            panels_data.widgets.push(_.extend(getTextWidgetContents($widget), {
                                                filter: "1",
                                                type: "visual",
                                                panels_info: panels_info
                                            }));
                                        }

                                        // Continue
                                        return true;
                                    }
                                    else if (widgetContent.indexOf('panel-layout') !== -1) {
                                        // Check if this is a layout widget
                                        var $widgetContent = jQuery('<div>' + widgetContent + '</div>');
                                        if ($widgetContent.find('.panel-layout .panel-grid').length) {
                                            // This is a standard editor class widget
                                            panels_info.class = 'CleverSoft_Panels_Widgets_Layout';
                                            panels_data.widgets.push({
                                                panels_data: thisModel.getPanelsDataFromHtml(widgetContent, editorClass),
                                                panels_info: panels_info
                                            });

                                            // continue
                                            return true;
                                        }
                                    }

                                    // This is a standard editor class widget
                                    panels_info.class = editorClass;
                                    panels_data.widgets.push(_.extend(getTextWidgetContents($widget), {
                                        filter: "1",
                                        type: "visual",
                                        panels_info: panels_info
                                    }));
                                    return true;
                                });
                            });
                        });

                        // Remove all the Page Builder content
                        $html.find('.panel-layout').remove();
                        $html.find('style[data-panels-style-for-post]').remove();

                        // If there's anything left, add it to an editor widget at the end of panels_data
                        if ($html.html().replace(/^\s+|\s+$/gm, '').length) {
                            panels_data.grids.push({
                                cells: 1,
                                style: {}
                            });
                            panels_data.grid_cells.push({
                                grid: panels_data.grids.length - 1,
                                weight: 1
                            });
                            panels_data.widgets.push({
                                filter: "1",
                                text: $html.html().replace(/^\s+|\s+$/gm, ''),
                                title: "",
                                type: "visual",
                                panels_info: {
                                    class: editorClass,
                                    type: editorClass,
                                    raw: false,
                                    grid: panels_data.grids.length - 1,
                                    cell: 0
                                }
                            });
                        }

                        return panels_data;
                    }
                    else {
                        // This is probably just old school post content
                        return {
                            grid_cells: [{grid: 0, weight: 1}],
                            grids: [{cells: 1}],
                            widgets: [
                                {
                                    filter: "1",
                                    text: html,
                                    title: "",
                                    type: "visual",
                                    panels_info: {
                                        class: editorClass,
                                        type: editorClass,
                                        raw: false,
                                        grid: 0,
                                        cell: 0
                                    }
                                }
                            ]
                        };
                    }
                }
            });

        }, {}],
        18: [function (require, module, exports) {
            module.exports = Backbone.Model.extend({
                /* A collection of widgets */
                widgets: {},
                items : null,
                /* The row this model belongs to */
                row: null,

                defaults: {
                    weight: 0,
                    style: {}
                },

                indexes: null,

                /**
                 * Set up the cell model
                 */
                initialize: function () {
                    this.set('widgets', new panels.collection.widgets());
                    if(!_.isUndefined(this.get('has_child'))) {
                        this.set('items', new panels.collection.childWidgets());
                    }
                    this.on('destroy', this.onDestroy, this);
                },

                /**
                 * Triggered when we destroy a cell
                 */
                onDestroy: function () {
                    // Destroy all the widgets
                    _.invoke(this.get('widgets').toArray(), 'destroy');
                    this.get('widgets').reset();
                },

                /**
                 * Create a clone of the cell, along with all its widgets
                 */
                clone: function (row, cloneOptions) {
                    if (_.isUndefined(row)) {
                        row = this.row;
                    }
                    cloneOptions = _.extend({cloneWidgets: true}, cloneOptions);

                    var clone = new this.constructor(this.attributes);
                    clone.set('collection', row.get('cells'), {silent: true});
                    clone.row = row;

                    if (cloneOptions.cloneWidgets) {
                        // Now we're going add all the widgets that belong to this, to the clone
                        this.get('widgets').each(function (widget) {
                            clone.get('widgets').add(widget.clone(clone, cloneOptions), {silent: true});
                        });
                    }

                    return clone;
                }

            });

        }, {}],
        19: [function (require, module, exports) {
            module.exports = Backbone.Model.extend({
                defaults: {
                    text: '',
                    data: '',
                    time: null,
                    count: 1
                }
            });

        }, {}],
        20: [function (require, module, exports) {
            module.exports = Backbone.Model.extend({
                /* The builder model */
                builder: null,

                defaults: {
                    style: {}
                },

                indexes: null,

                /**
                 * Initialize the row model
                 */
                initialize: function () {
                    if (_.isEmpty(this.get('cells'))) {
                        this.set('cells', new panels.collection.cells());
                    }
                    else {
                        // Make sure that the cells have this row set as their parent
                        this.get('cells').each(function (cell) {
                            cell.row = this;
                        }.bind(this));
                    }
                    this.on('destroy', this.onDestroy, this);
                },

                /**
                 * Add cells to the model row
                 *
                 * @param newCells the updated collection of cell models
                 */
                setCells: function (newCells) {
                    var currentCells = this.get('cells') || new panels.collection.cells();
                    var cellsToRemove = [];

                    currentCells.each(function (cell, i) {
                        var newCell = newCells.at(i);
                        if (newCell) {
                            cell.set('weight', newCell.get('weight'));
                        } else {
                            var newParentCell = currentCells.at(newCells.length - 1);

                            // First move all the widgets to the new cell
                            var widgetsToMove = cell.get('widgets').models.slice();
                            for (var j = 0; j < widgetsToMove.length; j++) {
                                widgetsToMove[j].moveToCell(newParentCell, {silent: false});
                            }

                            cellsToRemove.push(cell);
                        }
                    });

                    _.each(cellsToRemove, function (cell) {
                        currentCells.remove(cell);
                    });

                    if (newCells.length > currentCells.length) {
                        _.each(newCells.slice(currentCells.length, newCells.length), function (newCell) {
                            // TODO: make sure row and collection is set correctly when cell is created then we can just add new cells
                            newCell.set({collection: currentCells});
                            newCell.row = this;
                            currentCells.add(newCell);
                        }.bind(this));
                    }

                    // Rescale the cells when we add or remove
                    this.reweightCells();
                },

                /**
                 * Make sure that all the cell weights add up to 1
                 */
                reweightCells: function () {
                    var totalWeight = 0;
                    var cells = this.get('cells');
                    cells.each(function (cell) {
                        totalWeight += cell.get('weight');
                    });

                    cells.each(function (cell) {
                        cell.set('weight', cell.get('weight') / totalWeight);
                    });

                    // This is for the row view to hook into and resize
                    this.trigger('reweight_cells');
                },

                /**
                 * Triggered when the model is destroyed
                 */
                onDestroy: function () {
                    // Also destroy all the cells
                    _.invoke(this.get('cells').toArray(), 'destroy');
                    this.get('cells').reset();
                },

                /**
                 * Create a clone of the row, along with all its cells
                 *
                 * @param {panels.model.builder} builder The builder model to attach this to.
                 *
                 * @return {panels.model.row} The cloned row.
                 */
                clone: function (builder) {
                    if (_.isUndefined(builder)) {
                        builder = this.builder;
                    }

                    var clone = new this.constructor(this.attributes);
                    clone.set('collection', builder.get('rows'), {silent: true});
                    clone.builder = builder;

                    var cellClones = new panels.collection.cells();
                    this.get('cells').each(function (cell) {
                        cellClones.add(cell.clone(clone), {silent: true});
                    });

                    clone.set('cells', cellClones);

                    return clone;
                }
            });

        }, {}],
        21: [function (require, module, exports) {
            /**
             * Model for an instance of a widget
             */
            module.exports = Backbone.Model.extend({

                cell: null,

                defaults: {
                    // The PHP Class of the widget
                    class: null,

                    // Is this class missing? Missing widgets are a special case.
                    missing: false,

                    // The values of the widget
                    values: {},

                    // Have the current values been passed through the widgets update function
                    raw: false,

                    // Visual style fields
                    style: {},

                    read_only: false,

                    widget_id: ''
                },

                indexes: null,

                initialize: function () {
                    var widgetClass = this.get('class');
                    if (_.isUndefined(panelsOptions.widgets[widgetClass]) || !panelsOptions.widgets[widgetClass].installed) {
                        if(_.isUndefined(panelsOptions.widgets[this.get('type')])) this.set('missing', true);
                    }
                },

                /**
                 * @param field
                 * @returns {*}
                 */
                getWidgetField: function (field) {
                    if (_.isUndefined(panelsOptions.widgets[this.get('class')])) {
                        if (field === 'title' || field === 'description') {
                            if (!_.isUndefined(panelsOptions.widgets[this.get('type')]) ) {
                                return panelsOptions.widgets[this.get('type')][field];
                            } else return panelsOptions.loc.missing_widget[field];
                        } else {
                            return '';
                        }
                    } else if (this.has('label') && !_.isEmpty(this.get('label'))) {
                        // Use the label instead of the actual widget title
                        return this.get('label');
                    } else {
                        return panelsOptions.widgets[this.get('class')][field];
                    }
                },

                /**
                 * Move this widget model to a new cell. Called by the views.
                 *
                 * @param panels.model.cell newCell
                 * @param object options The options passed to the
                 *
                 * @return boolean Indicating if the widget was moved into a different cell
                 */
                moveToCell: function (newCell, options, at) {
                    options = _.extend({
                        silent: true
                    }, options);

                    this.cell = newCell;
                    this.collection.remove(this, options);
                    newCell.get('widgets').add(this, _.extend({
                        at: at
                    }, options));

                    // This should be used by views to reposition everything.
                    this.trigger('move_to_cell', newCell, at);

                    return this;
                },

                /**
                 * Trigger an event on the model that indicates a user wants to edit it
                 */
                triggerEdit: function () {
                    this.trigger('user_edit', this);
                },

                /**
                 * Trigger an event on the widget that indicates a user wants to duplicate it
                 */
                triggerDuplicate: function () {
                    this.trigger('user_duplicate', this);
                },

                /**
                 * This is basically a wrapper for set that checks if we need to trigger a change
                 */
                setValues: function (values) {
                    var hasChanged = false;
                    if (JSON.stringify(values) !== JSON.stringify(this.get('values'))) {
                        hasChanged = true;
                    }

                    this.set('values', values, {silent: true});

                    if (hasChanged) {
                        // We'll trigger our own change events.
                        // NB: Must include the model being changed (i.e. `this`) as a workaround for a bug in Backbone 1.2.3
                        this.trigger('change', this);
                        this.trigger('change:values');
                    }
                },

                /**
                 * Create a clone of this widget attached to the given cell.
                 *
                 * @param {panels.model.cell} cell The cell model we're attaching this widget clone to.
                 * @returns {panels.model.widget}
                 */
                clone: function (cell, options) {
                    if (_.isUndefined(cell)) {
                        cell = this.cell;
                    }

                    var clone = new this.constructor(this.attributes);

                    // Create a deep clone of the original values
                    var cloneValues = JSON.parse(JSON.stringify(this.get('values')));

                    // We want to exclude any fields that start with _ from the clone. Assuming these are internal.
                    var cleanClone = function (vals) {
                        _.each(vals, function (el, i) {
                            if (_.isString(i) && i[0] === '_') {
                                delete vals[i];
                            }
                            else if (_.isObject(vals[i])) {
                                cleanClone(vals[i]);
                            }
                        });

                        return vals;
                    };
                    cloneValues = cleanClone(cloneValues);

                    if (this.get('class') === "CleverSoft_Panels_Widgets_Layout") {
                        // Special case of this being a layout widget, it needs a new ID
                        cloneValues.builder_id = Math.random().toString(36).substr(2);
                    }

                    clone.set('widget_id', '');
                    clone.set('values', cloneValues, {silent: true});
                    clone.set('collection', cell.get('widgets'), {silent: true});
                    clone.cell = cell;

                    // This is used to force a form reload later on
                    clone.isDuplicate = true;

                    return clone;
                },

                /**
                 * Gets the value that makes most sense as the title.
                 */
                getTitle: function () {
                    var widgetData = panelsOptions.widgets[this.get('class')];

                    if (_.isUndefined(widgetData)) {
                        if(!_.isUndefined(panelsOptions.widgets[this.get('type')])) {
                            return panelsOptions.widgets[this.get('type')].description;
                        } else return this.get('type').replace(/_/g, ' ');
                    }
                    else if (!_.isUndefined(widgetData.panels_title)) {
                        // This means that the widget has told us which field it wants us to use as a title
                        var a  = 'a';
                        if (widgetData.panels_title === false) {
                            return panelsOptions.widgets[this.get('class')].description;
                        }
                    }

                    var values = this.get('values');

                    // Create a list of fields to check for a title
                    var titleFields = ['title', 'text'];

                    for (var k in values) {
                        if (values.hasOwnProperty(k)) {
                            titleFields.push(k);
                        }
                    }

                    titleFields = _.uniq(titleFields);

                    for (var i in titleFields) {
                        if (
                            !_.isUndefined(values[titleFields[i]]) &&
                            _.isString(values[titleFields[i]]) &&
                            values[titleFields[i]] !== '' &&
                            values[titleFields[i]] !== 'on' &&
                            titleFields[i][0] !== '_' && !jQuery.isNumeric(values[titleFields[i]])
                        ) {
                            var title = values[titleFields[i]];
                            title = title.replace(/<\/?[^>]+(>|$)/g, "");
                            var parts = title.split(" ");
                            parts = parts.slice(0, 20);
                            return parts.join(' ');
                        }
                    }

                    // If we still have nothing, then just return the widget description
                    return this.getWidgetField('description');
                }

            });

        }, {}],
        22: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = Backbone.View.extend({
                wrapperTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-context-menu').html())),
                sectionTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-context-menu-section').html())),

                contexts: [],
                active: false,

                events: {
                    'keyup .cs-search-wrapper input': 'searchKeyUp'
                },

                /**
                 * Intialize the context menu
                 */
                initialize: function () {
                    // this.listenContextMenu();
                    this.render();
                    this.attach();
                },

                /**
                 * Listen for the right click context menu
                 */
                listenContextMenu: function () {
                    var thisView = this;

                    $(window).on('contextmenu', function (e) {
                        if (thisView.active && !thisView.isOverEl(thisView.$el, e)) {
                            thisView.closeMenu();
                            thisView.active = false;
                            e.preventDefault();
                            return false;
                        }

                        if (thisView.active) {
                            // Lets not double up on the context menu
                            return true;
                        }

                        // Other components should listen to activate_context
                        thisView.active = false;
                        thisView.trigger('activate_context', e, thisView);

                        if (thisView.active) {
                            // We don't want the default event to happen.
                            e.preventDefault();

                            thisView.openMenu({
                                left: e.pageX,
                                top: e.pageY
                            });
                        }
                    });
                },

                render: function () {
                    this.setElement(this.wrapperTemplate());
                },

                attach: function () {
                    this.$el.appendTo('body');
                },

                /**
                 * Display the actual context menu.
                 *
                 * @param position
                 */
                openMenu: function (position) {
                    this.trigger('open_menu');

                    // Start listening for situations when we should close the menu
                    $(window).on('keyup', {menu: this}, this.keyboardListen);
                    $(window).on('click', {menu: this}, this.clickOutsideListen);

                    // Set the maximum height of the menu
                    this.$el.css('max-height', $(window).height() - 20);

                    // Correct the left position
                    if (position.left + this.$el.outerWidth() + 10 >= $(window).width()) {
                        position.left = $(window).width() - this.$el.outerWidth() - 10;
                    }
                    if (position.left <= 0) {
                        position.left = 10;
                    }

                    // Check top position
                    if (position.top + this.$el.outerHeight() - $(window).scrollTop() + 10 >= $(window).height()) {
                        position.top = $(window).height() + $(window).scrollTop() - this.$el.outerHeight() - 10;
                    }
                    if (position.left <= 0) {
                        position.left = 10;
                    }

                    // position the contextual menu
                    this.$el.css({
                        left: position.left + 1,
                        top: position.top + 1
                    }).show();
                    this.$('.cs-search-wrapper input').focus();
                },

                closeMenu: function () {
                    this.trigger('close_menu');

                    // Stop listening for situations when we should close the menu
                    $(window).off('keyup', this.keyboardListen);
                    $(window).off('click', this.clickOutsideListen);

                    this.active = false;
                    this.$el.empty().hide();
                },

                /**
                 * Keyboard events handler
                 */
                keyboardListen: function (e) {
                    var menu = e.data.menu;

                    switch (e.which) {
                        case 27:
                            menu.closeMenu();
                            break;
                    }
                },

                /**
                 * Listen for a click outside the menu to close it.
                 * @param e
                 */
                clickOutsideListen: function (e) {
                    var menu = e.data.menu;
                    if (e.which !== 3 && menu.$el.is(':visible') && !menu.isOverEl(menu.$el, e)) {
                        menu.closeMenu();
                    }
                },

                /**
                 * Add a new section to the contextual menu.
                 *
                 * @param settings
                 * @param items
                 * @param callback
                 */
                addSection: function (id, settings, items, callback) {
                    var thisView = this;
                    settings = _.extend({
                        display: 5,
                        defaultDisplay: false,
                        search: true,

                        // All the labels
                        sectionTitle: '',
                        searchPlaceholder: '',

                        // This is the key to be used in items for the title. Makes it easier to list objects
                        titleKey: 'title'
                    }, settings);

                    // Create the new section
                    var section = $(this.sectionTemplate({
                        settings: settings,
                        items: items
                    })).attr('id', 'panels-menu-section-' + id);
                    this.$el.append(section);

                    section.find('.cs-item:not(.cs-confirm)').click(function () {
                        var $$ = $(this);
                        callback($$.data('key'));
                        thisView.closeMenu();
                    });

                    section.find('.cs-item.cs-confirm').click(function () {
                        var $$ = $(this);

                        if ($$.hasClass('cs-confirming')) {
                            callback($$.data('key'));
                            thisView.closeMenu();
                            return;
                        }

                        $$
                            .data('original-text', $$.html())
                            .addClass('cs-confirming')
                            .html('<span class="dashicons dashicons-yes"></span> ' + panelsOptions.loc.dropdown_confirm);

                        setTimeout(function () {
                            $$.removeClass('cs-confirming');
                            $$.html($$.data('original-text'));
                        }, 2500);
                    });

                    section.data('settings', settings).find('.cs-search-wrapper input').trigger('keyup');

                    this.active = true;
                },

                /**
                 * Check if a section exists in the current menu.
                 *
                 * @param id
                 * @returns {boolean}
                 */
                hasSection: function (id) {
                    return this.$el.find('#panels-menu-section-' + id).length > 0;
                },

                /**
                 * Handle searching inside a section.
                 *
                 * @param e
                 * @returns {boolean}
                 */
                searchKeyUp: function (e) {
                    var
                        $$ = $(e.currentTarget),
                        section = $$.closest('.cs-section'),
                        settings = section.data('settings');

                    if (e.which === 38 || e.which === 40) {
                        // First, lets check if this is an up, down or enter press
                        var
                            items = section.find('ul li:visible'),
                            activeItem = items.filter('.cs-active').eq(0);

                        if (activeItem.length) {
                            items.removeClass('cs-active');

                            var activeIndex = items.index(activeItem);

                            if (e.which === 38) {
                                if (activeIndex - 1 < 0) {
                                    activeItem = items.last();
                                } else {
                                    activeItem = items.eq(activeIndex - 1);
                                }
                            }
                            else if (e.which === 40) {
                                if (activeIndex + 1 >= items.length) {
                                    activeItem = items.first();
                                } else {
                                    activeItem = items.eq(activeIndex + 1);
                                }
                            }
                        }
                        else if (e.which === 38) {
                            activeItem = items.last();
                        }
                        else if (e.which === 40) {
                            activeItem = items.first();
                        }

                        activeItem.addClass('cs-active');
                        return false;
                    }
                    if (e.which === 13) {
                        if (section.find('ul li:visible').length === 1) {
                            // We'll treat a single visible item as active when enter is clicked
                            section.find('ul li:visible').trigger('click');
                            return false;
                        }
                        section.find('ul li.cs-active:visible').trigger('click');
                        return false;
                    }

                    if ($$.val() === '') {
                        // We'll display the defaultDisplay items
                        if (settings.defaultDisplay) {
                            section.find('.cs-item').hide();
                            for (var i = 0; i < settings.defaultDisplay.length; i++) {
                                section.find('.cs-item[data-key="' + settings.defaultDisplay[i] + '"]').show();
                            }
                        } else {
                            // We'll just display all the items
                            section.find('.cs-item').show();
                        }
                    } else {
                        section.find('.cs-item').hide().each(function () {
                            var item = $(this);
                            if (item.html().toLowerCase().indexOf($$.val().toLowerCase()) !== -1) {
                                item.show();
                            }
                        });
                    }

                    // Now, we'll only show the first settings.display visible items
                    section.find('.cs-item:visible:gt(' + (
                        settings.display - 1
                    ) + ')').hide();


                    if (section.find('.cs-item:visible').length === 0 && $$.val() !== '') {
                        section.find('.cs-no-results').show();
                    } else {
                        section.find('.cs-no-results').hide();
                    }
                },

                /**
                 * Check if the given mouse event is over the element
                 * @param el
                 * @param event
                 */
                isOverEl: function (el, event) {
                    var elPos = [
                        [el.offset().left, el.offset().top],
                        [el.offset().left + el.outerWidth(), el.offset().top + el.outerHeight()]
                    ];

                    // Return if this event is over the given element
                    return (
                        event.pageX >= elPos[0][0] && event.pageX <= elPos[1][0] &&
                        event.pageY >= elPos[0][1] && event.pageY <= elPos[1][1]
                    );
                }

            });

        }, {}],
        23: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = Backbone.View.extend({

                // Config options
                config: {},

                template: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder').html())),
                dialogs: {},
                rowsSortable: null,
                dataField: false,
                currentData: '',

                attachedToEditor: false,
                attachedVisible: false,
                liveEditor: undefined,
                menu: false,

                activeCell: null,
                activeWidget: null,

                events: {
                    'click .cs-tool-button.cs-widget-add': 'displayAddWidgetDialog',
                    // 'click .cs-tool-button.cs-row-add': 'displayAddRowDialog',
                    'click .cs-tool-button.cs-prebuilt-add': 'displayAddPrebuiltDialog',
                    'click .cs-tool-button.cs-history': 'displayHistoryDialog',
                    'click .cs-tool-button.cs-live-editor': 'displayLiveEditor'
                },

                /* A row collection */
                rows: null,
                /* A row collection */
                cells: null,

                /**
                 * Initialize the builder
                 */
                initialize: function (options) {
                    var builder = this;

                    this.config = _.extend({
                        loadLiveEditor: false,
                        builderSupports: {}
                    }, options.config);

                    // These are the actions that a user can perform in the builder
                    this.config.builderSupports = _.extend({
                        addRow: true,
                        editRow: true,
                        deleteRow: true,
                        moveRow: true,
                        addWidget: true,
                        editWidget: true,
                        deleteWidget: true,
                        moveWidget: true,
                        prebuilt: true,
                        tabItemEditor: true,
                        history: true,
                        liveEditor: true,
                        revertToEditor: true
                    }, this.config.builderSupports);

                    // Automatically load the live editor as soon as it's ready
                    if (options.config.loadLiveEditor) {
                        this.on('builder_live_editor_added', function () {
                            this.displayLiveEditor();
                        });
                    }

                    // Now lets create all the dialog boxes that the main builder interface uses
                    this.dialogs = {
                        widgets: new panels.dialog.widgets(),
                        childWidgets: new panels.dialog.childWidgets(),
                        row: new panels.dialog.row(),
                        prebuilt: new panels.dialog.prebuilt(),
                        tabItemEditor: new panels.dialog.tabItemEditor(),
                        rowItemEditor: new panels.dialog.rowItemEditor(),
                        scrolltoItemEditor: new panels.dialog.scrolltoItemEditor(),
                        textboxItemEditor: new panels.dialog.textboxItemEditor(),
                        innerrowItemEditor: new panels.dialog.innerrowItemEditor()
                    };

                    // Set the builder for each dialog and render it.
                    _.each(this.dialogs, function (p, i, d) {
                        d[i].setBuilder(builder);
                    });

                    this.dialogs.row.setRowDialogType('create');

                    // This handles a new row being added to the collection - we'll display it in the interface
                    this.model.get('rows').on('add', this.onAddRow, this);

                    // Reflow the entire builder when ever the
                    $(window).resize(function (e) {
                        if (e.target === window) {
                            builder.trigger('builder_resize');
                        }
                    });

                    // When the data changes in the model, store it in the field
                    this.model.on('change:data load_panels_data', this.storeModelData, this);

                    // Handle a content change
                    //this.on('content_change', this.handleContentChange, this);
                    this.on('display_builder', this.handleDisplayBuilder, this);
                    this.on('hide_builder', this.handleHideBuilder, this);
                    this.on('builder_rendered builder_resize', this.handleBuilderSizing, this);
                    this.model.on('change:data load_panels_data', this.toggleWelcomeDisplay, this);

                    this.on('display_builder', this.wrapEditorExpandAdjust, this);

                    // Create the context menu for this builder
                    this.menu = new panels.utils.menu({});
                    this.menu.on('activate_context', this.activateContextMenu, this);

                    if (this.config.loadOnAttach) {
                        this.on('builder_attached_to_editor', function () {
                            this.displayAttachedBuilder();
                        }, this);
                    }


                    return this;
                },

                /**
                 * Render the builder interface.
                 *
                 * @return {panels.view.builder}
                 */
                render: function () {
                    // this.$el.html( this.template() );
                    var a = this.template();
                    this.setElement(this.template());
                    this.$el
                        .attr('id', 'cleversoft-panels-builder-' + this.cid)
                        .addClass('cs-builder-container');

                    this.trigger('builder_rendered');

                    return this;
                },

                /**
                 * Attach the builder to the given container
                 *
                 * @param container
                 * @returns {panels.view.builder}
                 */
                attach: function (options) {

                    options = _.extend({
                        container: false,
                        dialog: false
                    }, options);

                    if (options.dialog) {
                        // We're going to add this to a dialog
                        this.dialog = new panels.dialog.builder();
                        this.dialog.builder = this;
                    } else {
                        // Attach this in the standard way
                        this.$el.appendTo(options.container);
                        this.metabox = options.container.closest('.postbox');
                        this.initSortable();
                        this.trigger('attached_to_container', options.container);
                    }

                    this.trigger('builder_attached');

                    // Add support for components we have

                    if (this.supports('liveEditor')) {
                        this.addLiveEditor();
                    }
                    if (this.supports('history')) {
                        this.addHistoryBrowser();
                    }

                    // Hide toolbar buttons we don't support
                    var toolbar = this.$('.cs-builder-toolbar');
                    var welcomeMessageContainer = this.$('.cs-panels-welcome-message');
                    var welcomeMessage = panelsOptions.loc.welcomeMessage;

                    var supportedItems = [];

                    if (!this.supports('addWidget')) {
                        toolbar.find('.cs-widget-add').hide();
                    } else {
                        supportedItems.push(welcomeMessage.addWidgetButton);
                    }
                    // if (!this.supports('addRow')) {
                    //     toolbar.find('.cs-row-add').hide();
                    // } else {
                    //     supportedItems.push(welcomeMessage.addRowButton);
                    // }
                    if (!this.supports('prebuilt')) {
                        toolbar.find('.cs-prebuilt-add').hide();
                    } else {
                        supportedItems.push(welcomeMessage.addPrebuiltButton);
                    }

                    var msg = '';
                    if (supportedItems.length === 3) {
                        msg = welcomeMessage.threeEnabled;
                    } else if (supportedItems.length === 2) {
                        msg = welcomeMessage.twoEnabled;
                    } else if (supportedItems.length === 1) {
                        msg = welcomeMessage.oneEnabled;
                    } else if (supportedItems.length === 0) {
                        msg = welcomeMessage.addingDisabled;
                    }

                    var resTemplate = _.template(panels.helpers.utils.processTemplate(msg));
                    var msgHTML = resTemplate({items: supportedItems}) + ' ' + welcomeMessage.docsMessage;
                    welcomeMessageContainer.find('.cs-message-wrapper').html(msgHTML);

                    return this;
                },

                /**
                 * This will move the Page Builder meta box into the editor if we're in the post/page edit interface.
                 *
                 * @returns {panels.view.builder}
                 */
                attachToEditor: function () {
                    if (this.config.editorType !== 'tinyMCE') {
                        return this;
                    }

                    this.attachedToEditor = true;
                    var metabox = this.metabox;
                    var thisView = this;


                    // Switch back to the standard editor
                    if (this.supports('revertToEditor')) {
                        metabox.find('.cs-switch-to-standard').click(function (e) {
                            e.preventDefault();

                            if (!confirm(panelsOptions.loc.confirm_stop_builder)) {
                                return;
                            }

                            // User is switching to the standard visual editor
                            thisView.addHistoryEntry('back_to_editor');
                            thisView.model.loadPanelsData(false);

                            // Switch back to the standard editor
                            $('#wp-content-wrap').show();
                            metabox.hide();

                            // Resize to trigger reflow of WordPress editor stuff
                            $(window).resize();

                            thisView.attachedVisible = false;
                            thisView.trigger('hide_builder');
                        }).show();
                    }

                    // Move the panels box into a tab of the content editor
                    metabox.insertAfter('#wp-content-wrap').hide().addClass('attached-to-editor');

                    // Switch to the Page Builder interface as soon as we load the page if there are widgets or the normal editor
                    // isn't supported.
                    var data = this.model.get('data');
                    if (!_.isEmpty(data.widgets) || !_.isEmpty(data.grids) || !this.supports('revertToEditor')) {
                        this.displayAttachedBuilder();
                    }

                    // We will also make this sticky if its attached to an editor.
                    var stickToolbar = function () {
                        var toolbar = thisView.$('.cs-builder-toolbar');

                        if (thisView.$el.hasClass('cs-display-narrow')) {
                            // In this case, we don't want to stick the toolbar.
                            toolbar.css({
                                top: 0,
                                left: 0,
                                width: '100%',
                                position: 'absolute'
                            });
                            thisView.$el.css('padding-top', toolbar.outerHeight());
                            return;
                        }

                        var newTop = $(window).scrollTop() - thisView.$el.offset().top;

                        if ($('#wpadminbar').css('position') === 'fixed') {
                            newTop += $('#wpadminbar').outerHeight();
                        }

                        var limits = {
                            top: 0,
                            bottom: thisView.$el.outerHeight() - toolbar.outerHeight() + 20
                        };

                        if (newTop > limits.top && newTop < limits.bottom) {
                            if (toolbar.css('position') !== 'fixed') {
                                // The toolbar needs to stick to the top, over the interface
                                toolbar.css({
                                    top: $('#wpadminbar').outerHeight(),
                                    left: thisView.$el.offset().left,
                                    width: thisView.$el.outerWidth(),
                                    position: 'fixed'
                                });
                            }
                        } else {
                            // The toolbar needs to be at the top or bottom of the interface
                            toolbar.css({
                                top: Math.min(Math.max(newTop, 0), thisView.$el.outerHeight() - toolbar.outerHeight() + 20),
                                left: 0,
                                width: '100%',
                                position: 'absolute'
                            });
                        }

                        thisView.$el.css('padding-top', toolbar.outerHeight());
                    };

                    this.on('builder_resize', stickToolbar, this);
                    $(document).scroll(stickToolbar);
                    stickToolbar();

                    this.trigger('builder_attached_to_editor');

                    return this;
                },

                /**
                 * Display the builder interface when attached to a WordPress editor
                 */
                displayAttachedBuilder: function () {
                    // Switch to the Page Builder interface

                    // Hide the standard content editor
                    $('#wp-content-wrap').hide();


                    $('#editor-expand-toggle').on('change.editor-expand', function () {
                        if (!$(this).prop('checked')) {
                            $('#wp-content-wrap').hide();
                        }
                    });

                    // Show page builder and the inside div
                    this.metabox.show().find('> .inside').show();

                    // Triggers full refresh
                    $(window).resize();
                    $(document).scroll();

                    // Make sure the word count is visible
                    this.attachedVisible = true;
                    this.trigger('display_builder');

                    return true;
                },

                /**
                 * Initialize the row sortables
                 */
                initSortable: function () {
                    if (!this.supports('moveRow')) {
                        return this;
                    }

                    // Create the sortable for the rows
                    var builderView = this;
                    var a = this.$('.cs-rows-container');
                    this.rowsSortable = this.$('.cs-rows-container').sortable({
                        appendTo: '#wpwrap',
                        items: '.cs-row-container',
                        handle: '.cs-row-move',
                        axis: 'y',
                        tolerance: 'pointer',
                        scroll: false,
                        stop: function (e, ui) {
                            builderView.addHistoryEntry('row_moved');

                            var $$ = $(ui.item),
                                row = $$.data('view');

                            builderView.model.get('rows').remove(row.model, {
                                'silent': true
                            });
                            builderView.model.get('rows').add(row.model, {
                                'silent': true,
                                'at': $$.index()
                            });

                            row.trigger('move', $$.index());

                            builderView.model.refreshPanelsData();
                        }
                    });

                    return this;
                },

                /**
                 * Refresh the row sortable
                 */
                refreshSortable: function () {
                    // Refresh the sortable to account for the new row
                    if (!_.isNull(this.rowsSortable)) {
                        this.rowsSortable.sortable('refresh');
                    }
                },

                /**
                 * Set the field that's used to store the data
                 * @param field
                 */
                setDataField: function (field, options) {
                    options = _.extend({
                        load: true
                    }, options);

                    this.dataField = field;
                    this.dataField.data('builder', this);

                    if (options.load && field.val() !== '') {
                        var data = this.dataField.val();
                        try {
                            data = JSON.parse(data);
                        }
                        catch (err) {
                            data = {};
                        }
                        this.model.loadPanelsData(data);
                        this.currentData = data;
                        this.toggleWelcomeDisplay();
                    }

                    return this;
                },

                /**
                 * Store the model data in the data html field set in this.setDataField.
                 */
                storeModelData: function () {
                    var data = JSON.stringify(this.model.get('data'));

                    if ($(this.dataField).val() !== data) {
                        // If the data is different, set it and trigger a content_change event
                        $(this.dataField).val(data);
                        $(this.dataField).trigger('change');
                        this.trigger('content_change');
                    }
                },

                /**
                 * HAndle the visual side of adding a new row to the builder.
                 *
                 * @param row
                 * @param collection
                 * @param options
                 */
                onAddRow: function (row, collection, options) {
                    options = _.extend({noAnimate: false}, options);
                    // Create a view for the row
                    var rowView = new panels.view.row({model: row});
                    rowView.builder = this;
                    rowView.render();

                    // Attach the row elements to this builder
                    if (_.isUndefined(options.at) || collection.length <= 1) {
                        // Insert this at the end of the widgets container
                        rowView.$el.appendTo(this.$('.cs-rows-container'));
                    } else {
                        // We need to insert this at a specific position
                        rowView.$el.insertAfter(
                            this.$('.cs-rows-container .cs-row-container').eq(options.at - 1)
                        );
                    }

                    if (options.noAnimate === false) {
                        rowView.visualCreate();
                    }

                    this.refreshSortable();
                    rowView.resize();
                },

                /**
                 * Display the dialog to add a new widget.
                 *
                 * @returns {boolean}
                 */
                displayAddWidgetDialog: function () {
                    this.dialogs.widgets.openDialog();
                },

                /**
                 * Display the dialog to add a new row.
                 */
                // displayAddRowDialog: function () {
                //     var row = new panels.model.row();
                //     var cells = new panels.collection.cells([{weight: 0.5}, {weight: 0.5}]);
                //     cells.each(function (cell) {
                //         cell.row = row;
                //     });
                //     row.set('cells', cells);
                //     row.builder = this.model;

                //     this.dialogs.row.setRowModel(row);
                //     this.dialogs.row.openDialog();
                // },

                /**
                 * Display the dialog to add prebuilt layouts.
                 *
                 * @returns {boolean}
                 */
                displayAddPrebuiltDialog: function () {
                    this.dialogs.prebuilt.openDialog();
                },
                /**
                 * Display the dialog to add Tab Item editor layouts.
                 *
                 * @returns {boolean}
                 */
                displayTabItemEditorDialog: function () {
                    //this.dialogs.widgets.updateDialog();
                    //this.dialogs.tabItemEditor.openDialog();
                },
                /**
                 * Display the dialog to add Tab Item editor layouts.
                 *
                 * @returns {boolean}
                 */
                displayAddChildWidgetDialog: function () {
                    this.dialogs.childWidgets.openDialog();
                },

                /**
                 * Display the history dialog.
                 *
                 * @returns {boolean}
                 */
                displayHistoryDialog: function () {
                    this.dialogs.history.openDialog();
                },

                /**
                 * Handle pasting a row into the builder.
                 */
                pasteRowHandler: function () {
                    var pastedModel = panels.helpers.clipboard.getModel('row-model');

                    if (!_.isEmpty(pastedModel) && pastedModel instanceof panels.model.row) {
                        this.addHistoryEntry('row_pasted');
                        pastedModel.builder = this.model;
                        this.model.get('rows').add(pastedModel, {
                            at: this.model.get('rows').indexOf(this.model) + 1
                        });
                        this.model.refreshPanelsData();
                    }
                },

                /**
                 * Get the model for the currently selected cell
                 */
                getActiveCell: function (options) {
                    options = _.extend({
                        createCell: true
                    }, options);

                    if (!this.model.get('rows').length) {
                        // There aren't any rows yet
                        if (options.createCell) {
                            // Create a row with a single cell
                            this.model.addRow({}, [{weight: 1}], {noAnimate: true});
                        } else {
                            return null;
                        }
                    }

                    // Make sure the active cell isn't empty, and it's in a row that exists
                    var activeCell = this.activeCell;
                    if (_.isEmpty(activeCell) || this.model.get('rows').indexOf(activeCell.model.row) === -1) {
                        return this.model.get('rows').last().get('cells').first();
                    } else {
                        return activeCell.model;
                    }
                },
                /**
                 * Get the model for the currently selected widget
                 */
                getActiveWidget: function (options) {
                    var cell = this.getActiveCell({createCell: true});
                    return cell.activeWidget;
                },

                /**
                 * Add a live editor to the builder
                 *
                 * @returns {panels.view.builder}
                 */
                addLiveEditor: function () {
                    if (_.isEmpty(this.config.liveEditorPreview)) {
                        return this;
                    }

                    // Create the live editor and set the builder to this.
                    this.liveEditor = new panels.view.liveEditor({
                        builder: this,
                        previewUrl: this.config.liveEditorPreview
                    });

                    // Display the live editor button in the toolbar
                    if (this.liveEditor.hasPreviewUrl()) {
                        this.$('.cs-builder-toolbar .cs-live-editor').show();
                    }

                    this.trigger('builder_live_editor_added');

                    return this;
                },

                /**
                 * Show the current live editor
                 */
                displayLiveEditor: function () {
                    if (_.isUndefined(this.liveEditor)) {
                        return;
                    }

                    this.liveEditor.open();
                },

                /**
                 * Add the history browser.
                 *
                 * @return {panels.view.builder}
                 */
                addHistoryBrowser: function () {
                    if (_.isEmpty(this.config.liveEditorPreview)) {
                        return this;
                    }

                    this.dialogs.history = new panels.dialog.history();
                    this.dialogs.history.builder = this;
                    this.dialogs.history.entries.builder = this.model;

                    // Set the revert entry
                    this.dialogs.history.setRevertEntry(this.model);

                    // Display the live editor button in the toolbar
                    this.$('.cs-builder-toolbar .cs-history').show();
                },

                /**
                 * Add an entry.
                 *
                 * @param text
                 * @param data
                 */
                addHistoryEntry: function (text, data) {
                    if (_.isUndefined(data)) {
                        data = null;
                    }

                    if (!_.isUndefined(this.dialogs.history)) {
                        this.dialogs.history.entries.addEntry(text, data);
                    }
                },

                supports: function (thing) {

                    if (thing === 'rowAction') {
                        // Check if this supports any row action
                        return this.supports('addRow') || this.supports('editRow') || this.supports('deleteRow');
                    } else if (thing === 'widgetAction') {
                        // Check if this supports any widget action
                        return this.supports('addWidget') || this.supports('editWidget') || this.supports('deleteWidget');
                    }

                    return _.isUndefined(this.config.builderSupports[thing]) ? false : this.config.builderSupports[thing];
                },

                /**
                 * Handle a change of the content
                 */
                handleContentChange: function () {

                    // Make sure we actually need to copy content.
                    if (panelsOptions.copy_content && this.attachedToEditor && this.$el.is(':visible')) {

                        var panelsData = this.model.getPanelsData();
                        if (!_.isEmpty(panelsData.widgets)) {
                            // We're going to create a copy of page builder content into the post content
                            $.post(
                                panelsOptions.ajaxurl,
                                {
                                    action: 'so_panels_builder_content',
                                    panels_data: JSON.stringify(panelsData),
                                    page_id: this.config.postId
                                },
                                function (content) {
                                    if (content !== '') {
                                        //this.updateEditorContent(content);
                                    }
                                }.bind(this)
                            );
                        }
                    }
                },

                /**
                 * Update editor content with the given content.
                 *
                 * @param content
                 */
                updateEditorContent: function (content) {
                    // Switch back to the standard editor
                    if (this.config.editorType !== 'tinyMCE' || typeof tinyMCE === 'undefined' || _.isNull(tinyMCE.get("content"))) {
                        var $editor = $(this.config.editorId);
                        $editor.val(content).trigger('change').trigger('keyup');
                    } else {
                        var contentEd = tinyMCE.get("content");

                        contentEd.setContent(content);

                        contentEd.fire('change');
                        contentEd.fire('keyup');
                    }

                },



                /**
                 * Handle displaying the builder
                 */
                handleDisplayBuilder: function () {
                    if(window.pageContent) var content = JSON.parse(window.pageContent);
                    else var content = false;
                    var editor = typeof tinyMCE !== 'undefined' ? tinyMCE.get('content') : false;
                    var editorContent = ( editor && _.isFunction(editor.getContent) ) ? editor.getContent() : (content ?  content.content : '');

                    if (
                        (
                            _.isEmpty(this.model.get('data')) ||
                            ( _.isEmpty(this.model.get('data').widgets) && _.isEmpty(this.model.get('data').grids) )
                        ) &&
                        editorContent !== ''
                    ) {
                        var editorClass = panelsOptions.text_widget;
                        // There is a small chance a theme will have removed this, so check
                        if (_.isEmpty(editorClass)) {
                            return;
                        }

                        // Create the existing page content in a single widget
                        this.model.loadPanelsData(this.model.getPanelsDataFromHtml(editorContent, editorClass));
                        this.model.trigger('change');
                        this.model.trigger('change:data');
                    }

                    $('#post-status-info').addClass('for-cleversoft-panels');
                },

                handleHideBuilder: function () {
                    $('#post-status-info').show().removeClass('for-cleversoft-panels');
                },

                wrapEditorExpandAdjust: function () {
                    try {
                        var events = ( $.hasData(window) && $._data(window) ).events.scroll,
                            event;

                        for (var i = 0; i < events.length; i++) {
                            if (events[i].namespace === 'editor-expand') {
                                event = events[i];

                                // Wrap the call
                                $(window).unbind('scroll', event.handler);
                                $(window).bind('scroll', function (e) {
                                    if (!this.attachedVisible) {
                                        event.handler(e);
                                    }
                                }.bind(this));

                                break;
                            }
                        }
                    }
                    catch (e) {
                        // We tried, we failed
                        return;
                    }
                },

                /**
                 * Either add or remove the narrow class
                 * @returns {exports}
                 */
                handleBuilderSizing: function () {
                    var width = this.$el.width();

                    if (!width) {
                        return this;
                    }

                    if (width < 480) {
                        this.$el.addClass('cs-display-narrow');
                    } else {
                        this.$el.removeClass('cs-display-narrow');
                    }

                    return this;
                },

                /**
                 * Set the parent dialog for all the dialogs in this builder.
                 *
                 * @param text
                 * @param dialog
                 */
                setDialogParents: function (text, dialog) {
                    _.each(this.dialogs, function (p, i, d) {
                        d[i].setParent(text, dialog);
                    });

                    // For any future dialogs
                    this.on('add_dialog', function (newDialog) {
                        newDialog.setParent(text, dialog);
                    }, this);
                },

                /**
                 * This shows or hides the welcome display depending on whether there are any rows in the collection.
                 */
                toggleWelcomeDisplay: function () {
                    if (!this.model.get('rows').isEmpty()) {
                        this.$('.cs-panels-welcome-message').hide();
                    } else {
                        this.$('.cs-panels-welcome-message').show();
                    }
                },

                /**
                 * Activate the contextual menu
                 * @param e
                 * @param menu
                 */
                activateContextMenu: function (e, menu) {
                    var builder = this;

                    // Of all the visible builders, find the topmost
                    var topmostBuilder = $('.cleversoft-panels-builder:visible')
                        .sort(function (a, b) {
                            return $(a).zIndex() > $(b).zIndex() ? 1 : -1;
                        })
                        .last();

                    var topmostDialog = $('.cs-panels-dialog-wrapper:visible')
                        .sort(function (a, b) {
                            return $(a).zIndex() > $(b).zIndex() ? 1 : -1;
                        })
                        .last();

                    var closestDialog = builder.$el.closest('.cs-panels-dialog-wrapper');

                    // Only run this if its element is the topmost builder, in the topmost dialog
                    if (
                        builder.$el.is(topmostBuilder) &&
                        (
                            topmostDialog.length === 0 ||
                            topmostDialog.is(closestDialog)
                        )
                    ) {
                        // Get the element we're currently hovering over
                        var over = $([])
                            .add(builder.$('.cs-panels-welcome-message:visible'))
                            .add(builder.$('.cs-rows-container > .cs-row-container'))
                            .add(builder.$('.cs-cells > .cell'))
                            .add(builder.$('.cell-wrapper > .cs-widget'))
                            .filter(function (i) {
                                return menu.isOverEl($(this), e);
                            });

                        var activeView = over.last().data('view');
                        if (activeView !== undefined && activeView.buildContextualMenu !== undefined) {
                            // We'll pass this to the current active view so it can popular the contextual menu
                            activeView.buildContextualMenu(e, menu);
                        }
                        else if (over.last().hasClass('cs-panels-welcome-message')) {
                            // The user opened the contextual menu on the welcome message
                            this.buildContextualMenu(e, menu);
                        }
                    }
                },

                /**
                 * Build the contextual menu for the main builder - before any content has been added.
                 */
                buildContextualMenu: function (e, menu) {
                    var actions = {};

                    if (this.supports('addRow')) {
                        actions.add_row = {title: panelsOptions.loc.contextual.add_row};
                    }

                    if (panels.helpers.clipboard.canCopyPaste()) {
                        if (panels.helpers.clipboard.isModel('row-model') && this.supports('addRow')) {
                            actions.paste_row = {title: panelsOptions.loc.contextual.row_paste};
                        }
                    }

                    if (!_.isEmpty(actions)) {
                        menu.addSection(
                            'builder-actions',
                            {
                                sectionTitle: panelsOptions.loc.contextual.row_actions,
                                search: false
                            },
                            actions,
                            function (c) {
                                switch (c) {
                                    // case 'add_row':
                                    //     this.displayAddRowDialog();
                                    //     break;

                                    case 'paste_row':
                                        this.pasteRowHandler();
                                        break;
                                }
                            }.bind(this)
                        );
                    }
                }
            });

        }, {}],
        24: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = Backbone.View.extend({
                template: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-cell').html())),
                events: {
                    'click .cell-wrapper': 'handleCellClick'
                },

                /* The row view that this cell is a part of */
                row: null,
                widgetSortable: null,

                initialize: function () {
                    this.model.get('widgets').on('add', this.onAddWidget, this);
                },

                /**
                 * Render the actual cell
                 */
                render: function () {
                    var templateArgs = {
                        weight: this.model.get('weight'),
                        totalWeight: this.row.model.get('cells').totalWeight()
                    };

                    this.setElement(this.template(templateArgs));
                    this.$el.data('view', this);

                    // Now lets render any widgets that are currently in the row
                    var thisView = this;
                    this.model.get('widgets').each(function (widget) {
                        var widgetView = new panels.view.widget({model: widget});
                        widgetView.cell = thisView;
                        widgetView.render();

                        widgetView.$el.appendTo(thisView.$('.widgets-container'));
                    });

                    this.initSortable();
                    this.initResizable();

                    return this;
                },

                /**
                 * Initialize the widget sortable
                 */
                initSortable: function () {
                    if (!this.row.builder.supports('moveWidget')) {
                        return this;
                    }

                    var cellView = this;

                    // Go up the view hierarchy until we find the ID attribute
                    var builderID = cellView.row.builder.$el.attr('id');

                    // Create a widget sortable that's connected with all other cells
                    this.widgetSortable = this.$('.widgets-container').sortable({
                        placeholder: "cs-widget-sortable-highlight",
                        connectWith: '#' + builderID + ' .cs-cells .cell .widgets-container',
                        tolerance: 'pointer',
                        scroll: false,
                        over: function (e, ui) {
                            // This will make all the rows in the current builder resize
                            cellView.row.builder.trigger('widget_sortable_move');
                        },
                        stop: function (e, ui) {
                            cellView.row.builder.addHistoryEntry('widget_moved');

                            var $$ = $(ui.item),
                                widget = $$.data('view'),
                                targetCell = $$.closest('.cell').data('view');

                            // Move the model and the view to the new cell
                            widget.model.moveToCell(targetCell.model, {}, $$.index());
                            widget.cell = targetCell;

                            widget.cell.row.builder.model.refreshPanelsData();
                        },
                        helper: function (e, el) {
                            var helper = el.clone()
                                .css({
                                    'width': el.outerWidth(),
                                    'z-index': 10000,
                                    'position': 'fixed'
                                })
                                .addClass('widget-being-dragged').appendTo('body');

                            // Center the helper to the mouse cursor.
                            if (el.outerWidth() > 720) {
                                helper.animate({
                                    'margin-left': e.pageX - el.offset().left - (
                                        480 / 2
                                    ),
                                    'width': 480
                                }, 'fast');
                            }

                            return helper;
                        }
                    });

                    return this;
                },

                /**
                 * Refresh the widget sortable when a new widget is added
                 */
                refreshSortable: function () {
                    if (!_.isNull(this.widgetSortable)) {
                        this.widgetSortable.sortable('refresh');
                    }
                },

                /**
                 * This will make the cell resizble
                 */
                initResizable: function () {
                    if (!this.row.builder.supports('editRow')) {
                        return this;
                    }

                    // var neighbor = this.$el.previous().data('view');
                    var handle = this.$('.resize-handle').css('position', 'absolute');
                    var container = this.row.$el;
                    var cellView = this;

                    // The view of the cell to the left is stored when dragging starts.
                    var previousCell;

                    handle.draggable({
                        axis: 'x',
                        containment: container,
                        start: function (e, ui) {
                            // Set the containment to the cell parent
                            previousCell = cellView.$el.prev().data('view');
                            if (_.isUndefined(previousCell)) {
                                return;
                            }

                            // Create the clone for the current cell
                            var newCellClone = cellView.$el.clone().appendTo(ui.helper).css({
                                position: 'absolute',
                                top: '0',
                                width: cellView.$el.outerWidth(),
                                left: 5,
                                height: cellView.$el.outerHeight()
                            });
                            newCellClone.find('.resize-handle').remove();

                            // Create the clone for the previous cell
                            var prevCellClone = previousCell.$el.clone().appendTo(ui.helper).css({
                                position: 'absolute',
                                top: '0',
                                width: previousCell.$el.outerWidth(),
                                right: 5,
                                height: previousCell.$el.outerHeight()
                            });
                            prevCellClone.find('.resize-handle').remove();

                            $(this).data({
                                'newCellClone': newCellClone,
                                'prevCellClone': prevCellClone
                            });
                        },
                        drag: function (e, ui) {
                            // Calculate the new cell and previous cell widths as a percent
                            var containerWidth = cellView.row.$el.width() + 10;
                            var ncw = cellView.model.get('weight') - (
                                (
                                    ui.position.left + handle.outerWidth() / 2
                                ) / containerWidth
                            );
                            var pcw = previousCell.model.get('weight') + (
                                (
                                    ui.position.left + handle.outerWidth() / 2
                                ) / containerWidth
                            );

                            $(this).data('newCellClone').css('width', containerWidth * ncw)
                                .find('.preview-cell-weight').html(Math.round(ncw * 1000) / 10);

                            $(this).data('prevCellClone').css('width', containerWidth * pcw)
                                .find('.preview-cell-weight').html(Math.round(pcw * 1000) / 10);
                        },
                        stop: function (e, ui) {
                            // Remove the clones
                            $(this).data('newCellClone').remove();
                            $(this).data('prevCellClone').remove();

                            var containerWidth = cellView.row.$el.width() + 10;
                            var ncw = cellView.model.get('weight') - (
                                (
                                    ui.position.left + handle.outerWidth() / 2
                                ) / containerWidth
                            );
                            var pcw = previousCell.model.get('weight') + (
                                (
                                    ui.position.left + handle.outerWidth() / 2
                                ) / containerWidth
                            );

                            if (ncw > 0.02 && pcw > 0.02) {
                                cellView.row.builder.addHistoryEntry('cell_resized');
                                cellView.model.set('weight', ncw);
                                previousCell.model.set('weight', pcw);
                                cellView.row.resize();
                            }

                            ui.helper.css('left', -handle.outerWidth() / 2);

                            // Refresh the panels data
                            cellView.row.builder.model.refreshPanelsData();
                        }
                    });

                    return this;
                },

                /**
                 * This is triggered when ever a widget is added to the row collection.
                 *
                 * @param widget
                 */
                onAddWidget: function (widget, collection, options) {
                    options = _.extend({noAnimate: false}, options);

                    // Create the view for the widget
                    var view = new panels.view.widget({
                        model: widget
                    });
                    view.cell = this;
                    view.builder = this.row.builder;
                    if (_.isUndefined(widget.isDuplicate)) {
                        widget.isDuplicate = false;
                    }

                    // Render and load the form if this is a duplicate
                    view.render({
                        'loadForm': widget.isDuplicate
                    });

                    if (_.isUndefined(options.at) || collection.length <= 1) {
                        // Insert this at the end of the widgets container
                        view.$el.appendTo(this.$('.widgets-container'));
                    } else {
                        // We need to insert this at a specific position
                        view.$el.insertAfter(
                            this.$('.widgets-container .cs-widget').eq(options.at - 1)
                        );
                    }

                    if (options.noAnimate === false) {
                        // We need an animation
                        view.visualCreate();
                    }

                    this.refreshSortable();
                    this.refreshSortable();
                    this.row.resize();
                    this.activeAccordion();
                },

                activeAccordion: function () {
                    $( ".cs-widget.ui-draggable" ).accordion({
                        active:false,
                        collapsible: true,
                        beforeActivate:function(event, ui ){
                            if (!_.isUndefined(event.originalEvent) && !_.isUndefined(event.originalEvent.target)) {
                                var fromIcon = $(event.originalEvent.target).is('.ui-accordion-header > .ui-icon');
                                return fromIcon;
                            }
                            
                        }
                    });
                    if ($( ".clever-wrap-identify").hasClass("clever-wrap-element")) {
                        $(".clever-wrap-identify").closest('.cs-widget.ui-draggable').addClass("show-icon");
                    }
                },

                /**
                 * Handle this cell being clicked on
                 *
                 * @param e
                 * @returns {boolean}
                 */
                handleCellClick: function (e) {
                    // Remove all existing selected cell indication for this builder
                    this.row.builder.$el.find('.cs-cells .cell').removeClass('cell-selected');

                    if (this.row.builder.activeCell === this && !this.model.get('widgets').length) {
                        // This is a click on an empty cell
                        this.row.builder.activeCell = null;
                    }
                    else {
                        this.$el.addClass('cell-selected');
                        this.row.builder.activeCell = this;
                    }
                },

                /**
                 * Insert a widget from the clipboard
                 */
                pasteHandler: function () {
                    var pastedModel = panels.helpers.clipboard.getModel('widget-model');
                    if (!_.isEmpty(pastedModel) && pastedModel instanceof panels.model.widget) {
                        this.row.builder.addHistoryEntry('widget_pasted');
                        pastedModel.cell = this.model;
                        this.model.get('widgets').add(pastedModel);
                        this.row.builder.model.refreshPanelsData();
                    }
                },

                /**
                 * Build up the contextual menu for a cell
                 *
                 * @param e
                 * @param menu
                 */
                buildContextualMenu: function (e, menu) {
                    var thisView = this;

                    if (!menu.hasSection('add-widget-below')) {
                        menu.addSection(
                            'add-widget-cell',
                            {
                                sectionTitle: panelsOptions.loc.contextual.add_widget_cell,
                                searchPlaceholder: panelsOptions.loc.contextual.search_widgets,
                                defaultDisplay: panelsOptions.contextual.default_widgets
                            },
                            panelsOptions.widgets,
                            function (c) {
                                thisView.row.builder.addHistoryEntry('widget_added');
                                var widget = new panels.model.widget({
                                    class: c
                                });

                                // Add the widget to the cell model
                                widget.cell = thisView.model;
                                widget.cell.get('widgets').add(widget);

                                thisView.row.builder.model.refreshPanelsData();
                            }
                        );
                    }

                    var actions = {};
                    if (this.row.builder.supports('addWidget') && panels.helpers.clipboard.isModel('widget-model')) {
                        actions.paste = {title: panelsOptions.loc.contextual.cell_paste_widget};
                    }

                    if (!_.isEmpty(actions)) {
                        menu.addSection(
                            'cell-actions',
                            {
                                sectionTitle: panelsOptions.loc.contextual.cell_actions,
                                search: false
                            },
                            actions,
                            function (c) {
                                switch (c) {
                                    case 'paste':
                                        this.pasteHandler();
                                        break;
                                }

                                this.row.builder.model.refreshPanelsData();
                            }.bind(this)
                        );
                    }

                    // Add the contextual menu for the parent row
                    this.row.buildContextualMenu(e, menu);
                }
            });

        }, {}],
        25: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = Backbone.View.extend({
                dialogTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-dialog').html())),
                dialogTabTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-dialog-tab').html())),

                tabbed: false,
                rendered: false,
                builder: false,
                className: 'cs-panels-dialog-wrapper',
                dialogClass: '',
                dialogIcon: '',
                parentDialog: false,
                dialogOpen: false,
                editableLabel: false,

                events: {
                    'click .cs-update': 'updateDialog',
                    'click .cs-close': 'updateDialog',
                    'click .cs-nav.cs-previous': 'navToPrevious',
                    'click .cs-nav.cs-next': 'navToNext'
                },

                initialize: function () {
                    // The first time this dialog is opened, render it
                    this.once('open_dialog', this.render);
                    this.once('open_dialog', this.attach);
                    this.once('open_dialog', this.setDialogClass);

                    this.trigger('initialize_dialog', this);

                    if (!_.isUndefined(this.initializeDialog)) {
                        this.initializeDialog();
                    }
                },

                /**
                 * Returns the next dialog in the sequence. Should be overwritten by a child dialog.
                 * @returns {null}
                 */
                getNextDialog: function () {
                    return null;
                },

                /**
                 * Returns the previous dialog in this sequence. Should be overwritten by child dialog.
                 * @returns {null}
                 */
                getPrevDialog: function () {
                    return null;
                },

                /**
                 * Adds a dialog class to uniquely identify this dialog type
                 */
                setDialogClass: function () {
                    if (this.dialogClass !== '') {
                        this.$('.cs-panels-dialog').addClass(this.dialogClass);
                    }
                },

                /**
                 * Set the builder that controls this dialog.
                 * @param {panels.view.builder} builder
                 */
                setBuilder: function (builder) {
                    this.builder = builder;

                    // Trigger an add dialog event on the builder so it can modify the dialog in any way
                    builder.trigger('add_dialog', this, this.builder);

                    return this;
                },

                /**
                 * Attach the dialog to the window
                 */
                attach: function () {
                    this.$el.appendTo('body');

                    return this;
                },

                /**
                 * Converts an HTML representation of the dialog into arguments for a dialog box
                 * @param html HTML for the dialog
                 * @param args Arguments passed to the template
                 * @returns {}
                 */
                parseDialogContent: function (html, args) {
                    // Add a CID
                    args = _.extend({cid: this.cid}, args);


                    var c = $((
                        _.template(panels.helpers.utils.processTemplate(html))
                    )(args));
                    var r = {
                        title: c.find('.title').html(),
                        buttons: c.find('.buttons').html(),
                        content: c.find('.content').html()
                    };

                    if (c.has('.left-sidebar')) {
                        r.left_sidebar = c.find('.left-sidebar').html();
                    }

                    if (c.has('.right-sidebar')) {
                        r.right_sidebar = c.find('.right-sidebar').html();
                    }

                    return r;

                },

                /**
                 * Render the dialog and initialize the tabs
                 *
                 * @param attributes
                 * @returns {panels.view.dialog}
                 */
                renderDialog: function (attributes) {
                    attributes = _.extend({
                        editableLabel: this.editableLabel,
                        dialogIcon: this.dialogIcon
                    }, attributes);
                    this.$el.html(this.dialogTemplate(attributes)).hide();
                    this.$el.data('view', this);
                    this.$el.addClass('cs-panels-dialog-wrapper');

                    if (this.parentDialog !== false) {
                        // Add a link to the parent dialog as a sort of crumbtrail.
                        var thisDialog = this;
                        var dialogParent = $('<h3 class="cs-parent-link"></h3>').html(this.parentDialog.text + '<div class="cs-separator"></div>');
                        dialogParent.click(function (e) {
                            e.preventDefault();
                            thisDialog.updateDialog();
                            thisDialog.parentDialog.openDialog();
                        });
                        this.$('.cs-title-bar').prepend(dialogParent);
                    }

                    if (this.$('.cs-title-bar .cs-title-editable').length) {
                        // Added here because .cs-edit-title is only available after the template has been rendered.
                        this.initEditableLabel();
                    }

                    return this;
                },

                /**
                 * Initialize the sidebar tabs
                 */
                initTabs: function () {
                    var tabs = this.$('.cs-sidebar-tabs li a');

                    if (tabs.length === 0) {
                        return this;
                    }

                    var thisDialog = this;
                    tabs.click(function (e) {
                        e.preventDefault();
                        var $$ = $(this);

                        thisDialog.$('.cs-sidebar-tabs li').removeClass('tab-active');
                        thisDialog.$('.cs-content .cs-content-tabs > *').hide();

                        $$.parent().addClass('tab-active');

                        var url = $$.attr('href');
                        if (!_.isUndefined(url) && url.charAt(0) === '#') {
                            // Display the new tab
                            var tabName = url.split('#')[1];
                            thisDialog.$('.cs-content .cs-content-tabs .tab-' + tabName).show();
                        }

                        // This lets other dialogs implement their own custom handlers
                        thisDialog.trigger('tab_click', $$);

                    });

                    // Trigger a click on the first tab
                    this.$('.cs-sidebar-tabs li a').first().click();
                    return this;
                },

                initToolbar: function () {
                    // Trigger simplified click event for elements marked as toolbar buttons.
                    var buttons = this.$('.cs-toolbar .cs-buttons .cs-toolbar-button');
                    buttons.click(function (e) {
                        e.preventDefault();

                        this.trigger('button_click', $(e.currentTarget));
                    }.bind(this));

                    // Handle showing and hiding the dropdown list items
                    var $dropdowns = this.$('.cs-toolbar .cs-buttons .cs-dropdown-button');
                    $dropdowns.click(function (e) {
                        e.preventDefault();
                        var $dropdownButton = $(e.currentTarget);
                        var $dropdownList = $dropdownButton.siblings('.cs-dropdown-links-wrapper');
                        if ($dropdownList.is('.hidden')) {
                            $dropdownList.removeClass('hidden');
                        } else {
                            $dropdownList.addClass('hidden');
                        }

                    }.bind(this));

                    // Hide dropdown list on click anywhere, unless it's a dropdown option which requires confirmation in it's
                    // unconfirmed state.
                    $('html').click(function (e) {
                        this.$('.cs-dropdown-links-wrapper').not('.hidden').each(function (index, el) {
                            var $dropdownList = $(el);
                            var $trgt = $(e.target);
                            if ($trgt.length === 0 || !(
                                (
                                    $trgt.is('.cs-needs-confirm') && !$trgt.is('.cs-confirmed')
                                ) || $trgt.is('.cs-dropdown-button')
                            )) {
                                $dropdownList.addClass('hidden');
                            }
                        });
                    }.bind(this));
                },

                /**
                 * Initialize the editable dialog title
                 */
                initEditableLabel: function () {
                    var $editElt = this.$('.cs-title-bar .cs-title-editable');

                    $editElt.keypress(function (event) {
                        var enterPressed = event.type === 'keypress' && event.keyCode === 13;
                        if (enterPressed) {
                            // Need to make sure tab focus is on another element, otherwise pressing enter multiple times refocuses
                            // the element and allows newlines.
                            var tabbables = $(':tabbable');
                            var curTabIndex = tabbables.index($editElt);
                            tabbables.eq(curTabIndex + 1).focus();
                            // After the above, we're somehow left with the first letter of text selected,
                            // so this removes the selection.
                            window.getSelection().removeAllRanges();
                        }
                        return !enterPressed;
                    }).blur(function () {
                        var newValue = $editElt.text().replace(/^\s+|\s+$/gm, '');
                        var oldValue = $editElt.data('original-value').replace(/^\s+|\s+$/gm, '');
                        if (newValue !== oldValue) {
                            $editElt.text(newValue);
                            this.trigger('edit_label', newValue);
                        }

                    }.bind(this));

                    $editElt.focus(function () {
                        $editElt.data('original-value', $editElt.text());
                        panels.helpers.utils.selectElementContents(this);
                    });
                },

                /**
                 * Quickly setup the dialog by opening and closing it.
                 */
                setupDialog: function () {
                    this.openDialog();
                    this.updateDialog();
                },

                /**
                 * Refresh the next and previous buttons.
                 */
                refreshDialogNav: function () {
                    this.$('.cs-title-bar .cs-nav').show().removeClass('cs-disabled');

                    // Lets also hide the next and previous if we don't have a next and previous dialog
                    var nextDialog = this.getNextDialog();
                    var nextButton = this.$('.cs-title-bar .cs-next');

                    var prevDialog = this.getPrevDialog();
                    var prevButton = this.$('.cs-title-bar .cs-previous');

                    if (nextDialog === null) {
                        nextButton.hide();
                    }
                    else if (nextDialog === false) {
                        nextButton.addClass('cs-disabled');
                    }

                    if (prevDialog === null) {
                        prevButton.hide();
                    }
                    else if (prevDialog === false) {
                        prevButton.addClass('cs-disabled');
                    }
                },

                /**
                 * Open the dialog
                 */
                openDialog: function (options) {
                    options = _.extend({
                        silent: false
                    }, options);

                    if (!options.silent) {
                        this.trigger('open_dialog');
                    }

                    this.dialogOpen = true;

                    this.refreshDialogNav();

                    // Stop scrolling for the main body
                    panels.helpers.pageScroll.lock();

                    // Start listen for keyboard keypresses.
                    $(window).on('keyup', this.keyboardListen);

                    this.$el.show();

                    if (!options.silent) {
                        // This triggers once everything is visible
                        this.trigger('open_dialog_complete');
                        this.builder.trigger('open_dialog', this);
                        $(document).trigger('open_dialog', this);
                    }
                },

                /**
                 * Close the dialog
                 *
                 * @param e
                 * @returns {boolean}
                 */
                closeDialog: function (options) {
                    this.updateModel({
                        refresh:false
                    });

                    this.dialogOpen = false;
                    this.$el.hide();
                    panels.helpers.pageScroll.unlock();

                    // Stop listen for keyboard keypresses.
                    $(window).off('keyup', this.keyboardListen);
                    $('.cs-panels-live-editor').removeClass('cs-toolbar-loading');
                },

                updateDialog: function (options) {
                    options = _.extend({
                        silent: false
                    }, options);

                    if (!options.silent) {
                        this.trigger('close_dialog');
                    }

                    this.dialogOpen = false;
                    this.$el.hide();
                    panels.helpers.pageScroll.unlock();

                    // Stop listen for keyboard keypresses.
                    $(window).off('keyup', this.keyboardListen);

                    if (!options.silent) {
                        // This triggers once everything is hidden
                        this.trigger('close_dialog_complete');
                        this.builder.trigger('close_dialog', this);
                    }
                    this.updateDbPanelsData();
                    $('.cs-panels-live-editor').removeClass('cs-toolbar-loading');
                },
                /*
                 * update database
                 */
                updateDbPanelsData: function() {
                    this.$('.cs-preview-overlay').show();
                    $('.cs-panels-live-editor').addClass('cs-toolbar-loading');
                    var panelsData = this.builder.model.getPanelsData();

                    var data = {
                        action: 'so_panels_update_database',
                        panels_data: JSON.stringify(panelsData),
                        page_id: this.builder.config.postId,
                        showLoader: true,
                        done_url: panelsOptions.doneurl
                    };
                    $.post(panelsOptions.ajaxurl,data);
                },

                /**
                 * Keyboard events handler
                 */
                keyboardListen: function (e) {
                    // [Esc] to close
                    if (e.which === 27) {
                        $('.cs-panels-dialog-wrapper .cs-update').trigger('click');
                    }
                },

                /**
                 * Navigate to the previous dialog
                 */
                navToPrevious: function () {
                    this.updateDialog();

                    var prev = this.getPrevDialog();
                    if (prev !== null && prev !== false) {
                        prev.openDialog();
                    }
                },

                /**
                 * Navigate to the next dialog
                 */
                navToNext: function () {
                    this.updateDialog();

                    var next = this.getNextDialog();
                    if (next !== null && next !== false) {
                        next.openDialog();
                    }
                },

                /**
                 * Get the values from the form and convert them into a data array
                 */
                getFormValues: function (formSelector) {
                    if (_.isUndefined(formSelector)) {
                        formSelector = '.cs-content';
                    }

                    var $f = this.$(formSelector);

                    var data = {}, parts;

                    // Find all the named fields in the form
                    $f.find('[name]').each(function () {
                        var $$ = $(this);

                        try {

                            var name = /([A-Za-z_]+)\[(.*)\]/.exec($$.attr('name'));
                            if (_.isEmpty(name)) {
                                return true;
                            }

                            // Create an array with the parts of the name
                            if (_.isUndefined(name[2])) {
                                parts = $$.attr('name');
                            } else {
                                parts = name[2].split('][');
                                parts.unshift(name[1]);
                            }

                            parts = parts.map(function (e) {
                                if (!isNaN(parseFloat(e)) && isFinite(e)) {
                                    return parseInt(e);
                                } else {
                                    return e;
                                }
                            });

                            var sub = data;
                            var fieldValue = null;

                            var fieldType = (
                                _.isString($$.attr('type')) ? $$.attr('type').toLowerCase() : false
                            );

                            // First we need to get the value from the field
                            if (fieldType === 'checkbox') {
                                if ($$.is(':checked')) {
                                    fieldValue = $$.val() !== '' ? $$.val() : true;
                                } else {
                                    fieldValue = null;
                                }
                            }
                            else if (fieldType === 'radio') {
                                if ($$.is(':checked')) {
                                    fieldValue = $$.val();
                                } else {
                                    //skip over unchecked radios
                                    return;
                                }
                            }
                            else if ($$.prop('tagName') === 'SELECT') {
                                var selected = $$.find('option:selected');

                                if (selected.length === 1) {
                                    fieldValue = $$.find('option:selected').val();
                                }
                                else if (selected.length > 1) {
                                    // This is a mutli-select field
                                    fieldValue = _.map($$.find('option:selected'), function (n, i) {
                                        return $(n).val();
                                    });
                                }

                            } else {
                                // This is a fallback that will work for most fields
                                fieldValue = $$.val();
                            }

                            // Now, we need to filter this value if necessary
                            if (!_.isUndefined($$.data('panels-filter'))) {
                                switch ($$.data('panels-filter')) {
                                    case 'json_parse':
                                        // Attempt to parse the JSON value of this field
                                        try {
                                            fieldValue = JSON.parse(fieldValue);
                                        }
                                        catch (err) {
                                            fieldValue = '';
                                        }
                                        break;
                                }
                            }

                            // Now convert this into an array
                            if (fieldValue !== null) {
                                for (var i = 0; i < parts.length; i++) {
                                    if (i === parts.length - 1) {
                                        if (parts[i] === '') {
                                            // This needs to be an array
                                            sub.push(fieldValue);
                                        } else {
                                            sub[parts[i]] = fieldValue;
                                        }
                                    } else {
                                        if (_.isUndefined(sub[parts[i]])) {
                                            if (parts[i + 1] === '') {
                                                sub[parts[i]] = [];
                                            } else {
                                                sub[parts[i]] = {};
                                            }
                                        }
                                        sub = sub[parts[i]];
                                    }
                                }
                            }
                        }
                        catch (error) {
                            // Ignore this error, just log the message for debugging
                            console.log('Field [' + $$.attr('name') + '] could not be processed and was skipped - ' + error.message);
                        }

                    }); // End of each through input fields

                    return data;
                },

                /**
                 * Set a status message for the dialog
                 */
                setStatusMessage: function (message, loading, error) {
                    var msg = error ? '<span class="dashicons dashicons-warning"></span>' + message : message;
                    this.$('.cs-toolbar .cs-status').html(msg);
                    if (!_.isUndefined(loading) && loading) {
                        this.$('.cs-toolbar .cs-status').addClass('cs-panels-loading');
                    } else {
                        this.$('.cs-toolbar .cs-status').removeClass('cs-panels-loading');
                    }
                },

                /**
                 * Set the parent after.
                 */
                setParent: function (text, dialog) {
                    this.parentDialog = {
                        text: text,
                        dialog: dialog
                    };
                }
            });

        }, {}],
        26: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = Backbone.View.extend({
                template: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-live-editor').html())),

                previewScrollTop: 0,
                loadTimes: [],
                previewFrameId: 1,

                previewUrl: null,
                previewIframe: null,

                events: {
                    'click .live-editor-close': 'close',
                    'click .live-editor-logout': 'logout',
                    'click .live-editor-collapse': 'collapse',
                    'click .live-editor-mobile': 'mobileToggle',
                    'click .live-editor-tablet': 'mobileToggle',
                    'click .live-editor-desktop': 'mobileToggle',
                    'click .live-editor-save-options': 'handleSaveOptions',
                    'click #cs-panel-saver-menu-save-template': 'openMenuTemplate',
                    'submit #cs-template-library-save-template-form': 'saveTemplate',
                    'click .save_template__btn-close': 'closeFormTemplate'
                },

                preventClick:['.clever-tabs .nav-tabs > li' , '.clever-tabs .tab-content'],

                initialize: function (options) {
                    options = _.extend({
                        builder: false,
                        previewUrl: false
                    }, options);

                    if (_.isEmpty(options.previewUrl)) {
                        options.previewUrl = panelsOptions.ajaxurl + "&action=so_panels_live_editor_preview";
                    }

                    this.builder = options.builder;
                    this.previewUrl = options.previewUrl;

                    this.builder.model.on('refresh_panels_data', this.handleRefreshData, this);
                    this.builder.model.on('load_panels_data', this.handleLoadData, this);
                },

                /**
                 * Render the live editor
                 */
                render: function () {
                    this.setElement(this.template());
                    this.$el.hide();
                    var thisView = this;

                    var isMouseDown = false;

                    $(document)
                        .mousedown(function () {
                            isMouseDown = true;
                        })
                        .mouseup(function () {
                            isMouseDown = false;
                        });

                    // Handle highlighting the relevant widget in the live editor preview
                    this.$el.on('mouseenter', '.cs-widget-wrapper', function () {
                        var $$ = $(this),
                            previewWidget = $$.data('live-editor-preview-widget');

                        if (!isMouseDown && previewWidget !== undefined && previewWidget.length && !thisView.$('.cs-preview-overlay').is(':visible')) {
                            thisView.highlightElement(previewWidget);
                            thisView.scrollToElement(previewWidget);
                        }
                    });

                    thisView.$el.on('mouseleave', '.cs-widget-wrapper', function () {
                        var $$ = $(this),
                            previewWidget = $$.data('live-editor-preview-widget');
                        thisView.resetHighlights(previewWidget);
                    });

                    thisView.builder.on('open_dialog', function () {
                        var $$ = $(this),
                            previewWidget = $$.data('live-editor-preview-widget');
                        thisView.resetHighlights(previewWidget);
                    });

                    return this;
                },

                /**
                 * Attach the live editor to the document
                 */
                attach: function () {
                    this.$el.appendTo('body');
                },

                /**
                 * Display the live editor
                 */
                open: function () {
                    if (this.$el.html() === '') {
                        this.render();
                    }
                    if (this.$el.closest('body').length === 0) {
                        this.attach();
                    }

                    // Disable page scrolling
                    panels.helpers.pageScroll.lock();

                    if (this.$el.is(':visible')) {
                        return this;
                    }

                    // Refresh the preview display
                    this.$el.show();
                    this.refreshPreview(this.builder.model.getPanelsData());

                    // Move the builder view into the Live Editor
                    this.originalContainer = this.builder.$el.parent();
                    this.builder.$el.appendTo(this.$('.cs-live-editor-builder'));
                    this.builder.$('.cs-tool-button.cs-live-editor').hide();
                    this.builder.trigger('builder_resize');


                    if ($('#original_post_status').val() === 'auto-draft' && !this.autoSaved) {
                        // The live editor requires a saved draft post, so we'll create one for auto-draft posts
                        var thisView = this;

                        if (wp.autosave) {
                            // Set a temporary post title so the autosave triggers properly
                            if ($('#title[name="post_title"]').val() === '') {
                                $('#title[name="post_title"]').val(panelsOptions.loc.draft).trigger('keydown');
                            }

                            $(document).one('heartbeat-tick.autosave', function () {
                                thisView.autoSaved = true;
                                thisView.refreshPreview(thisView.builder.model.getPanelsData());
                            });
                            wp.autosave.server.triggerSave();
                        }
                    }
                },

                /**
                 * Close the live editor
                 */
                close: function () {
                    if (!this.$el.is(':visible')) {
                        return this;
                    }
                    ///update Panels data into database

                    panels.helpers.pageScroll.unlock();
                    // Move the builder back to its original container
                    //this.builder.$el.appendTo(this.originalContainer);
                    //this.builder.$('.cs-tool-button.cs-live-editor').show();
                    this.builder.trigger('builder_resize');
                    this.updateDbPanelsData();
                },
                /**
                 * Close the live editor and logout
                 */
                logout: function () {
                    var data = {
                        action: 'so_panels_logout',
                        done_url: panelsOptions.doneurl
                    };
                    $.post(
                        panelsOptions.ajaxurl,
                        data,
                        function (result) {
                            window.location = panelsOptions.doneurl
                        })
                },
                /*
                 * save database
                 */
                updateDbPanelsData: function() {
                    this.$('.cs-preview-overlay').show();
                    $('.cs-panels-live-editor').addClass('cs-toolbar-loading');
                    var panelsData = this.builder.model.getPanelsData();

                    var data = {
                        action: 'so_panels_save_database',
                        panels_data: JSON.stringify(panelsData),
                        page_id: this.builder.config.postId,
                        showLoader: true,
                        done_url: panelsOptions.doneurl
                    };
                    $.post(
                        panelsOptions.ajaxurl,
                        data,
                        function (result) {
                            window.location = panelsOptions.doneurl
                        })
                },

                /**
                 * Collapse the live editor
                 */
                collapse: function () {
                    this.$el.toggleClass('cs-collapsed');
                    $('.cs-panels-dialog-wrapper').hide();
                    
                    var text = this.$('.live-editor-collapse span');
                    text.html(text.data(this.$el.hasClass('cs-collapsed') ? 'expand' : 'collapse'));
                },

                /**
                 *
                 * @param over
                 * @return {*|Object} The item we're hovering over.
                 */
                highlightElement: function (over) {
                    over.find('.cs-edit-element-section').show();
                    if (over.attr('data-widget-id').length) {
                        this.handleOpenDialog();
                    }
                },

                /**
                 * Reset highlights in the live preview
                 */
                resetHighlights: function (over) {
                    // var body = this.previewIframe.contents().find('body');
                    // body.find('.cs-edit-element-section').hide();
                    if (!_.isUndefined(over) && !_.isUndefined(over.find('.cs-edit-element-section'))) over.find('.cs-edit-element-section').hide();
                },

                handleOpenDialog: function () {
                    var thisView = this;
                    var body = this.previewIframe.contents().find('body');
                    body.find('.cs-edit-element-section').unbind().click(function() {
                        var $widget_id = $(this).closest('.cs-panel').attr('data-widget-id');
                        var widget = thisView.$('.cs-live-editor-builder .cs-widget[data-widget-id="'+$widget_id+'"]').first();
                        if (widget.find('.panel-info').first().length === 0) {
                            var $$click = widget.find('.title h4').first();
                        } else {
                            var $$click = widget.find('.panel-info').first();
                        }
                        $$click.trigger('click');
                    });
                },

                /**
                 * Scroll over an element in the live preview
                 * @param over
                 */
                scrollToElement: function (over) {
                    // var contentWindow = this.$('.cs-preview iframe')[0].contentWindow;
                    // contentWindow.liveEditorScrollTo(over,contentWindow);
                },

                handleRefreshData: function (newData, args) {
                    if (!this.$el.is(':visible')) {
                        return this;
                    }

                    this.refreshPreview(newData);
                },

                handleLoadData: function () {
                    if (!this.$el.is(':visible')) {
                        return this;
                    }

                    this.refreshPreview(this.builder.model.getPanelsData());
                },

                /**
                 * Refresh the Live Editor preview.
                 * @returns {exports}
                 */
                refreshPreview: function (data) {
                    var loadTimePrediction = this.loadTimes.length ?
                        _.reduce(this.loadTimes, function (memo, num) {
                            return memo + num;
                        }, 0) / this.loadTimes.length : 1000;

                    // Store the last preview iframe position
                    if (!_.isNull(this.previewIframe)) {
                        if (!this.$('.cs-preview-overlay').is(':visible')) {
                            this.previewScrollTop = this.previewIframe.contents().scrollTop();
                        }
                    }

                    // Add a loading bar
                    this.$('.cs-preview-overlay').show();
                    $('.cs-panels-live-editor').addClass('cs-toolbar-loading');
                    // jQuery('body').addClass('site-panels-loading');
                    // jQuery('body').hide();
                    this.$('.cs-preview-overlay .cs-loading-bar')
                        .clearQueue()
                        .css('width', '0%')
                        .animate({width: '100%'}, parseInt(loadTimePrediction) + 100);


                    this.postToIframe(
                        {
                            live_editor_panels_data: JSON.stringify(data),
                            live_editor_post_ID: this.builder.config.postId
                        },
                        this.previewUrl,
                        this.$('.cs-preview')
                    );

                    this.previewIframe.data('load-start', new Date().getTime());
                },

                /**
                 * Use a temporary form to post data to an iframe.
                 *
                 * @param data The data to send
                 * @param url The preview URL
                 * @param target The target iframe
                 */
                postToIframe: function (data, url, target) {
                    // Store the old preview

                    if (!_.isNull(this.previewIframe)) {
                        this.previewIframe.remove();
                    }

                    var iframeId = 'cleversoft-panels-live-preview-' + this.previewFrameId;

                    // Remove the old preview frame
                    this.previewIframe = $('<iframe src="javascript:false;" />')
                        .attr({
                            'id': iframeId,
                            'class':'cleversoft-panels-iframe-preview',
                            'name': iframeId
                        })
                        .appendTo(target);

                    this.setupPreviewFrame(this.previewIframe);

                    // We can use a normal POST form submit
                    var tempForm = $('<form id="soPostToPreviewFrame" method="post" />')
                        .attr({
                            id: iframeId,
                            target: this.previewIframe.attr('id'),
                            action: url
                        })
                        .appendTo('body');

                    $.each(data, function (name, value) {
                        $('<input type="hidden" />')
                            .attr({
                                name: name,
                                value: value
                            })
                            .appendTo(tempForm);
                    });

                    //$('#maincontent').remove();

                    tempForm
                        .submit()
                        .remove();

                    this.previewFrameId++;

                    return this.previewIframe;
                },

                /**
                 * Do all the basic setup for the preview Iframe element
                 * @param iframe
                 */
                setupPreviewFrame: function (iframe) {
                    // Create the main builder model
                    var builderModel = new panels.model.builder();

                    // Now for the view to display the builder
                    var builderView = new panels.view.builder({
                        model: builderModel,
                        config: {}
                    });
                    var thisView = this;
                    iframe
                        .data('iframeready', false)
                        .on('iframeready', function () {
                            var $$ = $(this),
                                $iframeContents = $$.contents();

                            if ($$.data('iframeready')) {
                                // Skip this if the iframeready function has already run
                                return;
                            }

                            $$.data('iframeready', true);

                            if ($$.data('load-start') !== undefined) {
                                thisView.loadTimes.unshift(new Date().getTime() - $$.data('load-start'));

                                if (!_.isEmpty(thisView.loadTimes)) {
                                    thisView.loadTimes = thisView.loadTimes.slice(0, 4);
                                }
                            }

                            setTimeout(function () {
                                // Scroll to the correct position
                                $iframeContents.scrollTop(thisView.previewScrollTop);
                                thisView.$('.cs-preview-overlay').hide();
                                $('.cs-panels-live-editor').removeClass('cs-toolbar-loading');
                                // jQuery('body').removeClass('site-panels-loading');
                                // jQuery('body').show();
                                var options = {
                                    verticalMargin: 0,
                                    autoFit: true,
                                    auto_height: true,
                                    autoFitByOverflow: true,
                                    staticGrid: 'false',
                                    animate: true,
                                    resizable: {
                                        handles: 'e'
                                    }
                                };
                            }, 100);

                            // Lets find all the first level grids. This is to account for the Page Builder layout widget.
                            var layoutWrapper = $iframeContents.find('#pl-' + thisView.builder.config.postId);
                            //prevent click for unwanted elements
                            layoutWrapper.find('.cs-panel .prevent-click-show-form').closest('.cs-panel').addClass('prevent-click-panel');
                            layoutWrapper.find('.panel-grid .panel-grid-cell .cs-panel')
                                .filter(function () {
                                    // Filter to only include non nested
                                    return $(this).closest('.panel-layout').is(layoutWrapper);
                                }).not('.prevent-click-panel')
                                .each(function (i, el) {
                                    var $$ = $(el);
                                    var widgetEdit = thisView.$('.cs-live-editor-builder .cs-widget-wrapper').eq($$.data('index'));
                                    widgetEdit.data('live-editor-preview-widget', $$);

                                    $$
                                        .css({
                                            'cursor': 'pointer'
                                        })
                                        .mouseenter(function () {
                                            widgetEdit.parent().addClass('cs-hovered');
                                            thisView.highlightElement($$);
                                        })
                                        .mouseleave(function () {
                                            widgetEdit.parent().removeClass('cs-hovered');
                                            thisView.resetHighlights($$);
                                        })
                                        .on('click',function (e) {
                                            e.preventDefault();
                                            // click element in preview frame to show option in sidebar
                                            // widgetEdit.find('.title h4').click();
                                        });
                                })
                            ;
                            layoutWrapper.find('.panel-grid .panel-grid-cell .cs-panel.prevent-click-panel').filter(function () {
                                // Filter to only include non nested
                                return $(this).closest('.panel-layout').is(layoutWrapper);
                            })
                                .each(function (i, el) {
                                    var $$ = $(el);
                                    var widgetEdit = thisView.$('.cs-live-editor-builder .cs-widget-wrapper').eq($$.data('index'));
                                    widgetEdit.data('live-editor-preview-widget', $$);

                                    $$
                                        .css({
                                            'cursor': 'pointer'
                                        })
                                        .mouseenter(function () {
                                            widgetEdit.parent().addClass('cs-hovered');
                                            thisView.highlightElement($$);
                                        })
                                        .mouseleave(function () {
                                            widgetEdit.parent().removeClass('cs-hovered');
                                            thisView.resetHighlights($$);
                                        })
                                        .on('click', function (e) {
                                            e.preventDefault();
                                            var $element = $(e.target);
                                            if($element.hasClass('has-own-click-event') || $element.closest('.has-own-click-event').length > 0) {
                                                var $dataId = $element.data('id');
                                                var $containerId = $element.attr('data-widget-id');
                                                widgetEdit.parent().find('#'+$containerId + ' .tab-item[data-id="#'+$dataId+'"]').click();
                                            } else {
                                                widgetEdit.find('.title h4').click();
                                            }

                                            // When we click a widget, send that click to the form
                                            //
                                        });
                                })
                            ;

                            // Prevent default clicks inside the preview iframe
                            $iframeContents.find("a").not('.prevent-click-show-form a').css({'pointer-events': 'none'}).click(function (e) {
                                e.preventDefault();
                            });

                        })
                        .on('load', function () {
                            var $$ = $(this);
                            if (!$$.data('iframeready')) {
                                $$.trigger('iframeready');
                            }
                        });
                },

                /**
                 * Return true if the live editor has a valid preview URL.
                 * @return {boolean}
                 */
                hasPreviewUrl: function () {
                    return this.$('form.live-editor-form').attr('action') !== '';
                },

                /**
                 * Toggle the size of the preview iframe to simulate mobile devices.
                 * @param e
                 */
                mobileToggle: function (e) {
                    var button = $(e.currentTarget);
                    this.$('.live-editor-mode').not(button).removeClass('cs-active');
                    button.addClass('cs-active');

                    this.$el
                        .removeClass('live-editor-desktop-mode live-editor-tablet-mode live-editor-mobile-mode')
                        .addClass('live-editor-' + button.data('mode') + '-mode');

                },
                handleSaveOptions: function(e) {
                    this.$('.cs-panel-footer-sub-menu-wrapper').toggleClass('open');
                    if ($('body').hasClass('overlay_save_template')) $('body').removeClass('overlay_save_template');
                    if (this.$('.cs-panel-footer-sub-menu-wrapper').hasClass('open') && this.$('.save_template__inner').hasClass('open')) $('body').addClass('overlay_save_template');
                },
                openMenuTemplate: function (e) {
                    this.$('.save_template__inner').addClass('open');
                    $('body').addClass('overlay_save_template');
                },
                saveTemplate: function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.$('.cs-preview-overlay').show();
                    $('.cs-panels-live-editor').addClass('cs-toolbar-loading');
                    var panelsData = this.builder.model.getPanelsData();
                    var title = this.$('#cs-template-library-save-template-name').val();
                    var data = {
                        action: 'so_panels_save_template',
                        panels_data: JSON.stringify(panelsData),
                        title: title,
                        showLoader: true,
                        done_url: panelsOptions.doneurl
                    };

                    $.post(
                        panelsOptions.ajaxurl,
                        data,
                        function (result) {
                            window.location = panelsOptions.doneurl
                        })
                },
                closeFormTemplate: function (e) {
                    this.$('.save_template__inner').removeClass('open');
                    $('body').removeClass('overlay_save_template');
                }
            });

        }, {}],
        27: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = Backbone.View.extend({
                template: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-row').html())),

                events: {
                    // 'click .cs-row-settings': 'editSettingsHandler',
                    'click .cs-row-duplicate': 'duplicateHandler',
                    'click .cs-row-delete': 'confirmedDeleteHandler'
                },

                builder: null,
                dialog: null,

                /**
                 * Initialize the row view
                 */
                initialize: function () {

                    var rowCells = this.model.get('cells');
                    rowCells.on('add', this.handleCellAdd, this);
                    rowCells.on('remove', this.handleCellRemove, this);
                    this.model.on('reweight_cells', this.resize, this);

                    this.model.on('destroy', this.onModelDestroy, this);
                    this.model.on('visual_destroy', this.visualDestroyModel, this);

                    var thisView = this;
                    rowCells.each(function (cell) {
                        thisView.listenTo(cell.get('widgets'), 'add', thisView.resize);
                    });

                    // When ever a new cell is added, listen to it for new widgets
                    rowCells.on('add', function (cell) {
                        thisView.listenTo(cell.get('widgets'), 'add', thisView.resize);
                    }, this);

                    this.model.on('change:label', this.onLabelChange, this);
                },

                /**
                 * Render the row.
                 *
                 * @returns {panels.view.row}
                 */
                render: function () {
                    var rowColorLabel = this.model.has('color_label') ? this.model.get('color_label') : 1;
                    var rowLabel = this.model.has('label') ? this.model.get('label') : '';
                    this.setElement(this.template({rowColorLabel: rowColorLabel, rowLabel: rowLabel}));
                    this.$el.data('view', this);

                    // Create views for the cells in this row
                    var thisView = this;
                    this.model.get('cells').each(function (cell) {
                        var cellView = new panels.view.cell({
                            model: cell
                        });
                        cellView.row = thisView;
                        cellView.render();
                        cellView.$el.appendTo(thisView.$('.cs-cells'));
                    });
                    
                    if (!$.trim(this.$('.cs-row-toolbar').html()).length) {
                        this.$('.cs-row-toolbar').remove();
                    }

                    // Resize the rows when ever the widget sortable moves
                    this.builder.on('widget_sortable_move', this.resize, this);
                    this.builder.on('builder_resize', this.resize, this);

                    this.resize();

                    return this;
                },

                /**
                 * Give a visual indication of the creation of this row
                 */
                visualCreate: function () {
                    this.$el.hide().fadeIn('fast');
                },

                /**
                 * Visually resize the row so that all cell heights are the same and the widths so that they balance to 100%
                 *
                 * @param e
                 */
                resize: function (e) {
                    // Don't resize this
                    if (!this.$el.is(':visible')) {
                        return;
                    }

                    // Reset everything to have an automatic height
                    this.$('.cs-cells .cell-wrapper').css('min-height', 0);
                    this.$('.cs-cells .resize-handle').css('height', 0);

                    // We'll tie the values to the row view, to prevent issue with values going to different rows
                    var height = 0;
                    this.$('.cs-cells .cell').each(function () {
                        height = Math.max(
                            height,
                            $(this).height()
                        );

                        $(this).css(
                            'width',
                            ( $(this).data('view').model.get('weight') * 100) + "%"
                        );
                    });

                    // Resize all the grids and cell wrappers
                    this.$('.cs-cells .cell-wrapper').css('min-height', Math.max(height, 63));
                    this.$('.cs-cells .resize-handle').css('height', this.$('.cs-cells .cell-wrapper').outerHeight());
                },

                /**
                 * Remove the view from the dom.
                 */
                onModelDestroy: function () {
                    this.remove();
                },

                /**
                 * Fade out the view and destroy the model
                 */
                visualDestroyModel: function () {
                    this.builder.addHistoryEntry('row_deleted');
                    var thisView = this;
                    this.$el.fadeOut('normal', function () {
                        thisView.model.destroy();
                        thisView.builder.model.refreshPanelsData();
                    });
                },

                onLabelChange: function (model, text) {
                    if (this.$('.cs-row-label').length == 0) {
                        this.$('.cs-row-toolbar').prepend('<h3 class="cs-row-label">' + text + '</h3>');
                    } else {
                        this.$('.cs-row-label').text(text);
                    }
                },

                /**
                 * Duplicate this row.
                 *
                 * @return {boolean}
                 */
                duplicateHandler: function () {
                    this.builder.addHistoryEntry('row_duplicated');

                    var duplicateRow = this.model.clone(this.builder.model);

                    this.builder.model.get('rows').add(duplicateRow, {
                        at: this.builder.model.get('rows').indexOf(this.model) + 1
                    });

                    this.builder.model.refreshPanelsData();
                },

                /**
                 * Copy the row to a localStorage
                 */
                copyHandler: function () {
                    panels.helpers.clipboard.setModel(this.model);
                },

                /**
                 * Create a new row and insert it
                 */
                pasteHandler: function () {
                    var pastedModel = panels.helpers.clipboard.getModel('row-model');

                    if (!_.isEmpty(pastedModel) && pastedModel instanceof panels.model.row) {
                        this.builder.addHistoryEntry('row_pasted');
                        pastedModel.builder = this.builder.model;
                        this.builder.model.get('rows').add(pastedModel, {
                            at: this.builder.model.get('rows').indexOf(this.model) + 1
                        });
                        this.builder.model.refreshPanelsData();
                    }
                },

                /**
                 * Handles deleting the row with a confirmation.
                 */
                confirmedDeleteHandler: function (e) {
                    if (confirm('Are you sure you want to clear all content?')) {
                        this.visualDestroyModel();
                    }
                },

                /**
                 * Handle displaying the settings dialog
                 */
                editSettingsHandler: function () {
                    if (!this.builder.supports('editRow')) {
                        return;
                    }
                    // Lets open up an instance of the settings dialog
                    if (this.dialog === null) {
                        // Create the dialog
                        this.dialog = new panels.dialog.row();
                        this.dialog.setBuilder(this.builder).setRowModel(this.model);
                    }

                    this.dialog.openDialog();

                    return this;
                },

                /**
                 * Handle deleting this entire row.
                 */
                deleteHandler: function () {
                    this.model.destroy();
                    return this;
                },

                /**
                 * Handle a new cell being added to this row view. For now we'll assume the new cell is always last
                 */
                handleCellAdd: function (cell) {
                    var cellView = new panels.view.cell({
                        model: cell
                    });
                    cellView.row = this;
                    cellView.render();
                    cellView.$el.appendTo(this.$('.cs-cells'));
                },

                /**
                 * Handle a cell being removed from this row view
                 */
                handleCellRemove: function (cell) {
                    // Find the view that ties in to the cell we're removing
                    this.$('.cs-cells > .cell').each(function () {
                        var view = $(this).data('view');
                        if (_.isUndefined(view)) {
                            return;
                        }

                        if (view.model.cid === cell.cid) {
                            // Remove this view
                            view.remove();
                        }
                    });
                },

                /**
                 * Build up the contextual menu for a row
                 *
                 * @param e
                 * @param menu
                 */
                buildContextualMenu: function (e, menu) {
                    var options = [];
                    for (var i = 1; i < 5; i++) {
                        options.push({
                            title: i + ' ' + panelsOptions.loc.contextual.column
                        });
                    }

                    if (this.builder.supports('addRow')) {
                        menu.addSection(
                            'add-row',
                            {
                                sectionTitle: panelsOptions.loc.contextual.add_row,
                                search: false
                            },
                            options,
                            function (c) {
                                this.builder.addHistoryEntry('row_added');

                                var columns = Number(c) + 1;
                                var weights = [];
                                for (var i = 0; i < columns; i++) {
                                    weights.push({weight: 100 / columns});
                                }

                                // Create the actual row
                                var newRow = new panels.model.row({
                                    collection: this.collection
                                });

                                var cells = new panels.collection.cells(weights);
                                cells.each(function (cell) {
                                    cell.row = newRow;
                                });
                                newRow.setCells(cells);
                                newRow.builder = this.builder.model;

                                this.builder.model.get('rows').add(newRow, {
                                    at: this.builder.model.get('rows').indexOf(this.model) + 1
                                });

                                this.builder.model.refreshPanelsData();
                            }.bind(this)
                        );
                    }

                    var actions = {};

                    if (this.builder.supports('editRow')) {
                        actions.edit = {title: panelsOptions.loc.contextual.row_edit};
                    }

                    // Copy and paste functions
                    if (panels.helpers.clipboard.canCopyPaste()) {
                        actions.copy = {title: panelsOptions.loc.contextual.row_copy};
                        if (this.builder.supports('addRow') && panels.helpers.clipboard.isModel('row-model')) {
                            actions.paste = {title: panelsOptions.loc.contextual.row_paste};
                        }
                    }

                    if (this.builder.supports('addRow')) {
                        actions.duplicate = {title: panelsOptions.loc.contextual.row_duplicate};
                    }

                    if (this.builder.supports('deleteRow')) {
                        actions.delete = {title: panelsOptions.loc.contextual.row_delete, confirm: true};
                    }

                    if (!_.isEmpty(actions)) {
                        menu.addSection(
                            'row-actions',
                            {
                                sectionTitle: panelsOptions.loc.contextual.row_actions,
                                search: false,
                            },
                            actions,
                            function (c) {
                                switch (c) {
                                    case 'edit':
                                        this.editSettingsHandler();
                                        break;
                                    case 'copy':
                                        this.copyHandler();
                                        break;
                                    case 'paste':
                                        this.pasteHandler();
                                        break;
                                    case 'duplicate':
                                        this.duplicateHandler();
                                        break;
                                    case 'delete':
                                        this.visualDestroyModel();
                                        break;
                                }
                            }.bind(this)
                        );
                    }
                }
            });

        }, {}],
        28: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = Backbone.View.extend({

                stylesLoaded: false,

                initialize: function () {

                },

                /**
                 * Render the visual styles object.
                 *
                 * @param type
                 * @param postId
                 */
                render: function (stylesType, postId, args) {
                    if (_.isUndefined(stylesType)) {
                        return;
                    }

                    // Add in the default args
                    args = _.extend({
                        builderType: '',
                        dialog: null
                    }, args);

                    this.$el.addClass('cs-visual-styles cs-' + stylesType + '-styles');

                    var postArgs = {
                        builderType: args.builderType
                    };

                    if (stylesType === 'cell') {
                        postArgs.index = args.index;
                    }

                    // Load the form
                    $.post(
                        panelsOptions.ajaxurl,
                        {
                            action: 'so_panels_style_form',
                            type: stylesType,
                            style: this.model.get('style'),
                            widget_id: this.model.get('widget_id'),
                            args: JSON.stringify(postArgs),
                            postId: postId
                        },
                        function (response) {
                            this.$el.html(response);
                            this.setupFields();
                            this.stylesLoaded = true;
                            this.trigger('styles_loaded', !_.isEmpty(response));
                            if (!_.isNull(args.dialog)) {
                                args.dialog.trigger('styles_loaded', !_.isEmpty(response));
                            }
                            $('.cs-row-styles').closest('.cs-sidebar').show();
                        }.bind(this)
                    );

                    return this;
                },

                /**
                 * Attach the style view to the DOM.
                 *
                 * @param wrapper
                 */
                attach: function (wrapper) {
                    wrapper.append(this.$el);
                },

                /**
                 * Detach the styles view from the DOM
                 */
                detach: function () {
                    this.$el.detach();
                },
                setupBackgroundDisplayPreview : function (previewEl,value) {
                    switch ( value) {
                        case 'parallax':
                            previewEl.css('background-attachment','fixed');
                            previewEl.css('background-position','center center');
                            previewEl.css('background-size','cover');
                            previewEl.css('background-repeat','unset');
                            break;
                        case 'parallax-original':
                            previewEl.css('background-position','center center');
                            previewEl.css('background-repeat','no-repeat');
                            previewEl.css('background-size','unset');
                            previewEl.css('background-attachment','unset');
                            break;
                        case 'tile':
                            previewEl.css('background-repeat','repeat');
                            previewEl.css('background-position','unset');
                            previewEl.css('background-attachment','unset');
                            previewEl.css('background-size','unset');
                            break;
                        case 'cover':
                            previewEl.css('background-position','center center');
                            previewEl.css('background-size','cover');
                            previewEl.css('background-attachment','unset');
                            previewEl.css('background-repeat','unset');
                            break;
                        case 'center':
                            previewEl.css('background-position','center center');
                            previewEl.css('background-repeat','no-repeat');
                            previewEl.css('background-size','unset');
                            previewEl.css('background-attachment','unset');
                            break;
                        case 'fixed':
                            previewEl.css('background-attachment','fixed');
                            previewEl.css('background-position','center center');
                            previewEl.css('background-size','cover');
                            previewEl.css('background-repeat','unset');
                            break;
                    }
                },
                /**
                 * Setup all the fields
                 */
                setupFields: function () {
                    var seft = this;
                    // Set up the sections as collapsible
                    this.$('.style-section-wrapper').each(function () {
                        var $s = $(this);

                        $s.find('.style-section-head').click(function (e) {
                            e.preventDefault();
                            $s.prevAll().find('.style-section-fields').slideUp('fast');
                            $s.nextAll().find('.style-section-fields').slideUp('fast');
                            $s.find('.style-section-fields').slideToggle('fast');
                        });
                    });

                    // Set up the color fields
                    if (!_.isUndefined($.fn.wpColorPicker)) {
                        if (_.isObject(panelsOptions.wpColorPickerOptions.palettes) && !$.isArray(panelsOptions.wpColorPickerOptions.palettes)) {
                            panelsOptions.wpColorPickerOptions.palettes = $.map(panelsOptions.wpColorPickerOptions.palettes, function (el) {
                                return el;
                            });
                        }
                        this.$('.cs-wp-color-field').wpColorPicker(panelsOptions.wpColorPickerOptions);
                    }

                    // Set up all the text fields
                    this.$('.style-field-text').each(function () {
                        var $$ = $(this);
                        var text = $$.find('input[type="text"]');

                        text.change(function() {
                            var input_el = $(this);
                            var panelId = input_el.attr('data-panel');
                            var previewEl = $(".cleversoft-panels-iframe-preview").contents().find(panelId+" "+".global-element").first();
                            if (input_el.attr('data-code')) {
                                switch(input_el.attr('data-attribute')) {
                                    case 'style':
                                        if (input_el.attr('data-unit')) {
                                            previewEl.css(input_el.attr('data-code'),input_el.val()+input_el.attr('data-unit'));
                                        } else {
                                            previewEl.css(input_el.attr('data-code'),input_el.val());
                                        }
                                        break;
                                    case 'class':
                                        previewEl.addClass(input_el.val());
                                        break;
                                    case 'attribute':
                                        previewEl.attr(input_el.attr('data-code'),input_el.val());
                                        break;
                                    default:
                                }
                            }
                        });
                    });

                    // Set up all the select fields
                    this.$('.style-field-select').each(function () {
                        var $$ = $(this);
                        var select = $$.find('select');
                        select.change(function() {
                            var select_el = $(this);
                            var panelId = select_el.attr('data-panel');
                            var previewEl = $(".cleversoft-panels-iframe-preview").contents().find(panelId+" "+".global-element").first();
                            if (select_el.attr('data-code')) {
                                switch(select_el.attr('data-attribute')) {
                                    case 'style':
                                        if (select_el.attr('data-mode') == 'hover') {
                                            if (select_el.attr('name') == 'style[background_display]') {
                                                previewEl.hover(function(){
                                                    seft.setupBackgroundDisplayPreview(previewEl, select_el.val());
                                                }, function(){ 
                                                    var normalStyle = $('select[data-code="'+select_el.attr('data-code')+'"][data-panel="'+panelId+'"][data-mode="normal"]').val();
                                                    seft.setupBackgroundDisplayPreview(previewEl, normalStyle);
                                                });   
                                            } else {
                                                previewEl.hover(function(){
                                                    previewEl.css(select_el.attr('data-code'),select_el.val());
                                                }, function(){ 
                                                    var normalStyle = $('select[data-code="'+select_el.attr('data-code')+'"][data-panel="'+panelId+'"][data-mode="normal"]').val();
                                                    previewEl.css(select_el.attr('data-code'),normalStyle);
                                                });  
                                            }
                                        } else {
                                            if (select_el.attr('name') == 'style[background_display]') {
                                                seft.setupBackgroundDisplayPreview(previewEl, select_el.val());
                                                previewEl.hover(function(){
                                                    var normalStyle = $('select[data-code="'+select_el.attr('data-code')+'"][data-panel="'+panelId+'"][data-mode="hover"]').val();
                                                    if (normalStyle) {
                                                        seft.setupBackgroundDisplayPreview(previewEl, normalStyle);
                                                    } else {
                                                        seft.setupBackgroundDisplayPreview(previewEl, select_el.val());
                                                    }
                                                }, function(){ 
                                                    seft.setupBackgroundDisplayPreview(previewEl, select_el.val());
                                                });  
                                            } else {
                                                previewEl.css(select_el.attr('data-code'),select_el.val());
                                                previewEl.hover(function(){
                                                    var normalStyle = $('select[data-code="'+select_el.attr('data-code')+'"][data-panel="'+panelId+'"][data-mode="hover"]').val();
                                                    if (normalStyle) {
                                                        previewEl.css(select_el.attr('data-code'),normalStyle);
                                                    } else {
                                                        previewEl.css(select_el.attr('data-code'),select_el.val());
                                                    }
                                                }, function(){ 
                                                    previewEl.css(select_el.attr('data-code'),select_el.val());
                                                });
                                            }
                                        }
                                        
                                        break;
                                    case 'class':
                                        previewEl.addClass(select_el.val());
                                        break;
                                    case 'attribute':
                                        previewEl.attr(select_el.attr('data-code'),select_el.val());
                                        break;
                                    default:
                                }
                            }
                        });
                    });

                    // Set up all the measurement fields
                    this.$('.style-field-measurement').each(function () {
                        var $$ = $(this);

                        var text = $$.find('input[type="text"]');
                        var unit = $$.find('select');
                        var hidden = $$.find('input[type="hidden"]');

                        text.focus(function () {
                            $(this).select();
                        });

                        /**
                         * Load value into the visible input fields.
                         * @param value
                         */
                        var loadValue = function (value) {
                            if (value === '') {
                                return;
                            }

                            var re = /(?:([0-9\.,\-]+)(.*))+/;
                            var valueList = hidden.val().split(' ');
                            var valueListValue = [];
                            for (var i in valueList) {
                                var match = re.exec(valueList[i]);
                                if (!_.isNull(match) && !_.isUndefined(match[1]) && !_.isUndefined(match[2])) {
                                    valueListValue.push(match[1]);
                                    unit.val(match[2]);
                                }
                            }

                            if (text.length === 1) {
                                // This is a single input text field
                                text.val(valueListValue.join(' '));
                            }
                            else {
                                // We're dealing with a multiple field
                                if (valueListValue.length === 1) {
                                    valueListValue = [valueListValue[0], valueListValue[0], valueListValue[0], valueListValue[0]];
                                }
                                else if (valueListValue.length === 2) {
                                    valueListValue = [valueListValue[0], valueListValue[1], valueListValue[0], valueListValue[1]];
                                }
                                else if (valueListValue.length === 3) {
                                    valueListValue = [valueListValue[0], valueListValue[1], valueListValue[2], valueListValue[1]];
                                }

                                // Store this in the visible fields
                                text.each(function (i, el) {
                                    $(el).val(valueListValue[i]);
                                });
                            }
                        };
                        loadValue(hidden.val());

                        /**
                         * Set value of the hidden field based on inputs
                         */
                        var setValue = function (e) {
                            var i;

                            if (text.length === 1) {
                                // We're dealing with a single measurement
                                var fullString = text
                                    .val()
                                    .split(' ')
                                    .filter(function (value) {
                                        return value !== '';
                                    })
                                    .map(function (value) {
                                        return value + unit.val();
                                    })
                                    .join(' ');
                                hidden.val(fullString);
                            }
                            else {
                                var target = $(e.target),
                                    valueList = [],
                                    emptyIndex = [],
                                    fullIndex = [];

                                text.each(function (i, el) {
                                    var value = $(el).val() !== '' ? parseFloat($(el).val()) : null;
                                    valueList.push(value);

                                    if (value === null) {
                                        emptyIndex.push(i);
                                    }
                                    else {
                                        fullIndex.push(i);
                                    }
                                });

                                if (emptyIndex.length === 3 && fullIndex[0] === text.index(target)) {
                                    text.val(target.val());
                                    valueList = [target.val(), target.val(), target.val(), target.val()];
                                }

                                if (JSON.stringify(valueList) === JSON.stringify([null, null, null, null])) {
                                    hidden.val('');
                                }
                                else {
                                    hidden.val(valueList.map(function (k) {
                                        return ( k === null ? 0 : k ) + unit.val();
                                    }).join(' '));
                                }
                            }
                            if (hidden.attr('data-code')) {
                                var panelId = hidden.attr('data-panel');
                                var previewEl = $(".cleversoft-panels-iframe-preview").contents().find(panelId+" "+".global-element").first();
                                if (hidden.attr('data-code')) {
                                    switch(hidden.attr('data-attribute')) {
                                        case 'style':
                                            if (hidden.attr('data-mode') == 'hover') {
                                                previewEl.hover(function(){
                                                    previewEl.css(hidden.attr('data-code'),hidden.val());
                                                }, function(){ 
                                                    var normalStyle = $('input[data-code="'+hidden.attr('data-code')+'"][data-panel="'+panelId+'"][data-mode="normal"]').val();
                                                    previewEl.css(hidden.attr('data-code'),normalStyle);
                                                });  
                                            } else {
                                                previewEl.css(hidden.attr('data-code'),hidden.val());
                                                previewEl.hover(function(){
                                                    var normalStyle = $('input[data-code="'+hidden.attr('data-code')+'"][data-panel="'+panelId+'"][data-mode="hover"]').val();
                                                    if (normalStyle) {
                                                        previewEl.css(hidden.attr('data-code'),normalStyle);
                                                    } else {
                                                        previewEl.css(hidden.attr('data-code'),hidden.val());
                                                    }
                                                }, function(){ 
                                                    previewEl.css(hidden.attr('data-code'),hidden.val());
                                                });
                                            }
                                            
                                            break;
                                        case 'class':
                                            previewEl.addClass(hidden.val());
                                            break;
                                        case 'attribute':
                                            previewEl.attr(hidden.attr('data-code'),hidden.val());
                                            break;
                                        default:
                                    }
                                }
                            }
                        };

                        // Set the value when ever anything changes
                        text.change(setValue);
                        unit.change(setValue);
                    });
                }

            });

        }, {}],
        29: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = Backbone.View.extend({
                template: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-widget').html())),

                childTabTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-tab-items').html())),
                childItemTabTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-tabs-item-child').html())),

                childScrollToTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-scrollto-items').html())),

                childInnerrowTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-innerrow-items').html())),
                childItemInnerrowTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-innerrows-item-child').html())),

                childRowTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-row-items').html())),
                childItemRowTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-rows-item-child').html())),

                childTextboxTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-textbox-items').html())),
                childItemTextboxTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-textboxs-item-child').html())),

                childBannerTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-banner-items').html())),
                childItemBannerTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-banners-item-child').html())),
                innerChildItemBannerTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-banners-item-inner-child').html())),

                childSliderTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-slider-items').html())),
                childItemSliderTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-sliders-item-child').html())),


                //global item template global-item-template
                globalItemTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-global-item-template').html())),
                //global child itemplate template
                globalChildItemsTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-global-child-items-template').html())),

                tab: {id:'', value:'', data:{}, editDialog : []},
                innerrow: {id:'', value:'', data:{}, editDialog : [], modelData: []},
                row: {id:'', value:'', data:{}, editDialog : [], modelData: []},
                scrollto: {id:'', value:'', data:{}, editDialog : [], modelData: []},
                textbox: {id:'', value:'', data:{}, editDialog : []},
                banner: {id:'', value:'', data:{}, editDialog : []},
                slider: {id:'', value:'', data:{}, editDialog : []},
                total_innerrow_stack_add: 0,


                // The cell view that this widget belongs to
                cell: null,

                // The edit dialog
                dialog: null,

                events: {
                    'click .widget-edit': 'editHandler',
                    'click .title h4': 'titleClickHandler',
                    'click .actions .widget-duplicate': 'duplicateHandler',
                    'click .actions .widget-delete': 'deleteHandler',
                    'click .cs-element-insert': 'addChildWidget',
                    'change .change-text-if-on-banner-widget': 'changePanelButtonText'
                },

                /**
                 * Initialize the widget
                 */
                initialize: function () {
                    var thisView = this;
                    this.model.on('user_edit', this.editHandler, this);              // When a user wants to edit the widget model
                    this.model.on('user_duplicate', this.duplicateHandler, this);      // When a user wants to duplicate the widget model
                    this.model.on('destroy', this.onModelDestroy, this);
                    this.model.on('visual_destroy', this.visualDestroyModel, this);

                    this.model.on('change:values', this.onModelChange, this);
                    this.model.on('change:label', this.onLabelChange, this);
                    if(this.model.get('items')) this.model.get('items').on('add', this.onAddItem, this);

                    this.on('tab_content_changed',function(event, $tab){
                        var dialog = event[0];
                        if(!_.isUndefined(dialog.model.get('targetId'))) {
                            this.tab.editDialog[dialog.model.widget.get('widget_id').toString() + dialog.model.get('idx').toString() +dialog.model.get('targetId').toString()] = dialog;
                        }
                    },this);

                    this.on('row_content_changed',function(event, $tab){
                        var dialog = event[0];
                        if(!_.isUndefined(dialog.model.get('targetId'))) {
                            this.row.editDialog[dialog.model.widget.get('widget_id').toString() + dialog.model.get('idx').toString() +dialog.model.get('targetId').toString()] = dialog;
                        }
                    },this);

                    this.on('textbox_content_changed',function(event, $textbox){
                        var dialog = event[0];
                        if(!_.isUndefined(dialog.model.get('targetId'))) {
                            this.textbox.editDialog[dialog.model.widget.get('widget_id').toString() + dialog.model.get('idx').toString() +dialog.model.get('targetId').toString()] = dialog;
                        }
                    },this);

                    this.on('slider_content_changed',function(event, $textbox){
                        var dialog = event[0];
                        if(!_.isUndefined(dialog.model.get('targetId'))) {
                            this.slider.editDialog[dialog.model.widget.get('widget_id').toString() + dialog.model.get('idx').toString() +dialog.model.get('targetId').toString()] = dialog;
                        }
                    },this);

                    this.on('banner_content_changed',function(event, $banner){
                        var dialog = event[0];
                        //if(!_.isUndefined(dialog.model.get('targetId'))) {
                        this.banner.editDialog[dialog.model.get('widget_id')] = dialog;
                        //}
                    },this);

                    this.on('innerrow_content_changed',function(event, $innerrow){
                        //thisView.innerrow.data = event[0];
                        var dialog = event[0];
                        if(!_.isUndefined(dialog.model.get('targetId'))) {
                            this.innerrow.editDialog[dialog.model.widget.get('widget_id').toString() + dialog.model.get('idx').toString() + dialog.model.get('targetId').toString()] = dialog;
                        }
                    },this);
                },
                //remove empty item from builder
                removeEmptyItem: function( $items) {
                    //$items = $items.filter(function($item) {return _.size($item) > 0} );
                    _.each($items, function($temps, $key) {
                        if(_.size($temps) > 0) {
                            if($.isArray($temps)) {
                                $temps = $temps.filter(function($temp) {return _.size($temp) > 0} );
                            }
                            $items[$key] = $temps;
                        }
                    });

                    return $items;
                },
                /*
                 * change panel button text for button element
                 */
                changePanelButtonText: function(e){
                    var $$el = $(e.target);

                },
                /*
                 * convert to an array
                 */
                itemsToArray: function($items, $default, child) {
                    var thisView= this;
                    var tempItems = [];
                    switch (child){
                        case 'tab':
                            var tabTitle=[];
                            break;
                        case 'scrollto':
                            var scrolltoTitle=[];
                            break;
                        case 'banner':
                            var bannerTitle=[];
                            break;
                        case 'innerrow':
                            var innerrowTitle=[];
                            break;
                        case 'row':
                            var rowTitle=[];
                            break;
                    }

                    if($items.length > 0 ) {
                        if ($items.length < $default) {
                            var n = $default;
                        } else {
                            var n = $items.length;
                        }
                        if (child == 'slider') {
                            n = $items.length;
                        }
                        switch (child){
                            case 'tab':
                                if (!_.isUndefined(this.model.get('totalTabpanels')) && this.model.get('totalTabpanels') > 0) {
                                    n = this.model.get('totalTabpanels');
                                }

                                tabTitle = Array(n);
                                break;
                            case 'scrollto':
                                scrolltoTitle = Array(n);
                                break;
                            case 'banner':
                                bannerTitle = Array(n);
                                break;
                            case 'innerrow':
                                innerrowTitle= Array(n);
                                break;
                            case 'row':
                                rowTitle= Array(n);
                                break;
                        }

                        tempItems = Array(n).fill({});

                    }

                    $items.each(function(item, it) {
                        var nodeItems = [];
                        var temp = panelsOptions.widgets[item.get('class')] ? panelsOptions.widgets[item.get('class')].title : panelsOptions.widgets[item.get('type')].title;
                        item.widgetTitle = temp;
                        item.tabTitle = !_.isUndefined(item.get('tabTitle')) ? item.get('tabTitle') : undefined;
                        item.rowTitle = !_.isUndefined(item.get('rowTitle')) ? item.get('rowTitle') : undefined;
                        ///add item into the node
                        nodeItems.push(item);
                        if (tempItems[item.get('idx')].length === 0 || _.size(tempItems[item.get('idx')]) === 0) {
                            tempItems[item.get('idx')] = nodeItems.concat(tempItems[item.get('idx')]);
                        } else {
                            tempItems[item.get('idx')] = tempItems[item.get('idx')].concat(nodeItems);
                        }
                        switch (child){
                            case 'tab':
                                tabTitle[item.get('idx')] =  (item.get('tabTitle')) ? item.get('tabTitle') : undefined;
                                break;
                            case 'row':
                                rowTitle[item.get('idx')] =  (item.get('rowTitle')) ? item.get('rowTitle') : undefined;
                                break;
                            case 'banner':
                                bannerTitle[item.get('idx')] =  (item.get('bannerTitle')) ? item.get('bannerTitle') : undefined;
                                break;
                            case 'innerrow':
                                innerrowTitle[item.get('idx')] = (item.get('innerrowTitle')) ? item.get('innerrowTitle') : (thisView.model.get('panelsItems')[item.get('idx')].width);
                                break;
                            case 'row':
                                rowTitle[item.get('idx')] = (item.get('rowTitle')) ? item.get('rowTitle') : (thisView.model.get('panelsItems')[item.get('idx')].width);
                                break;
                        }
                    });
                    switch (child){
                        case 'tab':
                            this.model.set('tabTitleArray',tabTitle);
                            break;
                        case 'banner':
                            this.model.set('bannerTitleArray',bannerTitle);
                            break;
                        case 'innerrow':
                            this.model.set('innerrowTitleArray',innerrowTitle);
                            break;
                        case 'row':
                            this.model.set('rowTitleArray',rowTitle);
                            break;
                    }
                    return tempItems;
                },
                //set default item
                setDefaultPanelValue: function($items){
                    var thisView = this;
                    if (_.size($items) === 0) {
                        if(this.model.get('has_child') === 'innerrow' ) {
                            var innerrow_layout = !_.isUndefined(this.model.get('innerrow_layout')) ? this.model.get('innerrow_layout') : 0;
                            $items = this.model.get('layouts')[innerrow_layout];
                        } else if(this.model.get('has_child') === 'row' ) {
                            var row_layout = !_.isUndefined(this.model.get('row_layout')) ? this.model.get('row_layout') : 0;
                            $items = this.model.get('layouts')[row_layout];
                        } else if (this.model.get('has_child') === 'scrollto') {
                            $items = [{
                                title: '',
                                link: '',
                                style: ''
                            }];
                        }
                        return $items;
                    }
                    _.each($items, function($item, $key){
                        if(_.size($item) === 0 ) {
                            switch (thisView.model.get('has_child')) {
                                case 'tab' :
                                    if(!_.isUndefined(thisView.model.get('default_items')[$key])) {
                                        $items[$key] = thisView.model.get('default_items')[$key];
                                    }
                                    break;
                                case 'textbox' :
                                    if(!_.isUndefined(thisView.model.get('default_items')[$key])) {
                                        $items[$key] = thisView.model.get('default_items')[$key];
                                    }
                                    break;
                                case 'slider' :
                                    if(!_.isUndefined(thisView.model.get('default_items')[$key])) {
                                        $items[$key] = thisView.model.get('default_items')[$key];
                                    }
                                    break;
                                case 'banner' :
                                    if(!_.isUndefined(thisView.model.get('default_items')[$key])) {
                                        $items[$key] = thisView.model.get('default_items')[$key];
                                    }
                                    break;
                                case 'innerrow':
                                    var innerrow_layout = !_.isUndefined(thisView.model.get('innerrow_layout')) ? thisView.model.get('innerrow_layout') : thisView.model.get('values').innerrow_layout;
                                    if(!_.isUndefined(thisView.model.get('layouts')[innerrow_layout][$key])) {
                                        $items[$key] = thisView.model.get('layouts')[innerrow_layout][$key];
                                    }
                                    break;
                                case 'row':
                                    var row_layout = !_.isUndefined(thisView.model.get('row_layout')) ? thisView.model.get('row_layout') : thisView.model.get('values').row_layout;
                                    if(!_.isUndefined(thisView.model.get('layouts')[row_layout][$key])) {
                                        $items[$key] = thisView.model.get('layouts')[row_layout][$key];
                                    }
                                    break;
                                default:
                                    if(!_.isUndefined(thisView.model.get('default_items')[$key])) {
                                        $items[$key] = thisView.model.get('default_items')[$key];
                                    }
                            }
                        }
                    });
                    return $items;
                },
                /*
                 * add child widget for tab and row
                 */
                addChildWidget: function(e) {
                    var $click = $(e.currentTarget);
                    this.setActiveWidget();
                    this.cell.row.builder.model.set('current_condition', !_.isUndefined($click.attr('condition')) ? $click.attr('condition') : this.model.get('has_child'));
                    this.cell.row.builder.model.set('parentWIdx', !_.isUndefined($click.data('parentWIdx')) ? $click.data('parentWIdx') : $click.attr('parent-widget-id'));
                    this.cell.row.builder.model.set('idx', $click.attr('idx'));
                    this.cell.row.builder.model.set('inner_lv', $click.parents(".clever-wrap-identify").length);
                    this.cell.row.builder.model.set('data-key-filter', $click.attr('data-key-filter'));
                    var $$c = !_.isUndefined($click.attr('condition')) ? $click.attr('condition') : (!_.isUndefined($click.attr('data-key-filter')) ? $click.attr('data-key-filter') : false);
                    if(!_.isUndefined(this.cell.row.builder.model.get('child_filter'))) this.cell.row.builder.model.set('child_filter', undefined);
                    if($$c) this.cell.row.builder.model.set('child_filter', $$c);
                    var $$d = new panels.dialog.childWidgets();
                    this.model.set('add_from_button','true');
                    $$d.setBuilder(this.cell.row.builder);
                    $$d.openDialog();
                },
                /**
                 * Render the widget
                 */
                render: function (options) {
                    var thisView = this;
                    options = _.extend({'loadForm': false,  'loadLoopItem': true}, options);
                    this.setElement(this.template({
                        title: this.model.getWidgetField('title'),
                        description: this.model.getTitle(),
                        code: this.model.getWidgetField('code'),
                        widget_id : this.model.get('widget_id')
                    }));
                    //
                    if(this.model.get('items')) {
                        /*
                         * convert items from object to an array
                         */
                        switch(this.model.get('has_child')){
                            case 'tab':
                                var $default = this.model.get('totalTabpanels') ? this.model.get('totalTabpanels') : this.model.get('default_items').length;
                                break;
                            case 'scrollto':
                                var $default = this.model.get('totalScrollTopanels') ? this.model.get('totalScrollTopanels') : this.model.get('default_items').length;
                                break;
                            case 'textbox':
                                var $default = this.model.get('totalTextBoxpanels') ? this.model.get('totalTextBoxpanels') : this.model.get('default_items').length;
                                break;
                            case 'slider':
                                var $default = this.model.get('totalSliderpanels') ? this.model.get('totalSliderpanels') : 1;
                                break;
                            case 'banner':
                                var $default = this.model.get('totalBannerpanels') ? this.model.get('totalBannerpanels') : 1;
                                break;
                            case 'innerrow' :
                                var $default = this.model.get('totalInnerrowpanels') ? this.model.get('totalInnerrowpanels') : this.model.get('default_items').length;
                                break;
                            case 'row':
                                var $default = this.model.get('totalRowpanels') ? this.model.get('totalRowpanels') : this.model.get('default_items').length;
                                break;
                        }
                        var $items = this.itemsToArray(this.model.get('items'), $default, this.model.get('has_child'));
                        //remove empty item
                        if($items.length > 0) {
                            $items = this.removeEmptyItem($items);
                        }
                        //set default item

                        $items = this.setDefaultPanelValue($items);
                        if (_.isEmpty(this.model.get('widget_id'))) {
                            this.model.set('widget_id', panels.helpers.utils.generateUUID());
                        }

                        //set data in model;
                        if(!_.isEmpty($items)) {
                            if(_.isUndefined(thisView.model.get('panelsItems'))) {
                                var $panelsItems = [];
                                _.each($items, function($item, $key){
                                    if($item.length > 0) {
                                        $panelsItems.push($item[0].get('values').layout);
                                    } else {
                                        $panelsItems.push($item);
                                    }
                                });
                                if($panelsItems.length > 0) thisView.model.set('panelsItems', $panelsItems);
                            }
                        }
                        /*
                         * show the items
                         */

                        switch(this.model.get('has_child')) {
                            case 'tab' :
                                var totalTabpanels = this.model.get('totalTabpanels');
                                if(totalTabpanels > this.model.get('default_items').length) {
                                    var emptyTab = Array(totalTabpanels);
                                } else {
                                    var emptyTab = this.model.get('default_items');
                                }

                                $(this.childTabTemplate({'items': ($items.length == 0 ? emptyTab : $items) ,'widget_id': this.model.get('widget_id') ,'tabTitleArray' : this.model.get('tabTitleArray') })).data({}).insertAfter(this.$el.find('.cs-widget-wrapper'));
                                //if($items.length === 0) this.model.set('totalTabpanels', this.model.get('default_items').length);
                                //else this.model.set('totalTabpanels', $items.length);
                                if(_.isUndefined(this.model.get('totalTabpanels')) || this.model.get('totalTabpanels') === null) this.model.set('totalTabpanels', this.model.get('default_items').length);
                                /*
                                 * add event click on the child
                                 */
                                this.addEventClickChildTab($items);
                                this.addEventClickAddPanelTab(this.$el);
                                break;
                            case 'scrollto' :
                                var totalScrollTopanels = !_.isUndefined(this.model.get('totalScrollTopanels')) ? this.model.get('totalScrollTopanels') : this.model.get('default_items').length ;
                                var emptyScrollTo = Array(totalScrollTopanels).fill({});
                                $(this.childScrollToTemplate({'items': ($items.length == 0 ? emptyScrollTo : $items) ,'widget_id': this.model.get('widget_id'), 'panelsItems': thisView.model.get('panelsItems')})).data({}).insertAfter(this.$el.find('.cs-widget-wrapper'));
                                
                                if(_.isUndefined(this.model.get('totalScrollTopanels')) || this.model.get('totalScrollTopanels') === null) this.model.set('totalScrollTopanels', this.model.get('default_items').length);
                                /*
                                 * add event click on the child
                                 */
                                this.addEventClickChildScrollTo($items);
                                this.addEventClickAddPanelScrollTo(this.$el);
                                break;    

                            case 'textbox' :
                                var totalTextBoxpanels = this.model.get('totalTextBoxpanels');
                                if(totalTextBoxpanels > this.model.get('default_items').length) {
                                    var emptyTextbox = Array(totalTextBoxpanels);
                                } else {
                                    var emptyTextbox = this.model.get('default_items');
                                }
                                var banner_idx = _.size(this.model.widget.get('items')) <= 1 ? 0 : (_.size(this.model.widget.get('items')) - 1);


                                $(this.childTextboxTemplate({'items': ($items.length == 0 ? emptyTextbox : $items) , 'banner_idx': banner_idx ,'widget': this.model, 'widget_id': this.model.get('widget_id') })).data({}).insertAfter(this.$el.find('.cs-widget-wrapper'));
                                if(_.isUndefined(this.model.get('totalTextBoxpanels')) || this.model.get('totalTextBoxpanels') === null) this.model.set('totalTextBoxpanels', this.model.get('default_items').length);
                                /*
                                 * add event click on the child
                                 */
                                // this.addEventClickChildTextbox($items);
                                //if(!this.model.get('items').length) {
                                //    this.addTextboxChildElementDefault(this.$el);
                                //}
                                break;

                            case 'slider' :
                                var totalSliderpanels = this.model.get('totalSliderpanels');
                                if(totalSliderpanels > 1) {
                                    var emptySlider = Array(totalSliderpanels);
                                } else {
                                    var emptySlider = [];
                                }
                                $(this.childSliderTemplate({'items': ($items.length == 0 ? emptySlider : $items), 'widget': this.model,'widget_id': this.model.get('widget_id') })).data({}).insertAfter(this.$el.find('.cs-widget-wrapper'));
                                if(_.isUndefined(this.model.get('totalSliderpanels')) || this.model.get('totalSliderpanels') === null) this.model.set('totalSliderpanels', this.model.get('default_items').length);
                                /*
                                 * add event click on the child
                                 */
                                //this.addEventClickChildSlider($items);
                                //this.addEventClickAddPanelBanner(this.$el);
                                break;

                            case 'banner' :
                                var totalBannerpanels = this.model.get('totalBannerpanels');
                                if(totalBannerpanels > 1) {
                                    var emptyBanner = Array(totalBannerpanels);
                                } else {
                                    var emptyBanner = [];
                                }
                                $(this.childBannerTemplate({'items': ($items.length == 0 ? emptyBanner : $items), 'widget': this.model,'widget_id': this.model.get('widget_id') })).data({}).insertAfter(this.$el.find('.cs-widget-wrapper'));
                                if(_.isUndefined(this.model.get('totalBannerpanels')) || this.model.get('totalBannerpanels') === null) this.model.set('totalBannerpanels', this.model.get('default_items').length);
                                /*
                                 * add event click on the child
                                 */
                                //this.addEventClickChildBanner($items);
                                //this.addEventClickAddPanelBanner(this.$el);
                                break;

                            case 'innerrow' :
                                var totalInnerrowpanels = !_.isUndefined(this.model.get('totalInnerrowpanels')) ? this.model.get('totalInnerrowpanels') : this.model.get('default_items').length ;
                                var emptyInnerrow = Array(totalInnerrowpanels).fill({});
                                $(this.childInnerrowTemplate({'items': ($items.length === 0 ? emptyInnerrow :  $items),'widget_id': this.model.get('widget_id'),'innerrowTitleArray' : this.model.get('innerrowTitleArray') , 'panelsItems': thisView.model.get('panelsItems')})).data({}).insertAfter(this.$el.find('.cs-widget-wrapper'));

                                if(_.isUndefined(this.model.get('totalInnerrowpanels')) || this.model.get('totalInnerrowpanels') === null) this.model.set('totalInnerrowpanels', this.model.get('default_items').length);
                                /*
                                 * add event click on the child
                                 */
                                this.addEventClickChildInnerrow($items);
                                this.addEventClickAddPanelInnerrow(this.$el);
                                break;

                            case 'row' :
                                var totalRowpanels = !_.isUndefined(this.model.get('totalRowpanels')) ? this.model.get('totalRowpanels') : this.model.get('default_items').length ;
                                var emptyRow = Array(totalRowpanels).fill({});
                                $(this.childRowTemplate({'items': ($items.length === 0 ? emptyRow :  $items),'widget_id': this.model.get('widget_id'),'rowTitleArray' : this.model.get('rowTitleArray') , 'panelsItems': thisView.model.get('panelsItems')})).data({}).insertAfter(this.$el.find('.cs-widget-wrapper'));

                                if(_.isUndefined(this.model.get('totalRowpanels')) || this.model.get('totalRowpanels') === null) this.model.set('totalRowpanels', this.model.get('default_items').length);
                                /*
                                 * add event click on the child
                                 */
                                this.addEventClickChildRow($items);
                                this.addEventClickAddPanelRow(this.$el);
                                break;

                            default :
                                break;
                        };

                        //add child item into panel item
                        if(!_.isEmpty($items)  && options.loadLoopItem) {
                            _.each($items, function($item, $key){
                                if($item.length > 0) {
                                    _.each($item, function($target, $k){
                                        if($target.length === 0 || $.isEmptyObject($target)) return false;
                                        if (_.isUndefined($target.get('has_child'))) {
                                            var _wF = panelsOptions.widgets[$target.get('type')];
                                            if(!_.isUndefined(_wF.has_child)) {
                                                $target.set('has_child', _wF.has_child);
                                            }

                                        }
                                        //put while loop here with parent target widget id into OPTIONS param
                                        thisView.onAddItem($target, new panels.collection.childWidgets(),{'loadForm': false,'dialog': false,  'classInner': ''});//'parentWIdx': thisView.model.get('widget_id'), 'parentWidget': thisView.model,
                                    });
                                }
                            });
                        }
                    }

                    this.$el.data('view', this);

                    // Remove any unsupported actions
                    if (!this.cell.row.builder.supports('editWidget') || this.model.get('read_only')) {
                        this.$('.actions .widget-edit').remove();
                        this.$el.addClass('cs-widget-no-edit');
                    }
                    if (!this.cell.row.builder.supports('addWidget')) {
                        this.$('.actions .widget-duplicate').remove();
                        this.$el.addClass('cs-widget-no-duplicate');
                    }
                    if (!this.cell.row.builder.supports('deleteWidget')) {
                        this.$('.actions .widget-delete').remove();
                        this.$el.addClass('cs-widget-no-delete');
                    }
                    if (!this.cell.row.builder.supports('moveWidget')) {
                        this.$el.addClass('cs-widget-no-move');
                    }
                    if (!$.trim(this.$('.actions').html()).length) {
                        this.$('.actions').remove();
                    }

                    if (this.model.get('read_only')) {
                        this.$el.addClass('cs-widget-read-only');
                    }

                    if (_.size(this.model.get('values')) === 0 || options.loadForm) {
                        // If this widget doesn't have a value, create a form and save it
                        var dialog = this.getEditDialog();

                        // Save the widget as soon as the form is loaded
                        dialog.once('form_loaded', dialog.saveWidget, dialog);

                        // Setup the dialog to load the form
                        dialog.setupDialog();
                    }
                    return this;
                },
                /**
                 * Handle this widget being clicked on
                 *
                 * @param e
                 * @returns {boolean}
                 */
                handleWidgetClick: function (e) {
                    // Remove all existing selected cell indication for this builder
                    this.cell.row.builder.activeWidget = this.model;
                },
                /**
                 * This is triggered when ever a item widget is added to the cell collection.
                 *
                 * @param widget
                 */
                onAddItem: function (item, collection, options) {
                    options = _.extend({noAnimate: false, dialog : true, loopChilds: false}, options);

                    // Create the view for the widget
                    var view = new panels.view.childWidget({
                        model: item
                    });

                    view.widget = this;
                    if(_.isUndefined(view.widget.cell)) {
                        view.widget.cell = !_.isUndefined(item.widget.cell) ? item.widget.cell : (!_.isUndefined(this.widget.cell)?this.widget.cell:undefined);
                    }

                    if(_.isUndefined(view.builder)) {
                        view.builder = !_.isUndefined(item.builder) ? item.builder : (!_.isUndefined(this.builder) ?  this.builder : null);
                    }

                    if (_.isUndefined(item.isDuplicate)) {
                        item.isDuplicate = false;
                    }
                    // console.log(view);
                    // Render and load the form if this is a duplicate
                    view.render({
                        'loadForm': !_.isUndefined(options.loadForm) ? options.loadForm : (item.isDuplicate ? item.isDuplicate : 'true'),
                        'loadLoopItem': false
                    });
                    //var target = this.$el.find('.element-item-wrapper[data-id="#tab'+event[0].model.get('idx')+'"] .tab-item-elements');

                    //add.data({'editDialog': event[0]}).prependTo(target);
                    if((_.isUndefined(this.dialog) || !this.dialog) && options.dialog) {
                        // this.dialog = view.dialog;
                    }
                    var temp = panelsOptions.widgets[item.get('class')] ? panelsOptions.widgets[item.get('class')].title : panelsOptions.widgets[item.get('type')].title;
                    var $cap = _.isUndefined(item.get('values').button_text) ? undefined : item.get('values').button_text;
                    var $has_child = panelsOptions.widgets[item.get('class')] ? panelsOptions.widgets[item.get('class')].has_child : panelsOptions.widgets[item.get('type')].has_child;

                    //if running in a loop
                    if(!_.isUndefined(options.parentWidget)) {
                        var $parentHasChild = !_.isUndefined(options.parentWidget.get('has_child')) ? options.parentWidget.get('has_child') : this.model.get('has_child');
                    } else if (view.builder && !_.isUndefined(view.builder.model.get('child_filter'))) {
                        var $parentHasChild = view.builder.model.get('child_filter');
                        options.classInner = 'inner inner';
                    } else {
                        var $parentHasChild = this.model.get('has_child');
                    }

                    //
                    //if(!_.isUndefined(item.get('has_child'))) {
                    var innerClass = !_.isUndefined(options.classInner) ? options.classInner : '';
                    if (!_.isUndefined(item.is_inner) ||  _.isUndefined($parentHasChild)  || item.get('items').length > 0) {
                        var add = view.$el;
                    } else {
                        switch ($parentHasChild) {
                            case 'tab':
                                var itemId = this.$el.find('.element-item-wrapper[data-id="#tab'+ item.get('idx')+'"] .cs-tab-item-child-wrapper').length;
                                var add = $(this.childItemTabTemplate({'widget':{'title':temp}, 'widget_id': this.model.get('widget_id'), 'itemId':  itemId }));
                                break;
                            case 'textbox':
                                var itemId = this.$el.find('.element-item-wrapper[data-id="#textbox'+ item.get('idx')+'"] .cs-textbox-item-child-wrapper').length;
                                var add = $(this.childItemTextboxTemplate({'widget':{'title':temp, 'cap': $cap, 'has_child': $has_child}, 'widget_id': item.get('widget_id'), 'itemId':  itemId , 'innerClass': innerClass}));
                                break;
                            case 'slider':
                                var itemId = this.$el.find('.element-item-wrapper[data-id="#slider'+ item.get('idx')+'"] .cs-slider-item-child-wrapper').length;
                                var add = $(this.childItemSliderTemplate({'widget':{'title':temp}, 'widget_id': this.model.get('widget_id'), 'itemId':  itemId , 'innerClass': innerClass}));
                                break;
                            case 'banner':
                                var itemId = this.$el.find('.element-item-wrapper[data-id="#banner'+ item.get('idx')+'"] .cs-banner-item-child-wrapper').length;
                                var add = $(this.childItemBannerTemplate({'widget':{'title':temp, 'cap': $cap, 'has_child': $has_child}, 'widget_id': item.get('widget_id'), 'itemId':  itemId , 'innerClass': innerClass}));
                                break;
                            case 'innerrow':
                                var itemId = this.$el.find('.element-item-wrapper[data-id="#innerrow'+ item.get('idx')+'"] .cs-innerrow-item-child-wrapper').length;
                                var add = $(this.childItemInnerrowTemplate({'widget':{'title':temp}, 'widget_id': this.model.get('widget_id') , 'itemId':  itemId }));
                                break;
                            case 'row':
                                var itemId = this.$el.find('.element-item-wrapper[data-id="#row'+ item.get('idx')+'"] .cs-row-item-child-wrapper').length;
                                var add = $(this.childItemRowTemplate({'widget':{'title':temp}, 'widget_id': this.model.get('widget_id'), 'itemId':  itemId }));
                                break;
                        }
                    }
                    //set target id
                    //view.dialog.model.set('targetId', itemId);
                    add.data({'editDialog': view.dialog});
                    switch ($parentHasChild) {
                        case 'tab':
                            this.tab.editDialog[item.get('widget_id').toString()] = view.dialog;
                            break;
                        case 'scrollto':
                            this.scrollto.editDialog[item.get('widget_id').toString()] = view.dialog;
                            break;
                        case 'textbox':
                            this.textbox.editDialog[item.get('widget_id').toString()] = view.dialog;
                            break;
                        case 'slider':
                            this.slider.editDialog[item.get('widget_id').toString()] = view.dialog;
                            break;
                        case 'banner':
                            this.banner.editDialog[item.get('widget_id').toString()] = view.dialog;
                            break;
                        case 'innerrow':
                            this.innerrow.editDialog[item.get('widget_id').toString()] = view.dialog;
                            break;
                        case 'row':
                            this.row.editDialog[item.get('widget_id').toString()] = view.dialog;
                            break;
                    }

                    var thisView = this;

                    //add event click on the item
                    if (add.find('.panel-info').length === 0) {
                        var $$click = add.find('.title h4');
                        $$click.on('click',function(event){
                            window.storeModelItem = item;
                            window.currentEl = $(this);
                        });
                    } else {
                        var $$click = add.find('.panel-info').parent();
                        $$click.on('click',function(event){
                            if ((item.get('panelsItems') && item.get('panelsItems').length) || (typeof item.get('has_child') !== 'undefined' && item.get('has_child') == 'tab')) {
                                return;
                            }
                            window.storeWidgetModel = item;
                            thisView.titleClickHandler(event);
                            window.currentEl = $(this);
                        });
                    }

                    

                    //add = view.$el;

                    if (_.isUndefined(options.at) || collection.length <= 1) {
                        // Insert this at the end of the widgets container
                        switch ($parentHasChild) {
                            case 'tab':
                                // add.insertBefore(this.$el.find('.element-item-wrapper[data-id="#tab'+ item.get('idx')+'"] .cs-tab-item-button'));
                                if(!_.isUndefined(options.parentWIdx)) {
                                    add.insertBefore(this.$el.find('#'+options.parentWIdx+' .element-item-wrapper[data-id="#tab'+ item.get('idx')+'"] .cs-tab-item-button'));
                                } else {
                                    if(!_.isUndefined(this.cell.row.builder.model.get('parentWIdx'))) {
                                        add.insertBefore(this.$el.find('#'+this.cell.row.builder.model.get('parentWIdx')+' .element-item-wrapper[data-id="#tab'+ item.get('idx')+'"] .cs-tab-item-button'));
                                    } else {
                                        add.insertBefore(this.$el.find('.element-item-wrapper[data-id="#tab'+ item.get('idx')+'"] .cs-tab-item-button'));
                                    }
                                }
                                break;
                            case 'textbox':
                                if(_.isUndefined(options.parentWIdx)) {
                                    if ( _.isUndefined(this.cell.row.builder.model.get('idx')) && this.cell.row.builder.model.get('current_condition') === 'textbox' && !_.isUndefined(this.model.get('add_from_button') )){
                                        add = this.addPanelTextBox(add);
                                        var elBefore = this.$el.find('#'+ this.model.get('widget_id') + ' .cs-element-insert');
                                        elBefore.each(function() {
                                            if (item.builder.model.get('parentWIdx') == $(this).attr('parent-widget-id')) {
                                                add.insertBefore($(this));
                                                return false;
                                            }
                                        });
                                        this.model.set('totalTextBoxpanels', $('#'+ this.model.get('widget_id')).find('.element-item-wrapper').length);
                                    } else {
                                        add.appendTo(this.$el.find('#'+ this.model.get('widget_id') + ' .textbox-item[data-id="#textbox'+ item.get('idx')+'"]'));
                                    }

                                } else {
                                    var a = this.$el.find('div.textbox-item[data-widget-id="'+options.parentWIdx+'"][data-id="#textbox'+ item.get('idx')+'"]');
                                    add.appendTo(this.$el.find('div.textbox-item[data-widget-id="'+options.parentWIdx+'"][data-id="#textbox'+ item.get('idx')+'"]'));
                                }
                                this.$el.find('.clever-textbox-wrap-identify').each(function(index){
                                    $(this).attr('data-id', index);
                                });

                                break;
                            case 'slider':
                                if(_.isUndefined(options.parentWIdx)) {
                                    if ( _.isUndefined(this.cell.row.builder.model.get('idx')) && this.cell.row.builder.model.get('current_condition') === 'slider' && !_.isUndefined(this.model.get('add_from_button') )){
                                        add = this.addPanelSlider(add);
                                        var elBefore = this.$el.find('#'+ this.model.get('widget_id') + ' .cs-element-insert');
                                        elBefore.each(function() {
                                            if (item.builder.model.get('parentWIdx') == $(this).attr('parent-widget-id')) {
                                                add.insertBefore($(this));
                                                return false;
                                            }
                                        });
                                 
										this.model.set('totalSliderpanels', this.model.get('items').length);
										/*<<<*/
                                    } else {
                                        add.appendTo(this.$el.find('#'+ this.model.get('widget_id') + ' .slider-item[data-id="#slider'+ item.get('idx')+'"]'));
                                    }

                                } else {
                                    var a = this.$el.find('div.slider-item[data-widget-id="'+options.parentWIdx+'"][data-id="#slider'+ item.get('idx')+'"]');
                                    add.appendTo(this.$el.find('div.slider-item[data-widget-id="'+options.parentWIdx+'"][data-id="#slider'+ item.get('idx')+'"]'));
                                }
                                this.$el.find('.clever-slider-wrap-identify').each(function(index){
                                    $(this).attr('data-id', index);
                                });
                                break;
                            case 'banner':
                                if(_.isUndefined(options.parentWIdx)) {
                                    if ( _.isUndefined(this.cell.row.builder.model.get('idx')) && this.cell.row.builder.model.get('current_condition') === 'banner' && !_.isUndefined(this.model.get('add_from_button') )){
                                        add = this.addPanelBanner(add);
                                        var elBefore = this.$el.find('#'+ this.model.get('widget_id') + ' .cs-element-insert');
                                        elBefore.each(function() {
                                            if (item.builder.model.get('parentWIdx') == $(this).attr('parent-widget-id')) {
                                                add.insertBefore($(this));
                                                return false;
                                            }
                                        });
                                        this.model.set('totalBannerpanels', $('#'+ this.model.get('widget_id')).find('.element-item-wrapper').length);
                                    } else {
                                        add.appendTo(this.$el.find('#'+ this.model.get('widget_id') + ' .banner-item[data-id="#banner'+ item.get('idx')+'"]'));
                                    }

                                } else {                                   
                                    var a = this.$el.find('div.banner-item[data-widget-id="'+options.parentWIdx+'"][data-id="#banner'+ item.get('idx')+'"]');
                                    add.appendTo(this.$el.find('div.banner-item[data-widget-id="'+options.parentWIdx+'"][data-id="#banner'+ item.get('idx')+'"]'));                                   
                                }
                                this.$el.find('.clever-banner-wrap-identify').each(function(index){
                                    $(this).attr('data-id', index);
                                });
                                break;
                            case 'innerrow':
                                if(!_.isUndefined(options.parentWIdx)) {
                                    add.insertBefore(this.$el.find('#'+options.parentWIdx+' .element-item-wrapper[data-id="#innerrow'+ item.get('idx')+'"] .cs-innerrow-item-button'));
                                } else {
                                    if(!_.isUndefined(this.cell.row.builder.model.get('parentWIdx'))) {
                                        add.insertBefore(this.$el.find('#'+this.cell.row.builder.model.get('parentWIdx')+' .element-item-wrapper[data-id="#innerrow'+ item.get('idx')+'"] .cs-innerrow-item-button'));
                                    } else {
                                        add.insertBefore(this.$el.find('.element-item-wrapper[data-id="#innerrow'+ item.get('idx')+'"] .cs-innerrow-item-button'));
                                    }
                                }
                                break;
                            case 'row':
                                if(!_.isUndefined(options.parentWIdx)) {
                                    add.insertBefore(this.$el.find('#'+options.parentWIdx+' .element-item-wrapper[data-id="#row'+ item.get('idx')+'"] .cs-row-item-button'));
                                } else {
                                    if(!_.isUndefined(this.cell.row.builder.model.get('parentWIdx'))) {
                                        add.insertBefore(this.$el.find('#'+this.cell.row.builder.model.get('parentWIdx')+' .element-item-wrapper[data-id="#row'+ item.get('idx')+'"] .cs-row-item-button'));
                                    } else {
                                        add.insertBefore(this.$el.find('.element-item-wrapper[data-id="#row'+ item.get('idx')+'"] .cs-row-item-button'));
                                    }
                                }
                                break;

                        }

                    } else {
                        // We need to insert this at a specific position
                        switch ($parentHasChild) {
                            case 'tab':
                                add.insertAfter(this.$el.find('.element-item-wrapper[data-id="#tab'+ item.get('idx')+'"] .cs-tab-item-child-wrapper').last());
                                break;
                            case 'textbox':
                                if(_.isUndefined(options.parentWIdx)) {
                                    if ( _.isUndefined(this.cell.row.builder.model.get('idx')) && this.model.get('has_child') === 'textbox' && !_.isUndefined(this.model.get('add_from_button') )){
                                        add = this.addPanelTextBox(add);
                                        add.insertBefore(this.$el.find('#'+ this.model.get('widget_id') + ' .cs-element-insert'));
                                        this.model.set('totalTextBoxpanels', $('#'+ this.model.get('widget_id')).find('.element-item-wrapper').length);
                                    } else {
                                        add.appendTo(this.$el.find('#'+ this.model.get('widget_id') + ' .textbox-item[data-id="#textbox'+ item.get('idx')+'"]'));
                                    }

                                } else {
                                    if( this.$el.find('div[data-widget-id="'+options.parentWIdx+'"] .cs-element-item-button').length > 0 ) {
                                        add.insertBefore(this.$el.find('div[data-widget-id="'+options.parentWIdx+'"] .cs-element-item-button'));
                                    } else {
                                        var a = this.$el.find('div.textbox-item[data-widget-id="'+options.parentWIdx+'"][data-id="#textbox'+ item.get('idx')+'"]');
                                        add.appendTo(this.$el.find('div.textbox-item[data-widget-id="'+options.parentWIdx+'"][data-id="#textbox'+ item.get('idx')+'"]'));
                                    }
                                }
                                this.$el.find('.clever-textbox-wrap-identify').each(function(index){
                                    $(this).attr('data-id', index);
                                });
                                break;
                            case 'slider':
                                if(_.isUndefined(options.parentWIdx)) {
                                    if ( _.isUndefined(this.cell.row.builder.model.get('idx')) && this.model.get('has_child') === 'slider' && !_.isUndefined(this.model.get('add_from_button') )){
                                        add = this.addPanelSlider(add);
                                        add.insertBefore(this.$el.find('#'+ this.model.get('widget_id') + ' .cs-element-insert'));
                                        this.model.set('totalSliderpanels', $('#'+ this.model.get('widget_id')).find('.element-item-wrapper').length);
                                    } else {
                                        add.appendTo(this.$el.find('#'+ this.model.get('widget_id') + ' .slider-item[data-id="#slider'+ item.get('idx')+'"]'));
                                    }

                                } else {                                    
                                    if( this.$el.find('div[data-widget-id="'+options.parentWIdx+'"] .cs-element-item-button').length > 0 ) {
                                        add.insertBefore(this.$el.find('div[data-widget-id="'+options.parentWIdx+'"] .cs-element-item-button'));
                                    } else {
                                        var a = this.$el.find('div.slider-item[data-widget-id="'+options.parentWIdx+'"][data-id="#slider'+ item.get('idx')+'"]');
                                        add.appendTo(this.$el.find('div.slider-item[data-widget-id="'+options.parentWIdx+'"][data-id="#slider'+ item.get('idx')+'"]'));
                                    }
                                }
                                this.$el.find('.clever-slider-wrap-identify').each(function(index){
                                    $(this).attr('data-id', index);
                                });
                                break;   
                            case 'banner':
                                if(_.isUndefined(options.parentWIdx)) {
                                    if ( _.isUndefined(this.cell.row.builder.model.get('idx')) && this.model.get('has_child') === 'banner' && !_.isUndefined(this.model.get('add_from_button') )){
                                        add = this.addPanelBanner(add);
                                        add.insertBefore(this.$el.find('#'+ this.model.get('widget_id') + ' .cs-element-insert'));
                                        this.model.set('totalBannerpanels', $('#'+ this.model.get('widget_id')).find('.element-item-wrapper').length);
                                    } else {
                                        add.appendTo(this.$el.find('#'+ this.model.get('widget_id') + ' .banner-item[data-id="#banner'+ item.get('idx')+'"]'));
                                    }

                                } else {
                                    if( this.$el.find('div[data-widget-id="'+options.parentWIdx+'"] .cs-element-item-button').length > 0 ) {
                                        add.insertBefore(this.$el.find('div[data-widget-id="'+options.parentWIdx+'"] .cs-element-item-button'));
                                    } else {
                                        var a = this.$el.find('div.banner-item[data-widget-id="'+options.parentWIdx+'"][data-id="#banner'+ item.get('idx')+'"]');
                                        add.appendTo(this.$el.find('div.banner-item[data-widget-id="'+options.parentWIdx+'"][data-id="#banner'+ item.get('idx')+'"]'));
                                    }
                                }
                                this.$el.find('.clever-banner-wrap-identify').each(function(index){
                                    $(this).attr('data-id', index);
                                });
                                break;
                            case 'innerrow':
                                add.insertAfter(this.$el.find('.element-item-wrapper[data-id="#innerrow'+ item.get('idx')+'"] .cs-innerrow-item-child-wrapper').last());
                                break;
                            case 'row':
                                add.insertAfter(this.$el.find('.element-item-wrapper[data-id="#row'+ item.get('idx')+'"] .cs-row-item-child-wrapper').last());
                                break;
                        }

                    }

                    if (options.noAnimate === false) {
                        // We need an animation
                        view.visualCreate();
                    }
                    
                    //add inner child template into builder
                    if(_.size(item.get('items')) > 0) {
                        var $$c = item.get('items');
                        if(!_.isUndefined(options.classInner)) {
                            var innerClass = options.classInner + ' inner';
                        } else {
                            var innerClass = 'inner';
                        }

                        $$c.each(function($child, $l){
                            if($child.length === 0 || $.isEmptyObject($child)) return false;
                            if (_.isUndefined($child.get('has_child'))) {
                                var _wF = panelsOptions.widgets[$child.get('type')];
                                if(!_.isUndefined(_wF.has_child)) {
                                    $child.set('has_child', _wF.has_child);
                                }

                            }
                            thisView.onAddItem($child, new panels.collection.childWidgets(),{'loadForm': false,'dialog': false, 'loopChilds': true, 'parentWIdx': item.get('widget_id'), 'parentWidget': item, 'classInner': innerClass})
                        })
                    }
                },
                /*
                 * add event click for add more of inner element
                 */
                addEventAddButtonElement: function(add,$element,item) {

                },
                /*
                 * add event click for ADD more panel button
                 */
                addEventClickAddPanelTab: function($element) {
                    var thisView = this;
                    var $this = $element;
                    $element.find('.icon-container.tab').on('click', function(){                       
						var $tabs = $this.children().children('.element-items-wrapper').children('.element-item-wrapper');						
                        var $num_tabs = $tabs.length;
                        var $widget_id = $tabs.last().attr('data-widget-id');
                        var $html = $this.find('.empty_tab[data-widget-id = "'+$widget_id+'"]').html();
                        $html = $html.replace(/{\$key}/g,$num_tabs).replace('$remove','').replace('{$key+1}',$num_tabs+1);

                        var $contentTabHtml = {
                            'title': '<li data-widget-id="'+$widget_id+'" data-id="tab'+$num_tabs+'" class="has-own-click-event "><a data-widget-id="'+$widget_id+'" data-toggle="tab" data-id="tab'+$num_tabs+'" href="#tab'+$widget_id+$num_tabs+'">Tab '+($num_tabs+1)+' Title</a></li>',
                            'content': '<div data-widget-id="'+$widget_id+'" id="tab'+$widget_id+$num_tabs+'" class="tab-pane fade in has-own-click-event ">' +
                            '<div class="tab-widget-items"><div class="uxb-empty-message">'+ $.mage.__('Add elements from left sidebar') + '</div></div>' +
                            '</div>'
                        };
                        $('.cs-preview iframe').contents().find('div[data-id="'+$widget_id+'"] li').last().after($($contentTabHtml.title));
                        $('.cs-preview iframe').contents().find('div[data-id="'+$widget_id+'"] .tab-pane').last().after($($contentTabHtml.content));
                        $tabs.last().after($html);
                        //add event for tab item on sidebar builder                       
                        thisView.addEventClickChildTab([], true, $this.children().children('.element-items-wrapper').children('.element-item-wrapper').last().find('.tab-item'));						
                        //add click event for tab on preview section
                        $('.cs-preview iframe').contents().find('div[data-id="'+$widget_id+'"] li').last().on('click',function(){                        
                            $this.children().children('.element-items-wrapper').children('.element-item-wrapper').last().find('.tab-item').click();							
                        });
                        thisView.model.set('totalTabpanels',$this.children().children('.element-items-wrapper').children('.element-item-wrapper').length);
                    });
                },

                /*
                 * add event click for ADD more panel button
                 */
                addEventClickAddPanelScrollTo: function($element) {
                    var thisView = this;
                    var $this = $element;
                    $element.find('.icon-container.scrollto').on('click', function(){
                        var $scrolltos = $this.find('.element-items-wrapper > .element-item-wrapper');
                        var $num_scrolltos = $scrolltos.length;
                        var $widget_id = $scrolltos.last().attr('data-widget-id');
                        var $html = $this.find('.empty_scrollto[data-widget-id = "'+$widget_id+'"]').html();
                        $html = $html.replace(/{\$key}/g,$num_scrolltos).replace('$remove','').replace('{$key+1}',$num_scrolltos+1);

                        var $contentScrollToHtml = {
                            'title': '<li data-widget-id="'+$widget_id+'" data-id="scrollto'+$num_scrolltos+'" class="has-own-click-event "><a data-widget-id="'+$widget_id+'" data-toggle="scrollto" data-id="scrollto'+$num_scrolltos+'" href="#scrollto'+$widget_id+$num_scrolltos+'">ScrollTo '+($num_scrolltos+1)+' Title</a></li>',
                            'content': '<div data-widget-id="'+$widget_id+'" id="scrollto'+$widget_id+$num_scrolltos+'" class="scrollto-pane fade in has-own-click-event ">' +
                            '<div class="scrollto-widget-items"><div class="uxb-empty-message">'+ $.mage.__('Add elements from left sidebar') + '</div></div>' +
                            '</div>'
                        };

                        //add item to model
                        var modelData = {
                            'title': '',
                            'link': '',
                            'style': ''
                        };

                        thisView.model.get('panelsItems').push(modelData);

                        $('.cs-preview iframe').contents().find('div[data-id="'+$widget_id+'"] li').last().after($($contentScrollToHtml.title));
                        $('.cs-preview iframe').contents().find('div[data-id="'+$widget_id+'"] .scrollto-pane').last().after($($contentScrollToHtml.content));
                        $scrolltos.last().after($html);
                        //add event for tab item on sidebar builder
                        thisView.addEventClickChildScrollTo([], true, $this.find('.element-items-wrapper > .element-item-wrapper').last().find('.scrollto-item'));
                        //add click event for tab on preview section
                        $('.cs-preview iframe').contents().find('div[data-id="'+$widget_id+'"] li').last().on('click',function(){
                            $this.find('.element-items-wrapper > .element-item-wrapper').last().find('.scrollto-item').click();
                        });
                        thisView.model.set('totalScrollTopanels',$this.find('.element-items-wrapper > .element-item-wrapper').length);
                    });
                },
                /*
                 * add event click for ADD more panel button
                 */
                addTextboxChildElementDefault: function($element) {
                    var textAreaEditor = new panels.model.childWidget({
                        class:"CleverSoft\\CleverBuilder\\Block\\Builder\\Widget\\TextAreaEditor",
                        has_child:false,
                        idx :"0",
                        widget_id: panels.helpers.utils.generateUUID()
                    });
                    this.model.get('items').add(textAreaEditor);
                    // this.model.get('items').add(button);
                },

                /*
                 * add event click for ADD more panel button
                 */
                addPanelBanner: function($html) {
                    var thisView = this;
                    var $banners = $('#'+ this.model.get('widget_id')).find('.element-item-wrapper');
                    var $num_banners = $banners.length;
                    var $widget_id = this.model.get('widget_id');
                    var $temp = $('<div class="element-item-wrapper" data-widget-id="'+$widget_id+'" data-id="#banner'+$num_banners+'">'+
                        '<div data-widget-id="'+$widget_id+'" data-id="#banner'+$num_banners+'" class="banner-item"></div>'+
                        '</div>');
                    //add click event for banner on preview section
                    $temp.find('.banner-item').html($html);
                    return $temp;
                },

                /*
                 * add event click for ADD more panel button
                 */
                addPanelSlider: function($html) {
                    var thisView = this;
                    var $sliders = $('#'+ this.model.get('widget_id')).find('.element-item-wrapper');
                    var $num_sliders = $sliders.length;
                    var $widget_id = this.model.get('widget_id');
                    var $temp = $('<div class="element-item-wrapper" data-widget-id="'+$widget_id+'" data-id="#slider'+$num_sliders+'">'+
                        '<div data-widget-id="'+$widget_id+'" data-id="#slider'+$num_sliders+'" class="slider-item"></div>'+
                        '</div>');
                    //add click event for banner on preview section
                    $temp.find('.slider-item').html($html);
                    return $temp;
                },

                addPanelTextBox: function($html) {
                    var thisView = this;
                    var $textboxs = $('#'+ this.model.get('widget_id')).find('.element-item-wrapper');
                    var $num_textboxs = $textboxs.length;
                    var $widget_id = this.model.get('widget_id');
                    var $temp = $('<div class="element-item-wrapper" data-widget-id="'+$widget_id+'" data-id="#textbox'+$num_textboxs+'">'+
                        '<div data-widget-id="'+$widget_id+'" data-id="#textbox'+$num_textboxs+'" class="textbox-item"></div>'+
                        '</div>');
                    //add click event for banner on preview section
                    $temp.find('.textbox-item').html($html);
                    return $temp;
                },

                /*
                 * add event click for ADD more panel button
                 */
                addEventClickAddPanelInnerrow: function($element) {
                    var thisView = this;
                    var $this = $element;
                    $element.find('.icon-container.innerrow').on('click', function() {
                        thisView.total_innerrow_stack_add = thisView.total_innerrow_stack_add + 1;
                        var $widget_id = $(this).closest('.plus-icon').attr('parent-widget-id');
                        var $innerrows = $this.find('.element-items-wrapper > .element-item-wrapper[data-widget-id="'+$widget_id+'"]');
                        var $num_$innerrows = $innerrows.length;
                        var $html = $this.find('.empty_innerrow[data-widget-id="'+$widget_id+'"]').html();
                        $html = $html.replace(/{\$key}/g, $num_$innerrows).replace('$remove','').replace('{$key+1}',$num_$innerrows+1);
                        //
                        var $contentRowHtml = '<div class="column-wrap" data-width="12" data-animated="true">' +
                            '<div class="col-inner box-shadow-0 box-shadow-0-hover">' +
                            '<content content="col_grid" shortcode="shortcode">'+
                            '<div class="col_grid-empty">'+
                            '<div class="uxb-empty-message">' + $.mage.__('Add elements from left sidebar') + '</div>' +
                            '</div>'+
                            '</content>' +
                            '</div></div>'
                        ;

                        // add item to rowstack content
                        //insert panel to sidebar
                        var windowjQuery = $('.cs-preview iframe')[0].contentWindow.jQuery;
                       
                        windowjQuery('#innerrow' + $widget_id).append($contentRowHtml);

                        //add item to model
                        var modelData = {
                            "x": '',
                            "y": '',
                            "width": 12,
                            "height": 2,
                            "col_depth": 0,
                            "col_hover_depth": 0
                        };

                        thisView.model.get('panelsItems').push(modelData);

                        //insert panel to sidebar
                        $innerrows.last().after($html);
                        //add innerrowstack item

                        thisView.addEventClickChildInnerrow([], true, $this.find('.element-items-wrapper > .element-item-wrapper').last());
                        thisView.model.set('totalInnerrowpanels', $this.find('.element-items-wrapper > .element-item-wrapper').length);
                    });
                },
                /*
                 * add event click for ADD more panel button
                 */
                addEventClickAddPanelRow: function($element) {
                    var thisView = this;
                    var $this = $element;
                    $element.find('.icon-container.row').on('click', function(){
                        thisView.total_row_stack_add = thisView.total_row_stack_add + 1;
                        var $widget_id = $(this).closest('.plus-icon').attr('parent-widget-id');
                        var $rows = $this.find('.element-items-wrapper > .element-item-wrapper[data-widget-id="'+$widget_id+'"]');
                        var $num_$rows = $rows.length;
                        var $html = $this.find('.empty_row[data-widget-id="'+$widget_id+'"]').html();
                        $html = $html.replace(/{\$key}/g, $num_$rows).replace('$remove','').replace('{$key+1}',$num_$rows+1);
                        //
                        var $contentRowHtml = '<div class="column-wrap" data-width="12" data-animated="true">' +
                            '<div class="col-inner box-shadow-0 box-shadow-0-hover">' +
                            '<content content="col_grid" shortcode="shortcode">'+
                            '<div class="col_grid-empty">'+
                            '<div class="uxb-empty-message">' + $.mage.__('Add elements from left sidebar') + '</div>' +
                            '</div>'+
                            '</content>' +
                            '</div></div>'
                        ;

                        // add item to rowstack content
                        //insert panel to sidebar
                        var windowjQuery = $('.cs-preview iframe')[0].contentWindow.jQuery;
                       
                        windowjQuery('#row' + $widget_id).append($contentRowHtml);

                        //add item to model
                        var modelData = {
                            "x": '',
                            "y"      : '',
                            "width": 12,
                            "height": 2,
                            "col_depth": 0,
                            "col_hover_depth": 0
                        };

                        thisView.model.get('panelsItems').push(modelData);

                        //insert panel to sidebar
                        $rows.last().after($html);
                        //add rowstack item

                        thisView.addEventClickChildRow([], true, $this.find('.element-items-wrapper > .element-item-wrapper').last());
						thisView.model.set('totalRowpanels', (thisView.model.get('panelsItems').length));
                    });
                },

                /*
                 * add event click for row Item
                 */
                addEventClickChildRow: function($items, $addRow , $addRowElement){
                    var thisView = this;
                    if(!_.isUndefined($addRow)){
                        var $element = $addRowElement;
                    } else {
                        var $element = this.$el.find('.row-item');
                    }
                    $element.click(function(e){
                        thisView.setActiveWidget();
                        var $el = $(e.target).closest('.row-item');
                        if (!$el.length) return;
                        var $id = ($el.attr('data-id')).match(/\d+/g);
                        var $contentId = $el.attr('data-widget-id');
                        var $data = $(this).data('thisRow') ? $(this).data('thisRow') : {};
                        if(_.size($data) === 0 || $data.length === 0) {
                            if(!_.isUndefined($items[$id])) {
                                $data.data = $items[$id];
                            }
                        }
                        $id = $id[0];
                        var $val = $el.find('.widget-info').text();
                        var dialog = new panels.dialog.rowItemEditor();
                        dialog.setBuilder(thisView.cell.row.builder);
                        dialog.removeCurrentRowDialog();
                        dialog.setRow({id:$id , value: $val, data:  (_.size($data) > 0 ? $data.data : {}), editDialog : (_.size($data) > 0 ? $data.editDialog : []), modelData : thisView.model.get('panelsItems')[$id]});
                        thisView.cell.row.builder.model.set('current_condition','row');
                        dialog.setBuilder(thisView.cell.row.builder);
                        dialog.setCurrentRowDialog();
                        //
                        dialog.openDialog();
                    });
                },

                /*
                 * add event click for child tab
                 */
                addEventClickChildTab: function($items, $addTab , $addTabElement){
                    var thisView = this;
                    if(!_.isUndefined($addTab)){
                        var $element = $addTabElement;
                    } else {
                        var $element = this.$el.find('.tab-item');
                    }
                    $element.click(function(e){
                        thisView.setActiveWidget();
                        var $el = $(e.currentTarget);
                        //
                        var $id = ($el.attr('data-id')).match(/\d+/g);
                        var $contentId = $el.attr('data-widget-id');
                        var $data = $(this).data('thisTab') ? $(this).data('thisTab') : {};

                        if(_.size($data) === 0 || $data.length === 0) {
                            if(!_.isUndefined($items[$id])) {
                                $data.data = $items[$id];
                            }
                        }
                        $id = $id[0];
                        var $val = $el.find('.widget-info').text();
                        var dialog = new panels.dialog.tabItemEditor();
                        dialog.setBuilder(thisView.cell.row.builder);
                        dialog.removeCurrentTabDialog();
                        dialog.setTab({id:$id , value: $val, data:  (_.size($data) > 0 ? $data.data : {}), editDialog : (_.size($data) > 0 ? $data.editDialog : [])});
                        thisView.cell.row.builder.model.set('current_condition','tab');
                        dialog.setBuilder(thisView.cell.row.builder);
                        dialog.setCurrentTabDialog();
                        //change active tab in preview
                        $('.cs-preview iframe').contents().find('div[data-id="'+$contentId+'"] li').removeClass('active');
                        $('.cs-preview iframe').contents().find('div[data-id="'+$contentId+'"] li[data-id="tab'+$id+'"]').addClass('active');
                        $('.cs-preview iframe').contents().find('div[data-id="'+$contentId+'"] .tab-pane').removeClass('active');
                        $('.cs-preview iframe').contents().find('#tab'+$contentId+$id).addClass('active');
                        //
                        dialog.openDialog();
                    });
                },
                /*
                 * add event click for child scrollto
                 */
                addEventClickChildScrollTo: function($items, $addScrollTo , $addScrollToElement){
                    var thisView = this;
                    if(!_.isUndefined($addScrollTo)){
                        var $element = $addScrollToElement;
                    } else {
                        var $element = this.$el.find('.scrollto-item');
                    }
                    $element.click(function(e){
                        thisView.setActiveWidget();
                        var $el = $(e.currentTarget);
                        //
                        var $id = ($el.attr('data-id')).match(/\d+/g);
                        var $contentId = $el.attr('data-widget-id');
                        var $data = $(this).data('thisScrollTo') ? $(this).data('thisScrollTo') : {};

                        if(_.size($data) === 0 || $data.length === 0) {
                            if(!_.isUndefined($items[$id])) {
                                $data.data = $items[$id];
                            }
                        }
                        $id = $id[0];
                        var $val = $el.find('.widget-info').text();
                        var dialog = new panels.dialog.scrolltoItemEditor();
                        dialog.setBuilder(thisView.cell.row.builder);
                        dialog.removeCurrentScrollToDialog();
                        dialog.setScrollTo({id:$id , value: $val, data:  (_.size($data) > 0 ? $data.data : {}), editDialog : (_.size($data) > 0 ? $data.editDialog : []), modelData : thisView.model.get('panelsItems')[$id]});
                        thisView.cell.row.builder.model.set('current_condition','scrollto');
                        dialog.setBuilder(thisView.cell.row.builder);
                        dialog.setCurrentScrollToDialog();
                        //change active scrollto in preview
                        $('.cs-preview iframe').contents().find('div[data-id="'+$contentId+'"] li').removeClass('active');
                        $('.cs-preview iframe').contents().find('div[data-id="'+$contentId+'"] li[data-id="scrollto'+$id+'"]').addClass('active');
                        $('.cs-preview iframe').contents().find('div[data-id="'+$contentId+'"] .scrollto-pane').removeClass('active');
                        $('.cs-preview iframe').contents().find('#scrollto'+$contentId+$id).addClass('active');
                        //
                        dialog.openDialog();
                    });
                },

                /*
                 * add event click for child banner
                 */
                addEventClickChildBanner: function($items, $addBanner , $addBannerElement){
                    var thisView = this;
                    if(!_.isUndefined($addBanner)){
                        var $element = $addBannerElement;
                    } else {
                        var $element = this.$el.find('.banner-item');
                    }
                    $element.click(function(e){
                        thisView.setActiveWidget();
                        var $el = $(e.currentTarget);
                        //
                        var $id = ($el.attr('data-id')).match(/\d+/g);
                        var $contentId = $el.attr('data-widget-id');
                        var $data = $(this).data('thisBanner') ? $(this).data('thisBanner') : {};
                        if(_.size($data) === 0 || $data.length === 0) {
                            if(!_.isUndefined($items[$id])) {
                                $data.data = $items[$id];
                            }
                        }
                        $id = $id[0];
                        var $val = $el.find('.widget-info').text();
                        var dialog = new panels.dialog.bannerItemEditor();
                        dialog.setBuilder(thisView.cell.row.builder);
                        dialog.removeCurrentBannerDialog();
                        dialog.setBanner({id:$id , value: $val, data:  (_.size($data) > 0 ? $data.data : {}), editDialog : (_.size($data) > 0 ? $data.editDialog : [])});
                        thisView.cell.row.builder.model.set('current_condition','banner');
                        dialog.setBuilder(thisView.cell.row.builder);
                        dialog.setCurrentBannerDialog();
                        //change active banner in preview
                        $('.cs-preview iframe').contents().find('div[data-id="'+$contentId+'"] li').removeClass('active');
                        $('.cs-preview iframe').contents().find('div[data-id="'+$contentId+'"] li[data-id="banner'+$id+'"]').addClass('active');
                        $('.cs-preview iframe').contents().find('div[data-id="'+$contentId+'"] .banner-pane').removeClass('active');
                        $('.cs-preview iframe').contents().find('#banner'+$contentId+$id).addClass('active');
                        //
                        dialog.openDialog();
                    });
                },

                /*
                 * add event click for innerrow Item
                 */
                addEventClickChildInnerrow: function($items, $addInnerrow , $addInnerrowElement){
                    var thisView = this;
                    if(!_.isUndefined($addInnerrow)){
                        var $element = $addInnerrowElement;
                    } else {
                        var $element = this.$el.find('.innerrow-item');
                    }
                    $element.click(function(e){
                        thisView.setActiveWidget();
                        var $el = $(e.target).closest('.innerrow-item');
                        var $id = ($el.attr('data-id')).match(/\d+/g);
                        var $contentId = $el.attr('data-widget-id');
                        var $data = $(this).data('thisInnerrow') ? $(this).data('thisInnerrow') : {};
                        if(_.size($data) === 0 || $data.length === 0) {
                            if(!_.isUndefined($items[$id])) {
                                $data.data = $items[$id];
                            }
                        }
                        $id = $id[0];
                        var $val = $el.find('.widget-info').text();
                        var dialog = new panels.dialog.innerrowItemEditor();
                        dialog.setBuilder(thisView.cell.row.builder);
                        dialog.removeCurrentInnerrowDialog();
                        dialog.setInnerrow({id:$id , value: $val, data:  (_.size($data) > 0 ? $data.data : {}), editDialog : (_.size($data) > 0 ? $data.editDialog : []), modelData : thisView.model.get('panelsItems')[$id]});
                        thisView.cell.row.builder.model.set('current_condition','innerrow');
                        dialog.setBuilder(thisView.cell.row.builder);
                        dialog.setCurrentInnerrowDialog();
                        //
                        dialog.openDialog();
                    });
                },

                /**
                 * Display an animation that implies creation using a visual animation
                 */
                visualCreate: function () {
                    this.$el.hide().fadeIn('fast');
                },

                /**
                 * Get the dialog view of the form that edits this widget
                 *
                 * @returns {null}
                 */
                getEditDialog: function () {
                    if (this.dialog === null || window.storeWidgetModel) {
                        this.dialog = new panels.dialog.widget({
                            model: window.storeWidgetModel ? window.storeWidgetModel : this.model
                        });
                        this.dialog.setBuilder(this.cell.row.builder);

                        // Store the widget view
                        this.dialog.widgetView = this;
                        window.storeWidgetModel = false;
                        window.storeModelItem = false;
                    }

                    return this.dialog;
                },

                /**
                 * Handle clicking on edit widget.
                 *
                 * @returns {boolean}
                 */
                editHandler: function () {
                    // Create a new dialog for editing this
                    this.getEditDialog().openDialog();
                    //setWidgetActive
                    this.setActiveWidget();
                },

                setActiveWidget: function() {
                    this.cell.row.builder.activeWidget = this.cell.model.activeWidget = this.model;
                },

                titleClickHandler: function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    if (!this.cell.row.builder.supports('editWidget') || this.model.get('read_only')) {
                        return this;
                    }
                    if (!window.storeWidgetModel) {
                        if (window.storeModelItem) {
                            window.storeWidgetModel = window.storeModelItem;
                        } else {
                            window.storeWidgetModel = this.model;
                        }
                    }
                    this.editHandler();
                    this.handleWidgetClick();
                    return this;
                },

                /**
                 * Handle clicking on duplicate.
                 *
                 * @returns {boolean}
                 */
                duplicateHandler: function () {
                    // Add the history entry
                    this.cell.row.builder.addHistoryEntry('widget_duplicated');

                    // Create the new widget and connect it to the widget collection for the current row
                    var newWidget = this.model.clone(this.model.cell);

                    this.cell.model.get('widgets').add(newWidget, {
                        // Add this after the existing model
                        at: this.model.collection.indexOf(this.model) + 1
                    });

                    this.cell.row.builder.model.refreshPanelsData();
                    return this;
                },

                /**
                 * Copy the row to a cookie based clipboard
                 */
                copyHandler: function () {
                    panels.helpers.clipboard.setModel(this.model);
                },

                /**
                 * Handle clicking on delete.
                 *
                 * @returns {boolean}
                 */
                deleteHandler: function () {
                    this.model.trigger('visual_destroy');
                    return this;
                },

                onModelChange: function () {
                    // Update the description when ever the model changes
                    this.$('.description').html(this.model.getTitle());
                },

                onLabelChange: function (model) {
                    this.$('.title > h4').text(model.getWidgetField('title'));
                },

                /**
                 * When the model is destroyed, fade it out
                 */
                onModelDestroy: function () {
                    this.remove();
                },

                /**
                 * Visually destroy a model
                 */
                visualDestroyModel: function () {
                    // Add the history entry
                    this.cell.row.builder.addHistoryEntry('widget_deleted');

                    var thisView = this;
                    this.$el.fadeOut('fast', function () {
                        thisView.cell.row.resize();
                        thisView.model.destroy();
                        thisView.cell.row.builder.model.refreshPanelsData();
                        thisView.remove();
                    });
                    return this;
                },

                /**
                 * Build up the contextual menu for a widget
                 *
                 * @param e
                 * @param menu
                 */
                buildContextualMenu: function (e, menu) {
                    if (this.cell.row.builder.supports('addWidget')) {
                        menu.addSection(
                            'add-widget-below',
                            {
                                sectionTitle: panelsOptions.loc.contextual.add_widget_below,
                                searchPlaceholder: panelsOptions.loc.contextual.search_widgets,
                                defaultDisplay: panelsOptions.contextual.default_widgets
                            },
                            panelsOptions.widgets,
                            function (c) {
                                this.cell.row.builder.addHistoryEntry('widget_added');
                                var widget = new panels.model.widget({
                                    class: c
                                });
                                widget.cell = this.cell.model;

                                // Insert the new widget below
                                this.cell.model.get('widgets').add(widget, {
                                    // Add this after the existing model
                                    at: this.model.collection.indexOf(this.model) + 1
                                });

                                this.cell.row.builder.model.refreshPanelsData();
                            }.bind(this)
                        );
                    }

                    var actions = {};

                    if (this.cell.row.builder.supports('editWidget') && !this.model.get('read_only')) {
                        actions.edit = {title: panelsOptions.loc.contextual.widget_edit};
                    }

                    // Copy and paste functions
                    if (panels.helpers.clipboard.canCopyPaste()) {
                        actions.copy = {title: panelsOptions.loc.contextual.widget_copy};
                    }

                    if (this.cell.row.builder.supports('addWidget')) {
                        actions.duplicate = {title: panelsOptions.loc.contextual.widget_duplicate};
                    }

                    if (this.cell.row.builder.supports('deleteWidget')) {
                        actions.delete = {title: panelsOptions.loc.contextual.widget_delete, confirm: true};
                    }

                    if (!_.isEmpty(actions)) {
                        menu.addSection(
                            'widget-actions',
                            {
                                sectionTitle: panelsOptions.loc.contextual.widget_actions,
                                search: false
                            },
                            actions,
                            function (c) {
                                switch (c) {
                                    case 'edit':
                                        this.editHandler();
                                        break;
                                    case 'copy':
                                        this.copyHandler();
                                        break;
                                    case 'duplicate':
                                        this.duplicateHandler();
                                        break;
                                    case 'delete':
                                        this.visualDestroyModel();
                                        break;
                                }
                            }.bind(this)
                        );
                    }

                    // Lets also add the contextual menu for the entire row
                    this.cell.buildContextualMenu(e, menu);
                }

            });

        }, {}],
        30: [function (require, module, exports) {
            var $ = jQuery;

            var customHtmlWidget = {
                addWidget: function (idBase, widgetContainer, widgetId) {
                    var component = wp.customHtmlWidgets;

                    var fieldContainer = $('<div></div>');
                    var syncContainer = widgetContainer.find('.widget-content:first');
                    syncContainer.before(fieldContainer);

                    var widgetControl = new component.CustomHtmlWidgetControl({
                        el: fieldContainer,
                        syncContainer: syncContainer
                    });

                    widgetControl.initializeEditor();

                    // HACK: To ensure CodeMirror resize for the gutter.
                    widgetControl.editor.codemirror.refresh();

                    return widgetControl;
                }
            };

            module.exports = customHtmlWidget;

        }, {}],
        31: [function (require, module, exports) {
            var customHtmlWidget = require('./custom-html-widget');
            var mediaWidget = require('./media-widget');
            var textWidget = require('./text-widget');

            var jsWidget = {
                CUSTOM_HTML: 'custom_html',
                MEDIA_AUDIO: 'media_audio',
                MEDIA_GALLERY: 'media_gallery',
                MEDIA_IMAGE: 'media_image',
                MEDIA_VIDEO: 'media_video',
                TEXT: 'text',

                addWidget: function (widgetContainer, widgetId) {
                    var idBase = widgetContainer.find('> .id_base').val();
                    var widget;

                    switch (idBase) {
                        case this.CUSTOM_HTML:
                            widget = customHtmlWidget;
                            break;
                        case this.MEDIA_AUDIO:
                        case this.MEDIA_GALLERY:
                        case this.MEDIA_IMAGE:
                        case this.MEDIA_VIDEO:
                            widget = mediaWidget;
                            break;
                        case this.TEXT:
                            widget = textWidget;
                            break
                    }

                    widget.addWidget(idBase, widgetContainer, widgetId);
                },
            };

            module.exports = jsWidget;

        }, {"./custom-html-widget": 30, "./media-widget": 32, "./text-widget": 33}],
        32: [function (require, module, exports) {
            var $ = jQuery;

            var mediaWidget = {
                addWidget: function (idBase, widgetContainer, widgetId) {
                    var component = wp.mediaWidgets;

                    var ControlConstructor = component.controlConstructors[idBase];
                    if (!ControlConstructor) {
                        return;
                    }

                    var ModelConstructor = component.modelConstructors[idBase] || component.MediaWidgetModel;
                    var syncContainer = widgetContainer.find('> .widget-content');
                    var controlContainer = $('<div class="media-widget-control"></div>');
                    syncContainer.before(controlContainer);

                    var modelAttributes = {};
                    syncContainer.find('.media-widget-instance-property').each(function () {
                        var input = $(this);
                        modelAttributes[input.data('property')] = input.val();
                    });
                    modelAttributes.widget_id = widgetId;

                    var widgetModel = new ModelConstructor(modelAttributes);

                    var widgetControl = new ControlConstructor({
                        el: controlContainer,
                        syncContainer: syncContainer,
                        model: widgetModel,
                    });

                    widgetControl.render();

                    return widgetControl;
                }
            };

            module.exports = mediaWidget;

        }, {}],
        33: [function (require, module, exports) {
            var $ = jQuery;

            var textWidget = {
                addWidget: function (idBase, widgetContainer, widgetId) {
                    var component = wp.textWidgets;

                    var options = {};
                    var visualField = widgetContainer.find('.visual');
                    // 'visual' field and syncContainer were introduced together in 4.8.1
                    if (visualField.length > 0) {
                        // If 'visual' field has no value it's a legacy text widget.
                        if (!visualField.val()) {
                            return null;
                        }

                        var fieldContainer = $('<div></div>');
                        var syncContainer = widgetContainer.find('.widget-content:first');
                        syncContainer.before(fieldContainer);

                        options = {
                            el: fieldContainer,
                            syncContainer: syncContainer
                        };
                    } else {
                        options = {el: widgetContainer};
                    }

                    var widgetControl = new component.TextWidgetControl(options);

                    widgetControl.initializeEditor();

                    return widgetControl;
                }
            };

            module.exports = textWidget;

        }, {}],
        /*
         * open tab item editor
         */
        34: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = panels.view.dialog.extend({

                tabItemEditorTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-tabs-item-editor').html())),

                builder: null,
                dialogClass: 'cs-panels-dialog-tab-item-editor-layouts',
                dialogIcon: 'layouts',

                events: {
                    'click .cs-update': 'insertChildHandle',
                    'click .cs-close': 'closeChildHandle',
                    'click .cs-element-insert': 'addChildWidget',
                    'click .cs-toolbar .cs-delete': 'deleteHandler',
                    'click .cs-insert': 'insertChildHandle', 
                    'keyup .tab-item-title': 'changeItemTitle'                    
                },

                /**
                 * Initialize the tab item dialog.
                 */
                initializeDialog: function () {
                    var thisView = this;
                    this.on('open_dialog', function () {
                        //var a = thisView;
                    }, this);

                    this.on('open_dialog_complete', function () {
                        thisView.$('.current-tab-title').text('{'+thisView.tab.value+'}');
                        thisView.builder.trigger('builder_resize');
                    });

                    this.on('tab_item_model_change', function(event){                    
                    });

                    this.on('close_dialog', function( ) {                        
                    });
                    this.on('close_dialog_complete', function( ) {});
                },

                /**
                 * Render the tab item layouts dialog
                 */
                render: function () {
                    var thisView = this;
                    this.renderDialog(this.parseDialogContent($('#cleversoft-panels-tabs-item').html(), {}));
                    var widgetTab = {};                    
                    var $t = $(this.tabItemEditorTemplate({
                            'widget': widgetTab,
                            'id': this.tab.id,
                            'value':this.tab.value
                        })
                    );/// give the data here
                    $t.data(!_.isUndefined(thisView.tab.data) ? thisView.tab.data : {}).appendTo(this.$('.panels-tab-editor'));
                },
                /*
                 * set builder
                 */
                setBuilder: function(builder,model) {
                    if(!this.builder) {
                        if(!builder) {
                            var builderModel = new panels.model.builder();
                            // Now for the view to display the builder
                            this.builder = new panels.view.builder({
                                model: builderModel,
                                config: {}
                            });
                        } else {
                            this.builder = builder;
                        }

                    }
                },
                /*
                 *
                 */
                setEditDialog: function(dialog) {
                    this.tab.editDialog  = dialog;
                },
                /*
                 *
                 */
                insertChildHandle: function() {
                    this.updateDialog();
                    //reset tab object
                    this.tab = null;//reset tab object;
                    this.builder.model.set('current_condition', null);
                    this.builder.dialogs.tabItemEditor.$el.remove();
                    this.builder.dialogs.tabItemEditor = null;
                    //reload panel data
                    this.builder.model.refreshPanelsData();
                },
                /*
                 *
                 */
                closeChildHandle: function() {
                    this.updateDialog();
                    //reset tab object
                    this.tab = null;//reset tab object;
                    this.builder.model.set('current_condition', null);
                    this.builder.dialogs.tabItemEditor.$el.remove();
                    this.builder.dialogs.tabItemEditor = null;                   
                },
                /**
                 * When the user clicks delete.
                 *
                 * @returns {boolean}
                 */
                deleteHandler: function () {
                    var self = this;
					/*haunv8888 custom >>>*/
                    if (this.builder.activeWidget.get('items').models.length) {
						if (typeof this.tab.widget_id !== 'undefined') {
							let tmp_arr = [];
							let tmparr_innerrow = [];
							let $widget = this.tab.widget_id;
							_.each(self.builder.activeWidget.get('items').models, function($item, index) {
								if ($item.get('widget_id') == $widget) {
									$item.set('totalTabpanels',Number($item.get('totalTabpanels'))-1);
									if (typeof $item.get('tabTitleArray') !== 'undefined') $item.set('tabTitleArray',$item.get('tabTitleArray').filter((vl, key) => key != self.tab.id));
									if (typeof $item.get('has_child') !== 'undefined' && $item.get('has_child') === 'tab' && $item.get('items').models.length) {
										let arr_tmp = [];
										_.each($item.get('items').models, function($ite){
											let $idx = typeof $ite.get('idx') !== 'undefined' ? Number($ite.get('idx')): $ite.get('idx');
											if (typeof $idx !== 'undefined') {
												if ($idx == self.tab.id) {
													arr_tmp.push($ite);
													tmparr_innerrow.push($ite.get('widget_id'));
												} else if ($idx > Number(self.tab.id)) {
													$idx--;
													$ite.set('idx', $idx);
												}
											} else {
												arr_tmp.push($ite);
											}
										});
										if (_.size(arr_tmp)) {
											self.builder.activeWidget.get('items').models[index].get('items').remove(arr_tmp);
										}
									}
								} else if (typeof $item.get('parent_widget_id') !== 'undefined' && $item.get('parent_widget_id') === $widget ){
									let $idx = typeof $item.get('idx') !== 'undefined' ? Number($item.get('idx')): $item.get('idx');
									if (typeof $idx !== 'undefined') {
										if ($idx > Number(self.tab.id)) {
											$idx--;
											$item.set('idx', $idx);
										} else if ($idx == self.tab.id) {
											tmp_arr.push($item);
											tmparr_innerrow.push($item.get('widget_id'));
										}
									}
								} else if (typeof $item.get('parent_widget_id') !== 'undefined' && tmparr_innerrow.indexOf($item.get('parent_widget_id')) !== -1){
									tmp_arr.push($item);
									tmparr_innerrow.push($item.get('widget_id'));
								} else if ($item.get('items').models.length) {
									_.each($item.get('items').models, function($ite, _index){
										if ($ite.get('widget_id') == $widget) {
											$ite.set('totalTabpanels',Number($ite.get('totalTabpanels'))-1);
											if (typeof $ite.get('tabTitleArray') !== 'undefined') $ite.set('tabTitleArray',$ite.get('tabTitleArray').filter((vl, key) => key != self.tab.id));
											if (typeof $ite.get('has_child') !== 'undefined' && $ite.get('has_child') === 'tab' && $ite.get('items').models.length) {
												let arr_tmp = [];
												_.each($ite.get('items').models, function($_ite){
													let $idx = typeof $_ite.get('idx') !== 'undefined' ? Number($_ite.get('idx')): $_ite.get('idx');
													if (typeof $idx !== 'undefined') {
														if ($idx == self.tab.id) {
															arr_tmp.push($_ite);
															tmparr_innerrow.push($_ite.get('widget_id'));
														} else if ($idx > Number(self.tab.id)) {
															$idx--;
															$_ite.set('idx', $idx);
														}
													} else {
														arr_tmp.push($_ite);
													}
												});
												if (_.size(arr_tmp)) {
													self.builder.activeWidget.get('items').models[index].get('items').models[_index].get('items').remove(arr_tmp);
												}
											}
										}
									});
								}
							});
							if (_.size(tmp_arr)) {
								this.builder.activeWidget.get('items').remove(tmp_arr);
							}
						} else {
							let innerrow_arr = [];
							let tmp_arr = [];
							_.each(self.builder.activeWidget.get('items').models, function($it){
								console.log('$it >>>>', $it);
								let $idx = $it.get('idx');
								if (typeof $idx !== 'undefined') {
									if ((typeof $it.get('inner_lv') !== 'undefined' && $it.get('inner_lv') == 1) || typeof $it.get('inner_lv') === 'undefined') {
										if ($idx == self.tab.id) {
											//$it.destroy();
											tmp_arr.push($it);
											innerrow_arr.push($it.get('widget_id'));
										} else if (Number($idx) > Number(self.tab.id)) {
											$idx = Number($idx) -1;
											$it.set('idx', $idx);
										}
									} else {
										if (typeof $it.get('parent_widget_id') !== 'undefined' && innerrow_arr.indexOf($it.get('parent_widget_id')) !== -1) {
											tmp_arr.push($it);
											innerrow_arr.push($it.get('widget_id'));
										}
									}
								}
							});
							if (_.size(tmp_arr)) {
								self.builder.activeWidget.get('items').remove(tmp_arr);
							}
						}
                    } else {
                        if (this.builder.activeWidget.get('has_child') !== 'tab') {
                            var $panelsItems = this.tab.panels_item;
                            var $widget = this.tab.widget_id;
                        } else {
                            var $panelsItems = this.builder.activeWidget.get('panelsItems');
                            var $widget = this.builder.activeWidget.get('widget_id');
                        }
						if (!jQuery.isEmptyObject($panelsItems)) {
							delete $panelsItems[this.tab.id];
							$panelsItems = this.removeEmptyItem($panelsItems);
							if (this.builder.activeWidget.get('has_child') !== 'tab') {
								var $items = this.builder.activeWidget.get('items').models;
								_.each($items, function($item) {
									if ($item.get('widget_id') == $widget) {
										$item.get('items').models[self.tab.id].destroy();                  
									}
								});
							} else {
								this.builder.activeWidget.set('panelsItems',$panelsItems);
							}
						}
                    }
					
					if (typeof this.builder.activeWidget.get('totalTabpanels') !== 'undefined') {
						this.builder.activeWidget.set('totalTabpanels',this.builder.activeWidget.get('totalTabpanels')-1);
					}
					if (typeof $this.builder.activeWidget.get('tabTitleArray') !== 'undefined') this.builder.activeWidget.get('tabTitleArray',this.builder.activeWidget.get('tabTitleArray').filter((vl, key) => key != self.tab.id));			

                    this.updateDialog();

                    if (this.builder.activeWidget.get('has_child') !== 'tab') {
                        var $widget = this.tab.widget_id;
                    } else {
                        var $widget = this.builder.activeWidget.get('widget_id');
                    }

                    this.builder.$el.find('.element-item-wrapper[data-widget-id="'+$widget+'"]')[self.tab.id].remove();

                    this.builder.model.refreshPanelsData();
                    var $i = 0;
                    var $j = 0;

                    _.each(this.builder.$el.find('.element-item-wrapper[data-widget-id="'+$widget+'"]'), function($item) {
                        if ($($item).attr('databa-id') != '#tab{$key}') {
                            $($item).attr('data-id',"#tab"+$i);
                            $i++;
                        }
                        
                    });
									
                    let $contit = 0;
                    console.log('this.builder.$el ', this.builder.$el.attr('class'), $widget);
                    _.each(this.builder.$el.find('[class^=element-item-wrapper][data-widget-id='+$widget+']'), function($item){
                        if ($($item).find('span[class*=cs-element-insert][parent-widget-id='+$widget+']').attr('idx') !== '{$key}') {
                            $($item).find('span[class*=cs-element-insert][parent-widget-id='+$widget+']').attr('idx', $contit);
                            $contit++;
                        }
                    });				
					
                    _.each(this.builder.$el.find('.element-item-wrapper .tab-item[data-widget-id="'+$widget+'"]'), function($item) {
                        if ($($item).attr('data-id') != '#tab{$key}') {
                            $($item).attr('data-id',"#tab"+$j);
                            $j++;
                        }
                    });

                },
                removeEmptyItem: function( $items) {
                    var $index = 0;
                    var $new_items = new Array();

                    _.each($items, function($temps, $key) {
                        if(_.size($temps) > 0) {
                            if(jQuery.isArray($temps)) {
                                $temps = $temps.filter(function ($temp) {
                                    return _.size($temp) > 0
                                });
                            }
                            $new_items[$index] = $temps;
                            $index++;
                        }
                    });
                    return $new_items;
                },
                /*
                 * set option
                 */
                setTab: function(Tab) {
                    this.tab = Tab;
                },
                /*
                 *get tab data
                 */
                getTab: function(){
                    return this.tab;
                },
                /*
                 * show dialog add widget
                 */

                addChildWidget: function() {
                    this.updateDialog();
                    this.builder.dialogs.childWidgets.openDialog();                    
                },

                /*
                 *
                 */
                removeCurrentTabDialog: function(options) {
                    if(this.builder.dialogs.tabItemEditor) {
                        this.builder.dialogs.tabItemEditor.$el.remove();
                        this.builder.dialogs.tabItemEditor = null;
                    }
                },
                /*
                 *
                 */
                setCurrentTabDialog: function(options) {
                    this.builder.dialogs.tabItemEditor = this;
                    this.builder.model.set('tabTitle'+[this.tab.id], this.tab.value);
                },

                /*
                 * change tab item title on preview
                 */
                changeItemTitle: function(e) {
                    var $element = $(e.target);
                    var $val = $element.val();
                    if (this.builder.activeWidget.get('has_child') !== 'tab') {
                        var $widget_id = this.tab.widget_id;
                    } else {
                        var $widget_id = this.builder.activeWidget.get('widget_id');
                    }
                    $('.cs-preview iframe').contents().find('div[data-id="'+$widget_id+'"] a[data-id="'+$element.attr('name')+'"]').text($val);
                    this.$('.current-tab-title').text('{'+$val+'}');
                    this.tab.value = $val;
                    this.builder.$el.find('#'+$widget_id+' .tab-item[data-id="#tab'+this.tab.id+'"] .widget-info').text($val);                    
                    this.builder.model.set('tabTitle'+$widget_id+[this.tab.id], $val);
                }
            });

        }, {}],
        /*
         * begging tab panel ( tab item ) builder
         */
        35: [function (require, module, exports) {
            var panels = window.panels;

            module.exports = Backbone.Collection.extend({
                model: panels.model.childWidget,

                initialize: function () {

                }

            });

        }, {}],
        36: [function (require, module, exports) {
            /**
             * Model for an instance of a widget
             */
            module.exports = Backbone.Model.extend({

                widget: null,
                items: null,

                defaults: {
                    // The PHP Class of the widget
                    class: null,

                    // Is this class missing? Missing widgets are a special case.
                    missing: false,

                    // The values of the widget
                    values: {},

                    // Have the current values been passed through the widgets update function
                    raw: false,

                    // Visual style fields
                    style: {},

                    read_only: false,

                    title:'',

                    child_widget_id: ''
                },

                indexes: null,

                initialize: function () {
                    this.set('items', new panels.collection.childWidgets());
                    var widgetClass = this.get('class');
                    if (_.isUndefined(panelsOptions.widgets[widgetClass]) || !panelsOptions.widgets[widgetClass].installed) {
                        if(_.isUndefined(panelsOptions.widgets[this.get('type')])) this.set('missing', true);
                    }
                },

                /**
                 * @param field
                 * @returns {*}
                 */
                getWidgetField: function (field) {
                    if (_.isUndefined(panelsOptions.widgets[this.get('class')])) {
                        if (field === 'title' || field === 'description') {
                            if (!_.isUndefined(panelsOptions.widgets[this.get('type')]) ) {
                                return panelsOptions.widgets[this.get('type')][field];
                            } else return panelsOptions.loc.missing_widget[field];
                        } else {
                            return '';
                        }
                    } else if (this.has('label') && !_.isEmpty(this.get('label'))) {
                        // Use the label instead of the actual widget title
                        return this.get('label');
                    } else {
                        return panelsOptions.widgets[this.get('class')][field];
                    }
                },

                /**
                 * Move this widget model to a new cell. Called by the views.
                 *
                 * @param panels.model.cell newCell
                 * @param object options The options passed to the
                 *
                 * @return boolean Indicating if the widget was moved into a different cell
                 */
                moveToWidget: function (newWidget, options, at) {
                    options = _.extend({
                        silent: true
                    }, options);

                    this.widget = newWidget;
                    this.collection.remove(this, options);
                    newWidget.get('items').add(this, _.extend({
                        at: at
                    }, options));

                    // This should be used by views to reposition everything.
                    this.trigger('move_to_widget', newWidget, at);

                    return this;
                },

                /**
                 * Trigger an event on the model that indicates a user wants to edit it
                 */
                triggerEdit: function () {
                    this.trigger('user_edit', this);
                },

                /**
                 * Trigger an event on the widget that indicates a user wants to duplicate it
                 */
                triggerDuplicate: function () {
                    this.trigger('user_duplicate', this);
                },

                /**
                 * This is basically a wrapper for set that checks if we need to trigger a change
                 */
                setValues: function (values) {
                    var hasChanged = false;
                    if (JSON.stringify(values) !== JSON.stringify(this.get('values'))) {
                        hasChanged = true;
                    }

                    this.set('values', values, {silent: true});

                    if (hasChanged) {
                        // We'll trigger our own change events.
                        // NB: Must include the model being changed (i.e. `this`) as a workaround for a bug in Backbone 1.2.3
                        this.trigger('change', this);
                        this.trigger('change:values');
                    }
                },

                /**
                 * Create a clone of this widget attached to the given cell.
                 *
                 * @param {panels.model.cell} cell The cell model we're attaching this widget clone to.
                 * @returns {panels.model.widget}
                 */
                clone: function (widget, options) {
                    if (_.isUndefined(widget)) {
                        widget = this.widget;
                    }

                    var clone = new this.constructor(this.attributes);

                    // Create a deep clone of the original values
                    var cloneValues = JSON.parse(JSON.stringify(this.get('values')));

                    // We want to exclude any fields that start with _ from the clone. Assuming these are internal.
                    var cleanClone = function (vals) {
                        _.each(vals, function (el, i) {
                            if (_.isString(i) && i[0] === '_') {
                                delete vals[i];
                            }
                            else if (_.isObject(vals[i])) {
                                cleanClone(vals[i]);
                            }
                        });

                        return vals;
                    };
                    cloneValues = cleanClone(cloneValues);

                    if (this.get('class') === "CleverSoft_Panels_Widgets_Layout") {
                        // Special case of this being a layout widget, it needs a new ID
                        cloneValues.builder_id = Math.random().toString(36).substr(2);
                    }

                    clone.set('child_widget_id', '');
                    clone.set('values', cloneValues, {silent: true});
                    clone.set('collection', widget.get('items'), {silent: true});
                    clone.cell = cell;

                    // This is used to force a form reload later on
                    clone.isDuplicate = true;

                    return clone;
                },

                /**
                 * Gets the value that makes most sense as the title.
                 */
                getTitle: function () {
                    var widgetData = panelsOptions.widgets[this.get('class')];

                    if (_.isUndefined(widgetData)) {
                        if(!_.isUndefined(panelsOptions.widgets[this.get('type')])) {
                            return panelsOptions.widgets[this.get('type')].description;
                        } else {
                            if (!_.isUndefined(this.get('type'))) {
                                return this.get('type').replace(/_/g, ' ');
                            }
                            return;
                        }
                    }
                    else if (!_.isUndefined(widgetData.panels_title)) {
                        // This means that the widget has told us which field it wants us to use as a title
                        var a  = 'a';
                        if (widgetData.panels_title === false) {
                            return panelsOptions.widgets[this.get('class')].description;
                        }
                    }

                    var values = this.get('values');

                    // Create a list of fields to check for a title
                    var titleFields = ['title', 'text'];

                    for (var k in values) {
                        if (values.hasOwnProperty(k)) {
                            titleFields.push(k);
                        }
                    }

                    titleFields = _.uniq(titleFields);

                    for (var i in titleFields) {
                        if (
                            !_.isUndefined(values[titleFields[i]]) &&
                            _.isString(values[titleFields[i]]) &&
                            values[titleFields[i]] !== '' &&
                            values[titleFields[i]] !== 'on' &&
                            titleFields[i][0] !== '_' && !jQuery.isNumeric(values[titleFields[i]])
                        ) {
                            var title = values[titleFields[i]];
                            title = title.replace(/<\/?[^>]+(>|$)/g, "");
                            var parts = title.split(" ");
                            parts = parts.slice(0, 20);
                            return parts.join(' ');
                        }
                    }

                    // If we still have nothing, then just return the widget description
                    return this.getWidgetField('description');
                }

            });

        }, {}],
        37: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = Backbone.View.extend({
                template: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-widget').html())),

                childTabTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-tab-items').html())),
                childItemTabTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-tabs-item-child').html())),

                childRowTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-row-items').html())),
                childItemRowTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-rows-item-child').html())),

                childScrollToTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-scrollto-items').html())),

                childInnerrowTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-innerrow-items').html())),
                childItemInnerrowTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-innerrows-item-child').html())),

                childTextboxTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-textbox-items').html())),
                childItemTextboxTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-textboxs-item-child').html())),


                childBannerTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-banner-items').html())),
                childItemBannerTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-banners-item-child').html())),
                innerChildItemBannerTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-banners-item-inner-child').html())),

                childSliderTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-slider-items').html())),
                childItemSliderTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-builder-sliders-item-child').html())),


                tab: {id:'', value:'', data:{}, editDialog : []},
                innerrow: {id:'', value:'', data:{}, editDialog : [], modelData: []},
                row: {id:'', value:'', data:{}, editDialog : [], modelData: []},
                textbox: {id:'', value:'', data:{}, editDialog : []},
                banner: {id:'', value:'', data:{}, editDialog : []},
                slider: {id:'', value:'', data:{}, editDialog : []},
                total_innerrow_stack_add: 0,



                // The cell view that this widget belongs to
                cell: null,

                // The edit dialog
                dialog: null,

                events: {
                    'click .widget-edit': 'editHandler',
                    'click .title h4': 'titleClickHandler',
                    'click .actions .widget-duplicate': 'duplicateHandler',
                    'click .actions .widget-delete': 'deleteHandler'
                },

                /**
                 * Initialize the widget
                 */
                initialize: function () {
                    this.model.on('user_edit', this.editHandler, this);              // When a user wants to edit the widget model
                    this.model.on('user_duplicate', this.duplicateHandler, this);      // When a user wants to duplicate the widget model
                    this.model.on('destroy', this.onModelDestroy, this);
                    this.model.on('visual_destroy', this.visualDestroyModel, this);

                    this.model.on('change:values', this.onModelChange, this);
                    this.model.on('change:label', this.onLabelChange, this);
                    this.model.get('items').on('add', this.onAddItem, this);

                    this.on('tab_content_changed',function(event, $tab){
                        var dialog = event[0];
                        if(!_.isUndefined(dialog.model.get('targetId'))) {
                            this.tab.editDialog[dialog.model.widget.get('widget_id').toString() + dialog.model.get('idx').toString() +dialog.model.get('targetId').toString()] = dialog;
                        }
                    },this);

                    this.on('row_content_changed',function(event, $tab){
                        var dialog = event[0];
                        if(!_.isUndefined(dialog.model.get('targetId'))) {
                            this.row.editDialog[dialog.model.widget.get('widget_id').toString() + dialog.model.get('idx').toString() +dialog.model.get('targetId').toString()] = dialog;
                        }
                    },this);

                    this.on('scrollto_content_changed',function(event, $tab){
                        var dialog = event[0];
                        if(!_.isUndefined(dialog.model.get('targetId'))) {
                            this.scrollto.editDialog[dialog.model.widget.get('widget_id').toString() + dialog.model.get('idx').toString() +dialog.model.get('targetId').toString()] = dialog;
                        }
                    },this);

                    this.on('textbox_content_changed',function(event, $textbox){
                        var dialog = event[0];
                        if(!_.isUndefined(dialog.model.get('targetId'))) {
                            this.textbox.editDialog[dialog.model.widget.get('widget_id').toString() + dialog.model.get('idx').toString() +dialog.model.get('targetId').toString()] = dialog;
                        }
                    },this);

                    this.on('slider_content_changed',function(event, $textbox){
                        var dialog = event[0];
                        if(!_.isUndefined(dialog.model.get('targetId'))) {
                            this.slider.editDialog[dialog.model.widget.get('widget_id').toString() + dialog.model.get('idx').toString() +dialog.model.get('targetId').toString()] = dialog;
                        }
                    },this);

                    this.on('banner_content_changed',function(event, $banner){
                        var dialog = event[0];
                        //if(!_.isUndefined(dialog.model.get('targetId'))) {
                        this.banner.editDialog[dialog.model.get('widget_id')] = dialog;
                        //}
                    },this);

                    this.on('innerrow_content_changed',function(event, $innerrow){
                        //thisView.innerrow.data = event[0];
                        var dialog = event[0];
                        if(!_.isUndefined(dialog.model.get('targetId'))) {
                            this.innerrow.editDialog[dialog.model.widget.get('widget_id').toString() + dialog.model.get('idx').toString() + dialog.model.get('targetId').toString()] = dialog;
                        }
                    },this);
                },

                //remove empty item from builder
                removeEmptyItem: function( $items) {
                    //$items = $items.filter(function($item) {return _.size($item) > 0} );
                    _.each($items, function($temps, $key) {
                        if(_.size($temps) > 0) {
                            if($.isArray($temps)) {
                                $temps = $temps.filter(function($temp) {return _.size($temp) > 0} );
                            }
                            $items[$key] = $temps;
                        }
                    });
                    return $items;
                },
                /*
                 * change panel button text for button element
                 */
                changePanelButtonText: function(e){
                    var $$el = $(e.target);

                },
                /*
                 * convert to an array
                 */
                itemsToArray: function($items, $default, child) {
                    var thisView= this;
                    var tempItems = [];
                    switch (child){
                        case 'tab':
                            var tabTitle=[];
                            break;
                        case 'scrollto':
                            var scrolltoTitle=[];
                            break;
                        case 'banner':
                            var bannerTitle=[];
                            break;
                        case 'innerrow':
                            var innerrowTitle=[];
                            break;
                        case 'row':
                            var rowTitle=[];
                            break;
                    }
                    if($items.length > 0 ) {
                        if ($items.length < $default) {
                            var n = $default;
                        } else {
                            var n = $items.length;
                        }
                        if (child == 'slider') {
                            n = $items.length;
                        }
                        tempItems = Array(n).fill({});

                        switch (child){
                            case 'tab':
                                tabTitle = Array(n);
                                break;
                            case 'scrollto':
                                scrolltoTitle = Array(n);
                                break;
                            case 'banner':
                                bannerTitle = Array(n);
                                break;
                            case 'innerrow':
                                innerrowTitle= Array(n);
                                break;
                            case 'row':
                                rowTitle = Array(n);
                                break;
                        }

                    }

                    $items.each(function(item, it) {
                        var nodeItems = [];
                        var temp = panelsOptions.widgets[item.get('class')] ? panelsOptions.widgets[item.get('class')].title : panelsOptions.widgets[item.get('type')].title;
                        item.widgetTitle = temp;
                        item.tabTitle = !_.isUndefined(item.get('tabTitle')) ? item.get('tabTitle') : undefined;
                        item.rowTitle = !_.isUndefined(item.get('rowTitle')) ? item.get('rowTitle') : '12';
                        ///add item into the node
                        nodeItems.push(item);
                        if (_.size(tempItems[item.get('idx')]) === 0 || _.size(tempItems[item.get('idx')]) === 0) {
                            tempItems[item.get('idx')] = nodeItems.concat(tempItems[item.get('idx')]);
                        } else {
                            tempItems[item.get('idx')] = tempItems[item.get('idx')].concat(nodeItems);
                        }
                        switch (child){
                            case 'tab':
                                tabTitle[item.get('idx')] =  (item.get('tabTitle')) ? item.get('tabTitle') : undefined;
                                break;
                            case 'banner':
                                bannerTitle[item.get('idx')] =  (item.get('bannerTitle')) ? item.get('bannerTitle') : undefined;
                                break;
                            case 'innerrow':
                                innerrowTitle[item.get('idx')] = (item.get('innerrowTitle')) ? item.get('innerrowTitle') : (thisView.model.get('panelsItems')[item.get('idx')].width);
                                break;
                            case 'row':
                                rowTitle[item.get('idx')] =  (item.get('rowTitle')) ? item.get('rowTitle') : (thisView.model.get('panelsItems')[item.get('idx')].width);
                                break;
                        }
                    });
                    switch (child){
                        case 'tab':
                            this.model.set('tabTitleArray',tabTitle);
                            break;
                        case 'scrollto':
                        case 'banner':
                            this.model.set('bannerTitleArray',bannerTitle);
                            break;
                        case 'innerrow':
                            this.model.set('innerrowTitleArray',innerrowTitle);
                            break;
                        case 'row':
                            this.model.set('rowTitleArray',rowTitle);
                            break;
                    }
                    return tempItems;
                },
                //set default item
                setDefaultPanelValue: function($items){
                    var thisView = this;
                    if (_.size($items) === 0) {
                        if(this.model.get('has_child') === 'innerrow' ) {
                            var innerrow_layout = !_.isUndefined(this.model.get('innerrow_layout')) ? this.model.get('innerrow_layout') : 0;
                            $items = this.model.get('layouts')[innerrow_layout];
                        } else if(this.model.get('has_child') === 'row' ) {
                            var row_layout = !_.isUndefined(this.model.get('row_layout')) ? this.model.get('row_layout') : 0;
                            $items = this.model.get('layouts')[row_layout];
                        }
                        return $items;
                    }
                    _.each($items, function($item, $key){
                        if(_.size($item) === 0 ) {
                            switch (thisView.model.get('has_child')) {
                                case 'tab' :
                                    if(!_.isUndefined(thisView.model.get('default_items')[$key])) {
                                        $items[$key] = thisView.model.get('default_items')[$key];
                                    }
                                    break;
                                case 'textbox' :
                                    if(!_.isUndefined(thisView.model.get('default_items')[$key])) {
                                        $items[$key] = thisView.model.get('default_items')[$key];
                                    }
                                    break;
                                case 'slider' :
                                    if(!_.isUndefined(thisView.model.get('default_items')[$key])) {
                                        $items[$key] = thisView.model.get('default_items')[$key];
                                    }
                                    break;
                                case 'banner' :
                                    if(!_.isUndefined(thisView.model.get('default_items')[$key])) {
                                        $items[$key] = thisView.model.get('default_items')[$key];
                                    }
                                    break;
                                case 'innerrow':
                                    var innerrow_layout = !_.isUndefined(thisView.model.get('innerrow_layout')) ? thisView.model.get('innerrow_layout') : '0';
                                    if(!_.isUndefined(thisView.model.get('layouts')[innerrow_layout][$key])) {
                                        $items[$key] = thisView.model.get('layouts')[innerrow_layout][$key];
                                    }
                                    break;
                                case 'row':
                                    var row_layout = !_.isUndefined(thisView.model.get('row_layout')) ? thisView.model.get('row_layout') : '0';
                                    if(!_.isUndefined(thisView.model.get('layouts')[row_layout][$key])) {
                                        $items[$key] = thisView.model.get('layouts')[row_layout][$key];
                                    }
                                    break;
                                default:
                                    if(!_.isUndefined(thisView.model.get('default_items')[$key])) {
                                        $items[$key] = thisView.model.get('default_items')[$key];
                                    }
                            }
                        }
                    });
                    return $items;
                },
                /*
                 * add child widget for tab and innerrow
                 */
                addChildWidget: function(e) {
                    var $click = $(e.currentTarget);
                    this.setActiveWidget();
                    this.cell.row.builder.model.set('current_condition', this.model.get('has_child'));
                    this.cell.row.builder.model.set('parentWIdx', $click.data('parentWIdx'));
                    this.cell.row.builder.model.set('idx', $click.attr('idx'));
                    //if(this.dialog !== null) {
                    //    this.dialog.builder.dialogs.childWidgets.openDialog();
                    //} else {
                    //    this.cell.row.builder.dialogs.childWidgets.openDialog();
                    //}
                    //create dialog
                    var $$c = !_.isUndefined($click.attr('data-key-filter')) ? $click.attr('data-key-filter'): false;
                    if(!_.isUndefined(this.cell.row.builder.model.set('child_filter'))) this.cell.row.builder.model.set('child_filter', undefined);
                    if($$c) this.cell.row.builder.model.set('child_filter', $$c);
                    var $$d = new panels.dialog.childWidgets();
                    this.model.set('add_from_button','true');
                    //this.model.set('parentWIdx', $click.data('parentWIdx'));
                    $$d.setBuilder(this.cell.row.builder);
                    $$d.openDialog();

                    //this.builder.model.refreshPanelsData();
                },
                /**
                 * Render the widget
                 */
                render: function (options) {
                    var thisView = this;
                    options = _.extend({'loadForm': false,  'loadLoopItem': true}, options);
                    this.setElement(this.template({
                        title: this.model.getWidgetField('title'),
                        description: this.model.getTitle(),
                        code: this.model.getWidgetField('code'),
                        widget_id : this.model.get('widget_id')
                    }));
                    //
                    if(this.model.get('items')) {
                        /*
                         * convert items from object to an array
                         */
                        if(_.isUndefined(this.model.get('default_items'))) {
                            if (!_.isUndefined(this.model.get('type'))) {
                                this.model.set('default_items', panelsOptions.widgets[this.model.get('type')].default_items);
                            } else {
                                if (!_.isUndefined(this.model.get('panels_info')) && !_.isUndefined(this.model.get('panels_info')).type) {
                                    this.model.set('default_items', panelsOptions.widgets[this.model.get('panels_info').type].default_items);
                                }
                            }
                        }
                        switch(this.model.get('has_child')){
                            case 'tab':
                                var $default = this.model.get('totalTabpanels') ? this.model.get('totalTabpanels') : this.model.get('default_items').length;
                                break;
                            case 'scrollto':
                                var $default = this.model.get('totalScrollTopanels') ? this.model.get('totalScrollTopanels') : this.model.get('default_items').length;
                                break;
                            case 'textbox':
                                var $default = this.model.get('totalTextBoxpanels') ? this.model.get('totalTextBoxpanels') : 1;
                                break;
                            case 'slider':
                                var $default = this.model.get('totalSliderpanels') ? this.model.get('totalSliderpanels') : 1;
                                break;
                            case 'banner':
                                var $default = this.model.get('totalBannerpanels') ? this.model.get('totalBannerpanels') : 1;
                                break;
                            case 'innerrow' :
                                var $default = this.model.get('totalInnerrowpanels') ? this.model.get('totalInnerrowpanels') : this.model.get('default_items').length;
                                break;
                            case 'row':
                                var $default = this.model.get('totalRowpanels') ? this.model.get('totalRowpanels') : this.model.get('default_items').length;
                                break;
                        }
                        var $items = this.itemsToArray(this.model.get('items'), $default, this.model.get('has_child'));
                        //remove empty item
                        if($items.length > 0) {
                            $items = this.removeEmptyItem($items);
                        }
                        //set default item
                        $items = this.setDefaultPanelValue($items);
                        if (_.isEmpty(this.model.get('widget_id'))) {
                            this.model.set('widget_id', panels.helpers.utils.generateUUID());
                        }

                        //set data in model;
                        if(!_.isEmpty($items)) {
                            if(_.isUndefined(thisView.model.get('panelsItems'))) {
                                var $panelsItems = [];
                                _.each($items, function($item, $key){
                                    if(!jQuery.isEmptyObject($item) && $item.length > 0) {
                                        $panelsItems.push($item[0].get('values').layout);
                                    } else {
                                        $panelsItems.push($item);
                                    }
                                });
                                if($panelsItems.length > 0) thisView.model.set('panelsItems', $panelsItems);
                            }
                        }
                        /*
                         * show the items
                         */

                        switch(this.model.get('has_child')) {
                            case 'tab' :							
								let total_panels = 2;
								if (this.model.get('items').models.length) {
									let inner_column = [];
									total_panels = 0;
									_.each(this.model.get('items').models, function(vl){
										if (typeof vl.get('idx') !== 'undefined' && inner_column.indexOf(vl.get('idx')) === -1) {
											total_panels++;
											inner_column.push(vl.get('idx'));
										}
									});
									if (typeof this.model.get('tabTitleArray') !== 'undefined' && this.model.get('tabTitleArray').length > total_panels) {
										this.model.set('tabTitleArray', this.model.get('tabTitleArray').filter(vl => vl ? true: false));
									}
									if (typeof $items  !== 'undefined' && $items.length > total_panels) {
										$items = $items.filter(vl => !jQuery.isEmptyObject(vl));
									}
									if (typeof this.model.get('panelsItems') !== 'undefined' && this.model.get('panelsItems').length > total_panels) {
										this.model.set('panelsItems', this.model.get('panelsItems').filter(vl => vl ? true: false));
									}
								}
                                var totalTabpanels = typeof this.model.get('totalTabpanels') !== 'undefined' ? this.model.get('totalTabpanels') : total_panels;
								
                                if(totalTabpanels > this.model.get('default_items').length) {
                                    var emptyTab = Array(totalTabpanels);
                                } else {
                                    var emptyTab = this.model.get('default_items');
                                }
                                $(this.childTabTemplate({'items': ($items.length == 0 ? emptyTab : $items) ,'widget_id': this.model.get('widget_id') ,'tabTitleArray' : this.model.get('tabTitleArray') })).data({}).insertAfter(this.$el.find('.cs-widget-wrapper'));
                                if(_.isUndefined(this.model.get('totalTabpanels')) || this.model.get('totalTabpanels') === null) this.model.set('totalTabpanels', total_panels);
                                /*
                                 * add event click on the child
                                 */
                                this.addEventClickChildTab($items);
                                this.addEventClickAddPanelTab(this.$el);
                                break;

                            case 'scrollto' :
                                var totalScrollTopanels = this.model.get('totalScrollTopanels');
                                if(totalScrollTopanels > this.model.get('default_items').length) {
                                    var emptyScrollTo = Array(totalScrollTopanels);
                                } else {
                                    var emptyScrollTo = this.model.get('default_items');
                                }
                                $(this.childScrollToTemplate({'items': ($items.length == 0 ? emptyScrollTo : $items) ,'widget_id': this.model.get('widget_id'), 'panelsItems': thisView.model.get('panelsItems') })).data({}).insertAfter(this.$el.find('.cs-widget-wrapper'));
                                
                                if(_.isUndefined(this.model.get('totalScrollTopanels')) || this.model.get('totalScrollTopanels') === null) this.model.set('totalScrollTopanels', this.model.get('default_items').length);                                                        
                                break;    

                            case 'textbox' :
                                var totalTextBoxpanels = this.model.get('totalTextBoxpanels');
                                if(totalTextBoxpanels > this.model.get('default_items').length) {
                                    var emptyTextbox = Array(totalTextBoxpanels);
                                } else {
                                    var emptyTextbox = this.model.get('default_items');

                                }
                                var banner_idx = _.size(this.model.widget.get('items')) <= 1 ? 0 : (_.size(this.model.widget.get('items')) - 1);
                                $(this.childTextboxTemplate({'items': ($items.length == 0 ? emptyTextbox : $items), 'banner_idx': banner_idx ,'widget': this.model, 'widget_id': this.model.get('widget_id') })).data({}).insertAfter(this.$el.find('.cs-widget-wrapper'));
                                if(_.isUndefined(this.model.get('totalTextBoxpanels')) || this.model.get('totalTextBoxpanels') === null) this.model.set('totalTextBoxpanels', this.model.get('default_items').length);
                                
                                break;

                            case 'slider' :
                                var totalSliderpanels = this.model.get('totalSliderpanels');
                                if(totalSliderpanels > 1) {
                                    var emptySlider = Array(totalSliderpanels);
                                } else {
                                    var emptySlider = [];
                                }
                                $(this.childSliderTemplate({'items': ($items.length == 0 ? emptySlider : $items), 'widget': this.model,'widget_id': this.model.get('widget_id') })).data({}).insertAfter(this.$el.find('.cs-widget-wrapper'));
                                if(_.isUndefined(this.model.get('totalSliderpanels')) || this.model.get('totalSliderpanels') === null) this.model.set('totalSliderpanels', this.model.get('default_items').length);
                               
                                break;

                            case 'banner' :
                                var totalBannerpanels = this.model.get('totalBannerpanels');
                                if(totalBannerpanels > 1) {
                                    var emptyBanner = Array(totalBannerpanels);
                                } else {
                                    var emptyBanner = [];
                                }
                                $(this.childBannerTemplate({'items': ($items.length == 0 ? emptyBanner : $items), 'widget': this.model,'widget_id': this.model.get('widget_id') })).data({}).insertAfter(this.$el.find('.cs-widget-wrapper'));
                                if(_.isUndefined(this.model.get('totalBannerpanels')) || this.model.get('totalBannerpanels') === null) this.model.set('totalBannerpanels', this.model.get('default_items').length);
                                
                                break;

                            case 'innerrow' :
                                var totalInnerrowpanels = !_.isUndefined(this.model.get('totalInnerrowpanels')) ? this.model.get('totalInnerrowpanels') : this.model.get('default_items').length ;
                                var emptyInnerrow = Array(totalInnerrowpanels).fill({});
                                $(this.childInnerrowTemplate({'items': ($items.length === 0 ? emptyInnerrow :  $items),'widget_id': this.model.get('widget_id'),'innerrowTitleArray' : this.model.get('innerrowTitleArray') , 'panelsItems': thisView.model.get('panelsItems')})).data({}).insertAfter(this.$el.find('.cs-widget-wrapper'));

                                if(_.isUndefined(this.model.get('totalInnerrowpanels')) || this.model.get('totalInnerrowpanels') === null) this.model.set('totalInnerrowpanels', this.model.get('default_items').length);
                                /*
                                 * add event click on the child
                                 */
                                this.addEventClickChildInnerrow($items);
                                this.addEventClickAddPanelInnerrow(this.$el);
                                break;

                            case 'row' :
                                var totalRowpanels = !_.isUndefined(this.model.get('totalRowpanels')) ? this.model.get('totalRowpanels') : this.model.get('default_items').length ;
                                var emptyRow = Array(totalRowpanels).fill({});
                                $(this.childRowTemplate({'items': ($items.length === 0 ? emptyRow :  $items),'widget_id': this.model.get('widget_id'),'innerrowTitleArray' : this.model.get('innerrowTitleArray') , 'panelsItems': thisView.model.get('panelsItems')})).data({}).insertAfter(this.$el.find('.cs-widget-wrapper'));

                                if(_.isUndefined(this.model.get('totalRowpanels')) || this.model.get('totalRowpanels') === null) this.model.set('totalRowpanels', this.model.get('default_items').length);
                                /*
                                 * add event click on the child
                                 */
                                this.addEventClickChildRow($items);
                                this.addEventClickAddPanelRow(this.$el);
                                break;

                            default :
                                break;
                        };

                        //add child item into panel item
                        if(!_.isEmpty($items) && options.loadLoopItem) {
                            _.each($items, function($item, $key){
                                if($item.length > 0) {
                                    _.each($item, function($target, $k){
                                        if($target.length === 0 || $.isEmptyObject($target)) return false;
                                        if (_.isUndefined($target.get('has_child'))) {
                                            var _wF = panelsOptions.widgets[$target.get('type')];
                                            if(!_.isUndefined(_wF.has_child)) {
                                                $target.set('has_child', _wF.has_child);
                                            }

                                        }
                                        //put while loop here with parent target widget id into OPTIONS param
                                        thisView.onAddItem($target, new panels.collection.childWidgets(),{'loadForm': true,'dialog': false,  'classInner': ''});
                                    });
                                }
                            });
                        }
                    }

                    this.$el.data('view', this);

                    if (_.size(this.model.get('values')) === 0 || options.loadForm) {
                        // If this widget doesn't have a value, create a form and save it
                        var dialog = this.getEditDialog();

                        // Save the widget as soon as the form is loaded
                        dialog.once('form_loaded', dialog.saveWidget, dialog);

                        // Setup the dialog to load the form
                        dialog.setupDialog();
                    }
                    return this;
                },


                /*
                 * add event click for ADD more panel button
                 */
                addTextboxChildElementDefault: function($element) {
                    var textAreaEditor = new panels.model.childWidget({
                        class:"CleverSoft\\CleverBuilder\\Block\\Builder\\Widget\\TextAreaEditor",
                        has_child:false,
                        type:"CleverSoft\\CleverBuilder\\Block\\Builder\\Widget\\TextAreaEditor",
                        widget_id: panels.helpers.utils.generateUUID()
                    });
                    this.model.get('items').add(textAreaEditor);
                    // this.model.get('items').add(button);
                },

                /*
                 * add event click for tab Item
                 */
                addEventClickChildTab: function($items, $addTab , $addTabElement){
                    var thisView = this.widget;
                    if(!_.isUndefined($addTab)){
                        var $element = $addTabElement;
                    } else {
                        var $element = this.$el.find('.tab-item');
                    }
                    $element.click(function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        var $widget_id = $(this).attr('data-widget-id');
                        thisView.setActiveWidget();
                        var $el = $(e.target).closest('.tab-item');
                        var $id = ($el.attr('data-id')).match(/\d+/g);
                        var $contentId = $el.attr('data-widget-id');
                        var $data = $(this).data('thisTab') ? $(this).data('thisTab') : {};
                        if(_.size($data) === 0 || $data.length === 0) {
                            if(!_.isUndefined($items[$id])) {
                                $data.data = $items[$id];
                            }
                        }
                        $id = $id[0];

                        var $val = $el.find('.widget-info').text();
                        var dialog = new panels.dialog.tabItemEditor();
                        dialog.setBuilder(thisView.cell.row.builder);
                        dialog.removeCurrentTabDialog();
                        
                        var $newOne = '';
                        if (thisView.model.get('has_child') !== 'tab' && thisView.model.get('has_child') !== 'row') {
                            var $modelsItems = thisView.builder.activeWidget.get('items').models;

                            _.each($modelsItems, function($item) {
                                if ($item.get('widget_id') == $widget_id) {
                                    $newOne = $item.get('panelsItems')[$id];
                                    if (!_.isUndefined($newOne[0])) {
                                        delete $newOne[0];
                                        $newOne = Object.assign({}, $newOne);
                                    }
                                    return false;
                                }                  
                            });
                        }
                        dialog.setTab({widget_id: $widget_id, panels_item: $items, id:$id , value: $val, data:  (_.size($data) > 0 ? $data.data : {}), editDialog : (_.size($data) > 0 ? $data.editDialog : []), modelData : $newOne});
                        thisView.cell.row.builder.model.set('current_condition','tab');
                        dialog.setBuilder(thisView.cell.row.builder);
                        dialog.setCurrentTabDialog();
                        //
                        dialog.openDialog();
                    });
                },

                /*
                 * add event click for ADD more panel button
                 */
                addEventClickAddPanelInnerrow: function($element) {
                    var thisView = this;
                    var $this = $element;
                    $element.find('.icon-container.innerrow').on('click', function() {
                        thisView.total_innerrow_stack_add = thisView.total_innerrow_stack_add + 1;
                        var $widget_id = $(this).closest('.plus-icon').attr('parent-widget-id');
                        var $innerrows = $this.find('.element-items-wrapper > .element-item-wrapper[data-widget-id="'+$widget_id+'"]');
                        var $num_$innerrows = $innerrows.length;
                        var $html = $this.find('.empty_innerrow[data-widget-id="'+$widget_id+'"]').html();
                        $html = $html.replace(/{\$key}/g, $num_$innerrows).replace('$remove','').replace('{$key+1}',$num_$innerrows+1);
                        //
                        var $contentRowHtml = '<div class="column-wrap" data-width="12" data-animated="true">' +
                            '<div class="col-inner box-shadow-0 box-shadow-0-hover">' +
                            '<content content="col_grid" shortcode="shortcode">'+
                            '<div class="col_grid-empty">'+
                            '<div class="uxb-empty-message">' + $.mage.__('Add elements from left sidebar') + '</div>' +
                            '</div>'+
                            '</content>' +
                            '</div></div>'
                        ;

                        // add item to rowstack content
                        //insert panel to sidebar
                        var windowjQuery = $('.cs-preview iframe')[0].contentWindow.jQuery;
                        
                        windowjQuery('#innerrow' + $widget_id).append($contentRowHtml);

                        //add item to model
                        var modelData = {
                            "x": '',
                            "y"      : '',
                            "width": 12,
                            "height": 2,
                            "col_depth": 0,
                            "col_hover_depth": 0
                        };

                        thisView.model.get('panelsItems').push(modelData);

                        //insert panel to sidebar
                        $innerrows.last().after($html);
                        //add innerrowstack item

                        thisView.addEventClickChildInnerrow([], true, $this.children().children('.element-items-wrapper').children('.element-item-wrapper').last());
						thisView.model.set('totalInnerrowpanels', thisView.model.get('panelsItems').length);
						
                    });
                },

                /*
                 * add event click for ADD more panel button
                 */
                addEventClickAddPanelTab: function($element) {
                    var thisView = this;
                    var $this = $element;
                    $element.find('.icon-container.tab').on('click', function(){
                        thisView.total_tab_add = thisView.total_tab_add + 1;
                        var $widget_id = $(this).closest('.plus-icon').attr('parent-widget-id');
                        var $tabs = $this.find('.element-items-wrapper > .element-item-wrapper[data-widget-id="'+$widget_id+'"]');
                        var $num_tabs = $tabs.length;
                        var $html = $this.find('.empty_tab[data-widget-id = "'+$widget_id+'"]').html();
                        $html = $html.replace(/{\$key}/g, $num_tabs).replace('$remove','').replace('{$key+1}',$num_tabs+1);
                       
                        var $contentTabHtml = {
                            'title': '<li data-widget-id="'+$widget_id+'" data-id="tab'+$num_tabs+'" class="has-own-click-event "><a data-widget-id="'+$widget_id+'" data-toggle="tab" data-id="tab'+$num_tabs+'" href="#tab'+$widget_id+$num_tabs+'">Tab '+($num_tabs+1)+' Title</a></li>',
                            'content': '<div data-widget-id="'+$widget_id+'" id="tab'+$widget_id+$num_tabs+'" class="tab-pane fade in has-own-click-event ">' +
                            '<div class="tab-widget-items"><div class="uxb-empty-message">'+ $.mage.__('Add elements from left sidebar') + '</div></div>' +
                            '</div>'
                        };
                        $('.cs-preview iframe').contents().find('div[data-id="'+$widget_id+'"] li').last().after($($contentTabHtml.title));
                        $('.cs-preview iframe').contents().find('div[data-id="'+$widget_id+'"] .tab-pane').last().after($($contentTabHtml.content));
                        $tabs.last().after($html);
                        //add event for tab item on sidebar builder
                        thisView.addEventClickChildTab([], true, $this.find('.element-items-wrapper > .element-item-wrapper').last().find('.tab-item'));
                        //add click event for tab on preview section
                        $('.cs-preview iframe').contents().find('div[data-id="'+$widget_id+'"] li').last().on('click',function(){
                            $this.find('.element-items-wrapper > .element-item-wrapper').last().find('.tab-item').click();
                        });
                        //thisView.model.set('totalTabpanels',$this.find('.element-items-wrapper > .element-item-wrapper').length);
						thisView.model.set('totalTabpanels',$this.find('.element-items-wrapper > .element-item-wrapper[data-widget-id="'+$widget_id+'"]').length);
                    });
                },

                /*
                 * add event click for row Item
                 */
                addEventClickChildRow: function($items, $addRow , $addRowElement){
                    var thisView = this.widget;
                    if(!_.isUndefined($addRow)){
                        var $element = $addRowElement;
                    } else {
                        var $element = this.$el.find('.row-item');
                    }
                    $element.click(function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        var $widget_id = $(this).attr('data-widget-id');
                        thisView.setActiveWidget();
                        var $el = $(e.target).closest('.row-item');
                        var $id = ($el.attr('data-id')).match(/\d+/g);
                        var $contentId = $el.attr('data-widget-id');
                        var $data = $(this).data('thisRow') ? $(this).data('thisRow') : {};
                        if(_.size($data) === 0 || $data.length === 0) {
                            if(!_.isUndefined($items[$id])) {
                                $data.data = $items[$id];
                            }
                        }
                        $id = $id[0];

                        var $val = $el.find('.widget-info').text();
                        var dialog = new panels.dialog.rowItemEditor();
                        dialog.setBuilder(thisView.cell.row.builder);
                        dialog.removeCurrentRowDialog();
                        
                        var $newOne = '';
                        if (thisView.model.get('has_child') !== 'row') {
                            var $modelsItems = thisView.builder.activeWidget.get('items').models;

                            _.each($modelsItems, function($item) {
                                if ($item.get('widget_id') == $widget_id) {
                                    $newOne = $item.get('panelsItems')[$id];
                                    if (!_.isUndefined($newOne[0])) {
                                        delete $newOne[0];
                                        $newOne = Object.assign({}, $newOne);
                                    }
                                    return false;
                                }                  
                            });
                        }
                        dialog.setRow({widget_id: $widget_id, panels_item: $items, id:$id , value: $val, data:  (_.size($data) > 0 ? $data.data : {}), editDialog : (_.size($data) > 0 ? $data.editDialog : []), modelData : $newOne});
                        thisView.cell.row.builder.model.set('current_condition','row');
                        dialog.setBuilder(thisView.cell.row.builder);
                        dialog.setCurrentRowDialog();
                        //
                        dialog.openDialog();
                    });
                },

                /*
                 * add event click for innerrow Item
                 */
                addEventClickChildInnerrow: function($items, $addInnerrow , $addInnerrowElement){
                    

                    var thisView = this.widget;
                    if(!_.isUndefined($addInnerrow)){
                        var $element = $addInnerrowElement.children('.innerrow-item');
						
                    } else {
                        var $element = this.$el.find('.innerrow-item');
                    }

                    $element.click(function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        var $widget_id = $(this).attr('data-widget-id');
                        thisView.setActiveWidget();                       
						var $el = $(this);					
                        var $id = ($el.attr('data-id')).match(/\d+/g);
                        var $contentId = $el.attr('data-widget-id');
                        var $data = $(this).data('thisInnerrow') ? $(this).data('thisInnerrow') : {};
                        if(_.size($data) === 0 || $data.length === 0) {
                            if(!_.isUndefined($items[$id])) {
                                $data.data = $items[$id];
                            }
                        }
                        $id = $id[0];

                        var $val = $el.find('.widget-info').text();
                        var dialog = new panels.dialog.innerrowItemEditor();
                        dialog.setBuilder(thisView.cell.row.builder);
                        dialog.removeCurrentInnerrowDialog();

                        var $newOne = '';
                        if (thisView.model.get('has_child') !== 'innerrow') {
                            var $modelsItems = thisView.builder.activeWidget.get('items').models;

                            _.each($modelsItems, function($item) {
                                if ($item.get('widget_id') == $widget_id) {
                                    $newOne = $item.get('panelsItems')[$id];
                                    if (!_.isUndefined($newOne[0])) {
                                        delete $newOne[0];
                                        $newOne = Object.assign({}, $newOne);
                                    }
                                    return false;
                                }                  
                            });
                        }
                        dialog.setInnerrow({widget_id: $widget_id, panels_item: $items, id:$id , value: $val, data:  (_.size($data) > 0 ? $data.data : {}), editDialog : (_.size($data) > 0 ? $data.editDialog : []), modelData : $newOne});
                        thisView.cell.row.builder.model.set('current_condition','innerrow');
                        dialog.setBuilder(thisView.cell.row.builder);
                        dialog.setCurrentInnerrowDialog();                    
                        dialog.openDialog();
                    });
                },

                /*
                 * add event click for ADD more panel button
                 */
                addEventClickAddPanelRow: function($element) {
                    var thisView = this;
                    var $this = $element;
                    $element.find('.icon-container.row').on('click', function(){
                        thisView.total_row_stack_add = thisView.total_row_stack_add + 1;
                        var $widget_id = $(this).closest('.plus-icon').attr('parent-widget-id');
                        var $rows = $this.find('.element-items-wrapper > .element-item-wrapper[data-widget-id="'+$widget_id+'"]');
                        var $num_$rows = $rows.length;
                        var $html = $this.find('.empty_row[data-widget-id = "'+$widget_id+'"]').html();
                        $html = $html.replace(/{\$key}/g, $num_$rows).replace('$remove','').replace('{$key+1}',$num_$rows+1);
                        //
                        var $contentRowHtml = '<div class="column-wrap" data-width="12" data-animated="true">' +
                            '<div class="col-inner box-shadow-0 box-shadow-0-hover">' +
                            '<content content="col_grid" shortcode="shortcode">'+
                            '<div class="col_grid-empty">'+
                            '<div class="uxb-empty-message">' + $.mage.__('Add elements from left sidebar') + '</div>' +
                            '</div>'+
                            '</content>' +
                            '</div></div>'
                        ;

                        // add item to rowstack content
                        //insert panel to sidebar
                        var windowjQuery = $('.cs-preview iframe')[0].contentWindow.jQuery;
                       
                        windowjQuery('#row' + $widget_id).append($contentRowHtml);

                        //add item to model
                        var modelData = {
                            "x": '',
                            "y"      : '',
                            "width": 12,
                            "height": 2,
                            "col_depth": 0,
                            "col_hover_depth": 0
                        };

                        thisView.model.get('panelsItems').push(modelData);

                        //insert panel to sidebar
                        $rows.last().after($html);
                        //add rowstack item

                        thisView.addEventClickChildRow([], true, $this.find('.element-items-wrapper > .element-item-wrapper').last());
                        thisView.model.set('totalRowpanels', $this.find('.element-items-wrapper > .element-item-wrapper').length);
                    });
                },

                /**
                 * This is triggered when ever a item widget is added to the cell collection.
                 *
                 * @param widget
                 */
                onAddItem: function (item, collection, options) {
                    options = _.extend({noAnimate: false, dialog : true, loopChilds: false}, options);

                    // Create the view for the widget
                    var view = new panels.view.childWidget({
                        model: item
                    });

                    view.widget = this;
                    if(_.isUndefined(view.widget.cell)) {
                        view.widget.cell = !_.isUndefined(item.widget.cell) ? item.widget.cell : (!_.isUndefined(this.widget.cell)?this.widget.cell:undefined);
                    }

                    if(_.isUndefined(view.builder)) {
                        view.builder = !_.isUndefined(item.builder) ? item.builder : (!_.isUndefined(this.builder) ?  this.builder : null);
                    }
                    if (_.isUndefined(item.isDuplicate)) {
                        item.isDuplicate = false;
                    }

                    // Render and load the form if this is a duplicate
                    view.render({
                        'loadForm': !_.isUndefined(options.loadForm) ? options.loadForm : (item.isDuplicate ? item.isDuplicate : 'true'),
                        'loadLoopItem': false
                    });
                    //var target = this.$el.find('.element-item-wrapper[data-id="#tab'+event[0].model.get('idx')+'"] .tab-item-elements');

                    //add.data({'editDialog': event[0]}).prependTo(target);
                    if((_.isUndefined(this.dialog) || !this.dialog) && options.dialog) {
                        // this.dialog = view.dialog;
                    }
                    if (!_.isUndefined(item.get('panels_info')) && !_.isUndefined(item.get('panels_info').type)) {
                        var temp = panelsOptions.widgets[item.get('class')] ? panelsOptions.widgets[item.get('class')].title : panelsOptions.widgets[item.get('panels_info').type].title;
                        var $cap = _.isUndefined(item.get('values').button_text) ? undefined : item.get('values').button_text;
                        var $has_child = panelsOptions.widgets[item.get('class')] ? panelsOptions.widgets[item.get('class')].has_child : panelsOptions.widgets[item.get('panels_info').type].has_child;
                    } else {
                        var temp = panelsOptions.widgets[item.get('class')] ? panelsOptions.widgets[item.get('class')].title : panelsOptions.widgets[item.get('type')].title;
                        var $cap = _.isUndefined(item.get('values').button_text) ? undefined : item.get('values').button_text;
                        var $has_child = panelsOptions.widgets[item.get('class')] ? panelsOptions.widgets[item.get('class')].has_child : panelsOptions.widgets[item.get('type')].has_child;
                    }
                    
                    //if running in a loop
                    if(!_.isUndefined(options.parentWidget)) {
                        var $parentHasChild = !_.isUndefined(options.parentWidget.get('has_child')) ? options.parentWidget.get('has_child') : this.model.get('has_child');
                    } else {
                        var $parentHasChild = this.model.get('has_child');
                    }
                    //
                    //if(!_.isUndefined(item.get('has_child'))) {
                    var innerClass = !_.isUndefined(options.classInner) ? options.classInner : '';
                    if ( !_.isUndefined(item.is_inner)  || _.isUndefined($parentHasChild) || item.get('items').length > 0) {
                        var add = view.$el;
                    } else {
                        switch ($parentHasChild) {
                            case 'tab':
                                var itemId = this.$el.find('.element-item-wrapper[data-id="#tab'+ item.get('idx')+'"] .cs-tab-item-child-wrapper').length;
                                var add = $(this.childItemTabTemplate({'widget':{'title':temp}, 'widget_id': this.model.get('widget_id'), 'itemId':  itemId }));
                                break;
                            case 'textbox':
                                var itemId = this.$el.find('.element-item-wrapper[data-id="#textbox'+ item.get('idx')+'"] .cs-textbox-item-child-wrapper').length;
                                var add = $(this.childItemTextboxTemplate({'widget':{'title':temp, 'cap': $cap, 'has_child': $has_child}, 'widget_id': this.model.get('widget_id'), 'itemId':  itemId , 'innerClass': innerClass}));
                                break;
                            case 'slider':
                                var itemId = this.$el.find('.element-item-wrapper[data-id="#slider'+ item.get('idx')+'"] .cs-slider-item-child-wrapper').length;
                                var add = $(this.childItemSliderTemplate({'widget':{'title':temp, 'cap': $cap, 'has_child': $has_child}, 'widget_id': this.model.get('widget_id'), 'itemId':  itemId , 'innerClass': innerClass}));
                                break;
                            case 'banner':
                                var itemId = this.$el.find('.element-item-wrapper[data-id="#banner'+ item.get('idx')+'"] .cs-banner-item-child-wrapper').length;
                                var add = $(this.childItemBannerTemplate({'widget':{'title':temp, 'cap': $cap, 'has_child': $has_child}, 'widget_id': this.model.get('widget_id'), 'itemId':  itemId , 'innerClass': innerClass}));
                                break;
                            case 'innerrow':
                                var itemId = this.$el.find('.element-item-wrapper[data-id="#innerrow'+ item.get('idx')+'"] .cs-innerrow-item-child-wrapper').length;
                                var add = $(this.childItemInnerrowTemplate({'widget':{'title':temp}, 'widget_id': this.model.get('widget_id') , 'itemId':  itemId }));
                                break;
                            case 'row':
                                var itemId = this.$el.find('.element-item-wrapper[data-id="#row'+ item.get('idx')+'"] .cs-row-item-child-wrapper').length;
                                var add = $(this.childItemRowTemplate({'widget':{'title':temp}, 'widget_id': this.model.get('widget_id'), 'itemId':  itemId }));
                                break;
                        }
                    }
                    //set target id
                    //view.dialog.model.set('targetId', itemId);

                    add.data({'editDialog': view.dialog});
                    switch ($parentHasChild) {
                        case 'tab':
                            this.tab.editDialog[item.get('widget_id').toString()] = view.dialog;
                            break;
                        case 'textbox':
                            this.textbox.editDialog[item.get('widget_id').toString()] = view.dialog;
                            break;
                        case 'banner':
                            this.banner.editDialog[item.get('widget_id').toString()] = view.dialog;
                            break;
                        case 'innerrow':
                            this.innerrow.editDialog[item.get('widget_id').toString()] = view.dialog;
                            break;
                        case 'row':
                            this.row.editDialog[item.get('widget_id').toString()] = view.dialog;
                            break;
                    }

                    var thisView = this;

                    //add event click on the item
                    if (add.find('.panel-info').length === 0) {
                        var $$click = add.find('.title h4');
                    } else {
                        var $$click = add.find('.panel-info');
                    }
                    $$click.on('click',function(event){
                        event.preventDefault();
                        event.stopPropagation();
                        switch ($parentHasChild) {
                            case 'tab':
                                thisView.tab.editDialog[item.get('widget_id').toString()].openDialog();
                                break;
                            case 'textbox':
                                thisView.textbox.editDialog[item.get('widget_id').toString()].openDialog();
                                break;
                            case 'slider':
                                thisView.slider.editDialog[item.get('widget_id').toString()].openDialog();
                                break;
                            case 'banner':
                                thisView.banner.editDialog[item.get('widget_id').toString()].openDialog();
                                break;
                            case 'innerrow':
                                thisView.innerrow.editDialog[item.get('widget_id').toString()].openDialog();
                                break;
                            case 'row':
                                thisView.row.editDialog[item.get('widget_id').toString()].openDialog();
                                break;
                        }

                    });

                    //add = view.$el;

                    if (_.isUndefined(options.at) || collection.length <= 1) {
                        // Insert this at the end of the widgets container
                        switch ($parentHasChild) {
                            case 'tab':
                                add.insertBefore(this.$el.find('.element-item-wrapper[data-id="#tab'+ item.get('idx')+'"] .cs-tab-item-button'));
                                break;
                            case 'textbox':
                                if(_.isUndefined(options.parentWIdx)) {
                                    if (!_.isUndefined(this.model.get('add_from_button'))) {
                                        if ( _.isUndefined(this.cell.row.builder.model.get('idx')) && this.model.get('has_child') === 'textbox'){
                                            add = this.addPanelTextBox(add);
                                            add.insertBefore(this.$el.find('#'+ this.model.get('widget_id') + ' .cs-element-insert'));
                                            this.model.set('totalTextBoxpanels', $('#'+ this.model.get('widget_id')).find('.element-item-wrapper').length);
                                        } else {
                                            add.appendTo(this.$el.find('#'+ this.model.get('widget_id') + ' .textbox-item[data-id="#textbox'+ item.get('idx')+'"]'));
                                        }
                                    } else {
                                        add.appendTo(this.$el.find('#'+ this.model.get('widget_id') + ' .textbox-item[data-id="#textbox'+ item.get('idx')+'"]'));
                                    }
                                    
                                } else {
                                    //find('.empty_innerrow[data-widget-id = "'+$widget_id+'"]')
                                    if( this.$el.find('div[data-widget-id="'+options.parentWIdx+'"] .cs-element-item-button').length > 0 ) {
                                        add.insertBefore(this.$el.find('div[data-widget-id="'+options.parentWIdx+'"] .cs-element-item-button'));
                                    } else {
                                        var a = this.$el.find('div.textbox-item[data-widget-id="'+options.parentWIdx+'"][data-id="#textbox'+ item.get('idx')+'"]');
                                        add.appendTo(this.$el.find('div.textbox-item[data-widget-id="'+options.parentWIdx+'"][data-id="#textbox'+ item.get('idx')+'"]'));
                                    }
                                }
                                break;
                            case 'slider':
                                if(_.isUndefined(options.parentWIdx) && this.ceil) {
                                    if ( _.isUndefined(this.cell.row.builder.model.get('idx')) && this.model.get('has_child') === 'slider' && !_.isUndefined(this.model.get('add_from_button') )){
                                        add = this.addPanelSlider(add);
                                        add.insertBefore(this.$el.find('#'+ this.model.get('widget_id') + ' .cs-element-insert'));
                                        this.model.set('totalSliderpanels', $('#'+ this.model.get('widget_id')).find('.element-item-wrapper').length);
                                    } else {
                                        add.appendTo(this.$el.find('#'+ this.model.get('widget_id') + ' .slider-item[data-id="#slider'+ item.get('idx')+'"]'));
                                    }

                                } else {
                                    //find('.empty_innerrow[data-widget-id = "'+$widget_id+'"]')
                                    if( this.$el.find('div[data-widget-id="'+options.parentWIdx+'"] .cs-element-item-button').length > 0 ) {
                                        add.insertBefore(this.$el.find('div[data-widget-id="'+options.parentWIdx+'"] .cs-element-item-button'));
                                    } else {
                                        var a = this.$el.find('div.slider-item[data-widget-id="'+options.parentWIdx+'"][data-id="#slider'+ item.get('idx')+'"]');
                                        add.appendTo(this.$el.find('div.slider-item[data-widget-id="'+options.parentWIdx+'"][data-id="#slider'+ item.get('idx')+'"]'));
                                    }
                                }
                                break;
                            case 'banner':
                                if(_.isUndefined(options.parentWIdx)) {
                                    if ( _.isUndefined(this.cell.row.builder.model.get('idx')) && this.model.get('has_child') === 'banner' && !_.isUndefined(this.model.get('add_from_button') )){
                                        add = this.addPanelBanner(add);
                                        add.insertBefore(this.$el.find('#'+ this.model.get('widget_id') + ' .cs-element-insert'));
                                        this.model.set('totalBannerpanels', $('#'+ this.model.get('widget_id')).find('.element-item-wrapper').length);
                                    } else {
                                        add.appendTo(this.$el.find('#'+ this.model.get('widget_id') + ' .banner-item[data-id="#banner'+ item.get('idx')+'"]'));
                                    }

                                } else {
                                    //find('.empty_innerrow[data-widget-id = "'+$widget_id+'"]')
                                    if( this.$el.find('div[data-widget-id="'+options.parentWIdx+'"] .cs-element-item-button').length > 0 ) {
                                        add.insertBefore(this.$el.find('div[data-widget-id="'+options.parentWIdx+'"] .cs-element-item-button'));
                                    } else {
                                        var a = this.$el.find('div.banner-item[data-widget-id="'+options.parentWIdx+'"][data-id="#banner'+ item.get('idx')+'"]');
                                        add.appendTo(this.$el.find('div.banner-item[data-widget-id="'+options.parentWIdx+'"][data-id="#banner'+ item.get('idx')+'"]'));
                                    }
                                }
                                break;
                            case 'innerrow':
                                // add.insertBefore(this.$el.find('.element-item-wrapper[data-id="#innerrow'+ item.get('idx')+'"] .cs-innerrow-item-button'));
                                // break;
                                if(!_.isUndefined(options.parentWIdx)) {
                                    add.insertBefore(this.$el.find('#'+options.parentWIdx+' .element-item-wrapper[data-id="#innerrow'+ item.get('idx')+'"] .cs-innerrow-item-button'));
                                } else {
                                    if(!_.isUndefined(this.cell) && this.cell && !_.isUndefined(this.cell.row.builder.model.get('parentWIdx'))) {
                                        add.insertBefore(this.$el.find('#'+this.cell.row.builder.model.get('parentWIdx')+' .element-item-wrapper[data-id="#innerrow'+ item.get('idx')+'"] .cs-innerrow-item-button'));
                                    } else {
                                        add.insertBefore(this.$el.find('.element-item-wrapper[data-id="#innerrow'+ item.get('idx')+'"] .cs-innerrow-item-button'));
                                    }
                                }
                                break;
                            case 'row':
                                if(!_.isUndefined(options.parentWIdx)) {
                                    add.insertBefore(this.$el.find('#'+options.parentWIdx+' .element-item-wrapper[data-id="#row'+ item.get('idx')+'"] .cs-row-item-button'));
                                } else {
                                    if(!_.isUndefined(this.cell.row.builder.model.get('parentWIdx'))) {
                                        add.insertBefore(this.$el.find('#'+this.cell.row.builder.model.get('parentWIdx')+' .element-item-wrapper[data-id="#row'+ item.get('idx')+'"] .cs-row-item-button'));
                                    } else {
                                        add.insertBefore(this.$el.find('.element-item-wrapper[data-id="#row'+ item.get('idx')+'"] .cs-row-item-button'));
                                    }
                                }
                                break;
                        }

                    } else {
                        // We need to insert this at a specific position
                        switch ($parentHasChild) {
                            case 'tab':
                                add.insertAfter(this.$el.find('.element-item-wrapper[data-id="#tab' + item.get('idx') + '"] .cs-tab-item-child-wrapper').last());
                                break;
                            case 'textbox':
                                if (_.isUndefined(options.parentWIdx)) {
                                    if (_.isUndefined(this.cell.row.builder.model.get('idx')) && this.model.get('has_child') === 'textbox' && !_.isUndefined(this.model.get('add_from_button'))) {
                                        add = this.addPanelTextBox(add);
                                        add.insertBefore(this.$el.find('#' + this.model.get('widget_id') + ' .cs-element-insert'));
                                        this.model.set('totalTextBoxpanels', $('#' + this.model.get('widget_id')).find('.element-item-wrapper').length);
                                    } else {
                                        add.appendTo(this.$el.find('#' + this.model.get('widget_id') + ' .textbox-item[data-id="#textbox' + item.get('idx') + '"]'));
                                    }

                                } else {
                                    //find('.empty_innerrow[data-widget-id = "'+$widget_id+'"]')
                                    if (this.$el.find('div[data-widget-id="' + options.parentWIdx + '"] .cs-element-item-button').length > 0) {
                                        add.insertBefore(this.$el.find('div[data-widget-id="' + options.parentWIdx + '"] .cs-element-item-button'));
                                    } else {
                                        var a = this.$el.find('div.textbox-item[data-widget-id="' + options.parentWIdx + '"][data-id="#textbox' + item.get('idx') + '"]');
                                        add.appendTo(this.$el.find('div.textbox-item[data-widget-id="' + options.parentWIdx + '"][data-id="#textbox' + item.get('idx') + '"]'));
                                    }
                                }
                                break;
                            case 'slider':
                                if (_.isUndefined(options.parentWIdx)) {
                                    if (_.isUndefined(this.cell.row.builder.model.get('idx')) && this.model.get('has_child') === 'slider' && !_.isUndefined(this.model.get('add_from_button'))) {
                                        add = this.addPanelSlider(add);
                                        add.insertBefore(this.$el.find('#' + this.model.get('widget_id') + ' .cs-element-insert'));
                                        this.model.set('totalSliderpanels', $('#' + this.model.get('widget_id')).find('.element-item-wrapper').length);
                                    } else {
                                        add.appendTo(this.$el.find('#' + this.model.get('widget_id') + ' .slider-item[data-id="#slider' + item.get('idx') + '"]'));
                                    }

                                } else {
                                    //find('.empty_innerrow[data-widget-id = "'+$widget_id+'"]')
                                    if (this.$el.find('div[data-widget-id="' + options.parentWIdx + '"] .cs-element-item-button').length > 0) {
                                        add.insertBefore(this.$el.find('div[data-widget-id="' + options.parentWIdx + '"] .cs-element-item-button'));
                                    } else {
                                        var a = this.$el.find('div.slider-item[data-widget-id="' + options.parentWIdx + '"][data-id="#slider' + item.get('idx') + '"]');
                                        add.appendTo(this.$el.find('div.slider-item[data-widget-id="' + options.parentWIdx + '"][data-id="#slider' + item.get('idx') + '"]'));
                                    }
                                }
                                break;
                            case 'banner':
                                if (_.isUndefined(options.parentWIdx)) {
                                    if (_.isUndefined(this.cell.row.builder.model.get('idx')) && this.model.get('has_child') === 'banner' && !_.isUndefined(this.model.get('add_from_button'))) {
                                        add = this.addPanelBanner(add);
                                        add.insertBefore(this.$el.find('#' + this.model.get('widget_id') + ' .cs-element-insert'));
                                        this.model.set('totalBannerpanels', $('#' + this.model.get('widget_id')).find('.element-item-wrapper').length);
                                    } else {
                                        add.appendTo(this.$el.find('#' + this.model.get('widget_id') + ' .banner-item[data-id="#banner' + item.get('idx') + '"]'));
                                    }

                                } else {
                                    //find('.empty_innerrow[data-widget-id = "'+$widget_id+'"]')
                                    if (this.$el.find('div[data-widget-id="' + options.parentWIdx + '"] .cs-element-item-button').length > 0) {
                                        add.insertBefore(this.$el.find('div[data-widget-id="' + options.parentWIdx + '"] .cs-element-item-button'));
                                    } else {
                                        var a = this.$el.find('div.banner-item[data-widget-id="' + options.parentWIdx + '"][data-id="#banner' + item.get('idx') + '"]');
                                        add.appendTo(this.$el.find('div.banner-item[data-widget-id="' + options.parentWIdx + '"][data-id="#banner' + item.get('idx') + '"]'));
                                    }
                                }
                                break;
                            case 'innerrow':
                                add.insertAfter(this.$el.find('.element-item-wrapper[data-id="#innerrow' + item.get('idx') + '"] .cs-innerrow-item-child-wrapper').last());
                                break;
                            case 'row':
                                add.insertAfter(this.$el.find('.element-item-wrapper[data-id="#row' + item.get('idx') + '"] .cs-row-item-child-wrapper').last());
                                break;
                        }
                    }

                    if (options.noAnimate === false) {
                        // We need an animation
                        view.visualCreate();
                    }
                    // if(item.get('has_child')) {
                    //    this.addEventAddButtonElement(add,this.$el,item);
                    // }
                    //add inner child template into builder
                    if(_.size(item.get('items')) > 0) {
                        var $$c = item.get('items');
                        if(!_.isUndefined(options.classInner)) {
                            var innerClass = options.classInner + ' inner';
                        } else {
                            var innerClass = 'inner';
                        }
                        $$c.each(function($child, $l){
                            if($child.length === 0 || $.isEmptyObject($child)) return false;
                            if (_.isUndefined($child.get('has_child'))) {
                                var _wF = panelsOptions.widgets[$child.get('type')];
                                if(!_.isUndefined(_wF.has_child)) {
                                    $child.set('has_child', _wF.has_child);
                                }

                            }
                            thisView.onAddItem($child, new panels.collection.childWidgets(),{'loadForm': true,'dialog': false, 'loopChilds': true, 'parentWIdx': item.get('widget_id'), 'parentWidget': item , 'classInner': innerClass})
                        })
                    }
                },
                /**
                 * Display an animation that implies creation using a visual animation
                 */
                visualCreate: function () {
                    this.$el.hide().fadeIn('fast');
                },

                /**
                 * Get the dialog view of the form that edits this widget
                 *
                 * @returns {null}
                 */
                getEditDialog: function () {
                    if (this.dialog === null || window.storeModelItem) {
                        this.dialog = new panels.dialog.childWidget({
                            model: window.storeModelItem ? window.storeModelItem : this.model
                        });
                        var builder = !_.isUndefined(this.builder) ? this.builder : this.widget.cell.row.builder;

                        this.dialog.setBuilder(builder);

                        // Store the widget view
                        this.dialog.widgetView = this;

                        window.storeModelItem = false;
                        window.storeWidgetModel = false;
                    }
                    return this.dialog;
                },

                /**
                 * Handle clicking on edit widget.
                 *
                 * @returns {boolean}
                 */
                editHandler: function () {
                    // Create a new dialog for editing this
                    this.getEditDialog().openDialog();
                    //this.setActiveWidget();
                },

                //setActiveWidget: function() {
                //    var thisView = this;
                //    this.cell.row.builder.activeWidget = this.cell.model.activeWidget = this;
                //},

                titleClickHandler: function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    if (!this.widget.cell.row.builder.supports('editWidget') || this.widget.model.get('read_only')) {
                        return this;
                    }
                    this.editHandler();
                    return this;
                },

                /**
                 * Handle clicking on duplicate.
                 *
                 * @returns {boolean}
                 */
                duplicateHandler: function () {
                    // Add the history entry
                    this.widget.cell.row.builder.addHistoryEntry('widget_duplicated');

                    // Create the new widget and connect it to the widget collection for the current row
                    var newWidget = this.model.clone(this.model.w.model.widget);

                    this.widget.model.get('items').add(newWidget, {
                        // Add this after the existing model
                        at: this.model.collection.indexOf(this.model) + 1
                    });

                    this.widget.cell.row.builder.model.refreshPanelsData();
                    return this;
                },

                /**
                 * Copy the row to a cookie based clipboard
                 */
                copyHandler: function () {
                    panels.helpers.clipboard.setModel(this.model);
                },

                /**
                 * Handle clicking on delete.
                 *
                 * @returns {boolean}
                 */
                deleteHandler: function () {
                    this.model.trigger('visual_destroy');
                    return this;
                },

                onModelChange: function () {
                    // Update the description when ever the model changes
                    this.$('.description').html(this.model.getTitle());
                },

                onLabelChange: function (model) {
                    this.$('.title > h4').text(model.getWidgetField('title'));
                },

                /**
                 * When the model is destroyed, fade it out
                 */
                onModelDestroy: function () {
                    this.remove();
                },

                /**
                 * Visually destroy a model
                 */
                visualDestroyModel: function () {
                    // Add the history entry
                    if (this.cell) {
                        this.cell.row.builder.addHistoryEntry('widget_deleted');
                    } else {
                        this.widget.cell.row.builder.addHistoryEntry('widget_deleted');
                    }
                    var thisView = this;
                    this.$el.fadeOut('fast', function () {
                        thisView.widget.cell.row.resize();
                        thisView.model.destroy();
                        thisView.widget.cell.row.builder.model.refreshPanelsData();
                        $('[data-widget-id='+thisView.model.get('widget_id')+']').remove();
                        thisView.remove();
                    });
                    if (window.currentEl) {
                        if (window.currentEl.closest('.cs-element-item-child-wrapper').length) {
                            window.currentEl.closest('.cs-element-item-child-wrapper').fadeOut();  
                        } else if (window.currentEl.closest('.element-item-wrapper').length) {
							const datarowid = window.currentEl.closest('.element-item-wrapper').data('id');
							if (datarowid.indexOf('#row') === -1) {
								window.currentEl.closest('.element-item-wrapper').fadeOut();	
							}
                        }
                    }
                    return this;
                },

                /**
                 * Build up the contextual menu for a widget
                 *
                 * @param e
                 * @param menu
                 */
                buildContextualMenu: function (e, menu) {
                    if (this.widget.cell.row.builder.supports('addWidget')) {
                        menu.addSection(
                            'add-widget-below',
                            {
                                sectionTitle: panelsOptions.loc.contextual.add_widget_below,
                                searchPlaceholder: panelsOptions.loc.contextual.search_widgets,
                                defaultDisplay: panelsOptions.contextual.default_widgets
                            },
                            panelsOptions.widgets,
                            function (c) {
                                this.widget.cell.row.builder.addHistoryEntry('widget_added');

                                var childWidget = new panels.model.childWidget({
                                    class: c
                                });
                                childWidget.cell = this.widget.model;

                                // Insert the new widget below
                                this.widget.model.get('items').add(childWidget, {
                                    // Add this after the existing model
                                    at: this.model.collection.indexOf(this.model) + 1
                                });

                                this.widget.cell.row.builder.model.refreshPanelsData();
                            }.bind(this)
                        );
                    }

                    var actions = {};

                    if (this.widget.cell.row.builder.supports('editWidget') && !this.model.get('read_only')) {
                        actions.edit = {title: panelsOptions.loc.contextual.widget_edit};
                    }

                    // Copy and paste functions
                    if (panels.helpers.clipboard.canCopyPaste()) {
                        actions.copy = {title: panelsOptions.loc.contextual.widget_copy};
                    }

                    if (this.widget.cell.row.builder.supports('addWidget')) {
                        actions.duplicate = {title: panelsOptions.loc.contextual.widget_duplicate};
                    }

                    if (this.widget.cell.row.builder.supports('deleteWidget')) {
                        actions.delete = {title: panelsOptions.loc.contextual.widget_delete, confirm: true};
                    }

                    if (!_.isEmpty(actions)) {
                        menu.addSection(
                            'widget-actions',
                            {
                                sectionTitle: panelsOptions.loc.contextual.widget_actions,
                                search: false
                            },
                            actions,
                            function (c) {
                                switch (c) {
                                    case 'edit':
                                        this.editHandler();
                                        break;
                                    case 'copy':
                                        this.copyHandler();
                                        break;
                                    case 'duplicate':
                                        this.duplicateHandler();
                                        break;
                                    case 'delete':
                                        this.visualDestroyModel();
                                        break;
                                }
                            }.bind(this)
                        );
                    }

                    // Lets also add the contextual menu for the entire row
                    this.widget.buildContextualMenu(e, menu);
                }

            });

        }, {}],
        38: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = panels.view.dialog.extend({

                builder: null,
                widgetTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-dialog-widgets-widget').html())),
                filter: {},

                dialogClass: 'cs-panels-dialog-add-widget',
                dialogIcon: 'add-widget',

                events: {
                    'click .cs-update': 'updateDialog',
                    'click .cs-close': 'updateDialog',
                    'click .widget-type': 'childWidgetClickHandler',
                    'keyup .cs-sidebar-search': 'searchHandler'
                },

                /**
                 * Initialize the widget adding dialog
                 */
                initializeDialog: function () {
                    var thisView = this;

                    this.on('open_dialog', function () {
                        this.filter.search = '';
                        this.filterWidgets(this.filter);
                    }, this);

                    this.on('open_dialog_complete', function () {
                        // Clear the search and re-filter the widgets when we open the dialog
                        this.$('.cs-sidebar-search').val('').focus();
                        this.$el.find('.current-tab-title').text($.mage.__('Child Widget'));
                        this.balanceWidgetHeights();
                    });

                    // We'll implement a custom tab click handler
                    this.on('tab_click', this.tabClickHandler, this);
                },

                render: function () {
                    // Render the dialog and attach it to the builder interface
                    this.renderDialog(this.parseDialogContent($('#cleversoft-panels-dialog-widgets').html(), {}));
                    var $$condition = this.builder.model.get('current_condition');

                    var thisView = this;
                    // Add all the widgets
                    _.each(panelsOptions.widgets, function (widget) {
                        if(!_.isUndefined($$condition) ) {
                            if(!_.isUndefined(thisView.builder.model.get('child_filter'))) {
                                if($.inArray(widget.code, panelsOptions.childWidgetFilter[thisView.builder.model.get('child_filter')]) === -1 || $.inArray(widget.code, panelsOptions.childWidgetFilterExClude[thisView.builder.model.get('child_filter')]) !== -1) return;
                            } else {
                                if(!_.isUndefined(panelsOptions.childWidgetFilter[$$condition])) {
                                    if($.inArray(widget.code, panelsOptions.childWidgetFilter[$$condition]) === -1 || $.inArray(widget.code, panelsOptions.childWidgetFilterExClude[$$condition]) !== -1) return;
                                }
                            }
                        }
                        var data = {
                            title: widget.title,
                            description: widget.description,
                            type: widget.type,
                            code: widget.code
                        };
                        //if(widget.area == 'widget') {
                        var $w = $(this.widgetTemplate(data));
                        //} else if( widget.area == 'element' ) {
                        //    var $w = $(this.elementTemplate(data));
                        //}

                        if (_.isUndefined(widget.icon) || widget.icon.trim() == '' ) {
                            widget.icon = 'dashicons dashicons-admin-generic';
                        }

                        $('<span class="'+widget.area+'-icon" />').addClass(widget.icon).prependTo($w.find('.widget-type-wrapper'));

                        var $data = {
                            'class': widget.class,
                            'type' : widget.type,
                            'title' : widget.title,
                            'items': !_.isUndefined(widget.has_child),
                            'has_child' : !_.isUndefined(widget.has_child) ? widget.has_child : false,
                            'layouts' : !_.isUndefined(widget.layouts) ? widget.layouts : false,
                            'default_items' : !_.isUndefined(widget.default_items) ? widget.default_items : false
                        };

                        $w.data($data).appendTo(this.$('.'+widget.area+'-type-list'));
                    }, this);

                    var thisDialog = this;
                    $(window).resize(function () {
                        thisDialog.balanceWidgetHeights();
                    });
                },

                /**
                 * Handle changes to the search value
                 */
                searchHandler: function (e) {
                    if (e.which === 13) {
                        var visibleWidgets = this.$('.widget-type-list .widget-type:visible, .element-type-list .widget-type:visible, .layout-type-list .widget-type:visible');
                        if (visibleWidgets.length === 1) {
                            visibleWidgets.click();
                        }
                    }
                    else {
                        this.filter.search = $(e.target).val().trim();
                        this.filterWidgets(this.filter);
                    }
                },

                /**
                 * Filter the widgets that we're displaying
                 * @param filter
                 */
                filterWidgets: function (filter) {
                    if (_.isUndefined(filter)) {
                        filter = {};
                    }

                    if (_.isUndefined(filter.groups)) {
                        filter.groups = '';
                    }

                    this.$('.widget-type-list .widget-type, .element-type-list .widget-type, .layout-type-list .widget-type').each(function () {
                        var $$ = $(this), showWidget;
                        var widgetClass = $$.data('class');

                        var widgetData = (
                            !_.isUndefined(panelsOptions.widgets[widgetClass])
                        ) ? panelsOptions.widgets[widgetClass] : (!_.isUndefined(panelsOptions.widgets[$$.data('type')]) ? panelsOptions.widgets[$$.data('type')] : null );

                        if (_.isEmpty(filter.groups)) {
                            // This filter doesn't specify groups, so show all
                            showWidget = true;
                        } else if (widgetData !== null && !_.isEmpty(_.intersection(filter.groups, panelsOptions.widgets[widgetClass].groups))) {
                            // This widget is in the filter group
                            showWidget = true;
                        } else {
                            // This widget is not in the filter group
                            showWidget = false;
                        }

                        // This can probably be done with a more intelligent operator
                        if (showWidget) {

                            if (!_.isUndefined(filter.search) && filter.search !== '') {
                                // Check if the widget title contains the search term
                                if (widgetData.title.toLowerCase().indexOf(filter.search.toLowerCase()) === -1) {
                                    showWidget = false;
                                }
                            }

                        }

                        if (showWidget) {
                            $$.show();
                        } else {
                            $$.hide();
                        }
                    });

                    // Balance the tags after filtering
                    this.balanceWidgetHeights();
                },

                /**
                 * Add the widget to the current builder
                 *
                 * @param e
                 */
                childWidgetClickHandler: function (e) {
                    // Add the history entry
                    this.builder.addHistoryEntry('child_widget_added');

                    var $w = $(e.currentTarget);

                    var child = this.builder.model.get('current_condition') ? this.builder.model.get('current_condition') : 'tab' ;
                    var childWidget = new panels.model.childWidget({
                        class: $w.data('class'),
                        type : $w.data('type'),
                        title : $w.data('title'),
                        items: $w.data('items') ? new panels.collection.childWidgets() : false,
                        layouts: $w.data('layouts') ? $w.data('layouts') : false,
                        has_child: $w.data('has_child') ? $w.data('has_child') : false,
                        default_items: $w.data('default_items') ? $w.data('default_items') : (!_.isUndefined(panelsOptions.widgets[$w.data('type')].default_items)?panelsOptions.widgets[$w.data('type')].default_items:false),
                        widget_id: panels.helpers.utils.generateUUID(),
                        parent_widget_id: this.builder.model.get('parentWIdx'),
                        inner_lv: this.builder.model.get('inner_lv')
                    });

                    // Add the widget to the cell model
                    childWidget.widget = this.builder.getActiveWidget();
                    childWidget.widget.cell = this.builder.getActiveCell();
                    childWidget.builder = this.builder;
                    if(_.isUndefined(this.builder.model.get('idx'))) {
                        childWidget.set('idx',childWidget.widget.get('items').length);
                    } else {
                        childWidget.set('idx',this.builder.model.get('idx'));
                    }

                    // Add the widget to the cell model
                    var $a = $w.data('has_child');
                    if($a) {
                        childWidget.is_inner = true;
                        if($w.data('has_child') === 'innerrow') {
                            var dialog = new panels.dialog.innerrowLayoutsItem();
                            dialog.setBuilder(this.builder);
                            dialog.setWidget(childWidget);
                            this.updateDialog();
                            dialog.openDialog();
                        } else if($w.data('has_child') === 'row') {
                            var dialog = new panels.dialog.rowLayoutsItem();
                            dialog.setBuilder(this.builder);
                            dialog.setWidget(childWidget);
                            this.updateDialog();
                            dialog.openDialog();
                        } else if($w.data('has_child') === 'banner') {
                            var dialog = new panels.dialog.bannerItem();
                            dialog.setBuilder(this.builder);
                            dialog.setWidget(childWidget);
                            this.updateDialog();
                            dialog.openDialog();
                        } else {
                            // if(_.isUndefined(this.builder.model.get('idx'))) {
                            //     childWidget.widget.get('items').add(childWidget);
                            // } else {
                            //     childWidget.widget.get('items').at(this.builder.model.get('idx')).get('items').add(childWidget,{at:childWidget.widget.get('items').at(this.builder.model.get('idx')).get('items').length});
                            // }
                            childWidget.widget.get('items').add(childWidget);
                            this.updateDialog();
                            this.builder.model.refreshPanelsData();
                        }

                    } else {
                        if(child === 'banner') {
                            if(_.isUndefined(this.builder.model.get('idx'))) {
                                childWidget.widget.get('items').add(childWidget);
                            } else {
                                childWidget.widget.get('items').at(this.builder.model.get('idx')).get('items').add(childWidget);
                            }
                        } else {
                            childWidget.widget.get('items').add(childWidget);
                        }
                    }

                    switch (child) {
                        case 'tab' :
                            this.builder.dialogs.widgets.trigger('tab_content_changed', [childWidget]);
                            break;
                        case 'textbox' :
                            this.builder.dialogs.widgets.trigger('textbox_content_changed', [childWidget]);
                            break;
                        case 'slider' :
                            this.builder.dialogs.widgets.trigger('slider_content_changed', [childWidget]);
                            break;
                        case 'banner' :
                            this.builder.dialogs.widgets.trigger('banner_content_changed', [childWidget]);
                            break;
                        case 'innerrow' :
                            this.builder.dialogs.widgets.trigger('innerrow_content_changed', [childWidget]);
                            break;
                        case 'row' :
                            this.builder.dialogs.widgets.trigger('row_content_changed', [childWidget]);
                            break;
                        default :
                            break;
                    }
                    this.updateDialog();
                    this.builder.model.refreshPanelsData();
                },

                /**
                 * Balance widgets in a given row so they have enqual height.
                 * @param e
                 */
                balanceWidgetHeights: function (e) {
                    this.balanceWidgetHeightsArea('widget-type-list');
                    this.balanceWidgetHeightsArea('element-type-list');
                    this.balanceWidgetHeightsArea('layout-type-list');

                },
                /*
                 * Balance widgets in a given row so they have enqual height for each area.
                 * @param area
                 */
                balanceWidgetHeightsArea: function(area) {
                    var widgetRows = [[]];
                    var previousWidget = null;

                    // Work out how many widgets there are per row
                    var perRow = Math.round(this.$('.'+ area + ' .widget-type').parent().width() / this.$('.'+ area + ' .widget-type').width());

                    // Add clears to create balanced rows
                    this.$('.'+ area + ' .widget-type')
                        .css('clear', 'none')
                        .filter(':visible')
                        .each(function (i, el) {
                            if (i % perRow === 0 && i !== 0) {
                                $(el).css('clear', 'both');
                            }
                        });

                    // Group the widgets into rows
                    this.$('.widget-type-wrapper')
                        .css('height', 'auto')
                        .filter(':visible')
                        .each(function (i, el) {
                            var $el = $(el);
                            if (previousWidget !== null && previousWidget.position().top !== $el.position().top) {
                                widgetRows[widgetRows.length] = [];
                            }
                            previousWidget = $el;
                            widgetRows[widgetRows.length - 1].push($el);
                        });

                    // Balance the height of the widgets within the row.
                    _.each(widgetRows, function (row, i) {
                        var maxHeight = _.max(row.map(function (el) {
                            return el.height();
                        }));
                        // Set the height of each widget in the row
                        _.each(row, function (el) {
                            el.height(maxHeight);
                        });

                    });
                }
            });

        }, {}],
        39: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;
            var jsWidget = require('../view/widgets/js-widget');

            module.exports = panels.view.dialog.extend({

                builder: null,
                sidebarWidgetTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-dialog-widget-sidebar-widget').html())),

                dialogClass: 'cs-panels-dialog-edit-widget',
                dialogIcon: 'add-widget',

                widgetView: false,
                savingWidget: false,
                editableLabel: true,

                events: {
                    'click .cs-update': 'saveHandler',
                    'click .cs-close': 'closeDialog',
                    'click .cs-nav.cs-previous': 'navToPrevious',
                    'click .cs-nav.cs-next': 'navToNext',

                    // Action handlers
                    'click .cs-toolbar .cs-delete': 'deleteHandler',
                    'click .cs-toolbar .cs-duplicate': 'duplicateHandler'
                },

                initializeDialog: function () {
                    var thisView = this;
                    this.model.on('change:values', this.handleChangeValues, this);
                    this.model.on('destroy', this.remove, this);

                    this.on('open_dialog', function () {
                        // thisView.$('.action-buttons a').hide();
                    }, this);

                    this.on('close_dialog', function () {
                        var condition = thisView.builder.model.get('current_condition') + '_content_changed';
                        this.widgetView.widget.trigger(condition,[thisView]);
                        //thisView.builder.dialogs.widgets.setEditDialog(thisView, thisView.builder.model.get('current_condition') );
                    }, this);

                    // Refresh panels data after both dialog form components are loaded
                    this.dialogFormsLoaded = 0;
                    this.on('form_loaded styles_loaded', function () {
                        this.dialogFormsLoaded++;
                        if (this.dialogFormsLoaded === 2) {
                            thisView.updateModel({
                                refreshArgs: {
                                    silent: true
                                }
                            });
                        }
                    });

                    this.on('edit_label', function (text) {
                        // If text is set to default value, just clear it.
                        var temp = panelsOptions.widgets[this.model.get('class')] ? panelsOptions.widgets[this.model.get('class')].title : panelsOptions.widgets[this.model.get('type')].title;
                        if (text === temp) {
                            text = '';
                        }
                        this.model.set('label', text);
                        if (_.isEmpty(text)) {
                            this.$('.cs-title').text(this.model.getWidgetField('title'));
                        }
                    }.bind(this));
                },

                /**
                 * Render the widget dialog.
                 */
                render: function () {
                    if (!_.isUndefined(window.formContentLoaded) && !_.isUndefined(window.formContentLoaded[this.model.cid])) {
                        window.formContentLoaded[this.model.cid].show();
                        return false;
                    }
                    // Render the dialog and attach it to the builder interface
                    this.renderDialog(this.parseDialogContent($('#cleversoft-panels-dialog-widget').html(), {}));
                    this.loadForm();

                    var title = this.model.getWidgetField('title');
                    this.$('.cs-title .widget-name').html(title);
                    this.$('.cs-edit-title').val(title);

                    if (!this.builder.supports('addWidget')) {
                        this.$('.cs-buttons .cs-duplicate').remove();
                    }
                    if (!this.builder.supports('deleteWidget')) {
                        this.$('.cs-buttons .cs-delete').remove();
                    }

                    // Now we need to attach the style window
                    this.styles = new panels.view.styles();
                    this.styles.model = this.model;
                    this.styles.render('widget', this.builder.config.postId, {
                        builderType: this.builder.config.builderType,
                        dialog: this
                    });

                    var $rightSidebar = this.$('.cs-sidebar.cs-right-sidebar');
                    this.styles.attach($rightSidebar);

                    // Handle the loading class
                    this.styles.on('styles_loaded', function (hasStyles) {
                        // If we have styles remove the loading spinner, else remove the whole empty sidebar.
                        if (hasStyles) {
                            $rightSidebar.removeClass('cs-panels-loading');
                        } else {
                            $rightSidebar.closest('.cs-panels-dialog').removeClass('cs-panels-dialog-has-right-sidebar');
                            $rightSidebar.remove();
                        }
                    }, this);
                    $rightSidebar.addClass('cs-panels-loading');
                },

                /**
                 * Get the previous widget editing dialog by looking at the dom.
                 * @returns {*}
                 */
                getPrevDialog: function () {
                    var widgets = this.builder.$('.cs-cells .cell .cs-widget');
                    if (widgets.length <= 1) {
                        return false;
                    }
                    var currentIndex = widgets.index(this.widgetView.$el);

                    if (currentIndex === 0) {
                        return false;
                    } else {
                        do {
                            var widgetView = widgets.eq(--currentIndex).data('view');
                            if (!_.isUndefined(widgetView) && !widgetView.model.get('read_only')) {
                                return widgetView.getEditDialog();
                            }
                        } while (!_.isUndefined(widgetView) && currentIndex > 0);
                    }

                    return false;
                },

                /**
                 * Get the next widget editing dialog by looking at the dom.
                 * @returns {*}
                 */
                getNextDialog: function () {
                    var widgets = this.builder.$('.cs-cells .cell .cs-widget');
                    if (widgets.length <= 1) {
                        return false;
                    }

                    var currentIndex = widgets.index(this.widgetView.$el), widgetView;

                    if (currentIndex === widgets.length - 1) {
                        return false;
                    } else {
                        do {
                            widgetView = widgets.eq(++currentIndex).data('view');
                            if (!_.isUndefined(widgetView) && !widgetView.model.get('read_only')) {
                                return widgetView.getEditDialog();
                            }
                        } while (!_.isUndefined(widgetView));
                    }

                    return false;
                },

                /**
                 * Load the widget form from the server.
                 * This is called when rendering the dialog for the first time.
                 */
                loadForm: function () {
                    // don't load the form if this dialog hasn't been rendered yet
                    if (!this.$('> *').length) {
                        return;
                    }

                    this.$('.cs-content').addClass('cs-panels-loading');
                    this.$('.cs-content').append('<span class="zoo-loading" style="opacity: 1; visibility: visible; top: 50%;"></span>');

                    var data = {
                        'action': 'so_panels_widget_form',
                        'widget_id' : this.model.get('widget_id'),
                        'widget': this.model.get('class'),
                        'type' : this.model.get('type'),
                        'instance': JSON.stringify(this.model.get('values')),
                        'raw': this.model.get('raw')
                    };

                    $.post(
                        panelsOptions.ajaxurl,
                        data,
                        function (result) {
                            // Add in the CID of the widget model
                            var html = result.replace(/{\$id}/g, this.model.cid);

                            // Load this content into the form
                            var $soContent = this.$('.cs-content');
                            $soContent
                                .removeClass('cs-panels-loading')
                                .append(html);

                            this.$('.cs-content .zoo-loading').remove();    
                                
                            // Trigger all the necessary events
                            this.trigger('form_loaded', this);

                            //run mage init
                            if($('.clever-trigger-content-update').length > 0) $('.clever-trigger-content-update').trigger('contentUpdated');
                            $('.mage-init-dependency').trigger('contentUpdated');

                            // For legacy compatibility, trigger a panelsopen event
                            this.$('.panel-dialog').trigger('panelsopen');

                            // If the main dialog is closed from this point on, save the widget content
                            this.on('close_dialog', this.updateModel, this);

                            /*
                             * add event click to slider input field
                             *
                             */
                            var $builder = this;
                            $(document).on('changeSliderUi', function(event){
                                $builder.updateModel();
                            });

                            var widgetContent = $soContent.find('> .widget-content');
                            // If there's a widget content wrapper, this is one of the new widgets in WP 4.8 which need some special
                            // handling in JS.
                            if (widgetContent.length > 0) {
                                jsWidget.addWidget($soContent, this.model.widget_id);
                            }
                            if (_.isUndefined(window.formContentLoaded)) {
                                window.formContentLoaded = [];
                            }
                            window.formContentLoaded[this.model.cid] = $soContent.closest('.cs-panels-dialog-wrapper');
                        }.bind(this),
                        'html'
                    );
                },

                /**
                 * Save the widget from the form to the model
                 */
                updateModel: function (args) {
                    args = _.extend({
                        refresh: true,
                        refreshArgs: null
                    }, args);

                    // Get the values from the form and assign the new values to the model
                    this.savingWidget = true;

                    if (!this.model.get('missing')) {
                        // Only get the values for non missing widgets.
                        var values = this.getFormValues();
                        if (_.isUndefined(values.widgets)) {
                            values = {};
                        } else {
                            values = values.widgets;
                            values = values[Object.keys(values)[0]];
                        }

                        this.model.setValues(values);
                        this.model.set('raw', true); // We've saved from the widget form, so this is now raw
                    }

                    if (this.styles.stylesLoaded) {
                        // If the styles view has loaded
                        var style = {};
                        try {
                            style = this.getFormValues('.cs-sidebar .cs-visual-styles').style;
                        }
                        catch (e) {
                        }
                        this.model.set('style', style);
                    }

                    this.savingWidget = false;

                    if (args.refresh) {
                        this.builder.model.refreshPanelsData(args.refreshArgs);
                    }
                },

                /**
                 *
                 */
                handleChangeValues: function () {
                    if (!this.savingWidget) {
                        // Reload the form when we've changed the model and we're not currently saving from the form
                        this.loadForm();
                    }
                },

                /**
                 * Save a history entry for this widget. Called when the dialog is closed.
                 */
                saveHandler: function () {
                    this.builder.addHistoryEntry('child_widget_edited');
                    this.updateDialog();
                },

                /**
                 * When the user clicks delete.
                 *
                 * @returns {boolean}
                 */
                deleteHandler: function () {
                    this.model.trigger('visual_destroy');
                    this.updateDialog({silent: true});
                    this.builder.model.refreshPanelsData();

                    return false;
                },

                duplicateHandler: function () {
                    this.model.trigger('user_duplicate');

                    this.updateDialog({silent: true});
                    this.builder.model.refreshPanelsData();

                    return false;
                }

            });

        }, {"../view/widgets/js-widget": 31}],
        /*
         * open innerrow item editor
         */
        40: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = panels.view.dialog.extend({

                innerrowItemEditorTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-innerrow-item-editor').html())),

                builder: null,
                dialogClass: 'cs-panels-dialog-innerrow-item-editor-layouts',
                dialogIcon: 'layouts',
                innerrowStack : null,

                events: {
                    'click .cs-update': 'insertInnerrowItemHandle',
                    'click .cs-close': 'closeInnerrowItemHandle',
                    'click .cs-element-insert': 'addChildWidget',
                    'click .cs-toolbar .cs-delete': 'deleteHandler',
                    'change input[name="innerrow_item_width"]' : 'changePreviewWidth',
                    'change input[name="innerrow_item_depth"]' : 'changePreviewDepth',
                    'change input[name="innerrow_item_depth_hover"]' : 'changePreviewDepthHover',
                    'click .cs-insert': 'insertInnerrowItemHandle' //this.builder.model.refreshPanelsData();
                    //'keyup .innerrow-item-title': 'changeItemTitle',
                    //'click .title h4' : 'openWidgetEditor'
                },

                /**
                 * Initialize the tab item dialog.
                 */
                initializeDialog: function () {
                    var thisView = this;
                    this.on('open_dialog', function () {
                        
                    }, this);

                    this.on('open_dialog_complete', function () {
                        thisView.$('.slider-ui').trigger('contentUpdated');
                        thisView.addChangeVl(thisView);
                        // thisView.deleteHandler(thisView);
                        thisView.builder.trigger('builder_resize');
                    });

                    this.on('innerrow_item_model_change', function(event){

                    });

                    this.on('close_dialog', function( ) {
                    });
                    this.on('close_dialog_complete', function( ) {});
                },

                /**
                 * Render the innerrow item layouts dialog
                 */
                render: function () {
                    var thisView = this;
                    this.renderDialog(this.parseDialogContent($('#cleversoft-panels-innerrow-item').html(), {}));
                    var widgetInnerrow = {};
                    var $dept_unique = panels.helpers.utils.generateUUID();
                    var $dept_hover_unique = panels.helpers.utils.generateUUID();
                    var modelData = this.innerrow.modelData;

                    //calculate the height;
                    var $the_height = parseFloat(modelData.height) / 60;
                    if ($the_height === (1/1)) {
                        var height = '1-1';
                    } else if($the_height === (1/2)) {
                        var height = '1-2';
                    }else if($the_height === (1/3)) {
                        var height = '1-3';
                    }else if($the_height === (2/3)) {
                        var height = '2-3';
                    }else if($the_height === (1/4)) {
                        var height = '1-4';
                    }else if($the_height === (3/4)) {
                        var height = '3-4';
                    }

                    //
                    var $t = $(this.innerrowItemEditorTemplate({
                            'widget': widgetInnerrow,
                            'widget_id': thisView.builder.activeWidget.get('has_child') == 'innerrow' ? thisView.builder.activeWidget.get('widget_id') : this.innerrow.widget_id,
                            'id': this.innerrow.id,
                            'value':this.innerrow.value,
                            'depth' : {
                                'value' : modelData.col_depth,
                                'unique': $dept_unique
                            },
                            'depth_hover' : {
                                'value' : modelData.col_hover_depth,
                                'unique': $dept_hover_unique
                            },
                            'x': modelData.x,
                            'y': modelData.y,
                            'width': modelData.width,
                            'height': height,
                            'model_height': modelData.height,
                            'animate': _.isUndefined(modelData.animate) ? 'none' : modelData.animate,
                            'classes': _.isUndefined(modelData.class) ? '' : modelData.class
                        })
                    );/// give the data here
                    $t.data(!_.isUndefined(thisView.innerrow.data) ? thisView.innerrow.data : {}).appendTo(this.$('.panels-innerrow-editor'));
                },
                changePreviewWidth: function(event) {
                    var thisView = this;
                    var $innerrowItemID = thisView.innerrow.id;
                    var $previewInnerrowID = '#innerrow'+$(event.target).closest('.cs-innerrow-item-wrapper').attr('data-id');
                    var windowjQuery = $('.cs-preview iframe')[0].contentWindow.jQuery;

                    var $el = windowjQuery($previewInnerrowID).find('div[data-key="'+$innerrowItemID+'"]');
                    $el.attr('data-width',parseInt($(event.target).val()));

                    var $elEdit = windowjQuery($previewInnerrowID).children('.cs-edit-element-section').eq($innerrowItemID);
                    $elEdit.attr('data-width',parseInt($(event.target).val()));

                    $('.element-item[data-widget-id="'+$(event.target).closest('.cs-innerrow-item-wrapper').attr('data-id')+'"][data-id="#innerrow'+$innerrowItemID+'"] span.widget-info').html($(event.target).val()+'/12');
                },
                /*
                 * change depth value in preview
                 */
                changePreviewDepth: function(event) {
                    var $el = $('.cs-preview iframe').contents().find('#innerrow' + this.builder.activeWidget.get('widget_id')).children('div').eq(this.innerrow.id).find('.grid-stack-item-content');
                    $el.attr('class',$el.attr('class').replace('box-shadow-' + $(event.currentTarget).data('oldValue'), 'box-shadow-' + $(event.target).val() ));
                },
                /*
                 * change depth value in preview
                 */
                changePreviewDepthHover: function(event) {
                    var $el = $('.cs-preview iframe').contents().find('#innerrow' + this.builder.activeWidget.get('widget_id')).children('div').eq(this.innerrow.id).find('.grid-stack-item-content');
                    $el.attr('class',$el.attr('class').replace('box-shadow-' + $(event.currentTarget).data('oldValue')+'-hover', 'box-shadow-' + $(event.target).val()+'-hover' ));
                },
                /*
                 * add change Vl
                 */
                addChangeVl: function(thisView) {
                    var thisK = this;
                    var fields = this.$('.change-hidden-value');
                    if(fields.length > 0) {
                        fields.each(function(el){
                            $(this).on('click',function(el){
                                //console.log($('.cs-preview iframe').contents().$.find('#innerrow' + thisView.builder.activeWidget.get('widget_id')).data());
                                var $name = $(this).closest('ul').find('input[type="hidden"]').attr('name');
                                if($name.trim() ===  'innerrow_item_width') {
                                    thisView.builder.$el.find('#'+ thisView.builder.activeWidget.get('widget_id') +' .element-items-wrapper').children('.element-item-wrapper').eq(thisView.innerrow.id).find('.widget-info').text($(this).text().trim()+'/12');
                                    thisView.innerrow.value = $(this).text().trim()+'/12';
                                }
                                $(this).siblings().removeClass('active');
                                $(this).addClass('active');
                                if($name.trim() ===  'innerrow_item_height') {
                                    var $height = $(this).text().trim();
                                    $height = $height.split('-');
                                    var neww = (60 * parseInt($height[0])) /  parseInt($height[1]);
                                    $(this).closest('ul').find('input[type="hidden"]').val(neww);
                                } else {
                                    $(this).closest('ul').find('input[type="hidden"]').val($(this).text().trim());
                                }
                                $(this).closest('ul').find('input[type="hidden"]').trigger('change');
                            })
                        })
                    }
                },
                /*
                 * set builder
                 */
                setBuilder: function(builder,model) {
                    if(!this.builder) {
                        if(!builder) {
                            var builderModel = new panels.model.builder();
                            // Now for the view to display the builder
                            this.builder = new panels.view.builder({
                                model: builderModel,
                                config: {}
                            });
                        } else {
                            this.builder = builder;
                        }

                    }
                },
                /*
                 *
                 */
                setEditDialog: function(dialog) {
                    this.innerrow.editDialog  = dialog;
                },
                /*
                 *
                 */
                insertInnerrowItemHandle: function() {
                    //update model data
                    this.updateModelInnerrowData();
                    this.updateDialog();
                    this.builder.model.set('current_condition', null);
                    //reset tab object
                    this.innerrow = null;//reset tab object;
                    this.builder.dialogs.innerrowItemEditor.$el.remove();
                    this.builder.dialogs.innerrowItemEditor = null;
                    //reload panel data
                    this.builder.model.refreshPanelsData();
                },
                /*
                 *
                 */
                closeInnerrowItemHandle: function() {
                    //update model data
                    this.updateModelInnerrowData();
                    this.updateDialog();
                    this.builder.model.set('current_condition', null);
                    //reset tab object
                    this.innerrow = null;//reset tab object;
                    this.builder.dialogs.innerrowItemEditor.$el.remove();
                    this.builder.dialogs.innerrowItemEditor = null;
                    //reload panel data
                    // this.builder.model.refreshPanelsData();
                },
                /**
                 * When the user clicks delete.
                 *
                 * @returns {boolean}
                 */
                deleteHandler: function () {
                    var self = this;
                    var $panelsItems = this.innerrow.panels_item;
                    var $widget = this.innerrow.widget_id;
                    
                    delete $panelsItems[this.innerrow.id];
                    $panelsItems = this.removeEmptyItem($panelsItems);

                    var $items = this.builder.activeWidget.get('items').models;
					let tmp_arr = [];
					let tmparr_innerrow = [];
                    _.each(this.builder.activeWidget.get('items').models, function($item) {
                       
                        if ($item.get('widget_id') == $widget) {
                           var $panelsItem = $item.get('panelsItems');
                           delete $panelsItem[self.innerrow.id];
                           $panelsItem = self.removeEmptyItem($panelsItem);
                           $item.set('panelsItems',$panelsItem);
                           console.log('bug item', $item.get('totalInnerrowpanels'));
                           $item.set('totalInnerrowpanels',Number($item.get('totalInnerrowpanels'))-1);
						   if (typeof $item.get('has_child') !== 'undefined' && $item.get('has_child') === 'innerrow' && $item.get('items').models.length) {							   
							   let arr_tmp = [];
							   _.each($item.get('items').models, function($ite){
								   let $idx = typeof $ite.get('idx') !== 'undefined' ? Number($ite.get('idx')): $ite.get('idx');
								   if (typeof $idx !== 'undefined') {
										if ($idx == self.innerrow.id) {
											arr_tmp.push($ite);
											tmparr_innerrow.push($item.get('widget_id'));
										} else if ($idx > Number(self.innerrow.id)) {
											$idx--;
											$ite.set('idx', $idx);
										}
								   } else {
									   arr_tmp.push($ite);
								   }
							   });
							   if (_.size(arr_tmp)) {								  
								   self.builder.activeWidget.get('items').models[index].get('items').remove(arr_tmp);
							   }
						   }
                        } else if (typeof $item.get('parent_widget_id') !== 'undefined' && $item.get('parent_widget_id') === $widget ){
							let $idx = typeof $item.get('idx') !== 'undefined' ? Number($item.get('idx')): $item.get('idx');
							if (typeof $idx !== 'undefined') {
								if ($idx > Number(self.innerrow.id)) {
									$idx--;
									$item.set('idx', $idx);
								} else if ($idx == self.innerrow.id){
									tmp_arr.push($item);
									tmparr_innerrow.push($item.get('widget_id'));
								}
							}
						} else if (typeof $item.get('parent_widget_id') !== 'undefined' && tmparr_innerrow.indexOf($item.get('parent_widget_id')) !== -1){
							tmp_arr.push($item);
							tmparr_innerrow.push($item.get('widget_id'));
						} else if ($item.get('items').models.length) {
							_.each($item.get('items').models, function($ite, _index){
								if ($ite.get('widget_id') == $widget) {
									var $panelsItem = $ite.get('panelsItems');
									delete $panelsItem[self.innerrow.id];
									$panelsItem = self.removeEmptyItem($panelsItem);
									self.builder.activeWidget.get('items').models[index].get('items').models[_index].set('panelsItems',$panelsItem);
									self.builder.activeWidget.get('items').models[index].get('items').models[_index].set('totalInnerrowpanels',Number($ite.get('totalInnerrowpanels'))-1);
									if (typeof $ite.get('has_child') !== 'undefined' && $ite.get('has_child') === 'innerrow' && $ite.get('items').models.length) {
									   let arr_tmp = [];
									   _.each($ite.get('items').models, function($_ite, $_index){
										   let $idx = typeof $_ite.get('idx') !== 'undefined' ? Number($_ite.get('idx')): $_ite.get('idx');
										   if (typeof $idx !== 'undefined') {
												if ($idx == self.innerrow.id) {
													arr_tmp.push($_ite);
													tmparr_innerrow.push($_ite.get('widget_id'));
												} else if ($idx > Number(self.innerrow.id)) {
													$idx--;
													//$_ite.set('idx', $idx);
													self.builder.activeWidget.get('items').models[index].get('items').models[_index].get('items').models[$_index].set('idx', $idx);
												}
										   } else {
											   arr_tmp.push($_ite);
										   }
									   });
									   if (_.size(arr_tmp)) {
										   self.builder.activeWidget.get('items').models[index].get('items').models[_index].get('items').remove(arr_tmp);
									   }
									}
								}
							});
						}						
                    });
                    console.log('bug')                   
                    if (typeof this.builder.activeWidget.get('totalInnerrowpanels') !== 'undefined') {
                        this.builder.activeWidget.set('totalInnerrowpanels',Number(this.builder.activeWidget.get('totalInnerrowpanels'))-1);
                    }
					if (_.size(tmp_arr)) {
						this.builder.activeWidget.get('items').remove(tmp_arr);
					}				
                    
                    this.updateDialog();

                    if (this.builder.activeWidget.get('has_child') !== 'innerrow') {
                        var $widget = this.innerrow.widget_id;
                    } else {
                        var $widget = this.builder.activeWidget.get('widget_id');
                    }

                    if (this.builder.activeWidget.get('items').length > 0) this.builder.$el.find('.element-item-wrapper[data-widget-id="'+$widget+'"]')[self.innerrow.id].remove();

                    this.builder.model.refreshPanelsData();
                    var $i = 0;
                    var $j = 0;

                    _.each(this.builder.$el.find('.element-item-wrapper[data-widget-id="'+$widget+'"]'), function($item) {
                        if ($($item).attr('data-id') != '#innerrow{$key}') {
                            $($item).attr('data-id',"#innerrow"+$i);
                            $i++;
                        }
                        
                    });					
                    let $contit = 0;
                    _.each(this.builder.$el.find('[class^=element-item-wrapper][data-widget-id='+$widget+']'), function($item){
                        if ($($item).find('span[class*=cs-element-insert][parent-widget-id='+$widget+']').attr('idx') !== '{$key}') {
                            $($item).find('span[class*=cs-element-insert][parent-widget-id='+$widget+']').attr('idx', $contit);
                            $contit++;
                        }
                    });					
                    _.each(this.builder.$el.find('.element-item-wrapper .innerrow-item[data-widget-id="'+$widget+'"]'), function($item) {
                        if ($($item).attr('data-id') != '#innerrow{$key}') {
                            $($item).attr('data-id',"#innerrow"+$j);
                            $j++;
                        }
                    });

                },

                removeEmptyItem: function( $items) {
                    var $index = 0;
                    var $new_items = new Array();

                    _.each($items, function($temps, $key) {
                        if(_.size($temps) > 0) {
                            if(jQuery.isArray($temps)) {
                                $temps = $temps.filter(function ($temp) {
                                    return _.size($temp) > 0
                                });
                            }
                            $new_items[$index] = $temps;
                            $index++;
                        }
                    });

                    const $fn_items = $new_items.map(
                            obj => 
                            Object.keys(obj).filter(e => obj[e] !== null)
                            .reduce((o, e) => {o[e]  = obj[e]; return o;}, {})
                        )

                    return $fn_items;
                },

                /*
                 * set option
                 */
                setInnerrow: function(innerrow) {
                    this.innerrow = innerrow;
                },
                //update model data
                updateModelInnerrowData: function() {
                    var thisView = this;
                    var $el = this.$el;
                    var $width  = $el.find('input[name="innerrow_item_width"]').val();
                    var $height = $el.find('input[name="innerrow_item_height"]').val();
                    var $gsx = $el.find('input[name="innerrow_item_x"]').val();
                    var $gsy = $el.find('input[name="innerrow_item_y"]').val();
                    var $class = $el.find('input[name="innerrow_item_class"]').val();
                    var $depth = $el.find('input[name="innerrow_item_depth"]').val();
                    var $depth_hover = $el.find('input[name="innerrow_item_depth_hover"]').val();
                    var $animate = $el.find('select[name="innerrow_item_animate"]').val();
                    var $data = {
                        'width' :$width ? $width : 6,
                        'height' : $height ? $height : 60,
                        'x' : $gsx ? $gsx : 0,
                        'y' : $gsy ? $gsy : 60,
                        'class' : $class,
                        'col_depth' : $depth,
                        'col_hover_depth': $depth_hover,
                        'animate': $animate
                    }

                    if (this.builder.activeWidget.get('has_child') !== 'innerrow') {
                        var $panelsItem = thisView.innerrow.modelData;
                        var $widget = thisView.innerrow.widget_id;

                        var $newOne = $.extend($panelsItem, $data);
                        var $items = thisView.builder.activeWidget.get('items').models;

                        _.each($items, function($item) {
                            if ($item.get('widget_id') == $widget) {
                                $item.get('panelsItems')[thisView.innerrow.id] = $newOne;
                                return false;
                            }                  
                        });
                    } else {
                        var $panelsItems = this.builder.activeWidget.get('panelsItems');
                        var $panelsItem = $panelsItems[this.innerrow.id];
                        var $newOne = $.extend($panelsItem, $data);

                        this.builder.activeWidget.get('panelsItems')[this.innerrow.id] = $newOne;
                    }

                    //this.innerrow.modelData[this.innerrow.id] = $data;

                },
                /*
                 *get innerrow data
                 */
                getInnerrow: function(){
                    return this.innerrow;
                },
                /*
                 * show dialog add widget
                 */

                addChildWidget: function() {
                    this.updateDialog();
                    this.builder.dialogs.childWidgets.openDialog();
                    //this.builder.model.refreshPanelsData();
                },

                /*
                 *
                 */
                removeCurrentInnerrowDialog: function(options) {
                    if(this.builder.dialogs.innerrowItemEditor) {
                        this.builder.dialogs.innerrowItemEditor.$el.remove();
                        this.builder.dialogs.innerrowItemEditor = null;
                    }
                },
                /*
                 *
                 */
                setCurrentInnerrowDialog: function(options) {
                    this.builder.dialogs.innerrowItemEditor = this;
                    //this.builder.model.set('innerrowTitle'+[this.innerrow.id], this.innerrow.value);
                },

                /*
                 *
                 */
                removeCurrentTextboxDialog: function(options) {
                    if(this.builder.dialogs.textboxItemEditor) {
                        this.builder.dialogs.textboxItemEditor.$el.remove();
                        this.builder.dialogs.textboxItemEditor = null;
                    }
                },
                /*
                 *
                 */
                setCurrentTextboxDialog: function(options) {
                    this.builder.dialogs.textboxItemEditor = this;
                    //this.builder.model.set('textboxTitle'+[this.textbox.id], this.textbox.value);
                },

                /*
                 *
                 */
                removeCurrentBannerDialog: function(options) {
                    if(this.builder.dialogs.bannerItemEditor) {
                        this.builder.dialogs.bannerItemEditor.$el.remove();
                        this.builder.dialogs.bannerItemEditor = null;
                    }
                },
                /*
                 *
                 */
                setCurrentBannerDialog: function(options) {
                    this.builder.dialogs.bannerItemEditor = this;
                    //this.builder.model.set('bannerTitle'+[this.banner.id], this.banner.value);
                }
            });

        }, {}],
        /*
         * open innerrow layout item editor
         */
        41: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = panels.view.dialog.extend({

                innerrowLayoutsTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-innerrow-layouts').html())),

                builder: null,
                widget: null,

                events: {
                    'click .cs-update': 'closeInnerrowItemHandle',
                    'click .cs-close': 'closeInnerrowItemHandle',
                    'click .cs-apply': 'applyLayoutHandle',
                    'click .with-thumbnail': 'setActiveLayout'
                },

                /**
                 * Initialize the innerrow item dialog.
                 */
                initializeDialog: function () {
                    var thisView = this;
                    this.on('open_dialog', function () {
                    }, this);

                    this.on('open_dialog_complete', function () {
                    });
                    this.on('close_dialog', function( ) {
                    });
                    this.on('close_dialog_complete', function( ) {});
                },

                /**
                 * Render the innerrow item layouts dialog
                 */
                render: function () {
                    var thisView = this;
                    this.renderDialog(this.parseDialogContent($('#cleversoft-panels-innerrow-layouts-items').html(), {}));
                    var $t = $(this.innerrowLayoutsTemplate({}));/// give the data here
                    $t.data({}).appendTo(this.$('.panels-innerrow-layout_editor'));
                },

                /*layout handle
                 */
                applyLayoutHandle: function(e) {
                    if(!_.isUndefined(this.widget.is_inner) ){
                        this.widget.widget.get('items').add(this.widget);
                    } else {
                        this.widget.cell.get('widgets').add(this.widget);
                    }
                    this.updateDialog();
                    this.builder.model.refreshPanelsData();
                },
                closeInnerrowItemHandle: function() {

                    //update model data
                    this.updateDialog();
                    this.builder.model.set('current_condition', null);
                    //reset tab object
                    this.innerrow = null;//reset tab object;
                    if (!_.isUndefined(this.builder.dialogs.innerrowItemEditor)) {
                        this.builder.dialogs.innerrowItemEditor.$el.remove();
                        this.builder.dialogs.innerrowItemEditor = null;
                        this.builder.model.refreshPanelsData();
                    }
                },
                /*set builder
                 */
                setBuilder: function($builder) {
                    this.builder = $builder;
                },
                /*set widget view
                 */
                setWidget: function($widget) {
                    this.widget = $widget;
                },
                /*
                 set active layout
                 */
                setActiveLayout: function(e){
                    var target = $(e.target);
                    target.closest('.clever-row').find('li.active').removeClass('active');
                    target.closest('li').addClass('active');
                    //re-build panel data
                    var layout = target.closest('.with-thumbnail');
                    $(layout.attr('data-value-id')).val(layout.attr('data-layout'));
                    this.widget.set('totalInnerrowpanels', this.widget.get('layouts')[layout.attr('data-layout')].length);
                    this.widget.set('innerrow_layout', layout.attr('data-layout'));
                    
                }
            });
        }, {}],
        /*
         * open textbox item editor
         */
        42: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = panels.view.dialog.extend({

                textboxItemEditorTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-textboxs-item-editor').html())),

                builder: null,
                dialogClass: 'cs-panels-dialog-textbox-item-editor-layouts',
                dialogIcon: 'layouts',

                events: {
                    'click .cs-update': 'insertChildHandle',
                    'click .cs-close': 'insertChildHandle',
                    'click .cs-insert': 'insertChildHandle', //this.builder.model.refreshPanelsData();
                    'keyup .textbox-item-title': 'changeItemTitle'
                    //'click .title h4' : 'openWidgetEditor'
                },

                /**
                 * Initialize the textbox item dialog.
                 */
                initializeDialog: function () {
                    var thisView = this;
                    this.on('open_dialog', function () {
                        //var a = thisView;
                    }, this);

                    this.on('open_dialog_complete', function () {
                        thisView.$('.current-textbox-title').text('{'+thisView.textbox.value+'}');
                        thisView.builder.trigger('builder_resize');
                    });

                    this.on('textbox_item_model_change', function(event){
                        //thisView.$('.description').html(event[0].model.getTitle());
                    });

                    this.on('close_dialog', function( ) {
                        //var a = thisView;
                    });
                    this.on('close_dialog_complete', function( ) {});
                },

                /**
                 * Render the textbox item layouts dialog
                 */
                render: function () {
                    var thisView = this;
                    this.renderDialog(this.parseDialogContent($('#cleversoft-panels-textboxs-item').html(), {}));
                    var widgetTab = {};
                    if(_.size(this.textbox.data) > 0) {
                        widgetTab.title = panelsOptions.widgets[this.textbox.data.get('class')] ? panelsOptions.widgets[this.textbox.data.get('class')].title : panelsOptions.widgets[this.textbox.data.get('type')].title;
                        widgetTab.description = this.textbox.data.get('values')['text'];
                    }
                    var $t = $(this.textboxItemEditorTemplate({
                            'widget': widgetTab,
                            'id': this.textbox.id,
                            'value':this.textbox.value
                        })
                    );/// give the data here
                    $t.data(!_.isUndefined(thisView.textbox.data) ? thisView.textbox.data : {}).appendTo(this.$('.panels-textbox-editor'));
                },
                /*
                 * set builder
                 */
                setBuilder: function(builder,model) {
                    if(!this.builder) {
                        if(!builder) {
                            var builderModel = new panels.model.builder();
                            // Now for the view to display the builder
                            this.builder = new panels.view.builder({
                                model: builderModel,
                                config: {}
                            });
                        } else {
                            this.builder = builder;
                        }

                    }
                },
                /*
                 *
                 */
                setEditDialog: function(dialog) {
                    this.textbox.editDialog  = dialog;
                },
                /*
                 *
                 */
                openWidgetEditor: function(e) {
                    this.updateDialog();
                    if(_.isUndefined(this.textbox.editDialog)) {
                        //var dialog = new panels.dialog.childWidget({model: !_.isUndefined(this.model) ? this.model : (this.textbox.data ? this.textbox.data : new panels.model.childWidget())});
                        //dialog.setBuilder(this.builder);
                        //dialog.render();
                        //this.textbox.editDialog = dialog;
                        // Create the view for the widget
                        var view = new panels.view.childWidget({
                            model: !_.isUndefined(this.model) ? this.model : (this.textbox.data ? this.textbox.data : new panels.model.childWidget())
                        });
                        view.widget = view.model.widget;

                        // Render and load the form if this is a duplicate
                        this.textbox.editDialog = view.getEditDialog().setBuilder(this.builder);
                    }
                    this.textbox.editDialog.openDialog();
                },
                /*
                 *
                 */
                insertChildHandle: function() {
                    this.updateDialog();
                    //reset textbox object
                    this.textbox = null;//reset textbox object;
                    this.builder.model.set('current_condition', null);
                    this.builder.dialogs.textboxItemEditor.$el.remove();
                    this.builder.dialogs.textboxItemEditor = null;
                    //reload panel data
                    this.builder.model.refreshPanelsData();
                },
                /*
                 * set option
                 */
                setTab: function(Tab) {
                    this.textbox = Tab;
                },
                /*
                 *get textbox data
                 */
                getTab: function(){
                    return this.textbox;
                },

                /*
                 *
                 */
                removeCurrentTabDialog: function(options) {
                    if(this.builder.dialogs.textboxItemEditor) {
                        this.builder.dialogs.textboxItemEditor.$el.remove();
                        this.builder.dialogs.textboxItemEditor = null;
                    }
                },
                /*
                 *
                 */
                setCurrentTabDialog: function(options) {
                    this.builder.dialogs.textboxItemEditor = this;
                    this.builder.model.set('textboxTitle'+[this.textbox.id], this.textbox.value);
                },

                /*
                 * change textbox item title on preview
                 */
                changeItemTitle: function(e) {
                    var $element = $(e.target);
                    var $val = $element.val();
                    var $widget_id = this.builder.activeWidget.get('widget_id');//$(e.target).attr('name') = textbox+key
                    $('.cs-preview iframe').contents().find('div[data-id="'+$widget_id+'"] a[data-id="'+$element.attr('name')+'"]').text($val);
                    this.$('.current-textbox-title').text('{'+$val+'}');
                    this.textbox.value = $val;
                    this.builder.$el.find('div[data-id="#textbox'+this.textbox.id+'"] .widget-info').text($val);
                    this.builder.model.set('textboxTitle'+[this.textbox.id], $val);
                }
            });

        }, {}],

        /*
         * open banner item editor
         */
        43: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = panels.view.dialog.extend({

                bannerTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-banner').html())),

                builder: null,
                widget: null,

                events: {
                    'click .cs-update': 'closeBannerItemHandle',
                    'click .cs-close': 'closeBannerItemHandle',
                    'click .cs-apply': 'applyLayoutHandle',
                    'click .with-thumbnail': 'setActiveLayout'
                },

                /**
                 * Initialize the tab item dialog.
                 */
                initializeDialog: function () {
                    var thisView = this;
                    this.on('open_dialog', function () {
                    }, this);

                    this.on('open_dialog_complete', function () {
                    });
                    this.on('close_dialog', function( ) {
                    });
                    this.on('close_dialog_complete', function( ) {});
                },

                /**
                 * Render the tab item layouts dialog
                 */
                render: function () {
                    var thisView = this;
                    this.renderDialog(this.parseDialogContent($('#cleversoft-panels-banner-items').html(), {}));
                    var $t = $(this.bannerTemplate({}));/// give the data here
                    $t.find('li').first().addClass('active');
                    $t.data({}).appendTo(this.$('.panels-banner_editor'));
                },

                /*layout handle
                 */
                applyLayoutHandle: function(e) {
                    var thisView = this;
                    var $layout = this.widget.get('layout');
                    var $$name = _.isUndefined(this.widget.get('layout_name')) ? 'blank' : this.widget.get('layout_name');
                    if(_.isUndefined($layout)) {$layout = 1;}
                    var $childs = this.widget.get('layouts')[parseInt($layout) - 1];

                    var $defaults = {};
                    if ($childs.length > 0 ) {
                        $defaults = _.isUndefined($childs[0].defaults) ? {} : $childs[0].defaults;
                        _.each($childs, function($child, $key){
                            //insert childs into banner panel
                            var widget = new panels.model.childWidget({
                                class: $child.class,
                                type : $child.type,
                                idx: $key,
                                // has_button: $child.has_button,
                                items: _.isUndefined($child.items) ?  new panels.collection.childWidgets() : $child.items,
                                widget_id: panels.helpers.utils.generateUUID(),
                                values: _.isUndefined($child.values) ? {} : $child.values
                            });
                            widget.widget = thisView.widget;
                            widget.builder = thisView.builder;
                            //
                            if( !_.isUndefined($child.childs)) {
                                _.each($child.childs, function($item, $k){
                                    //add childs item into the current child
                                    //
                                    var child_widget = new panels.model.childWidget({
                                        class: $item.class,
                                        type : $item.type,
                                        idx : $key,
                                        // has_button: $item.has_button,
                                        items: _.isUndefined($item.items) ?  new panels.collection.childWidgets() : $item.items,
                                        widget_id: panels.helpers.utils.generateUUID(),
                                        values: _.isUndefined($item.values) ? {} : $item.values
                                    });
                                    child_widget.widget = thisView.widget;
                                    child_widget.builder = thisView.builder;
                                    widget.get('items').add(child_widget);
                                });
                            }

                            thisView.widget.get('items').add(widget);
                        });
                    }
                    this.widget.set('banner_layout',parseInt($layout) - 1);
                    this.widget.set('layout_name', $$name);
                    if(!_.isUndefined(this.widget.is_inner) ){
                        this.widget.widget.get('items').add(this.widget);
                    } else {
                        this.widget.cell.get('widgets').add(this.widget);
                        //this.onAddItem(this.widget.get('items'),new panels.collection.childWidgets(),{'loadForm': true,'dialog': false, 'loopChilds': true, 'parentWIdx': this.widget.get('widget_id'), 'parentWidget': this.widget});
                    }
                    if($defaults !== {}) {
                        this.widget.set('values',$defaults);
                    }
                    //
                    this.updateDialog();
                    this.builder.model.refreshPanelsData();
                },
                closeBannerItemHandle: function() {

                    //update model data
                    this.updateDialog();
                    this.builder.model.set('current_condition', null);
                    //reset tab object
                    this.banner = null;//reset tab object;
                    if(!_.isUndefined(this.builder.dialogs.bannerItemEditor) ){
                        this.builder.dialogs.bannerItemEditor.$el.remove();
                        this.builder.dialogs.bannerItemEditor = null;
                        this.builder.model.refreshPanelsData();
                    }
                },
                /*set builder
                 */
                setBuilder: function($builder) {
                    this.builder = $builder;
                },
                /*set widget view
                 */
                setWidget: function($widget) {
                    this.widget = $widget;
                },
                /*
                 set active layout
                 */
                setActiveLayout: function(e){
                    var target = $(e.target);
                    target.closest('.clever-banner').find('li.active').removeClass('active');
                    target.closest('li').addClass('active');
                    //re-build panel data
                    var layout = target.closest('.with-thumbnail');
                    var $$name = layout.attr('title').toLowerCase().replace(' ', '-');
                    this.widget.set('totalBannerpanels', this.widget.get('layouts')[parseInt(layout.attr('data-layout'))-1].length);
                    this.widget.set('layout', parseInt(layout.attr('data-layout')));
                    this.widget.set('layout_name', $$name);
                }
            });
        }, {}],
        /*
         * open scrollto item editor
         */
        44: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = panels.view.dialog.extend({

                scrolltoItemEditorTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-scrolltos-item-editor').html())),

                builder: null,
                dialogClass: 'cs-panels-dialog-scrollto-item-editor-layouts',
                dialogIcon: 'layouts',

                events: {
                    'click .cs-update': 'insertChildHandle',
                    'click .cs-close': 'closeChildHandle',
                    'click .cs-element-insert': 'addChildWidget',
                    'click .cs-toolbar .cs-delete': 'deleteHandler',
                    'click .cs-insert': 'insertChildHandle', //this.builder.model.refreshPanelsData();
                    'keyup .scrollto-item-title': 'changeItemTitle'
                    //'click .title h4' : 'openWidgetEditor'
                },

                /**
                 * Initialize the tab item dialog.
                 */
                initializeDialog: function () {
                    var thisView = this;
                    this.on('open_dialog', function () {
                        //var a = thisView;
                    }, this);

                    this.on('open_dialog_complete', function () {
                        thisView.$('.current-scrollto-title').text('{'+thisView.scrollto.value+'}');
                        thisView.builder.trigger('builder_resize');
                    });

                    this.on('scrollto_item_model_change', function(event){
                        //thisView.$('.description').html(event[0].model.getTitle());
                    });

                    this.on('close_dialog', function( ) {
                        //var a = thisView;
                    });
                    this.on('close_dialog_complete', function( ) {});
                },

                /**
                 * Render the scrollto item layouts dialog
                 */
                render: function () {
                    var thisView = this;
                    this.renderDialog(this.parseDialogContent($('#cleversoft-panels-scrolltos-item').html(), {}));
                    var widgetScrollTo = {};
                    var modelData = this.scrollto.modelData;
                    var $t = $(this.scrolltoItemEditorTemplate({
                            'widget': widgetScrollTo,
                            'id': this.scrollto.id,
                            'value':this.scrollto.value,
                            'title':modelData.title,
                            'link':modelData.link,
                            'style':modelData.style
                        })
                    );/// give the data here
                    $t.data(!_.isUndefined(thisView.scrollto.data) ? thisView.scrollto.data : {}).appendTo(this.$('.panels-scrollto-editor'));
                },
                /*
                 * set builder
                 */
                setBuilder: function(builder,model) {
                    if(!this.builder) {
                        if(!builder) {
                            var builderModel = new panels.model.builder();
                            // Now for the view to display the builder
                            this.builder = new panels.view.builder({
                                model: builderModel,
                                config: {}
                            });
                        } else {
                            this.builder = builder;
                        }

                    }
                },
                /*
                 *
                 */
                setEditDialog: function(dialog) {
                    this.scrollto.editDialog  = dialog;
                },
                /*
                 *
                 */
                insertChildHandle: function() {
                    this.updateDialog();
                    this.updateModelScrollToData();
                    //reset tab object
                    this.scrollto = null;//reset tab object;
                    this.builder.model.set('current_condition', null);
                    this.builder.dialogs.scrolltoItemEditor.$el.remove();
                    this.builder.dialogs.scrolltoItemEditor = null;
                    //reload panel data
                    this.builder.model.refreshPanelsData();
                },
                closeChildHandle: function() {
                    this.updateDialog();
                    this.updateModelScrollToData();
                    //reset tab object
                    this.scrollto = null;//reset tab object;
                    this.builder.model.set('current_condition', null);
                    this.builder.dialogs.scrolltoItemEditor.$el.remove();
                    this.builder.dialogs.scrolltoItemEditor = null;
                    //reload panel data
                    // this.builder.model.refreshPanelsData();
                },
                /**
                 * When the user clicks delete.
                 *
                 * @returns {boolean}
                 */
                deleteHandler: function () {

                    var self = this;
                    var $panelsItems = this.builder.activeWidget.get('panelsItems');
                    var $widget = this.builder.activeWidget.get('widget_id');

                    delete $panelsItems[this.scrollto.id];
                    $panelsItems = this.removeEmptyItem($panelsItems);

                    this.builder.activeWidget.set('panelsItems',$panelsItems);
                    this.builder.activeWidget.set('totalScrollTopanels', $panelsItems.length);

                    this.updateDialog();

                    this.builder.$el.find('.element-item-wrapper[data-widget-id="'+$widget+'"]')[this.scrollto.id].remove();

                    this.builder.model.refreshPanelsData();
                    var $i = 0;
                    var $j = 0;

                    _.each(this.builder.$el.find('.element-item-wrapper[data-widget-id="'+$widget+'"]'), function($item) {
                        if ($($item).attr('data-id') != '#scrollto{$key}') {
                            $($item).attr('data-id',"#scrollto"+$i);
                            $i++;
                        }
                        
                    });

                    _.each(this.builder.$el.find('.element-item-wrapper .scrollto-item[data-widget-id="'+$widget+'"]'), function($item) {
                        if ($($item).attr('data-id') != '#scrollto{$key}') {
                            $($item).attr('data-id',"#scrollto"+$j);
                            $j++;
                        }
                    });

                },

                removeEmptyItem: function( $items) {
                    var $index = 0;
                    var $new_items = new Array();

                    _.each($items, function($temps, $key) {
                        if(_.size($temps) > 0) {
                            if(jQuery.isArray($temps)) {
                                $temps = $temps.filter(function ($temp) {
                                    return _.size($temp) > 0
                                });
                            }
                            $new_items[$index] = $temps;
                            $index++;
                        }
                    });
                    return $new_items;
                },
                /*
                 * set option
                 */
                setScrollTo: function(ScrollTo) {
                    this.scrollto = ScrollTo;
                },
                /*
                 *get scrollto data
                 */
                getScrollTo: function(){
                    return this.scrollto;
                },
                updateModelScrollToData: function() {
                    var thisView = this;
                    var $el = this.$el;
                    var $title  = $el.find('input[name="title"]').val();
                    var $link  = $el.find('input[name="link"]').val();
                    var $style  = $el.find('input[name="style"]:checked').val();
                    var $data = {
                        'title' :$title,
                        'link' :$link,
                        'style' :$style
                    }

                    var $panelsItems = this.builder.activeWidget.get('panelsItems');
                    var $panelsItem = $panelsItems[this.scrollto.id];
                    var $newOne = $.extend($panelsItem, $data);

                    this.builder.activeWidget.get('panelsItems')[this.scrollto.id] = $newOne;
                },
                /*
                 * show dialog add widget
                 */

                addChildWidget: function() {
                    this.updateDialog();
                    this.builder.dialogs.childWidgets.openDialog();
                    //this.builder.model.refreshPanelsData();
                },

                /*
                 *
                 */
                removeCurrentScrollToDialog: function(options) {
                    if(this.builder.dialogs.scrolltoItemEditor) {
                        this.builder.dialogs.scrolltoItemEditor.$el.remove();
                        this.builder.dialogs.scrolltoItemEditor = null;
                    }
                },
                /*
                 *
                 */
                setCurrentScrollToDialog: function(options) {
                    this.builder.dialogs.scrolltoItemEditor = this;
                },

                /*
                 * change scrollto item title on preview
                 */
                changeItemTitle: function(e) {
                    var $element = $(e.target);
                    var $val = $element.val();
                    var $widget_id = this.builder.activeWidget.get('widget_id');

                    $('.cs-preview iframe').contents().find('div[data-id="'+$widget_id+'"] a[data-id="'+$element.attr('name')+'"]').text($val);
                    this.$('.current-scrollto-title').text('{'+$val+'}');
                    this.scrollto.value = $val;
                    this.builder.$el.find('#'+$widget_id+' .scrollto-item[data-id="#scrollto'+this.scrollto.id+'"] .widget-info').text($val);
                    this.builder.model.set('scrolltoTitle'+$widget_id+[this.scrollto.id], $val);
                }
            });

        }, {}],
        /*
         * open row item editor
         */
        45: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = panels.view.dialog.extend({

                rowItemEditorTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-row-item-editor').html())),

                builder: null,
                dialogClass: 'cs-panels-dialog-row-item-editor-layouts',
                dialogIcon: 'layouts',
                rowStack : null,

                events: {
                    'click .cs-update': 'insertRowItemHandle',
                    'click .cs-close': 'closeRowItemHandle',
                    'click .cs-element-insert': 'addChildWidget',
                    'click .cs-toolbar .cs-delete': 'deleteHandler',
                    'change input[name="row_item_width"]' : 'changePreviewWidth',
                    'change input[name="row_item_depth"]' : 'changePreviewDepth',
                    'change input[name="row_item_depth_hover"]' : 'changePreviewDepthHover',
                    'click .cs-insert': 'insertRowItemHandle'
                },

                /**
                 * Initialize the tab item dialog.
                 */
                initializeDialog: function () {
                    var thisView = this;
                    this.on('open_dialog', function () {

                    }, this);

                    this.on('open_dialog_complete', function () {
                        thisView.$('.slider-ui').trigger('contentUpdated');
                        thisView.addChangeVl(thisView);
                        // thisView.deleteHandler(thisView);
                        thisView.builder.trigger('builder_resize');
                    });

                    this.on('row_item_model_change', function(event){
                    });

                    this.on('close_dialog', function( ) {

                    });
                    this.on('close_dialog_complete', function( ) {});
                },

                /**
                 * Render the tab item layouts dialog
                 */
                render: function () {
                    var thisView = this;
                    this.renderDialog(this.parseDialogContent($('#cleversoft-panels-row-item').html(), {}));
                    var widgetRow = {};
                    var $dept_unique = panels.helpers.utils.generateUUID();
                    var $dept_hover_unique = panels.helpers.utils.generateUUID();
                    var modelData = this.row.modelData;

                    //calculate the height;
                    var $the_height = parseFloat(modelData.height) / 60;
                    if ($the_height === (1/1)) {
                        var height = '1-1';
                    } else if($the_height === (1/2)) {
                        var height = '1-2';
                    }else if($the_height === (1/3)) {
                        var height = '1-3';
                    }else if($the_height === (2/3)) {
                        var height = '2-3';
                    }else if($the_height === (1/4)) {
                        var height = '1-4';
                    }else if($the_height === (3/4)) {
                        var height = '3-4';
                    }

                    //
                    var $t = $(this.rowItemEditorTemplate({
                            'widget': widgetRow,
                            'widget_id': thisView.builder.activeWidget.get('has_child') == 'row' ? thisView.builder.activeWidget.get('widget_id') : this.row.widget_id,
                            'id': this.row.id,
                            'value':this.row.value,
                            'depth' : {
                                'value' : modelData.col_depth,
                                'unique': $dept_unique
                            },
                            'depth_hover' : {
                                'value' : modelData.col_hover_depth,
                                'unique': $dept_hover_unique
                            },
                            'x': modelData.x,
                            'y': modelData.y,
                            'width': modelData.width,
                            'height': height,
                            'model_height': modelData.height,
                            'animate': _.isUndefined(modelData.animate) ? 'none' : modelData.animate,
                            'classes': _.isUndefined(modelData.class) ? '' : modelData.class
                        })
                    );/// give the data here
                    $t.data(!_.isUndefined(thisView.row.data) ? thisView.row.data : {}).appendTo(this.$('.panels-row-editor'));
                },
                changePreviewWidth: function(event) {
                    var thisView = this;
                    var $rowItemID = thisView.row.id;
                    var $previewRowID = '#row'+$(event.target).closest('.cs-row-item-wrapper').attr('data-id');
                    var windowjQuery = $('.cs-preview iframe')[0].contentWindow.jQuery;

                    var $el = windowjQuery($previewRowID).find('div[data-key="'+$rowItemID+'"]');
                    $el.attr('data-width',parseInt($(event.target).val()));

                    var $elEdit = windowjQuery($previewRowID).children('.cs-edit-element-section').eq($rowItemID);
                    $elEdit.attr('data-width',parseInt($(event.target).val()));


                    $('.element-item[data-widget-id="'+thisView.builder.activeWidget.get('widget_id')+'"][data-id="#row'+$rowItemID+'"] span.widget-info').html($(event.target).val()+'/12');
                },
                /*
                 * change depth value in preview
                 */
                changePreviewDepth: function(event) {
                    var $el = $('.cs-preview iframe').contents().find('#row' + this.builder.activeWidget.get('widget_id')).children('div').eq(this.row.id).find('.row-stack-item-content');
                    $el.attr('class',$el.attr('class').replace('box-shadow-' + $(event.currentTarget).data('oldValue'), 'box-shadow-' + $(event.target).val() ));
                },
                /*
                 * change depth value in preview
                 */
                changePreviewDepthHover: function(event) {
                    var $el = $('.cs-preview iframe').contents().find('#row' + this.builder.activeWidget.get('widget_id')).children('div').eq(this.row.id).find('.row-stack-item-content');
                    $el.attr('class',$el.attr('class').replace('box-shadow-' + $(event.currentTarget).data('oldValue')+'-hover', 'box-shadow-' + $(event.target).val()+'-hover' ));
                },
                /*
                 * add change Vl
                 */
                addChangeVl: function(thisView) {
                    var thisK = this;
                    var fields = this.$('.change-hidden-value');
                    if(fields.length > 0) {
                        fields.each(function(el){
                            $(this).on('click',function(el){
                                //console.log($('.cs-preview iframe').contents().$.find('#row' + thisView.builder.activeWidget.get('widget_id')).data());
                                var $name = $(this).closest('ul').find('input[type="hidden"]').attr('name');
                                if($name.trim() ===  'row_item_width') {
                                    thisView.builder.$el.find('#'+ thisView.builder.activeWidget.get('widget_id') +' .element-items-wrapper').children('.element-item-wrapper').eq(thisView.row.id).find('.widget-info').text($(this).text().trim()+'/12');
                                    thisView.row.value = $(this).text().trim()+'/12';
                                }
                                $(this).siblings().removeClass('active');
                                $(this).addClass('active');
                                if($name.trim() ===  'row_item_height') {
                                    var $height = $(this).text().trim();
                                    $height = $height.split('-');
                                    var neww = (60 * parseInt($height[0])) /  parseInt($height[1]);
                                    $(this).closest('ul').find('input[type="hidden"]').val(neww);
                                } else {
                                    $(this).closest('ul').find('input[type="hidden"]').val($(this).text().trim());
                                }
                                $(this).closest('ul').find('input[type="hidden"]').trigger('change');
                            })
                        })
                    }
                },
                /*
                 * set builder
                 */
                setBuilder: function(builder,model) {
                    if(!this.builder) {
                        if(!builder) {
                            var builderModel = new panels.model.builder();
                            // Now for the view to display the builder
                            this.builder = new panels.view.builder({
                                model: builderModel,
                                config: {}
                            });
                        } else {
                            this.builder = builder;
                        }

                    }
                },
                /*
                 *
                 */
                setEditDialog: function(dialog) {
                    this.row.editDialog  = dialog;
                },
                /*
                 *
                 */
                insertRowItemHandle: function() {
                    //update model data
                    this.updateModelRowData();
                    this.updateDialog();
                    this.builder.model.set('current_condition', null);
                    //reset tab object
                    this.row = null;//reset tab object;
                    this.builder.dialogs.rowItemEditor.$el.remove();
                    this.builder.dialogs.rowItemEditor = null;
                    //reload panel data
                    this.builder.model.refreshPanelsData();
                },
                /*
                 *
                 */
                closeRowItemHandle: function() {
                    //update model data
                    this.updateModelRowData();
                    this.updateDialog();
                    this.builder.model.set('current_condition', null);
                    //reset tab object
                    this.row = null;//reset tab object;
                    this.builder.dialogs.rowItemEditor.$el.remove();
                    this.builder.dialogs.rowItemEditor = null;
                    //reload panel data
                    // this.builder.model.refreshPanelsData();
                },
                /**
                 * When the user clicks delete.
                 *
                 * @returns {boolean}
                 */
                deleteHandler: function () {
                    var self = this;
                    if (!_.isUndefined(this.builder.activeWidget.get('items').models[this.row.id])) {
                        
						let $panelsItems = this.builder.activeWidget.get('panelsItems');
						let tmp_arr = [];
						let innerrow_arr = [];
						_.each(self.builder.activeWidget.get('items').models, function($it){
							console.log('$it >>>>', $it);
							let $idx = $it.get('idx');
							if (typeof $idx !== 'undefined') {
								if ((typeof $it.get('inner_lv') !== 'undefined' && $it.get('inner_lv') == 1) || typeof $it.get('inner_lv') === 'undefined') {
									if ($idx == self.row.id) {										
										tmp_arr.push($it);
										innerrow_arr.push($it.get('widget_id'));
									} else if (Number($idx) > Number(self.row.id)) {
										$idx = Number($idx) -1;
										$it.set('idx', $idx);
									}
								} else {
									if (typeof $it.get('parent_widget_id') !== 'undefined' && innerrow_arr.indexOf($it.get('parent_widget_id')) !== -1) {
										tmp_arr.push($it);
										innerrow_arr.push($it.get('widget_id'));
									}
								}
							}
						});
						this.builder.activeWidget.set('items', newCollection);
						if (typeof $panelsItems[this.row.id] !== 'undefined') {							
							this.builder.activeWidget.set('panelsItems',$panelsItems.filter((item, idx) => idx != this.row.id));
						}
						if (_.size(tmp_arr)) {
							self.builder.activeWidget.get('items').remove(tmp_arr);
						}						
                    } else {
                        if (this.builder.activeWidget.get('has_child') !== 'row') {
                            var $panelsItems = this.row.panels_item;
                            var $widget = this.row.widget_id;
                        } else {
                            var $panelsItems = this.builder.activeWidget.get('panelsItems');
                            var $widget = this.builder.activeWidget.get('widget_id');
                        }

                        delete $panelsItems[this.row.id];
                        $panelsItems = this.removeEmptyItem($panelsItems);

                        if (this.builder.activeWidget.get('has_child') !== 'row') {
                            var $items = this.builder.activeWidget.get('items').models;
                            _.each($items, function($item) {
                                if ($item.get('widget_id') == $widget) {
                                   $item.set('panelsItems',$panelsItems);
                                   $item.set('totalRowpanels',$panelsItems.length);
                                   var $layout_by_id = self.removeEmptyItem($item.get('layouts')[$item.get('row_layout')]);
                                   delete $item.get('layouts')[$item.get('row_layout')];
                                   $item.get('layouts')[$item.get('row_layout')] = $layout_by_id;
                                   $item.get('items').models[self.row.id].destroy();     
                                }                  
                            });
                        } else {
                            this.builder.activeWidget.set('panelsItems',$panelsItems);
                        }
                    }
                    this.builder.activeWidget.set('totalRowpanels',this.builder.activeWidget.get('totalRowpanels')-1);

                    
                    this.updateDialog();

                    var $widget = this.builder.activeWidget.get('widget_id');

                    this.builder.$el.find('.element-item-wrapper[data-widget-id="'+$widget+'"]')[self.row.id].remove();

                    this.builder.model.refreshPanelsData();
                    var $i = 0;
                    var $j = 0;

                    _.each(this.builder.$el.find('.element-item-wrapper[data-widget-id="'+$widget+'"]'), function($item) {
                        if ($($item).attr('data-id') != '#row{$key}') {
                            $($item).attr('data-id',"#row"+$i);
                            $i++;
                        }
                        
                    });
                    let $contit = 0;
					console.log('this.builder.$el ', this.builder.$el.attr('class'), $widget);
                    _.each(this.builder.$el.find('[class^=element-item-wrapper][data-widget-id='+$widget+']'), function($item){
                        if ($($item).find('span[class*=cs-element-insert][parent-widget-id='+$widget+']').attr('idx') !== '{$key}') {
                            $($item).find('span[class*=cs-element-insert][parent-widget-id='+$widget+']').attr('idx', $contit);
                            $contit++;
                        }
                    });
                    _.each(this.builder.$el.find('.element-item-wrapper .row-item[data-widget-id="'+$widget+'"]'), function($item) {
                        if ($($item).attr('data-id') != '#row{$key}') {
                            $($item).attr('data-id',"#row"+$j);
                            $j++;
                        }
                    });

                },

                removeEmptyItem: function( $items) {
                    var $index = 0;
                    var $new_items = new Array();

                    _.each($items, function($temps, $key) {
                        if(_.size($temps) > 0) {
                            if(jQuery.isArray($temps)) {
                                $temps = $temps.filter(function ($temp) {
                                    return _.size($temp) > 0
                                });
                            }
                            $new_items[$index] = $temps;
                            $index++;
                        }
                    });
                    return $new_items;
                },

                /*
                 * set option
                 */
                setRow: function(row) {
                    this.row = row;
                },
                //update model data
                updateModelRowData: function() {
                    var thisView = this;
                    var $el = this.$el;
                    var $width  = $el.find('input[name="row_item_width"]').val();
                    var $height = $el.find('input[name="row_item_height"]').val();
                    var $gsx = $el.find('input[name="row_item_x"]').val();
                    var $gsy = $el.find('input[name="row_item_y"]').val();
                    var $class = $el.find('input[name="row_item_class"]').val();
                    var $depth = $el.find('input[name="row_item_depth"]').val();
                    var $depth_hover = $el.find('input[name="row_item_depth_hover"]').val();
                    var $animate = $el.find('select[name="row_item_animate"]').val();
                    var $data = {
                        'width' :$width,
                        'height' : $height,
                        'x' : $gsx,
                        'y' : $gsy,
                        'class' : $class,
                        'col_depth' : $depth,
                        'col_hover_depth': $depth_hover,
                        'animate': $animate
                    }

                    if (this.builder.activeWidget.get('has_child') !== 'row') {
                        var $panelsItem = thisView.row.modelData;
                        var $widget = thisView.row.widget_id;

                        var $newOne = $.extend($panelsItem, $data);
                        var $items = thisView.builder.activeWidget.get('items').models;

                        _.each($items, function($item) {
                            if ($item.get('widget_id') == $widget) {
                                $item.get('panelsItems')[thisView.row.id] = $newOne;
                                return false;
                            }                  
                        });
                    } else {
                        var $panelsItems = this.builder.activeWidget.get('panelsItems');
                        var $panelsItem = $panelsItems[this.row.id];
                        var $newOne = $.extend($panelsItem, $data);

                        this.builder.activeWidget.get('panelsItems')[this.row.id] = $newOne;
                    }

                    //this.row.modelData[this.row.id] = $data;

                },
                /*
                 *get row data
                 */
                getRow: function(){
                    return this.row;
                },
                /*
                 * show dialog add widget
                 */

                addChildWidget: function() {
                    this.updateDialog();
                    this.builder.dialogs.childWidgets.openDialog();
                    //this.builder.model.refreshPanelsData();
                },

                /*
                 *
                 */
                removeCurrentRowDialog: function(options) {
                    if(this.builder.dialogs.rowItemEditor) {
                        this.builder.dialogs.rowItemEditor.$el.remove();
                        this.builder.dialogs.rowItemEditor = null;
                    }
                },
                /*
                 *
                 */
                setCurrentRowDialog: function(options) {
                    this.builder.dialogs.rowItemEditor = this;
                    //this.builder.model.set('rowTitle'+[this.row.id], this.row.value);
                }
            });

        }, {}],
        /*
         * open row layout item editor
         */
        46: [function (require, module, exports) {
            var panels = window.panels, $ = jQuery;

            module.exports = panels.view.dialog.extend({

                rowLayoutsTemplate: _.template(panels.helpers.utils.processTemplate($('#cleversoft-panels-row-layouts').html())),

                builder: null,
                widget: null,

                events: {
                    'click .cs-update': 'closeRowItemHandle',
                    'click .cs-close': 'closeRowItemHandle',
                    'click .cs-apply': 'applyLayoutHandle',
                    'click .with-thumbnail': 'setActiveLayout'
                },

                /**
                 * Initialize the row item dialog.
                 */
                initializeDialog: function () {
                    var thisView = this;
                    this.on('open_dialog', function () {
                    }, this);

                    this.on('open_dialog_complete', function () {
                    });
                    this.on('close_dialog', function( ) {
                    });
                    this.on('close_dialog_complete', function( ) {});
                },

                /**
                 * Render the row item layouts dialog
                 */
                render: function () {
                    var thisView = this;
                    this.renderDialog(this.parseDialogContent($('#cleversoft-panels-row-layouts-items').html(), {}));
                    var $t = $(this.rowLayoutsTemplate({}));/// give the data here
                    $t.data({}).appendTo(this.$('.panels-row-layout_editor'));
                },

                /*layout handle
                 */
                applyLayoutHandle: function(e) {
                    if(!_.isUndefined(this.widget.is_inner) ){
                        this.widget.widget.get('items').add(this.widget);
                    } else {
                        this.widget.cell.get('widgets').add(this.widget);
                    }
                    this.updateDialog();
                    this.builder.model.refreshPanelsData();
                },
                closeRowItemHandle: function() {

                    //update model data
                    this.updateDialog();
                    this.builder.model.set('current_condition', null);
                    //reset tab object
                    this.row = null;//reset tab object;
                    if (!_.isUndefined(this.builder.dialogs.rowItemEditor)) {
                        this.builder.dialogs.rowItemEditor.$el.remove();
                        this.builder.dialogs.rowItemEditor = null;
                        this.builder.model.refreshPanelsData();
                    }
                },
                /*set builder
                 */
                setBuilder: function($builder) {
                    this.builder = $builder;
                },
                /*set widget view
                 */
                setWidget: function($widget) {
                    this.widget = $widget;
                },
                /*
                 set active layout
                 */
                setActiveLayout: function(e){
                    var target = $(e.target);
                    target.closest('.clever-row').find('li.active').removeClass('active');
                    target.closest('li').addClass('active');
                    //re-build panel data
                    var layout = target.closest('.with-thumbnail');
                    $(layout.attr('data-value-id')).val(layout.attr('data-layout'));
                    this.widget.set('totalRowpanels', this.widget.get('layouts')[layout.attr('data-layout')].length);
                    this.widget.set('row_layout', layout.attr('data-layout'));
                    
                }
            });
        }, {}]

    }, {}, [16]);
});