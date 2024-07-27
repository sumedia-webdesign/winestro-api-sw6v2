import template from './index.html.twig';
import './index.scss';

const { Component, Mixin, Context } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('sumedia-winestro-properties', {
    template,
    inject: ['sumediaWinestro', 'repositoryFactory'],
    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            langEnId: null,
            langDeId: null,
            languageId: null,

            isLoading: false,
            successful: false,
            properties: this.sumediaWinestro.propertyService.properties,
            formData: this.getPropertiesFormData()
        }
    },

    computed: {
        isPropertiesComplete() {
            return !Object.values(this.formData).includes(null);
        }
    },

    mounted() {
        this.loadLanguage().then(() => {
            this.loadPropertiesConfig();
        });
    },

    methods: {
        getPropertiesFormData() {
            let formData = {};
            Object.keys(this.sumediaWinestro.propertyService.properties).forEach((key) => {
                formData[key] = null;
            });
            return formData;
        },

        async getLanguageIdByCode(code) {
            let localeRepository = this.repositoryFactory.create('locale');
            let languageRepository = this.repositoryFactory.create('language');
            let criteria = new Criteria();
            criteria.addFilter(Criteria.equals('code', code));
            return await localeRepository.search(criteria, Context.api).then(async (result) => {
                if (result.length) {
                    let criteria = new Criteria();
                    criteria.addFilter(Criteria.equals('localeId', result[0].id));
                    return await languageRepository.search(criteria, Context.api).then((result) => {
                        if (result.length) {
                            return result[0].id;
                        } else {
                            return null;
                        }
                    });
                } else {
                    return null;
                }
            });
        },

        async loadLanguage() {
            this.langEnId = await this.getLanguageIdByCode('en-GB');
            this.langDeId = await this.getLanguageIdByCode('de-DE');
            this.languageId = Context.api.systemLanguageId;
        },

        async loadPropertiesConfig() {
            let properties = await this.sumediaWinestro.configService.get('properties');
            if (null !== properties && Object.values(properties).length) {
                for (let key in properties) {
                    this.formData[key] = properties[key];
                }
                for (let key in this.properties) {
                    if ('undefined' === typeof this.formData[key]) {
                        this.formData[key] = null;
                    }
                }
                this.successful = true;
            }
        },

        async createProperty(key) {
            if ("undefined" !== typeof this.properties[key]) {
                let name = this.languageId === this.langEnId ?
                    this.properties[key][0] :
                    this.properties[key][1];
                if (null === this.formData[key]) {
                    this.formData[key] = await this.sumediaWinestro.propertyService.createPropertyGroup(name);
                }
            }
        },

        async saveProperties(){
            this.isLoading = true;
            if (Object.values(this.formData).includes(null)) {
                return null;
            }
            await this.sumediaWinestro.configService.set('properties', this.formData);
            this.isLoading = false;
            this.successful = true;
            this.createNotificationSuccess({
                title: this.$tc('sumedia-winestro.properties.notificationSuccessTitle'),
                message: this.$tc('sumedia-winestro.properties.notificationSuccessMessage')
            });
        }
    }
});
