define([
    'uiRegistry',
    'Magento_Ui/js/form/form'
], function (uiRegistry, Form) {
    'use strict';
    return Form.extend({
        save: function (redirect, data) {
            this.validate();
            this.collectProducts();

            if (!this.additionalInvalid && !this.source.get('params.invalid')) {
                this.setAdditionalData(data)
                    .submit(redirect);
            } else {
                this.focusInvalid();
            }
        },

        collectProducts: function () {
            console.log('ghf');
            window.groupVendorPpcode_massaction =
                document.getElementById('groupVendorPpcode_massaction-form');
            if (window.groupVendorPpcode_massaction) {
                window.groupVendorPpcode_massaction.parentElement.removeChild(window.groupVendorPpcode_massaction);
            }

            if (window.groupVendorPpcode_massactionJsObject) {
                var selectedProducts = uiRegistry.get('index = in_profile_products');
                selectedProducts.value(window.groupVendorPpcode_massactionJsObject.checkedString);
            }
        },

        collectCategories: function ()
        {
            //TODO: dev
        },

        empty: function (e) {
            switch (e) {
                case "":
                case 0:
                case "0":
                case null:
                case false:
                    return true;
                default:
                    if (typeof e === "undefined") {
                        return true;
                    } else if (typeof e === "object" && Object.keys(e).length === 0) {
                        return true;
                    } else {
                        return false;
                    }
            }
        }
    });
});