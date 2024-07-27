const { Context, Utils } = Shopware;
const { Criteria } = Shopware.Data;

export default class SumediaWinestroTaskService {

    configService= null;
    tasks = {
        productImportTask: {
            id: Utils.createId(),
            type: 'productImport',
            name: null,
            winestroConnectionId: null,
            articleNumberFormat: '[articlenumber+year+bottling]',
            articleNumberYearSeparator: '+',
            bottlingSeparator: '+',
            defaultManufacturer: null,
            tax: null,
            reducedTax: null,
            deliveryTime: null,
            visibleInSalesChannelsIds: [],
            enabled: {
                enabled: true,
                activestatus: true,
                description: true,
                freeshipping: true,
                manufacturer: true
            },
            extensions: {},
            execute: []
        },
        productImageUpdateTask: {
            id: Utils.createId(),
            type: 'productImageUpdate',
            name: null,
            winestroConnectionId: null,
            maxImageWidth: 860,
            maxImageHeight: 860,
            mediaFolder: null,
            enabled: {
                enabled: true
            },
            extensions: {},
            execute: []
        },
        productStockTask: {
            id: Utils.createId(),
            type: 'productStock',
            name: null,
            winestroConnectionId: null,
            sellingLimit: 0,
            enabled: {
                enabled: true
            },
            extensions: {},
            execute: []
        },
        productCategoryAssignmentTask: {
            id: Utils.createId(),
            type: 'productCategoryAssignment',
            name: null,
            winestroConnectionId: null,
            salesChannelId: null,
            categoryIdentifier: 'Winestro',
            enabled: {
                enabled: true
            },
            extensions: {},
            execute: []
        },
        orderExportTask: {
            id: Utils.createId(),
            type: 'orderExport',
            name: null,
            winestroConnectionId: null,
            productsFromWinestroConnectionIds: null,
            productsFromSalesChannelsIds: null,
            enabled: {
                enabled: true,
                sendWinestroEmail: false
            },
            extensions: {},
            execute: []
        },
        orderStatusUpdateTask: {
            id: Utils.createId(),
            type: 'orderStatusUpdate',
            name: null,
            winestroConnectionId: null,
            suppressEmail: true,
            enabled: {
                enabled: true
            },
            extensions: {},
            execute: []
        },
        newsletterReceiverImportTask: {
            id: Utils.createId(),
            type: 'newsletterReceiverImport',
            name: null,
            winestroConnectionId: null,
            salesChannelId: null,
            enabled: {
                enabled: true,
            },
            extensions: {},
            execute: []
        }
    }

    extensions = {
        productStockAdder: {
            id: Utils.createId(),
            type: 'productStockAdder',
            name: null,
            taskId: null,
            winestroConnectionId: null,
            enabled: {
                enabled: true,
            }
        }
    }

    constructor(configService) {
        this.configService = configService;
    }

    async getTasks() {
        return await this.configService.get('tasks');
    }

    async getTask(taskId) {
        let tasks = await this.getTasks();
        tasks = null !== tasks ? tasks : {};

        for (let id in tasks) {
            if (id === taskId) {
                return tasks[id];
            }
        }
        return null;
    }

    async setTask(taskId, definition) {
        let tasks = await this.configService.get('tasks');
        tasks = null === tasks ? {} : tasks;

        tasks[taskId] = definition;

        return await this.configService.set('tasks', tasks);
    }

    async removeTask(taskId){
        let tasks = await this.configService.get('tasks');
        tasks = null === tasks ? {} : tasks;

        let newValue = {};
        for (let id in tasks) {
            if (id !== taskId) {
                newValue[id] = tasks[id];
            }
        }

        return await this.configService.set('tasks', newValue);
    }
}