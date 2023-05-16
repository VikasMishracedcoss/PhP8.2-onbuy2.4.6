define([
    'Magento_Ui/js/form/element/abstract',
    'uiRegistry',
    'jquery',
    'ko'
], function (Abstract, registry, $, ko) {
    'use strict';
    return Abstract.extend({
        validate: function () {
            this.showMessage('');
            console.log('ff');
            self = this;
            self.validate.on = true;

            var storeId = registry.get('index = root_level');
            var category = registry.get('index = level-0');

            var message = 'Enter Keyword for Searching Related Category.';
            if (storeId.value()) {
                console.log('testing');
                var data = {
                    searchText:  storeId.value()

                };

                var source = registry.get(this.provider);
                var error = true;
                var self = this;
                $.ajax({
                    method: "POST",
                    url: source.validate_url,
                    showLoader: true,
                    data: data,
                    complete: function (response) {
                        category.setOptions(response.responseJSON['category']);

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



