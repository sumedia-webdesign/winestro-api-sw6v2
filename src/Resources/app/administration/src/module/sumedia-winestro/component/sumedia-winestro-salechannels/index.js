import template from './index.html.twig';
import './index.scss';

const { Component, Mixin, Context } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('sumedia-winestro-salechannels', {
    template,
    inject: ['sumediaWinestro', 'repositoryFactory'],
    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            winestroConnections: {},
            winestroSalesChannels: {},
            winestroConnectionOptions: [],
            isMappingLoaded: false,
            paymentMapping: [],
            winestroPaymentMapping: [],
            winestrpPaymentNameMapping: [],
            winestroPaymentMappingOptions: [],
            shippingMapping: [],
            winestroShippingMapping: [],
            winestroShippingMappingOptions: [],

            data: [],
            columns: [
                {property: 'salesChannel', label: this.$tc('sumedia-winestro.salesChannels.form.salesChannel')},
                {property: 'winestroConnectionName', label: this.$tc('sumedia-winestro.salesChannels.form.winestroConnectionName')},

            ],
            config: {
                isOpen: false,
                isLoading: false
            },
            delete: {
                id: null,
                isOpen: false
            },
            formData: {
                salesChannelId: null,
                winestroConnectionId: null,
                paymentMapping: {},
                shippingMapping: {}
            }
        }
    },

    mounted() {
        this.loadWinestroConnections().then(() => {
            this.loadWinestroSalesChannels().then(async () => {
                this.loadData()
            })
        })
    },

    computed: {
        isCompleted() {
            return this.formData.salesChannelId &&
                this.formData.winestroConnectionId &&
                !Object.values(this.formData.paymentMapping).includes(null) &&
                !Object.values(this.formData.shippingMapping).includes(null)
        },
        isConfigReady() {
            if(this.formData.salesChannelId && this.formData.winestroConnectionId) {
                if (!this.isMappingLoaded) {
                    this.loadPaymentMapping().then(() => {
                        this.loadShippingMapping().then(() => {
                            this.checkMapping();
                        })
                    });
                    this.isMappingLoaded = true;
                }
                return true;
            }
            return false;
        }
    },

    watch: {
        winestroPaymentMapping() {
            let data = [];
            for(let name in this.winestroPaymentMapping) {
                let id = this.winestroPaymentMapping[name];
                data.push({
                    id,
                    value: id,
                    name: this.$tc("sumedia-winestro.payment-names." + name)
                })
            }
            this.winestroPaymentMappingOptions = data;
        },
        winestroShippingMapping() {
            let data = [];
            for(let name in this.winestroShippingMapping) {
                let id = this.winestroShippingMapping[name];
                data.push({
                    id,
                    value: id,
                    name: this.$tc("sumedia-winestro.shipping-names." + name)
                })
            }
            this.winestroShippingMappingOptions = data;
        }
    },

    methods: {
        async loadWinestroConnections() {
            this.winestroConnections = await this.sumediaWinestro.getWinestroConnections();
            let options = [];
            for (let winestroConnectionId in this.winestroConnections) {
                options.push({
                    id: winestroConnectionId,
                    value: winestroConnectionId,
                    name: this.winestroConnections[winestroConnectionId].name
                });
            }
            this.winestroConnectionOptions = options;
        },

        async loadWinestroSalesChannels() {
            this.winestroSalesChannels = await this.sumediaWinestro.getSalesChannels();
        },

        async loadPaymentMapping() {
            this.paymentMapping = [];
            if (this.formData.salesChannelId) {
                let salesChannelRepository = this.repositoryFactory.create('sales_channel');
                let criteria = new Criteria();
                criteria.addAssociation('paymentMethods');
                criteria.addFilter(Criteria.equals('id', this.formData.salesChannelId));
                salesChannelRepository.search(criteria, Context.api).then((result) => {
                    if (result.length) {
                        let paymentMethods = result[0].paymentMethods;
                        let data = [];
                        paymentMethods.forEach((paymentMethod) => {
                            data.push({
                                id: paymentMethod.id,
                                name: paymentMethod.name
                            });
                            if ('undefined' === typeof this.formData.paymentMapping[paymentMethod.id]) {
                                this.formData.paymentMapping[paymentMethod.id] = null;
                            }
                        });
                        this.paymentMapping = data;
                    }
                });
            }

            this.sumediaWinestro.apiService.post('sumedia-winestro/mapping', {'mapper': 'PaymentConfigMapper'}).then((result) => {
                if (result.success) {
                    this.winestroPaymentMapping = result.mapping;
                }
            });
        },

        async loadShippingMapping() {
            this.shippingMapping = [];
            if (this.formData.salesChannelId) {
                let salesChannelRepository = this.repositoryFactory.create('sales_channel');
                let criteria = new Criteria();
                criteria.addAssociation('shippingMethods');
                criteria.addFilter(Criteria.equals('id', this.formData.salesChannelId));
                salesChannelRepository.search(criteria, Context.api).then((result) => {
                    if (result.length) {
                        let shippingMethods = result[0].shippingMethods;
                        let data = [];
                        shippingMethods.forEach((shippingMethod) => {
                            data.push({
                                id: shippingMethod.id,
                                name: shippingMethod.name
                            });
                            if ('undefined' === typeof this.formData.shippingMapping[shippingMethod.id]) {
                                this.formData.shippingMapping[shippingMethod.id] = null;
                            }
                        });
                        this.shippingMapping = data;
                    }
                });
            }

            this.sumediaWinestro.apiService.post('sumedia-winestro/mapping', {'mapper': 'ShippingConfigMapper'}).then((result) => {
                if (result.success) {
                    this.winestroShippingMapping = result.mapping;
                }
            });
        },

        async loadData() {
            let data = [];
            for (let salesChannelId in this.winestroSalesChannels) {
                let salesChannel = this.winestroSalesChannels[salesChannelId];
                let sSalesChannel = await this.repositoryFactory.create('sales_channel')
                    .search((new Criteria).addFilter(
                        Criteria.equals('id', salesChannelId)), Context.api
                    ).then((result) => {
                        return result[0];
                    });

                for (let winestroConnectionId in salesChannel.winestroConnections) {
                    let winestroConnection = this.winestroConnections[winestroConnectionId];
                    data.push({
                        id: salesChannelId + '-' + winestroConnectionId,
                        salesChannel: sSalesChannel.name,
                        winestroConnectionName: winestroConnection.name
                    });
                }
            }
            this.data = data;
        },

        openConfig() {
            this.resetFormData();
            this.config.isOpen = true;
        },

        closeConfig() {
            this.config.isOpen = false;
        },

        openEdit(id) {
            this.populateFormData(id);
            this.config.isOpen = true;
        },

        openDelete(id) {
            this.delete.id = id;
            this.delete.isOpen = true;
        },

        closeDelete() {
            this.delete.id = null;
            this.delete.isOpen = false;
        },

        resetFormData() {
            this.formData = {
                salesChannelId: null,
                winestroShopId: null,
                paymentMapping: {},
                shippingMapping: {}
            }
            this.isMappingLoaded = false;
        },

        populateFormData(id) {
            let split = id.split('-');
            let salesChannelid = split[0];
            let winestroConnectionId = split[1];

            this.formData = {
                salesChannelId: salesChannelid,
                winestroConnectionId: winestroConnectionId,
                paymentMapping: {
                    ... this.formData.paymentMapping,
                    ... this.winestroSalesChannels[salesChannelid].paymentMapping,
                },
                shippingMapping: {
                    ... this.formData.shippingMapping,
                    ... this.winestroSalesChannels[salesChannelid].shippingMapping
                }
            }
            this.isMappingLoaded = false;
        },

        checkMapping() {
            let sid = this.formData.salesChannelId;
            let wid = this.formData.winestroConnectionId;
            if (sid && wid) {
                if ('undefined' !== typeof this.winestroSalesChannels[sid].winestroConnections[wid]) {
                    let paymentMapping = this.winestroSalesChannels[sid].winestroConnections[wid].paymentMapping;
                    let shippingMapping = this.winestroSalesChannels[sid].winestroConnections[wid].shippingMapping;

                    for (let id in paymentMapping) {
                        this.formData.paymentMapping[id] = paymentMapping[id];
                    }
                    for (let id in shippingMapping) {
                        this.formData.shippingMapping[id] = shippingMapping[id];
                    }
                }
            } else {
                for (let id in this.formData.paymentMapping) {
                    this.formData.paymentMapping[id] = null;
                }
                for (let id in this.formData.shippingMapping) {
                    this.formData.shippingMapping[id] = null;
                }
            }
        },

        async configSalesChannel() {
            this.config.successful = false;
            this.config.isLoading = true;
            if (!this.isCompleted) {
                return null;
            }

            let salesChannels = await this.sumediaWinestro.getSalesChannels();
            salesChannels = null === salesChannels ? {} : salesChannels;

            let existingEntryId = null;
            for (let salesChannelId in salesChannels) {
                if (salesChannelId === this.formData.salesChannelId) {
                    existingEntryId = salesChannelId;
                }
            }

            let sSalesChannel = await this.repositoryFactory.create('sales_channel')
                .search((new Criteria).addFilter(
                    Criteria.equals('id', this.formData.salesChannelId)
                ), Context.api).then((result) => {
                    return result[0];
                });

            let salesChannel = null;
            if (null !== existingEntryId) {
                salesChannel = salesChannels[existingEntryId];
            } else {
                salesChannel = this.sumediaWinestro.salesChannel;
                salesChannel.salesChannelId = sSalesChannel.id;
            }

            salesChannel.winestroConnections[this.formData.winestroConnectionId] = {
                winestroConnectionId: this.formData.winestroConnectionId,
                paymentMapping: this.formData.paymentMapping,
                shippingMapping: this.formData.shippingMapping
            }

            await this.sumediaWinestro.setSalesChannel(this.formData.salesChannelId, salesChannel);

            this.config.isLoading = false;
            this.config.successful = true;
            this.config.isOpen = false;
            this.resetFormData();

            this.createNotificationSuccess({
                title: this.$tc('sumedia-winestro.salesChannels.notificationSaveSuccessTitle'),
                message: this.$tc('sumedia-winestro.salesChannels.notificationSaveSuccessMessage')
            });

            this.loadWinestroSalesChannels().then(() => {
                this.loadData();
            });
        },

        async deleteDo() {
            let split = this.delete.id.split('-');
            let salesChannelId = split[0];
            let winestroConnectionId = split[1];

            await this.sumediaWinestro.removeSalesChannel(salesChannelId, winestroConnectionId);

            this.delete.isLoading = false;
            this.delete.isOpen = false;
            this.delete.id = null;

            this.createNotificationSuccess({
                title: this.$tc('sumedia-winestro.salesChannels.notificationDeleteSuccessTitle'),
                message: this.$tc('sumedia-winestro.salesChannels.notificationDeleteSuccessMessage')
            });

            this.loadWinestroSalesChannels().then(() => {
                this.loadWinestroSalesChannels().then(() => {
                    this.loadData();
                });
            });
        }
    }
});
