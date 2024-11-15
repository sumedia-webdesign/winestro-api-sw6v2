import template from './index.html.twig';
import './index.scss';

const { Component } = Shopware;

Component.register('sumedia-winestro', {
    template,

    inject: ['sumediaWinestro'],

    data() {
        return {
            isInstallationNeeded: true,
            isMigrationNeeded: false,
        }
    },

    mounted() {
        this.checkInstallationIsNeeded();
        this.checkMigrationIsNeeded();
    },

    methods: {
        async checkInstallationIsNeeded() {
            this.isInstallationNeeded = !(true === await this.sumediaWinestro.configService.get('installationDone'));
        },

        async checkMigrationIsNeeded() {
            this.isMigrationNeeded = false === await this.sumediaWinestro.configService.get('migrationDone');
        }
    }
});
