import { Component, Mixin } from 'src/core/shopware';
import Criteria from 'src/core/data-new/criteria.data';
import template from './sw-order-document-card.html.twig';
import './sw-order-document-card.scss';
import '../sw-order-document-settings-invoice-modal/';
import '../sw-order-document-settings-storno-modal/';
import '../sw-order-document-settings-delivery-note-modal/';
import '../sw-order-document-settings-credit-note-modal/';
import '../sw-order-document-settings-modal/';

Component.register('sw-order-document-card', {
    template,

    inject: [
        'documentService',
        'numberRangeService',
        'repositoryFactory',
        'context'
    ],

    mixins: [
        Mixin.getByName('listing'),
        Mixin.getByName('placeholder')
    ],

    props: {
        order: {
            type: Object,
            required: true
        },
        isLoading: {
            type: Boolean,
            required: true
        }
    },

    data() {
        return {
            documentsLoading: false,
            cardLoading: false,
            documents: [],
            documentTypes: null,
            showModal: false,
            currentDocumentType: null,
            documentNumber: null,
            documentComment: '',
            term: ''
        };
    },

    computed: {
        creditItems() {
            const items = [];

            this.order.lineItems.forEach((lineItem) => {
                if (lineItem.type === 'credit') {
                    items.push(lineItem);
                }
            });

            return items;
        },

        documentTypeRepository() {
            return this.repositoryFactory.create('document_type');
        },

        documentRepository() {
            return this.repositoryFactory.create('document');
        },

        documentsEmpty() {
            return this.documents.length === 0;
        },

        documentModal() {
            const subComponentName = this.currentDocumentType.technicalName.replace(/_/g, '-');
            if (this.$options.components[`sw-order-document-settings-${subComponentName}-modal`]) {
                return `sw-order-document-settings-${subComponentName}-modal`;
            }
            return 'sw-order-document-settings-modal';
        },

        documentCardStyles() {
            return `sw-order-document-card ${this.documentsEmpty ? 'sw-order-document-card--is-empty' : ''}`;
        },

        documentTypeCriteria() {
            const criteria = new Criteria(1, 100);
            criteria.addSorting(Criteria.sort('name', 'ASC'));

            return criteria;
        },

        documentCriteria() {
            const criteria = new Criteria(this.page, this.limit);
            criteria.addSorting(Criteria.sort('createdAt', 'DESC'));
            criteria.setTerm(this.term);
            criteria.addAssociation('documentType');
            criteria.addFilter(Criteria.equals('order.id', this.order.id));
            criteria.addFilter(Criteria.equals('order.versionId', this.order.versionId));

            return criteria;
        },

        getDocumentColumns() {
            return [{
                property: 'createdAt',
                dataIndex: 'createdAt',
                label: this.$tc('sw-order.documentCard.labelDate'),
                allowResize: false,
                primary: true
            }, {
                property: 'config.documentNumber',
                dataIndex: 'config.documentNumber',
                label: this.$tc('sw-order.documentCard.labelNumber'),
                allowResize: false
            }, {
                property: 'documentType.name',
                dataIndex: 'documentType.name',
                label: this.$tc('sw-order.documentCard.labelType'),
                allowResize: false
            }, {
                property: 'sent',
                dataIndex: 'sent',
                label: this.$tc('sw-order.documentCard.labelSent'),
                allowResize: false,
                align: 'center'
            }];
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.cardLoading = true;

            this.documentTypeRepository.search(this.documentTypeCriteria, this.context).then((response) => {
                this.documentTypes = response;
                this.cardLoading = false;
            });
        },

        getList() {
            this.documentsLoading = true;

            return this.documentRepository.search(this.documentCriteria, this.context).then((response) => {
                this.total = response.total;
                this.documents = response;
                this.documentsLoading = false;
                return Promise.resolve();
            });
        },

        documentTypeAvailable(documentType) {
            return (
                (
                    documentType.technicalName !== 'storno' &&
                    documentType.technicalName !== 'credit_note'
                ) ||
                (
                    (
                        documentType.technicalName === 'storno' ||
                        (
                            documentType.technicalName === 'credit_note' &&
                            this.creditItems.length !== 0
                        )
                    ) && this.invoiceExists()
                )
            );
        },

        invoiceExists() {
            return this.documents.some((document) => {
                return (document.documentType.technicalName === 'invoice');
            });
        },

        onSearchTermChange(searchTerm) {
            this.term = searchTerm;
            this.getList();
        },

        createDocument(orderId, documentTypeName, params, referencedDocumentId) {
            return this.documentService.createDocument(orderId, documentTypeName, params, referencedDocumentId);
        },

        onCancelCreation() {
            this.showModal = false;
            this.currentDocumentType = null;
        },

        onPrepareDocument(documentType) {
            this.currentDocumentType = documentType;
            this.showModal = true;
        },

        onCreateDocument(params, additionalAction, referencedDocumentId = null) {
            this.showModal = false;
            this.$nextTick().then(() => {
                return this.createDocument(
                    this.order.id,
                    this.currentDocumentType.technicalName,
                    params,
                    referencedDocumentId
                );
            }).then((response) => {
                this.getList();
                this.$emit('document-save');

                if (additionalAction === 'download') {
                    window.open(
                        this.documentService.generateDocumentLink(
                            response.data.documentId,
                            response.data.documentDeepLink,
                            true
                        ),
                        '_blank'
                    );
                }
            });
        },

        onPreview(params) {
            const config = JSON.stringify(params);
            window.open(
                this.documentService.generateDocumentPreviewLink(
                    this.order.id,
                    this.order.deepLinkCode,
                    this.currentDocumentType.technicalName, config
                ),
                '_blank'
            );
        },

        onDownload(id, deepLink) {
            window.open(this.documentService.generateDocumentLink(id, deepLink, false), '_blank');
        }
    }
});
