import { Component, Mixin } from 'src/core/shopware';
import Criteria from 'src/core/data-new/criteria.data';
import template from './sw-promotion-list.html.twig';
import './sw-promotion-list.scss';

Component.register('sw-promotion-list', {
    template,

    inject: ['repositoryFactory', 'context'],

    mixins: [
        Mixin.getByName('listing')
    ],

    data() {
        return {
            promotions: null,
            showDeleteModal: false,
            sortBy: 'name',
            isLoading: true
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        promotionRepository() {
            return this.repositoryFactory.create('promotion');
        },

        promotionColumns() {
            return this.getPromotionColumns();
        }
    },

    methods: {
        getList() {
            this.isLoading = true;
            const criteria = new Criteria(this.page, this.limit);
            criteria.setTerm(this.term);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));

            return this.promotionRepository.search(criteria, this.context).then((searchResult) => {
                this.total = searchResult.total;
                this.promotions = searchResult;
                this.isLoading = false;

                return this.promotions;
            });
        },

        onChangeLanguage() {
            this.getList();
        },

        getPromotionColumns() {
            return [{
                property: 'name',
                label: this.$tc('sw-promotion.list.columnName'),
                routerLink: 'sw.promotion.detail',
                inlineEdit: 'string',
                allowResize: true,
                primary: true
            }, {
                property: 'active',
                label: this.$tc('sw-promotion.list.columnActive'),
                inlineEdit: 'boolean',
                allowResize: true,
                align: 'center'
            }, {
                property: 'validFrom',
                label: this.$tc('sw-promotion.list.columnValidFrom'),
                inlineEdit: 'date',
                allowResize: true,
                align: 'center'
            }, {
                property: 'validUntil',
                label: this.$tc('sw-promotion.list.columnValidUntil'),
                inlineEdit: 'date',
                allowResize: true,
                align: 'center'
            }];
        }
    }
});
