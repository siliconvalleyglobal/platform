import { Component, Mixin } from 'src/core/shopware';
import template from './sw-order-detail.html.twig';
import './sw-order-detail.scss';

Component.register('sw-order-detail', {
    template,

    mixins: [
        Mixin.getByName('notification')
    ],

    inject: [
        'repositoryFactory',
        'context'
    ],

    data() {
        return {
            identifier: '',
            orderId: null,
            isEditing: false,
            isLoading: true,
            isSaveSuccessful: false
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier)
        };
    },

    computed: {
        showTabs() {
            return this.$route.meta.$module.routes.detail.children.length > 1;
        }
    },

    watch: {
        '$route.params.id'() {
            this.createdComponent();
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.orderId = this.$route.params.id;
        },

        updateIdentifier(identifier) {
            this.identifier = identifier;
        },

        onChangeLanguage() {
            this.$root.$emit('language-change');
        },

        saveEditsFinish() {
            this.isSaveSuccessful = false;
            this.isEditing = false;
        },

        onStartEditing() {
            this.$root.$emit('order-edit-start');
        },

        onSaveEdits() {
            this.$root.$emit('order-edit-save');
        },

        onCancelEditing() {
            this.$root.$emit('order-edit-cancel');
        },

        onUpdateLoading(loadingValue) {
            this.isLoading = loadingValue;
        },

        onUpdateEditing(editingValue) {
            this.isEditing = editingValue;
        },

        onError(error) {
            let errorDetails = null;

            try {
                errorDetails = error.response.data.errors[0].detail;
            } catch (e) {
                errorDetails = '';
            }

            this.createNotificationError({
                title: this.$tc('sw-order.detail.titleRecalculationError'),
                message: this.$tc('sw-order.detail.messageRecalculationError') + errorDetails
            });
        }
    }
});
