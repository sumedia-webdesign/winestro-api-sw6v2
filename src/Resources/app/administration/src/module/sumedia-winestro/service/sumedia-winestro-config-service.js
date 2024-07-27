const { Utils, Context } = Shopware;
const { Criteria } = Shopware.Data;
const ConfigPrefix = 'SumediaWinestroApi.config.';

export default class SumediaWinestroConfigService {

    repository = null;
    configDomain = null;

    constructor(repositoryFactory, configDomain) {
        this.repository = repositoryFactory.create('system_config');
        this.configDomain = configDomain;
    }

    async get(key) {
        key = this.configDomain + '.config.' + key;
        let criteria = (new Criteria())
            .addFilter(Criteria.equals('configurationKey', key));
        return await this.repository.search(criteria, Context.api).then((result) => {
            if (result.length) {
                return result[0].configurationValue;
            }
            return null;
        });
    }

    async set(key, value) {
        key = this.configDomain + '.config.' + key;
        let criteria = (new Criteria())
            .addFilter(Criteria.equals('configurationKey', key));
        return await this.repository.search(criteria, Context.api).then((result) => {
            if (result.length) {
                result[0].configurationValue = value;
                return this.repository.save(result[0]);
            } else {
                let entity = this.repository.create(Context);
                entity.id = Utils.createId();
                entity.configurationKey = key;
                entity.configurationValue = value;
                return this.repository.save(entity);
            }
        });
    }
}