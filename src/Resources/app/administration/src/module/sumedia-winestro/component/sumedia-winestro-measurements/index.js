import template from './index.html.twig';
import './index.scss';

const { Component, Mixin, Context } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('sumedia-winestro-measurements', {
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
            measurements: this.sumediaWinestro.measurementService.measurements,
            formData: {
                litre: null,
                kilo: null,
                gramperhundret: null,
                volumepercent: null
            }
        }
    },

    computed: {
        isMeasurementsComplete() {
            return !Object.values(this.formData).includes(null);
        }
    },

    mounted() {
        this.loadLanguage().then(() => {
            this.loadMeasurementConfig();
        });
    },

    methods: {
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

        async loadMeasurementConfig() {
            let measurements = await this.sumediaWinestro.configService.get('measurements');
            if (null !== measurements && Object.values(measurements).length) {
                for (let key in measurements) {
                    this.formData[key] = measurements[key];
                }
                for (let key in this.measurements) {
                    if ('undefined' === typeof this.formData[key]) {
                        this.formData[key] = null;
                    }
                }
                this.successful = true;
            }
        },

        async createMeasurement(key) {
            if ("undefined" !== typeof this.measurements[key]) {
                let name = this.languageId === this.langEnId ?
                    this.measurements[key][0] :
                    this.measurements[key][2];
                let shortCode = this.languageId === this.langEnId ?
                    this.measurements[key][1] :
                    this.measurements[key][3];
                if (null === this.formData[key]) {
                    this.formData[key] = await this.sumediaWinestro.measurementService.createUnit(name, shortCode);
                }
            }
        },

        async saveMeasurements(){
            this.isLoading = true;
            if (Object.values(this.formData).includes(null)) {
                return null;
            }
            await this.sumediaWinestro.configService.set('measurements', this.formData);
            this.isLoading = false;
            this.successful = true;
            this.createNotificationSuccess({
                title: this.$tc('sumedia-winestro.measurements.notificationSuccessTitle'),
                message: this.$tc('sumedia-winestro.measurements.notificationSuccessMessage')
            });
        }
    }
});
