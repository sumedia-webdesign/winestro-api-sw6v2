import template from './index.html.twig';
import './index.scss';

const { Component, Mixin, Utils, Context } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('sumedia-winestro-tasks', {
    template,
    inject: ['sumediaWinestro', 'repositoryFactory'],
    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            tasks: {},
            tasksOptions: [],
            extensions: {},
            extensionsOptions: [],
            extensionOptions: [
                {id: 'productStockAdder', value: 'productStockAdder', name: this.$tc('sumedia-winestro.tasks.extensionNames.productStockAdder')},
            ],
            taskOptions: [
                {id: 'productImport', value: 'productImport', name: this.$tc('sumedia-winestro.tasks.taskNames.productImport')},
                {id: 'productImageUpdate', value: 'productImageUpdate', name: this.$tc('sumedia-winestro.tasks.taskNames.productImageUpdate')},
                {id: 'productStock', value: 'productStock', name: this.$tc('sumedia-winestro.tasks.taskNames.productStock')},
                {id: 'productCategoryAssignment', value: 'productCategoryAssignment', name: this.$tc('sumedia-winestro.tasks.taskNames.productCategoryAssignment')},
                {id: 'orderExport', value: 'orderExport', name: this.$tc('sumedia-winestro.tasks.taskNames.orderExport')},
                {id: 'orderStatusUpdate', value: 'orderStatusUpdate', name: this.$tc('sumedia-winestro.tasks.taskNames.orderStatusUpdate')},
                {id: 'newsletterReceiverImport', value: 'newsletterReceiverImport', name: this.$tc('sumedia-winestro.tasks.taskNames.newsletterReceiverImport')}
            ],
            winestroConnections: {},
            winestroConnectionsOptions: [],
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
            data: [],
            columns: [
                {property: 'active', label: this.$tc('sumedia-winestro.tasks.listing.active')},
                {property: 'task', label: this.$tc('sumedia-winestro.tasks.listing.task')},
                {property: 'name', label: this.$tc('sumedia-winestro.tasks.listing.name')},
                {property: 'winestroShopName', label: this.$tc('sumedia-winestro.tasks.listing.winestroShopName')},
            ],
            config: {
                isLoading: false,
                isOpen: false
            },
            delete: {
                isLoading: false,
                isOpen: false,
                id: null
            },
            formData: {
                type: null,
                executeId: null,
                productImport: {
                    ...this.sumediaWinestro.taskService.tasks.productImportTask,
                    id: Utils.createId()
                },
                productImageUpdate: {
                    ...this.sumediaWinestro.taskService.tasks.productImageUpdateTask,
                    id: Utils.createId()
                },
                productStock: {
                    ...this.sumediaWinestro.taskService.tasks.productStockTask,
                    id: Utils.createId()
                },
                productCategoryAssignment: {
                    ...this.sumediaWinestro.taskService.tasks.productCategoryAssignmentTask,
                    id: Utils.createId()
                },
                orderExport: {
                    ...this.sumediaWinestro.taskService.tasks.orderExportTask,
                    id: Utils.createId()
                },
                orderStatusUpdate: {
                    ...this.sumediaWinestro.taskService.tasks.orderStatusUpdateTask,
                    id: Utils.createId()
                },
                newsletterReceiverImport: {
                    ...this.sumediaWinestro.taskService.tasks.newsletterReceiverImportTask,
                    id: Utils.createId()
                }
            }
        }
    },

    mounted() {
        this.loadWinestroConnections().then(() => {
            this.loadTasks();
        });
    },

    computed: {
        isTaskComplete() {
            switch (this.formData.type) {
                case 'productImport':
                    return '' !== this.formData.productImport.name &&
                        null !== this.formData.productImport.name &&
                        null !== this.formData.productImport.winestroConnectionId &&
                        null !== this.formData.productImport.defaultManufacturer &&
                        null !== this.formData.productImport.tax &&
                        null !== this.formData.productImport.reducedTax &&
                        this.formData.productImport.visibleInSalesChannelsIds.length &&
                        null !== this.formData.productImport.deliveryTime;
                case 'productImageUpdate':
                    return '' !== this.formData.productImageUpdate.name &&
                        null !== this.formData.productImageUpdate.name &&
                        null !== this.formData.productImageUpdate.winestroConnectionId &&
                        null !== this.formData.productImageUpdate.mediaFolder &&
                        null !== this.formData.productImageUpdate.maxImageWidth &&
                        null !== this.formData.productImageUpdate.maxImageHeight;
                case 'productStock':
                    return '' !== this.formData.productStock.name &&
                        null !== this.formData.productStock.name &&
                        null !== this.formData.productStock.winestroConnectionId &&
                        0 <= this.formData.productStock.sellingLimit;
                case 'productCategoryAssignment':
                    return '' !== this.formData.productCategoryAssignment.name &&
                        null !== this.formData.productCategoryAssignment.name &&
                        null !== this.formData.productCategoryAssignment.winestroConnectionId &&
                        null !== this.formData.productCategoryAssignment.categoryIdentifier &&
                        '' !== this.formData.productCategoryAssignment.categoryIdentifier &&
                        null !== this.formData.productCategoryAssignment.salesChannelId;
                case 'orderExport':
                    return '' !== this.formData.orderExport.name &&
                        null !== this.formData.orderExport.name &&
                        null !== this.formData.orderExport.winestroConnectionId &&
                        null !== this.formData.orderExport.productsFromWinestroConnectionIds &&
                        this.formData.orderExport.productsFromWinestroConnectionIds.length &&
                        null !== this.formData.orderExport.productsFromSalesChannelsIds &&
                        this.formData.orderExport.productsFromSalesChannelsIds.length;
                case 'orderStatusUpdate':
                    return '' !== this.formData.orderStatusUpdate.name &&
                        null !== this.formData.orderStatusUpdate.name &&
                        null !== this.formData.orderStatusUpdate.winestroConnectionId;
                case 'newsletterReceiverImport':
                    return '' !== this.formData.newsletterReceiverImport.name &&
                        null !== this.formData.newsletterReceiverImport.name &&
                        null !== this.formData.newsletterReceiverImport.salesChannelId &&
                        null !== this.formData.newsletterReceiverImport.winestroConnectionId;

            }
            return false;
        },
        salesChannelRepository() {
            return this.repositoryFactory.create('sales_channel');
        },
        filteredTasksOptions() {
            let taskIds = this.getInvalidTaskIds(this.formData[this.formData.type].id);
            return this.tasksOptions.filter(task => !taskIds.includes(task.id));
        },
    },

    watch: {
        tasks() {
            let data = [];
            for(let id in this.tasks) {
                data.push({
                    id,
                    value: id,
                    active: this.tasks[id].enabled.enabled
                        ? this.$tc('sumedia-winestro.yes')
                        : this.$tc('sumedia-winestro.no'),
                    task: this.$tc('sumedia-winestro.tasks.taskNames.' + this.tasks[id].type),
                    name: this.tasks[id].name,
                    winestroShopName: this.winestroConnections[this.tasks[id].winestroConnectionId].name
                });
            }
            this.data = data;

            let options = [];
            for(let id in this.tasks) {
                options.push({
                    id,
                    value: id,
                    name: this.tasks[id].name
                });
            }
            this.tasksOptions = options;
        },
        winestroConnections() {
            let data = [];
            for(let id in this.winestroConnections) {
                data.push({
                    id,
                    value: id,
                    name: this.winestroConnections[id].name,
                    label: this.winestroConnections[id].name
                });
            }
            this.winestroConnectionsOptions = data;
        }
    },

    methods: {
        async loadWinestroConnections() {
            this.winestroConnections = await this.sumediaWinestro.getWinestroConnections();
        },

        async loadTasks() {
            this.tasks = await this.sumediaWinestro.taskService.getTasks();
        },

        getInvalidTaskIds(taskId) {
            let taskIds = [taskId];
            if ('undefined' !== typeof this.tasks[taskId]) {
                for (let i in this.tasks[taskId].execute) {
                    if (this.tasks[taskId].execute[i].execute) {
                        taskIds = {...taskIds, ...this.getInvalidTaskIds(executeTaskId)}
                    } else {
                        taskIds.push(this.tasks[taskId].execute[i]);
                    }
                }
            }
            return taskIds;
        },

        resetFormData() {
            this.formData = {
                type: null,
                productImport: {
                    ...this.sumediaWinestro.taskService.tasks.productImportTask,
                    id: Utils.createId()
                },
                productImageUpdate: {
                    ...this.sumediaWinestro.taskService.tasks.productImageUpdateTask,
                    id: Utils.createId()
                },
                productStock: {
                    ...this.sumediaWinestro.taskService.tasks.productStockTask,
                    id: Utils.createId()
                },
                productCategoryAssignment: {
                    ...this.sumediaWinestro.taskService.tasks.productCategoryAssignmentTask,
                    id: Utils.createId()
                },
                orderExport: {
                    ...this.sumediaWinestro.taskService.tasks.orderExportTask,
                    id: Utils.createId()
                },
                orderStatusUpdate: {
                    ...this.sumediaWinestro.taskService.tasks.orderStatusUpdateTask,
                    id: Utils.createId()
                },
                newsletterReceiverImport: {
                    ...this.sumediaWinestro.taskService.tasks.newsletterReceiverImportTask,
                    id: Utils.createId()
                }
            }
        },

        populateFormData(taskId) {
            let task = this.tasks[taskId];
            this.formData[task.type] = task;
        },

        openConfig(taskId) {
            this.config.isOpen = true;
            this.formData.type = this.tasks[taskId].type;
            this.populateFormData(taskId);
        },

        openCreate() {
            this.resetFormData();
            this.config.isOpen = true;
        },

        closeConfig() {
            this.config.isOpen = false;
            this.formData.type = null;
            this.formData.executeId = null;
            this.resetFormData();
        },

        openDelete(id) {
            this.delete.id = id;
            this.delete.isOpen = true;
        },

        closeDelete() {
            this.delete.id = null;
            this.delete.isOpen = false;
        },

        async deleteTask() {
            await this.sumediaWinestro.taskService.removeTask(this.delete.id);
            this.createNotificationSuccess({
                title: this.$tc('sumedia-winestro.tasks.notificationTaskDeletedTitle'),
                message: this.$tc('sumedia-winestro.tasks.notificationTaskDeletedMessage')
            });
            this.closeDelete();
            this.loadTasks();
        },

        async saveTask(type) {
            if (!this.isTaskComplete) {
                return null;
            }
            this.config.isLoading = true;

            let definition = this.formData[type];
            let taskId = this.formData[type].id;

            await this.sumediaWinestro.taskService.setTask(taskId, definition);

            this.config.isLoading = false;
            this.config.isOpen = false;
            this.resetFormData();

            this.createNotificationSuccess({
                title: this.$tc('sumedia-winestro.tasks.notificationTaskSavedTitle'),
                message: this.$tc('sumedia-winestro.tasks.notificationTaskSavedMessage')
            });

            this.loadTasks();
        },

        addExecute(type, executeId) {
            let exists = false;
            this.formData[type].execute.forEach((id) => {
                if (id === executeId) {
                    exists = true;
                }
            });
            if (!exists) {
                this.formData[type].execute.push(executeId);
            }
            this.formData.executeId = null;
        },

        removeExecute(type, executeId) {
            let execute = [];
            this.formData[type].execute.forEach((id) => {
                if (id !== executeId) {
                    execute.push(id);
                }
            });
            this.formData[type].execute = execute;
        },

        async executeTask(taskId) {
            let executeConfig = await this.sumediaWinestro.configService.get('execute') || [];
            if (!executeConfig.length) {
                executeConfig = [];
            }
            if (executeConfig.includes(taskId)) {
                this.createNotificationError({
                    title: this.$tc('sumedia-winestro.tasks.notificationTaskExecuteDuplicateTitle'),
                    message: this.$tc('sumedia-winestro.tasks.notificationTaskExecuteDuplicateMessage')
                });
                return
            }
            executeConfig.push(taskId);
            this.sumediaWinestro.configService.set('execute', executeConfig);
            this.createNotificationSuccess({
                title: this.$tc('sumedia-winestro.tasks.notificationTaskExecuteTitle'),
                message: this.$tc('sumedia-winestro.tasks.notificationTaskExecuteMessage')
            });
        }
    }
});
