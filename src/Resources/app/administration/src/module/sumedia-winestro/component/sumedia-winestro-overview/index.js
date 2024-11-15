import template from './index.html.twig';
import './index.scss';

const { Component } = Shopware;

Component.register('sumedia-winestro-overview', {
    template,

    inject: ['sumediaWinestro'],

    data() {
        return {
            isInstallationNeeded: false,
            isMigrationNeeded: true,
            tasks: {}
        }
    },

    mounted() {
        this.checkInstallationIsNeeded();
        this.checkMigrationIsNeeded();
        this.loadTasks();
    },

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    },

    methods: {
        async checkInstallationIsNeeded() {
            this.isInstallationNeeded = true !== await this.sumediaWinestro.configService.get('installationDone');
        },

        async checkMigrationIsNeeded() {
            this.isMigrationNeeded = false === await this.sumediaWinestro.configService.get('migrationDone');
        },

        async loadTasks() {
            this.tasks = await this.sumediaWinestro.taskService.getTasks();
        }
    }
});
