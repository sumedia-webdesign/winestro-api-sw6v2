import template from './index.html.twig';
import './index.scss';

const { Component, Mixin, Utils, Context } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('sumedia-winestro-connections', {
    template,
    inject: ['sumediaWinestro'],
    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            winestroConnections: {},
            data: [],
            columns: [
                {property: 'winestroId', label: this.$tc('sumedia-winestro.connections.winestroId')},
                {property: 'winestroShopName', label: this.$tc('sumedia-winestro.connections.winestroShopName')}
            ],
            formData: {
                id: null,
                name: null,
                url: 'https://weinstore.net/xml/v20.0',
                userId: null,
                shopId: 1,
                secretId: 'api-usr',
                secretCode: null
            },
            checkSuccessful: false,
            create: {
                isOpen: false,
                isLoading: false
            },
            delete: {
                isOpen: false,
                isLoading: false,
                id: null
            },
            edit: {
                isOpen: false,
                isLoading: false
            }
        }
    },

    mounted() {
        this.loadData();
    },

    computed: {
        connectionCheckSuccessful() {
            let checkedData = this.checkedData;
            let formData = this.formData;
            if (
                this.checkSuccessful &&
                (
                    formData.name === '' ||
                    formData.url !== checkedData.url ||
                    formData.userId !== checkedData.userId ||
                    formData.shopId !== checkedData.shopId ||
                    formData.secretId !== checkedData.secretId ||
                    formData.secretCode !== checkedData.secretCode
                )
            ) {
                this.checkSuccessful = false;
                return false;
            }
            return this.checkSuccessful;
        }
    },

    watch: {
          winestroConnections() {
              let data = [];
              for (let id in this.winestroConnections) {
                  let connection = this.winestroConnections[id];
                  data.push({
                      id,
                      winestroId: connection.userId + '/' + connection.shopId + '/' + connection.secretId,
                      winestroShopName: connection.name
                  });
              }
              this.data = data;
          }
    },

    methods: {
        async loadData() {
            this.loadWinestroConnections();
        },

        async loadWinestroConnections(){
              this.winestroConnections = await this.sumediaWinestro.getWinestroConnections();
        },

        openCreate() {
            this.resetFormData();
            this.create.isOpen = true;
        },

        openEdit(id) {
            this.populateFormData(id);
            this.edit.isOpen = true;
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
            this.checkSuccessful = false;
            this.formData = {
                id: null,
                name: null,
                url: 'https://weinstore.net/xml/v20.0',
                userId: null,
                shopId: 1,
                secretId: 'api-usr',
                secretCode: null
            }
        },

        populateFormData(id) {
            this.checkSuccessful = false;
            this.formData = this.winestroConnections[id];
        },

        async checkConnection() {
            this.create.isLoading = true;
            this.edit.isLoading = true;
            this.checkSuccessful = false;
            let response = await this.sumediaWinestro.requestWinestroConnection(
                this.formData.url,
                this.formData.userId,
                this.formData.shopId,
                this.formData.secretId,
                this.formData.secretCode
            );
            if (!response.success) {
                this.createNotificationError({
                    title: this.$tc('sumedia-winestro.connections.checkConnection.title'),
                    message: response.message
                });
            } else {
                if (null === this.formData.name || '' === this.formData.name) {
                    this.formData.name = response.name;
                }
                this.checkSuccessful = true;
                this.checkedData = {...this.formData};
                this.createNotificationSuccess({
                    title: this.$tc('sumedia-winestro.connections.checkConnection.title'),
                    message: this.$tc('sumedia-winestro.connections.checkConnection.success')
                });
            }
            this.create.isLoading = false;
            this.edit.isLoading = false;
        },

        async setConnection() {
            this.create.isLoading = true;
            this.edit.isLoading = true;
            if (null === this.formData.id) {
                this.formData.id = this.sumediaWinestro.md5Service.md5(
                    this.formData.userId +
                    this.formData.shopId +
                    this.formData.secretId
                );
                if ('undefined' !== typeof this.winestroConnections[this.formData.id]) {
                    this.formData.id = Utils.createId();
                }
            }
            await this.sumediaWinestro.setWinestroConnection(
                this.formData.id,
                this.formData.name,
                this.formData.url,
                this.formData.userId,
                this.formData.shopId,
                this.formData.secretId,
                this.formData.secretCode
            ).then(() => {
                this.create.isLoading = false;
                this.create.isOpen = false;
                this.edit.isOpen = false;
                this.resetFormData();
                this.createNotificationSuccess({
                    title: this.$tc('sumedia-winestro.connections.create.notificationTitle'),
                    message: this.$tc('sumedia-winestro.connections.create.notificationSuccess')
                });
                this.loadWinestroConnections();
            });
        },

        async deleteConnection() {
            if (null === this.delete.id) {
                return;
            }
            this.delete.isLoading = true;
            await this.sumediaWinestro.removeWinestroConnection(this.delete.id).then(() => {
                this.closeDelete();
                this.createNotificationSuccess({
                    title: this.$tc('sumedia-winestro.connections.delete.notificationTitle'),
                    message: this.$tc('sumedia-winestro.connections.delete.notificationSuccess')
                });
                this.loadWinestroConnections();
            })
        }
    }
});
