import template from './index.html.twig';
import './index.scss';

const { Component, Mixin, Context, Utils } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('sumedia-winestro-installation', {
    template,
    inject: ['sumediaWinestro', 'repositoryFactory'],
    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            isLoading: true,
            langEnId: null,
            langDeId: null,
            successful: false,

            paymentMapping: [],
            winestroPaymentMapping: [],
            winestroPaymentMappingOptions: [],
            winestroConnections: {},
            winestroConnectionsOptions: {},
            shippingSettedUp: false,
            shippingMapping: [],
            winestroShippingMapping: [],
            winestroShippingMappingOptions: [],

            articleNumberFormatOptions: [
                {
                    id : '[articlenumber+year+bottling]',
                    value : '[articlenumber+year+bottling]',
                    name : this.$tc('sumedia-winestro.tasks.form.articlenumber') + ' und ' +
                        this.$tc('sumedia-winestro.tasks.form.year') + ' und ' +
                        this.$tc('sumedia-winestro.tasks.form.bottling'),
                },
                {
                    id : '[articlenumber+year]',
                    value : '[articlenumber+year]',
                    name : this.$tc('sumedia-winestro.tasks.form.articlenumber') + ' und ' +
                        this.$tc('sumedia-winestro.tasks.form.year') + ' ohne ' +
                        this.$tc('sumedia-winestro.tasks.form.bottling'),
                },
                {
                    id : '[articlenumber]',
                    value : '[articlenumber]',
                    name : this.$tc('sumedia-winestro.tasks.form.articlenumber') + ' ohne ' +
                        this.$tc('sumedia-winestro.tasks.form.year') + ' ohne ' +
                        this.$tc('sumedia-winestro.tasks.form.bottling'),
                }
            ],
            separatorOptions: [
                {id: ' ', value: ' ', name: 'Leerzeichen'},
                {id: '+', value: '+', name: '+'},
                {id: '-', value: '-', name: '-'},
                {id: '/', value: '/', name: '/'}
            ],

            connection: {
                isLoading: false,
                successful: false,
                checkSuccessful: false,
                checkedData: {},
                formData: {
                    id: null,
                    name: null,
                    url: 'https://weinstore.net/xml/v20.0',
                    userId: null,
                    shopId: 1,
                    secretId: 'api-usr',
                    secretCode: null
                }
            },
            measurements: {
                isLoading: false,
                successful: false,
                measurements: this.sumediaWinestro.measurementService.measurements,
                formData: {
                    litre: null,
                    kilo: null,
                    gramperhundret: null,
                    volumepercent: null
                }
            },
            properties: {
                isLoading: false,
                successful: false,
                properties: this.sumediaWinestro.propertyService.properties,
                formData: this.getPropertiesFormData(),
            },
            salesChannel: {
                isLoading: false,
                successful: false,
                formData: {... this.sumediaWinestro.salesChannel}
            },
            payment: {
                isLoading: false,
                successful: false,
                formData: {}
            },
            shipping: {
                isLoading: false,
                successful: false,
                formData: {}
            },
            tasks: {
                isLoading: false,
                successful: false,
                formData: {
                    tax: null,
                    reducedTax: null,
                    deliveryTime: null,
                    mediaFolder: null,
                    maxImageWidth: 1200,
                    maxImageHeight: 1200,
                    sellingLimit: 0,
                    defaultManufacturer: null,
                    articleNumberFormat: '[articlenumber+year+bottling]',
                    articleNumberYearSeparator: '+',
                    articleNumberBottlingSeparator: '+',

                    visibleInSalesChannelsIds: [],
                    productsFromWinestroConnectionIds: [],
                    productsFromSalesChannelsIds: [],

                    categoryIdentifier: 'Winestro',

                    productimport: false,
                    productimages: false,
                    productstock: false,
                    categories: false,
                    orderexport: false,
                    orderstatusupdate: false,
                    newsletterReceiver: false,

                    activestatus: true,
                    description: true,
                    freeshipping: true,
                    manufacturer: true,
                    sendWinestroEmail: false
                },
                tasks: {
                    productImportTask: {
                        ...this.sumediaWinestro.taskService.tasks.productImportTask,
                        id: Utils.createId(),
                        name: this.$tc('sumedia-winestro.tasks.productImportTask.name')
                    },
                    productImageUpdateTask: {
                        ...this.sumediaWinestro.taskService.tasks.productImageUpdateTask,
                        id: Utils.createId(),
                        name: this.$tc('sumedia-winestro.tasks.productImageUpdateTask.name')
                    },
                    productStockTask: {
                        ...this.sumediaWinestro.taskService.tasks.productStockTask,
                        id: Utils.createId(),
                        name: this.$tc('sumedia-winestro.tasks.productStockTask.name')
                    },
                    productCategoryAssignmentTask: {
                        ...this.sumediaWinestro.taskService.tasks.productCategoryAssignmentTask,
                        id: Utils.createId(),
                        name: this.$tc('sumedia-winestro.tasks.productCategoryAssignmentTask.name')
                    },
                    orderExportTask: {
                        ...this.sumediaWinestro.taskService.tasks.orderExportTask,
                        id: Utils.createId(),
                        name: this.$tc('sumedia-winestro.tasks.orderExportTask.name')
                    },
                    orderStatusUpdateTask: {
                        ...this.sumediaWinestro.taskService.tasks.orderStatusUpdateTask,
                        id: Utils.createId(),
                        name: this.$tc('sumedia-winestro.tasks.orderStatusUpdateTask.name')
                    },
                    newsletterReceiverImportTask: {
                        ...this.sumediaWinestro.taskService.tasks.newsletterReceiverImportTask,
                        id: Utils.createId(),
                        name: this.$tc('sumedia-winestro.tasks.newsletterReceiverImportTask.name')
                    }
                }
            }
        }
    },

    async mounted() {
        if (true === await this.sumediaWinestro.configService.get('installationDone')){
            this.connection.successful = true;
            this.measurements.successful = true;
            this.properties.successful = true;
            this.salesChannel.successful = true;
            this.payment.successful = true;
            this.shipping.successful = true;
            this.tasks.successful = true;
            this.successful = true;
            this.isLoading = false;
            this.$parent.$parent.$parent.isInstallationNeeded = false;
        } else {
            await this.loadLanguage();
            await this.loadMeasurementConfig();
            await this.loadPropertiesConfig();
            await this.loadWinestroConnectionConfig().then(async () => {
                await this.loadSalesChannelConfig().then(async () => {
                    await this.loadPaymentMapping();
                    await this.loadPaymentMappingConfig();
                    await this.loadShippingMapping();
                    await this.loadShippingMappingConfig();
                })
            });
        }
        this.isLoading = false;
    },

    computed: {
        connectionCheckSuccessful() {
            let checkedData = this.connection.checkedData;
            let formData = this.connection.formData;
            if (
                this.connection.checkSuccessful &&
                (
                    formData.name === '' ||
                    formData.url !== checkedData.url ||
                    formData.userId !== checkedData.userId ||
                    formData.shopId !== checkedData.shopId ||
                    formData.secretId !== checkedData.secretId ||
                    formData.secretCode !== checkedData.secretCode
                )
            ) {
                this.connection.checkSuccessful = false;
                return false;
            }
            return this.connection.checkSuccessful;
        },
        isMeasurementsComplete() {
            return !Object.values(this.measurements.formData).includes(null);
        },
        isPropertiesComplete() {
            return !Object.values(this.properties.formData).includes(null);
        },
        isSaleschannelComplete() {
            return !Object.values(this.saleschannel.formData).includes(null);
        },
        isPaymentComplete() {
            return !Object.values(this.payment.formData).includes(null);
        },
        isShippingComplete() {
            return !Object.values(this.shipping.formData).includes(null);
        },
        isTasksComplete() {
            return !Object.values(this.tasks.formData).includes(null) &&
                '' !== this.tasks.formData.categoryIdentifier &&
                this.tasks.formData.visibleInSalesChannelsIds.length &&
                this.tasks.formData.productsFromSalesChannelsIds.length &&
                this.tasks.formData.productsFromWinestroConnectionIds.length
        },
        salesChannelRepository() {
            return this.repositoryFactory.create('sales_channel');
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
                    name
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
                    name
                })
            }
            this.winestroShippingMappingOptions = data;
        }
    },

    methods: {
        getPropertiesFormData() {
            let formData = {};
            Object.keys(this.sumediaWinestro.propertyService.properties).forEach((key) => {
                formData[key] = null;
            });
            return formData;
        },

        async getLanguageIdByCode(code) {
            let localeRepository = this.repositoryFactory.create('locale');
            let languageRepository = this.repositoryFactory.create('language');
            let criteria = new Criteria();
            criteria.addFilter(Criteria.equals('code', code));
            return await localeRepository.search(criteria, Context.api).then(async (result) => {
                if (result.length) {
                    let criteria = new Criteria();
                    criteria.addFilter(Criteria.equals('localeId', result[0].id));
                    return await languageRepository.search(criteria, Context.api).then((result) => {
                        if (result.length) {
                            return result[0].id;
                        } else {
                            return null;
                        }
                    });
                } else {
                    return null;
                }
            });
        },

        async loadLanguage() {
            this.langEnId = await this.getLanguageIdByCode('en-GB');
            this.langDeId = await this.getLanguageIdByCode('de-DE');
            this.languageId = Context.api.systemLanguageId;
        },

        async loadPaymentMapping() {
            this.paymentMapping = [];
            if (this.salesChannel.formData.salesChannelId) {
                let salesChannelRepository = this.repositoryFactory.create('sales_channel');
                let criteria = new Criteria();
                criteria.addAssociation('paymentMethods');
                criteria.addFilter(Criteria.equals('id', this.salesChannel.formData.salesChannelId));
                salesChannelRepository.search(criteria, Context.api).then((result) => {
                    if (result.length) {
                        let paymentMethods = result[0].paymentMethods;
                        let data = [];
                        paymentMethods.forEach((paymentMethod) => {
                            data.push({
                                id: paymentMethod.id,
                                name: paymentMethod.name
                            });
                            if ('undefined' === typeof this.payment.formData[paymentMethod.id]) {
                                this.payment.formData[paymentMethod.id] = null;
                            }
                        });
                        this.paymentMapping = data;
                    }
                });
            }

            this.sumediaWinestro.apiService.post('sumedia-winestro/mapping').then((result) => {
                if (result.success) {
                    this.winestroPaymentMapping = result.mapping.paymentMapping;
                }
            });
        },

        async loadShippingMapping() {
            this.shippingMapping = [];
            if (this.salesChannel.formData.salesChannelId) {
                let salesChannelRepository = this.repositoryFactory.create('sales_channel');
                let criteria = new Criteria();
                criteria.addAssociation('shippingMethods');
                criteria.addFilter(Criteria.equals('id', this.salesChannel.formData.salesChannelId));
                salesChannelRepository.search(criteria, Context.api).then((result) => {
                    if (result.length) {
                        let shippingMethods = result[0].shippingMethods;
                        let data = [];
                        shippingMethods.forEach((shippingMethod) => {
                            data.push({
                                id: shippingMethod.id,
                                name: shippingMethod.name
                            });
                            if ('undefined' === typeof this.shipping.formData[shippingMethod.id]) {
                                this.shipping.formData[shippingMethod.id] = null;
                            }
                        });
                        this.shippingMapping = data;
                    }
                });
            }

            this.sumediaWinestro.apiService.post('sumedia-winestro/mapping').then((result) => {
                if (result.success) {
                    this.winestroShippingMapping = result.mapping.shippingMapping;
                }
            });
        },

        async loadWinestroConnectionConfig() {
            let winestroConnections = await this.sumediaWinestro.getWinestroConnections();
            if (null !== winestroConnections) {
                let winestroConnection = Object.values(winestroConnections)[0];
                if (Object.values(winestroConnection).length) {
                    this.connection.formData = winestroConnection;
                    this.connection.successful = true;
                    this.winestroConnectionsOptions = [
                        {
                            id: winestroConnection.id,
                            value: winestroConnection.id,
                            label: winestroConnection.name,
                        }
                    ]
                }
            }
        },

        async loadMeasurementConfig() {
            let measurements = await this.sumediaWinestro.configService.get('measurements');
            if (null !== measurements && Object.values(measurements).length) {
                for (let key in measurements) {
                    this.measurements.formData[key] = measurements[key];
                }
                this.measurements.successful = true;
            } else {
                for (let key in this.measurements.measurements) {
                    let name = Context.api.systemLanguageId === this.langEnId
                        ? this.measurements.measurements[key][0] : this.measurements.measurements[key][2];
                    let unit = await this.sumediaWinestro.measurementService.getUnit(name);
                    if (null !== unit) {
                        this.measurements.formData[key] = unit.id;
                    }
                }

            }
        },

        async loadPropertiesConfig() {
            let properties = await this.sumediaWinestro.configService.get('properties');
            if (null !== properties && Object.values(properties).length) {
                for (let key in properties) {
                    this.properties.formData[key] = properties[key];
                }
                this.properties.successful = true;
            } else {
                for (let key in this.properties.properties) {
                    let name = Context.api.systemLanguageId === this.langEnId
                        ? this.properties.properties[key][0] : this.properties.properties[key][1];
                    let unit = await this.sumediaWinestro.propertyService.getPropertyGroup(name);
                    if (null !== unit) {
                        this.properties.formData[key] = unit.id;
                    }
                }

            }
        },

        async loadSalesChannelConfig() {
            let salesChannels = await this.sumediaWinestro.getSalesChannels();
            if (null !== salesChannels && Object.values(salesChannels).length) {
                let salesChannel = Object.values(salesChannels)[0];
                this.salesChannel.formData = salesChannel;
                this.salesChannel.successful = true;
            }
        },

        async loadPaymentMappingConfig() {
            let salesChannels = await this.sumediaWinestro.getSalesChannels();
            if (null !== salesChannels && Object.values(salesChannels).length) {
                let salesChannel = Object.values(salesChannels)[0];
                if ('undefined' !== this.connection.formData.id) {
                    let paymentMapping = salesChannel.winestroConnections[this.connection.formData.id].paymentMapping;
                    if (null !== paymentMapping) {
                        this.payment.formData = paymentMapping;
                        this.salesChannel.formData.winestroConnections[this.connection.formData.id].paymentMapping = paymentMapping;
                        this.payment.successful = true;
                    }
                }
            }
        },

        async loadShippingMappingConfig() {
            let salesChannels = await this.sumediaWinestro.getSalesChannels();
            if (null !== salesChannels && Object.values(salesChannels).length) {
                let salesChannel = Object.values(salesChannels)[0];
                if ('undefined' !== this.connection.formData.id) {
                    let shippingMapping = salesChannel.winestroConnections[this.connection.formData.id].shippingMapping;
                    if (null !== shippingMapping) {
                        this.shipping.formData = shippingMapping;
                        this.salesChannel.formData.winestroConnections[this.connection.formData.id].shippingMapping = shippingMapping;
                        this.shipping.successful = true;
                    }
                }
            }
        },

        async checkConnection() {
            this.connection.isLoading = true;
            this.connection.checkSuccessful = false;
            let response = await this.sumediaWinestro.requestWinestroConnection(
                this.connection.formData.url,
                this.connection.formData.userId,
                this.connection.formData.shopId,
                this.connection.formData.secretId,
                this.connection.formData.secretCode
            );
            if (!response.success) {
                this.createNotificationError({
                    title: this.$tc('sumedia-winestro.connections.checkConnection.title'),
                    message: response.message
                });
            } else {
                if (null === this.connection.formData.name || '' === this.connection.formData.name) {
                    this.connection.formData.name = response.name;
                }
                this.connection.checkSuccessful = true;
                this.connection.checkedData = {...this.connection.formData};
                this.createNotificationSuccess({
                    title: this.$tc('sumedia-winestro.connections.checkConnection.title'),
                    message: this.$tc('sumedia-winestro.connections.checkConnection.success')
                });
            }
            this.connection.isLoading = false;
        },

        async createConnection() {
            this.connection.isLoading = true;
            let id = this.sumediaWinestro.getWinestroConnectionId(
                this.connection.formData.userId,
                this.connection.formData.shopId,
                this.connection.formData.secretId,
            );
            await this.sumediaWinestro.setWinestroConnection(
                id,
                this.connection.formData.name,
                this.connection.formData.url,
                this.connection.formData.userId,
                this.connection.formData.shopId,
                this.connection.formData.secretId,
                this.connection.formData.secretCode
            ).then(() => {
                this.connection.formData.id = id;
                this.connection.isLoading = false;
                this.connection.successful = true;
            });
        },

        async createAllMeasurements() {
            for (let key in this.measurements.measurements) {
                if (null === this.measurements.formData[key]) {
                    this.createMeasurement(key);
                }
            }
        },

        async createAllProperties() {
            for (let key in this.properties.properties) {
                if (null === this.properties.formData[key]) {
                    this.createProperty(key);
                }
            }
        },

        async createMeasurement(key) {
            if ("undefined" !== typeof this.measurements.measurements[key]) {
                let name = this.languageId === this.langEnId ?
                    this.measurements.measurements[key][0] :
                    this.measurements.measurements[key][2];
                let shortCode = this.languageId === this.langEnId ?
                    this.measurements.measurements[key][1] :
                    this.measurements.measurements[key][3];
                if (null === this.measurements.formData[key]) {
                    this.measurements.formData[key] = await this.sumediaWinestro.measurementService.createUnit(name, shortCode);
                }
            }
        },

        async createProperty(key) {
            console.log(this.properties.formData);
            if ("undefined" !== typeof this.properties.properties[key]) {
                let name = this.languageId === this.langEnId ?
                    this.properties.properties[key][0] :
                    this.properties.properties[key][1];
                if (null === this.properties.formData[key]) {
                    this.properties.formData[key] = await this.sumediaWinestro.propertyService.createPropertyGroup(name);
                }
            }
        },

        async saveMeasurements() {
            this.measurements.isLoading = true;
            if (Object.values(this.measurements.formData).includes(null)) {
                return null;
            }
            await this.sumediaWinestro.configService.set('measurements', this.measurements.formData);
            this.measurements.isLoading = false;
            this.measurements.successful = true;
        },

        async saveProperties() {
            this.properties.isLoading = true;
            if (Object.values(this.properties.formData).includes(null)) {
                return null;
            }
            await this.sumediaWinestro.configService.set('properties', this.properties.formData);
            this.properties.isLoading = false;
            this.properties.successful = true;
        },

        async saveSalesChannel() {
            this.salesChannel.isLoading = true;
            if (!this.salesChannel.formData.salesChannelId) {
                return null;
            }

            if (null === this.connection.formData.id) {
                return null;
            }

            if ('undefined' === typeof this.salesChannel.formData.winestroConnections[this.connection.formData.id]) {
                this.salesChannel.formData.winestroConnections[this.connection.formData.id] =
                    {...this.sumediaWinestro.winestroConnections, winestroConnectionId: this.connection.formData.id}
            }

            await this.sumediaWinestro.setSalesChannel(this.salesChannel.formData.salesChannelId, this.salesChannel.formData);
            this.salesChannel.isLoading = false;
            this.salesChannel.successful = true;

            await this.loadPaymentMapping();
            await this.loadShippingMapping();
        },

        async savePayment() {
            this.payment.isLoading = true;
            if (Object.values(this.payment.formData).includes(null)) {
                return null;
            }

            this.salesChannel.formData.winestroConnections[this.connection.formData.id].paymentMapping =
                this.payment.formData;
            await this.saveSalesChannel();
            this.payment.isLoading = false;
            this.payment.successful = true;
        },

        async saveShipping() {
            this.shipping.isLoading = true;
            if (Object.values(this.shipping.formData).includes(null)) {
                return null;
            }

            this.salesChannel.formData.winestroConnections[this.connection.formData.id].shippingMapping =
                this.shipping.formData;
            await this.saveSalesChannel();
            this.shipping.isLoading = false;
            this.shipping.successful = true;
        },

        async saveTax() {
            this.tax.isLoading = true;
            if (Object.values(this.tax.formData).includes(null)) {
                return null;
            }

            this.salesChannel.formData.winestroConnections[this.connection.formData.id].tax =
                this.tax.formData.tax;
            this.salesChannel.formData.winestroConnections[this.connection.formData.id].reducedTax =
                this.tax.formData.reducedTax;
            await this.saveSalesChannel();
            this.tax.isLoading = false;
            this.tax.successful = true;
        },

        async saveTasks() {
            this.tasks.isLoading = true;

            let tasks = {};

            tasks[this.tasks.tasks.productImportTask.id] = {
                ...this.tasks.tasks.productImportTask,
                winestroConnectionId: this.connection.formData.id,
                articleNumberFormat: this.tasks.formData.articleNumberFormat,
                articleNumberYearSeparator: this.tasks.formData.articleNumberYearSeparator,
                articleNumberBottlingSeparator: this.tasks.formData.articleNumberBottlingSeparator,
                defaultManufacturer: this.tasks.formData.defaultManufacturer,
                tax: this.tasks.formData.tax,
                reducedTax: this.tasks.formData.reducedTax,
                deliveryTime: this.tasks.formData.deliveryTime,
                visibleInSalesChannelsIds: [this.salesChannel.formData.id],
                enabled: {
                    enabled: this.tasks.formData.productimport,
                    activestatus: this.tasks.formData.activestatus,
                    description: this.tasks.formData.description,
                    freeshipping: this.tasks.formData.freeshipping,
                    manufacturer: this.tasks.formData.manufacturer
                },
                execute: [
                    this.tasks.tasks.productImageUpdateTask.id,
                    this.tasks.tasks.productStockTask.id,
                    this.tasks.tasks.productCategoryAssignmentTask.id
                ],
            }

            tasks[this.tasks.tasks.productImageUpdateTask.id] = {
                ...this.tasks.tasks.productImageUpdateTask,
                winestroConnectionId: this.connection.formData.id,
                maxWidth: this.tasks.formData.maxImageWidth,
                maxHeight: this.tasks.formData.maxImageHeight,
                mediaFolder: this.tasks.formData.mediaFolder,
                enabled: {
                    enabled: this.tasks.formData.productimages
                }
            }

            tasks[this.tasks.tasks.productStockTask.id] = {
                ...this.tasks.tasks.productStockTask,
                winestroConnectionId: this.connection.formData.id,
                sellingLimit: this.tasks.formData.sellingLimit,
                enabled: {
                    enabled: this.tasks.formData.productstock
                }
            }

            tasks[this.tasks.tasks.productCategoryAssignmentTask.id] = {
                ...this.tasks.tasks.productCategoryAssignmentTask,
                winestroConnectionId: this.connection.formData.id,
                salesChannelId: this.salesChannel.formData.salesChannelId,
                enabled: {
                    enabled: this.tasks.formData.categories
                }
            }

            tasks[this.tasks.tasks.orderExportTask.id] = {
                ...this.tasks.tasks.orderExportTask,
                winestroConnectionId: this.connection.formData.id,
                productsFromWinestroConnectionIds: [this.salesChannel.formData.id],
                productsFromSalesChannelsIds: [this.connection.formData.id],
                enabled: {
                    enabled: this.tasks.formData.newsletterReceiver,
                    sendWinestroEmail: this.tasks.formData.sendWinestroEmail
                }
            }

            tasks[this.tasks.tasks.orderStatusUpdateTask.id] = {
                ...this.tasks.tasks.orderStatusUpdateTask,
                winestroConnectionId: this.connection.formData.id,
                enabled: {
                    enabled: this.tasks.formData.orderstatusupdate
                }
            }

            tasks[this.tasks.tasks.newsletterReceiverImportTask.id] = {
                ...this.tasks.tasks.newsletterReceiverImportTask,
                winestroConnectionId: this.connection.formData.id,
                salesChannelId: this.salesChannel.formData.id,
                enabled: {
                    enabled: this.tasks.formData.newsletterReceiver
                }
            }

            for (let taskId in tasks) {
                await this.sumediaWinestro.taskService.setTask(taskId, tasks[taskId]);
            }

            this.tasks.isLoading = false;
            this.tasks.successful = true;

            this.postInstallation();
        },

        async postInstallation() {
            await this.sumediaWinestro.customFieldService.upsertCustomFields();

            let cronConfig = {}

            let id = Utils.createId();
            cronConfig[id] = {
                id,
                times: '15m',
                taskId: this.tasks.tasks.productImportTask.id,
                name: this.$tc('sumedia-winestro.cron.names.productImport'),
                enabled: {
                    enabled: true
                }
            }

            id = Utils.createId();
            cronConfig[id] = {
                id,
                times: '5m',
                taskId: this.tasks.tasks.orderExportTask.id,
                name: this.$tc('sumedia-winestro.cron.names.orderExport'),
                enabled: {
                    enabled: true
                }
            }

            id = Utils.createId();
            cronConfig[id] = {
                id,
                times: '5m',
                taskId: this.tasks.tasks.orderStatusUpdateTask.id,
                name: this.$tc('sumedia-winestro.cron.names.orderStatusUpdate'),
                enabled: {
                    enabled: true
                }
            }

            id = Utils.createId();
            cronConfig[id] = {
                id,
                times: '1h',
                taskId: this.tasks.tasks.newsletterReceiverImportTask.id,
                name: this.$tc('sumedia-winestro.cron.names.newsletterReceiverImport'),
                enabled: {
                    enabled: true
                }
            }

            await this.sumediaWinestro.configService.set('cron', cronConfig);

            this.sumediaWinestro.configService.set('installationDone', true);
            this.successful = true;
            this.$parent.$parent.$parent.isInstallationNeeded = false;
        }

    }
});
