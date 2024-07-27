const { Utils, Context } = Shopware;
const { Criteria } = Shopware.Data;

export default class SumediaWinestro {

    repositoryFactory = null;
    configService = null;
    apiService = null;
    md5Service = null;
    customFieldService = null;
    propertyService = null;
    measurementService = null;
    taskService = null;

    salesChannel = {
        salesChannelId: null,
        winestroConnections: {}
    }

    winestroConnections = {
        winestroConnectionId: null,
        paymentMapping: null,
        shippingMapping: null
    }

    constructor(
        repositoryFactory,
        configService,
        apiService,
        md5Service,
        customFieldService,
        propertyService,
        measurementService,
        taskService
    ) {
        this.repositoryFactory = repositoryFactory;
        this.configService = configService;
        this.apiService = apiService;
        this.md5Service = md5Service;
        this.customFieldService = customFieldService;
        this.propertyService = propertyService;
        this.measurementService = measurementService;
        this.taskService = taskService;
    }

    async getWinestroConnections() {
        return await this.configService.get('winestroConnections');
    }

    async requestWinestroConnection(url, userId, shopId, secretId, secretCode) {
        return await this.apiService.post('sumedia-winestro/check',
            {url, userId, shopId, secretId, secretCode})
            .then((response) => {
                if (!response.success) {
                    return {
                        success: false,
                        message: response.message
                    };
                } else {
                    return {
                        success: true,
                        name: response.winestroShopName
                    };
                }
            });
    }

    getWinestroConnectionId(userId, shopId, secretId) {
        return this.md5Service.md5(userId + shopId + secretId);
    }

    async setWinestroConnection(id, name, url, userId, shopId, secretId, secretCode) {
        let winestroConnections = await this.configService.get('winestroConnections');
        winestroConnections = null === winestroConnections ? {} : winestroConnections;

        winestroConnections[id] = {
            id, name, url,
            userId, shopId,
            secretId, secretCode
        }

        return await this.configService.set('winestroConnections', winestroConnections);
    }

    async removeWinestroConnection(id) {
        let winestroConnections = await this.configService.get('winestroConnections');
        winestroConnections = null === winestroConnections ? {} : winestroConnections;

        let newValue = {};
        for (let wid in winestroConnections) {
            if (wid !== id) {
                newValue[wid] = winestroConnections[wid];
            }
        }

        return await this.configService.set('winestroConnections', newValue);
    }

    async getSalesChannels() {
        return await this.configService.get('salesChannels');
    }
    
    async getSalesChannel(salesChannelId) {
        let salesChannels = this.getSalesChannels();
        salesChannels = null === salesChannels ? {} : salesChannels;
        
        for (let id in salesChannels) {
            if (id === salesChannelId) {
                return salesChannels[id];
            }
        }
        return null;
    }
    
    async setSalesChannel(salesChannelId, definition) {
        let salesChannels = await this.configService.get('salesChannels');
        salesChannels = null === salesChannels ? {} : salesChannels;

        salesChannels[salesChannelId] = definition;

        return await this.configService.set('salesChannels', salesChannels);
    }

    async removeSalesChannel(salesChannelId, winestroConnectionId){
        let salesChannels = await this.configService.get('salesChannels');
        salesChannels = null === salesChannels ? {} : salesChannels;

        let newValue = {};
        for (let sid in salesChannels) {
            if (sid !== salesChannelId) {
                newValue[sid] = salesChannels[sid];
            } else {
                let connections = {}
                for (let wid in salesChannels[sid].winestroConnections) {
                    if (wid !== winestroConnectionId) {
                        connections[wid] = salesChannels[sid].winestroConnections[wid];
                    }
                }
                if (Object.values(connections).length) {
                    newValue[sid] = salesChannels[sid];
                    newValue[sid].winestroConnections = connections;
                }
            }
        }

        return await this.configService.set('salesChannels', newValue);
    }
}