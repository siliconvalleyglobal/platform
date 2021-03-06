import { Component } from 'src/core/shopware';
import template from './sw-plugin-updates-list.html.twig';

Component.register('sw-plugin-updates', {
    template,

    props: {
        pageLoading: {
            type: Boolean,
            required: false,
            default: false
        }
    }
});
