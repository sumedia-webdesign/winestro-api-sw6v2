import './page/sumedia-winestro';
import './component/sumedia-winestro-overview';
import './component/sumedia-winestro-installation';
import './component/sumedia-winestro-connections';
import './component/sumedia-winestro-measurements';
import './component/sumedia-winestro-properties';
import './component/sumedia-winestro-salechannels';
import './component/sumedia-winestro-tasks';
import './component/sumedia-winestro-tasks-extensions';
import './component/sumedia-winestro-cron';
import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';
import SumediaWinestroApiService from "./service/sumedia-winestro-api-service";
import SumediaWinestroConfigService from "./service/sumedia-winestro-config-service";
import SumediaWinestroCustomFieldService from "./service/sumedia-winestro-custom-field-service";
import SumediaWinestroMeasurementService from "./service/sumedia-winestro-measurement-service";
import SumediaWinestroPropertyService from "./service/sumedia-winestro-property-service";
import SumediaWinestroMd5Service from "./service/sumedia-winestro-md5-service";
import SumediaWinestroTaskService from "./service/sumedia-winestro-task-service";
import SumediaWinestro from "./service/sumedia-winestro";

const { Application, Module } = Shopware;

const SUMEDIA_WINESTRO_CONFIG_DOMAIN = 'SumediaWinestroApi';

Application.addServiceProvider('sumediaWinestro', (container) => {
    const initContainer = Application.getContainer('init');

    let apiService = new SumediaWinestroApiService(initContainer.httpClient,container.loginService);
    let propertyService = new SumediaWinestroPropertyService(container.repositoryFactory);
    let md5Service = new SumediaWinestroMd5Service();
    let configService = new SumediaWinestroConfigService(container.repositoryFactory, SUMEDIA_WINESTRO_CONFIG_DOMAIN);
    let customFieldService = new SumediaWinestroCustomFieldService(container.repositoryFactory);
    let measurementService = new SumediaWinestroMeasurementService(container.repositoryFactory);
    let taskService = new SumediaWinestroTaskService(configService);

    return new SumediaWinestro(
        container.repositoryFactory,
        configService,
        apiService,
        md5Service,
        customFieldService,
        propertyService,
        measurementService,
        taskService,
    );
});

Module.register('sumedia-winestro', {
    type: 'plugin',
    name: 'sumedia-winestro',
    title: 'sumedia-winestro.title',
    description: 'sumedia-winestro.description',
    icon: 'regular-cog',
    color: '#ffcc00',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        configuration: {
            component: 'sumedia-winestro',
            path: 'configuration',
            children: {
                overview: {
                    component: 'sumedia-winestro-overview',
                    path: 'overview'
                },
                installation: {
                    component: 'sumedia-winestro-installation',
                    path: 'installation'
                },
                connections: {
                    component: 'sumedia-winestro-connections',
                    path: 'connections'
                },
                measurements: {
                    component: 'sumedia-winestro-measurements',
                    path: 'measurements'
                },
                properties: {
                    component: 'sumedia-winestro-properties',
                    path: 'properties'
                },
                salechannels: {
                    component: 'sumedia-winestro-salechannels',
                    path: 'salechannels'
                },
                tasks: {
                    component: 'sumedia-winestro-tasks',
                    path: 'tasks'
                },
                tasksExtensions: {
                    component: 'sumedia-winestro-tasks-extensions',
                    path: 'tasks-extensions'
                },
                cron: {
                    component: 'sumedia-winestro-cron',
                    path: 'cron'
                }
            }
        }
    },

    settingsItem: [{
        group: 'plugins',
        to: 'sumedia.winestro.configuration.overview',
        icon: 'regular-database'
    }]
});
