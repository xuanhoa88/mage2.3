/**
 * Created by Thuan on 8/21/2017.
 */
define(
    ['jquery',"jquery/ui",
        'jquery/ui', // Jquery UI Library
        'mage/translate'
    ],
    function($){
        "use strict";
        /*
         widget is to do event on switch button
         */
        $.widget('mage.CleverBuilderPanelButtons',{
            options: {
                switchOff : 'off',
                turningOff: $.mage.__("Turning Off..."),
                turningOn: $.mage.__("Turning On...")
            },
            _create: function(){
                var self = this;
                $(function() {
                    /*
                     init switch button clicked
                     */
                    var element = self.element;
                    var data = {};
                    element.find('a.btn.action').on('click', function (event) {
                        event.preventDefault();
                        if ($(this).hasClass(self.options.switchOff)) {
                            data.onAcitve = 0;
                        } else {
                            data.onAcitve = 1;
                        }
                        self.cleverAjax(data, $(this).attr('action'),$(this).attr('href'));
                    });
                    self._initUpdateButton();
                })
            },
            /*
             init Update button
             */
            _initUpdateButton: function(){
                var self = this;
                $("#cleverSavePageButtonPanel").on('click',function(event){
                    event.preventDefault();

                });
            },
            /*
             function to do ajax
             */
            cleverAjax : function (data, url, redirect) {
                var seft = this;
                $.ajax({
                    url: url,
                    data: data,
                    showLoader: true,
                    type: "POST",
                    success: function () {
                        var text = data.onAcitve ? seft.options.turningOn : seft.options.turningOff;
                        $("body").html('<p class="switch-redirecting">'+text+'</p>');
                        window.location = redirect;
                    }
                });
            }
        });

        return $.mage.CleverBuilderPanelButtons;
    });
