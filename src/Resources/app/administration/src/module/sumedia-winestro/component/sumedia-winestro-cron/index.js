import template from './index.html.twig';
import './index.scss';

const { Component, Mixin, Utils } = Shopware;

Component.register('sumedia-winestro-cron', {
    template,
    inject: ['sumediaWinestro'],
    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            crons: {},
            timesOptions: [
                {id: '5m', value: '5m', name: this.$tc('sumedia-winestro.cron.times.5m')},
                {id: '15m', value: '15m', name: this.$tc('sumedia-winestro.cron.times.15m')},
                {id: '30m', value: '30m', name: this.$tc('sumedia-winestro.cron.times.30m')},
                {id: '1h', value: '1h', name: this.$tc('sumedia-winestro.cron.times.1h')},
                {id: '6h', value: '6h', name: this.$tc('sumedia-winestro.cron.times.6h')},
                {id: '12h', value: '12h', name: this.$tc('sumedia-winestro.cron.times.12h')},
                {id: '1d', value: '1d', name: this.$tc('sumedia-winestro.cron.times.1d')},
                {id: '1w', value: '1w', name: this.$tc('sumedia-winestro.cron.times.1w')},
                {id: '1m', value: '1m', name: this.$tc('sumedia-winestro.cron.times.1m')},
            ],
            tasks: {},
            tasksOptions: [],
            data: [],
            columns: [
                {property: 'active', label: this.$tc('sumedia-winestro.cron.listing.active')},
                {property: 'times', label: this.$tc('sumedia-winestro.cron.listing.times')},
                {property: 'name', label: this.$tc('sumedia-winestro.cron.listing.name')},
                {property: 'task', label: this.$tc('sumedia-winestro.cron.listing.task')},
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
                id: Utils.createId(),
                times: null,
                name: null,
                taskId: null,
                enabled: {
                    enabled: true
                }
            }
        }
    },

    mounted() {
        this.loadTaskConfig().then(() => {
            this.loadCronConfig();
        });
    },

    watch: {
        crons() {
            let data = [];
            for(let id in this.crons) {
                data.push({
                    id,
                    value: id,
                    active: this.crons[id].enabled.enabled
                        ? this.$tc('sumedia-winestro.yes')
                        : this.$tc('sumedia-winestro.no'),
                    times: this.$tc('sumedia-winestro.cron.times.' + this.crons[id].times),
                    name: this.crons[id].name,
                    task: this.tasks[this.crons[id].taskId].name,
                });
            }
            this.data = data;
        },
        tasks() {
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
    },

    computed: {
        isCronComplete() {
            return '' !== this.formData && null !== this.formData;
        }
    },

    methods: {
        async loadCronConfig() {
            this.crons= await this.sumediaWinestro.configService.get('cron');
        },

        async loadTaskConfig() {
            this.tasks = await this.sumediaWinestro.taskService.getTasks();
        },

        resetFormData() {
            this.formData = {
                id: Utils.createId(),
                times: null,
                name: null,
                taskId: null,
                enabled: {
                    enabled: true
                }
            }
        },

        populateFormData(id) {
            this.formData = this.crons[id];
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

        async saveCron() {
            if (!this.isCronComplete) {
                return null;
            }
            this.config.isLoading = true;

            let cronConfig = await this.sumediaWinestro.configService.get('cron') || {};

            cronConfig[this.formData.id]  = this.formData;

            await this.sumediaWinestro.configService.set('cron', cronConfig);

            this.config.isLoading = false;
            this.config.isOpen = false;
            this.resetFormData();

            this.createNotificationSuccess({
                title: this.$tc('sumedia-winestro.cron.notificationExtensionSavedTitle'),
                message: this.$tc('sumedia-winestro.cron.notificationExtensionSavedMessage')
            });

            this.loadCronConfig();
        },

        openDelete(id) {
            this.delete.id = id;
            this.delete.isOpen = true;
        },

        closeDelete() {
            this.delete.id = null;
            this.delete.isOpen = false;
        },

        async deleteCron() {
            this.delete.isLoading = true;

            let cronConfig = await this.sumediaWinestro.configService.get('cron');
            let newValue = {};
            for (let i in cronConfig) {
                if (i !== this.delete.id) {
                    newValue[i] = cronConfig[i];
                }
            }
            await this.sumediaWinestro.configService.set('cron', newValue);

            this.delete.isLoading = false;
            this.delete.isOpen = false;
            this.delete.id = null;

            this.createNotificationSuccess({
                title: this.$tc('sumedia-winestro.cron.notificationExtensionDeletedTitle'),
                message: this.$tc('sumedia-winestro.cron.notificationExtensionDeletedMessage')
            });

            this.loadCronConfig();
        },
    }
});
