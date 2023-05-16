define([
    'Magento_Ui/js/form/element/select',
    'uiRegistry',
    'jquery',
    'ko'
], function (Abstract, registry, $, ko) {
    'use strict';
    return Abstract.extend({
        onUpdate: function () {
            console.log('tsest');

            // var id = $('#level_0').val();


            self = this;
            self.validate.on = true;

            var catDropdown = registry.get('index = attribute_mapping');
            console.log(catDropdown);
            var category = registry.get('index = level-0');

            var message = 'Enter Keyword for Searching Related Category.';
            if (category.value()) {
                console.log('testing');
                var data = {
                    feature:  category.value()

                };

                var source = registry.get(this.provider);
                var error = true;
                var self = this;
                $.ajax({
                    method: "POST",
                    url: source.get_attributes,
                    showLoader: true,
                    data: data,
                    complete: function (response) {
                        console.log(response);
                        console.log('resjpzzzonse');
                        console.log(catDropdown);

                        catDropdown.setOptions(response['responseText']['feature']);
                        // category.setOptions(response.responseJSON['category']);

                    }
                });
            } else {
                this.showMessage(message, 'error')
            }
        },



        showLoader: function (status) {
            var body = $('body').loader();
            if (status === true) {
                body.loader('show');
            } else {
                body.loader('hide');
            }
        },

        showMessage: function (message, type) {
            if (type === 'error') {
                this.notice(false);
                this.error(message);
                this.bubble('error', message);
            } else {
                this.error(false);
                this.notice(message);
                this.bubble('notice', message);
            }
        }
    });
});



