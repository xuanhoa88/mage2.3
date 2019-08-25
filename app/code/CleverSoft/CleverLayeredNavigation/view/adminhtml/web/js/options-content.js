define([
    'jquery',
    'Magento_Ui/js/modal/alert'
], function ($, alert) {
    'use strict';

    return function (config) {
        var isButtonPresent = false;
        var frontendInputSelector = $("#frontend_input");
        var displayModeSelector = $("#display_mode");        
        var frontendInput = frontendInputSelector.val();        
        var id = 'manage-options-panel';

        if(frontendInput == 'swatch_visual') {
            id = 'swatch-visual-options-panel'
        } else if(frontendInput == 'swatch_text') {
            id = 'swatch-text-options-panel';
        }
        
        var checkMode = function () {
            var displayMode = displayModeSelector.val();
            if (displayMode == 0 || displayMode == '0') {
                frontendInputSelector.val('swatch_text');
            } else if (displayMode == 1 || displayMode == '1') {
                frontendInputSelector.val('select');
            } else if (displayMode == 4 || displayMode == '4' || displayMode == 5 || displayMode == '5') {
                frontendInputSelector.val('swatch_visual');
            }
        }

        displayModeSelector.change(function() {
            checkMode();
        });
        
        var addButtonsFunction = function(){
            if(isButtonPresent) {
                return;
            }

            if($('#'+id+' td.col-delete').length == 0) {
                setTimeout(addButtonsFunction, 300);
                return;
            }
            isButtonPresent = true;
            $('#'+id+' td.col-delete').each(function(){
                var optionId = $(this).attr('id').replace('delete_button_swatch_container_', '');
                optionId = optionId.replace('delete_button_container_', '');

                $(this).prepend('<button id="settings_button_'+optionId+'" class="clevershopby-button-option action-settings" data-option-id="'+optionId+'"><span>'+config.buttonText+'</span></button>');
            });

            $('.clevershopby-button-option').on('click', function(e){
                var $button = $(this);
                var optionId = $button.data('option-id');
                //alert(optionId);

                var url = config.url.replace('__option_id__', optionId);
                var modalListSettings = alert({
                    title: config.modalHeadText,
                    content: $('#loader-spinner-html').html(),
                    buttons: [
                        {
                            text: 'Save',
                            class: 'action-primary action-accept',
                            click: function () {
                                $("#edit_options_form").submit();
                                //this.closeModal(true);
                                //amFinderCloseImportPopUp();
                            }
                        },
                        {
                            text: 'Cancel',
                            class: 'action-secondary',
                            click: function () {
                                this.closeModal(true);
                                //amFinderCloseImportPopUp();
                            }
                        }
                    ]
                });

                var functionUpdateModal = function(data){
                    $(modalListSettings).html(data);
                    $(modalListSettings).trigger('contentUpdated');

                    $('#preview_form').submit(function(e){
                        var formObj = $(this);
                        $("#edit_options_form").append($('#loader-spinner-html').html());
                        var formURL = formObj.attr("action");
                        var formData = formObj.serialize();

                        $.ajax({
                            url: formURL,
                            type: 'GET',
                            data:  formData,
                            cache: false,
                            processData:false,
                            success: functionUpdateModal
                        });
                        e.preventDefault(); //Prevent Default action.
                        e.stopPropagation();
                    });

                    $("#edit_options_form").submit(function(e)
                    {
                        var formObj = $(this);
                        $("#edit_options_form").append($('#loader-spinner-html').html());
                        var formURL = formObj.attr("action");
                        var formData = new FormData(this);
                        //$.scrollTo(modalListSettings);
                        /*window.scrollTo(0, 0);
                        $(modalListSettings).animate({
                            scrollTop: 0
                        }, 700);*/

                        $.ajax({
                            url: formURL,
                            type: 'POST',
                            data:  formData,
                            mimeType:"multipart/form-data",
                            contentType: false,
                            cache: false,
                            processData:false,
                            success: functionUpdateModal
                        });
                        e.preventDefault(); //Prevent Default action.
                        e.stopPropagation();
                    });

                };
                $.ajax({
                    url: url,
                    dataType: "html",
                    data: {form_key: FORM_KEY},
                    success: functionUpdateModal
                });
                e.stopPropagation();
                e.preventDefault();
            });
        };


        $(document).ready(function(){

            addButtonsFunction();
        });
        //$('body').bind('processStop', addButtonsFunction);
        //$('#'+id).bind('render',addButtonsFunction);


    }
});
