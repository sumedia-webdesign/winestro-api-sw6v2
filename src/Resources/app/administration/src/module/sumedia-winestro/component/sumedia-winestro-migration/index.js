import template from './index.html.twig';
import './index.scss';

const { Component, Mixin } = Shopware;

Component.register('sumedia-winestro-migration', {
    template,
    inject: ['sumediaWinestro'],
    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            isMigrationNeeded: true
        }
    },

    async mounted() {
        if (true === await this.sumediaWinestro.configService.get('migrationDone')){
            this.$parent.$parent.$parent.isMigrationNeeded = false;
        }
    },

    methods: {
        setMigrationDone() {
            this.sumediaWinestro.configService.set('migrationDone', true);
            this.isMigrationNeeded = false;
            this.$parent.$parent.$parent.isMigrationNeeded = false;
        }

    }
});
