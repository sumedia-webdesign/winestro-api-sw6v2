const { Utils, Context } = Shopware;
const { Criteria } = Shopware.Data;

export default class SumediaWinestroMeasurementService {

    repositoryFactory = null;

    measurements = {
        litre: ['Litre', 'l', 'Liter', 'l'],
        kilo: ['Kilogram', 'kg', 'Kilogramm', 'kg'],
        gramperhundret: ['Gram per 100 ml', 'g', 'Gramm pro 100 ml', 'g'],
        volumepercent: ['Volumepercent', '% vol.', 'Volumenprozent', '% vol.'],
    }

    constructor(repositoryFactory) {
        this.repositoryFactory = repositoryFactory;
    }

    async getUnit(name) {
        let unitRepository = this.repositoryFactory.create('unit');
        const criteria = new Criteria();
        criteria.addFilter(Criteria.equals('name', name));
        return await unitRepository.search(criteria, Context.api).then((result) => {
            if (result.length) {
                return result[0];
            }
            return null;
        });
    }

    async createUnit(name, shortcode){
        let unitRepository = this.repositoryFactory.create('unit');
        let unit = await this.getUnit(name);
        if (null === unit) {

            let unit = unitRepository.create(Context.api);
            unit.id = Utils.createId();
            unit.name = name;
            unit.shortCode = shortcode;
            await unitRepository.save(unit);
            return unit.id;
        }
    }
}