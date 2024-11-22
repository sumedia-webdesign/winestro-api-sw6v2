import template from './index.html.twig';
import './index.scss';

const { Component } = Shopware;

Component.register('sumedia-winestro-loganalyzer', {
    template,

    inject: ['sumediaWinestro'],

    data() {
        return {
            tasks: {},
            crons: {},
            log: []
        }
    },

    mounted() {
        this.loadTasks().then(() => {
            this.loadCrons().then(() => {
                this.loadLog();
            });
        });
    },

    methods: {
        async loadTasks() {
            this.tasks = await this.sumediaWinestro.taskService.getTasks();
        },

        async loadCrons() {
            this.crons = await this.sumediaWinestro.configService.get('cron');
        },

        async loadLog() {
            let response = await this.sumediaWinestro.apiService.post('sumedia-winestro/processlog')
            if (response.success) {
                let log = [];
                for (let i in response.lines) {
                    let line = response.lines[i];
                    let dateMatch = line.match(/\d{4}-(\d{2})-(\d{2})T(\d{2}:\d{2})/);
                    if ('undefined' !== typeof dateMatch[3]) {

                        let logId = line.match(/((?:[a-zA-Z0-9]{13}-?)+)\]/);
                        if (null !== logId) {
                            logId = logId[1];
                        }
                        let logType = line.match(/(cron|task)/);
                        if (null !== logType) {
                            logType = logType[1];
                        }
                        let runMatch = line.match(/run ([a-z0-9]{32})\]/);
                        if (null !== runMatch) {
                            runMatch = runMatch[1];
                        }
                        let isSuccess = line.match('success');
                        if (null !== isSuccess) {
                            isSuccess = true;
                        }
                        let isFailed = line.match('failed');
                        if (null !== isFailed) {
                            isSuccess = false;
                        }

                        if (null !== logId && null !== logType) {
                            if ('undefined' === typeof log[logId]) {
                                log[logId] = [];
                            }
                            log[logId]['type'] = logType;
                            log[logId]['date'] = dateMatch[2] + '.' + dateMatch[1] + ' ' + dateMatch[3];
                            if (null !== runMatch) {
                                log[logId]['id'] = runMatch;
                            }
                            if (null !== isSuccess) {
                                log[logId]['success'] = isSuccess;
                            }
                        }
                    }
                }

                let maxCount = 50;
                let count = 0;
                for (let logId in log) {
                    if (log[logId].id) {
                        if (
                            (log[logId].type === 'cron' && 'undefined' !== this.crons[logId] ) ||
                            (log[logId].type === 'task' && 'undefined' !== this.tasks[logId] )
                        ) {
                            this.log.push({
                                text: log[logId].date + ' ' + (log[logId].type === 'cron' ? this.crons[log[logId].id].name : this.tasks[log[logId].id].name) + ' (' + (log[logId].type === 'cron' ? 'Start' : 'Aufgabe') + ')',
                                type: log[logId].success ? 'success' : 'failed'
                            });
                            if (count++ > maxCount) {
                                break;
                            }
                        }
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
