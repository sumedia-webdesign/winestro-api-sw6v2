import template from './index.html.twig';
import './index.scss';

const { Component } = Shopware;

Component.register('sumedia-winestro', {
    template,

    inject: ['sumediaWinestro'],

    data() {
        return {
            isInstallationNeeded: true
        }
    },

    mounted() {
        this.checkInstallationIsNeeded();
    },

    methods: {
        async checkInstallationIsNeeded() {
            this.isInstallationNeeded = !(true === await this.sumediaWinestro.configService.get('installationDone'));
        }

    }
});
