import { Component, State, Mixin } from 'src/core/shopware';
import template from './sw-settings-shipping-detail.html.twig';

Component.register('sw-settings-shipping-detail', {
    template,

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            shippingMethod: {}
        };
    },

    computed: {
        shippingMethodStore() {
            return State.getStore('shipping_method');
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (this.$route.params.id) {
                this.shippingMethodId = this.$route.params.id;
                this.tax = this.shippingMethodStore.getById(this.shippingMethodId);
            }
        },

        onSave() {
            const shippingMethodName = this.shippingMethod.name;
            const titleSaveSuccess = this.$tc('sw-settings-shipping.detail.titleSaveSuccess');
            const messageSaveSuccess = this.$tc('sw-settings-shipping.detail.messageSaveSuccess', 0, {
                name: shippingMethodName
            });

            return this.tax.save().then(() => {
                this.createNotificationSuccess({
                    title: titleSaveSuccess,
                    message: messageSaveSuccess
                });
            });
        }
    }
});
