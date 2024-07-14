
const { Component, Mixin } = Shopware;
import template from './sumedia-wbo-api-test-button.html.twig';

Component.register('sumedia-wbo-api-test-button', {
    template,

    props: ['label'],
    inject: ['sumediaApiTest'],

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false,
        };
    },

    computed: {
        pluginConfig() {
            let $parent = this.$parent;

            while ($parent.actualConfigData === undefined) {
                $parent = $parent.$parent;
            }

            return $parent.actualConfigData.null;
        }
    },

    methods: {
        saveFinish() {
            this.isSaveSuccessful = false;
        },

        check() {
            this.isLoading = true;
            this.sumediaApiTest.check(this.pluginConfig).then((res) => {
                if (res.success) {
                    this.isSaveSuccessful = true;
                    this.createNotificationSuccess({
                        title: this.$tc('sumedia-wbo-api-test-button.title'),
                        message: this.$tc('sumedia-wbo-api-test-button.success')
                    });
                } else {
                    this.createNotificationError({
                        title: this.$tc('sumedia-wbo-api-test-button.title'),
                        message: this.$tc('sumedia-wbo-api-test-button.error')
                    });
                }

                this.isLoading = false;
            });
        }
    }
})
