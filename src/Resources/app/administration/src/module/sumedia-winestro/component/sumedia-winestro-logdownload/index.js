import template from './index.html.twig';
import './index.scss';

const { Component } = Shopware;

Component.register('sumedia-winestro-logdownload', {
    template,

    inject: ['sumediaWinestro'],

    data() {
        return {
            isInstallationNeeded: false,
            tasks: {},
            crons: {},
            executes: [],
            tasklog: [],
            cronlog: []
        }
    },

    mounted() {
        this.checkInstallationIsNeeded();
        this.loadTasks().then(() => {
            this.loadTaskLog();
            this.loadExecutes();
        });
        this.loadCrons().then(() => {
            this.loadCronLog();
        });
    },

    methods: {
        async checkInstallationIsNeeded() {
            this.isInstallationNeeded = true !== await this.sumediaWinestro.configService.get('installationDone');
        },

        async loadTasks() {
            this.tasks = await this.sumediaWinestro.taskService.getTasks();
        },

        async loadCrons() {
            this.crons = await this.sumediaWinestro.configService.get('cron');
        },

        async loadExecutes() {
            let executes = await this.sumediaWinestro.configService.get('execute');
            this.executes = [];
            for (let i in executes) {
                this.executes.push(this.tasks[executes[i]].name);
            }
        },

        async loadTaskLog() {
            let response = await this.sumediaWinestro.apiService.post('sumedia-winestro/tasklog')
            if (response.success) {
                this.tasklog = [];
                let dates = [];
                let runs = [];
                let success = [];
                for (let i in response.lines) {
                    let line = response.lines[i];
                    let dateMatch = line.match(/\d{4}-(\d{2})-(\d{2})T(\d{2}:\d{2})/);
                    if ('undefined' !== typeof dateMatch[3]) {
                        let logIdMatch = line.match(/([a-z0-9]{13})\]/);
                        if (null !== logIdMatch) {
                            let logId = logIdMatch[1];
                            dates[logId] = dateMatch[2] + '.' + dateMatch[1] + ' ' + dateMatch[3];

                            if ('undefined' === typeof runs[logId]) {
                                runs[logId] = 'unknown';
                            }
                            if ('undefined' === typeof success[logId]) {
                                success[logId] = 'unknown';
                            }

                            let runMatch = line.match(/\[task run:.*?([a-z0-9]{32})\]/);
                            if (null !== runMatch) {
                                runs[logId] = this.tasks[runMatch[1]].name;
                            }

                            if (line.match(/\[task successful\]/)) {
                                success[logId] = 'success';
                            }
                            if (line.match(/\[task failed\]/)) {
                                success[logId] = 'failed';
                            }
                        }
                    }
                }

                let maxCount = 30;
                let count = 0;
                for (let logId in dates) {
                    this.tasklog.push({
                        text: dates[logId] + ' ' + runs[logId],
                        type: success[logId]
                    });
                    if (count++ > maxCount) {
                        break;
                    }
                }

            }
        },

        async loadCronLog() {
            let response = await this.sumediaWinestro.apiService.post('sumedia-winestro/cronlog')
            if (response.success) {
                this.cronlog = [];
                let dates = [];
                let cron = [];
                let success = [];
                for (let i in response.lines) {
                    let line = response.lines[i];
                    let dateMatch = line.match(/\d{4}-(\d{2})-(\d{2})T(\d{2}:\d{2})/);
                    if ('undefined' !== typeof dateMatch[3]) {
                        let logIdMatch = line.match(/\[([a-z0-9]{13})\]/);
                        if (null !== logIdMatch) {
                            let logId = logIdMatch[1];
                            dates[logId] = dateMatch[2] + '.' + dateMatch[1] + ' ' + dateMatch[3];

                            if ('undefined' === typeof cron[logId]) {
                                cron[logId] = 'manually';
                            }
                            if ('undefined' === typeof success[logId]) {
                                success[logId] = 'unknown';
                            }

                            let cronMatch = line.match(/\[cron run ([a-z0-9]{32})\]/);
                            if (null !== cronMatch) {
                                cron[logId] = this.crons[cronMatch[1]].name;
                            }

                            if (line.match(/\[cron success\]/)) {
                                success[logId] = 'success';
                            }
                            if (line.match(/\[cron failed\]/)) {
                                success[logId] = 'failed';
                            }
                        }
                    }
                }

                let maxCount = 20;
                let count = 0;
                for (let logId in dates) {
                    this.cronlog.push({
                        text: dates[logId] + ' ' + cron[logId],
                        type: success[logId]
                    });
                    if (count++ > maxCount) {
                        break;
                    }
                }
            }
        },

        async downloadLogFiles() {
            let response = await this.sumediaWinestro.apiService.post('sumedia-winestro/logdownload');
            if (response.success) {
                let index = location.href.indexOf('/admin') || location.href.indexOf('/#/');
                let part = location.href.substring(0, index);
                window.open(part + '/sumedia-winestro/log-token-download?token=' + response.token, '_blank');
            }
        }
    }
});
