import template from './index.html.twig';
import './index.scss';

const { Component, Mixin, Utils, Context } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('sumedia-winestro-tasks-extensions', {
    template,
    inject: ['sumediaWinestro', 'repositoryFactory'],
    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            winestroConnections: {},
            winestroConnectionsOptions: [],
            extensionsOptions: [
                {id: 'productStockAdder', value: 'productStockAdder', name: this.$tc('sumedia-winestro.tasks-extensions.extensionsNames.productStockAdder')},
            ],
            tasks: {},
            tasksOptions: [],
            data: [],
            columns: [
                {property: 'active', label: this.$tc('sumedia-winestro.tasks-extensions.listing.active')},
                {property: 'task', label: this.$tc('sumedia-winestro.tasks-extensions.listing.task')},
                {property: 'name', label: this.$tc('sumedia-winestro.tasks-extensions.listing.name')},
                {property: 'winestroShopName', label: this.$tc('sumedia-winestro.tasks-extensions.listing.winestroShopName')},
            ],
            config: {
                isLoading: false,
                isOpen: false
            },
            delete: {
                isOpen: false,
                id: null
            },
            formData: {
                type: null,
                productStockAdder: {
                    ...this.sumediaWinestro.taskService.extensions.productStockAdder,
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

    watch: {
        winestroConnections() {
            let data = [];
            for(let id in this.winestroConnections) {
                data.push({
                    id,
                    value: id,
                    name: this.winestroConnections[id].name
                });
            }
            this.winestroConnectionsOptions = data;
        },
        tasks() {
            let extensions = [];
            let tasks = [];
            for (let taskId in this.tasks) {
                tasks.push({
                    id: taskId,
                    value: taskId,
                    name: this.tasks[taskId].name
                });
                for (let extensionId in this.tasks[taskId].extensions) {
                    extensions.push({
                        id: taskId + '-' + extensionId,
                        value: extensionId,
                        active: this.tasks[taskId].extensions[extensionId].enabled.enabled
                            ? this.$tc('sumedia-winestro.yes') : this.$tc('sumedia-winestro.no'),
                        task: this.tasks[taskId].name,
                        name: this.tasks[taskId].extensions[extensionId].name,
                        winestroShopName: this.winestroConnections[this.tasks[taskId].extensions[extensionId].winestroConnectionId].name
                    });
                }
            }
            this.tasksOptions = tasks;
            this.data = extensions;
        }
    },

    computed: {
        isExtensionComplete() {
            switch (this.formData.type) {
                case 'productStockAdder':
                    return '' !== this.formData.productStockAdder.name &&
                        null !== this.formData.productStockAdder.name &&
                        null !== this.formData.productStockAdder.winestroConnectionId;
            }
            return false;
        }
    },

    methods: {
        async loadWinestroConnections() {
            this.winestroConnections = await this.sumediaWinestro.getWinestroConnections();
        },

        async loadTasks() {
            this.tasks = await this.sumediaWinestro.taskService.getTasks();
        },

        resetFormData() {
            this.formData = {
                type: null,
                productStockAdder: {
                    ...this.sumediaWinestro.taskService.extensions.productStockAdder,
                    id: Utils.createId()
                }
            }
        },

        populateFormData(id) {
            let split = id.split('-');
            let taskId = split[0];
            let extensionId = split[1];

            let type = this.tasks[taskId].extensions[extensionId].type;

            this.formData.type = type;
            this.formData[type] = this.tasks[taskId].extensions[extensionId];
        },

        openConfig(id) {
            this.config.isOpen = true;
            if (id) {
                this.populateFormData(id);
            }
        },

        closeConfig() {
            this.config.isOpen = false;
            this.resetFormData();
        },

        async saveExtension(type) {
            if (!this.isExtensionComplete) {
                return null;
            }
            this.config.isLoading = true;

            let extensionId = this.formData[type].id;
            let taskId = this.formData[type].taskId;

            let task = await this.sumediaWinestro.taskService.getTask(taskId);
            if (null === task) {
                return;
            }

            if (Array.isArray(task.extensions) && !task.extensions.length) {
                task.extensions = {};
            }

            task.extensions[extensionId] = this.formData[type];

            await this.sumediaWinestro.taskService.setTask(taskId, task);

            this.config.isLoading = false;
            this.config.isOpen = false;
            this.resetFormData();

            this.createNotificationSuccess({
                title: this.$tc('sumedia-winestro.tasks-extensions.notificationExtensionSavedTitle'),
                message: this.$tc('sumedia-winestro.tasks-extensions.notificationExtensionSavedMessage')
            });

            this.loadTasks();
        },

        openDelete(id) {
            this.delete.id = id;
            this.delete.isOpen = true;
        },

        closeDelete() {
            this.delete.id = null;
            this.delete.isOpen = false;
        },

        async deleteExtension() {
            this.delete.isLoading = true;

            let split = this.delete.id.split('-');
            let taskId = split[0];
            let extensionId = split[1];

            let task = await this.sumediaWinestro.taskService.getTask(taskId);
            if (null === task) {
                return;
            }

            let newExtensions = {};
            for (let eid in task.extensions) {
                if (eid !== extensionId) {
                    newExtensions[eid] = task.extensions[eid];
                }
            }
            task.extensions = newExtensions;

            await this.sumediaWinestro.taskService.setTask(taskId, task);

            this.delete.isLoading = false;
            this.delete.isOpen = false;
            this.delete.id = null;

            this.createNotificationSuccess({
                title: this.$tc('sumedia-winestro.tasks-extensions.notificationExtensionDeletedTitle'),
                message: this.$tc('sumedia-winestro.tasks-extensions.notificationExtensionDeletedMessage')
            });

            this.loadTasks();
        },
    }
});
