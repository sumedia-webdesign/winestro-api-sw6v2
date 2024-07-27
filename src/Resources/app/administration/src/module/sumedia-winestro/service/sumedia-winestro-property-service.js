const { Context, Utils } = Shopware;
const { Criteria } = Shopware.Data;

export default class SumediaWinestroPropertyService {

    repositoryFactory = null;
    properties = {
        country: ['Country', 'Land'],
        year: ['Year', 'Jahrgang'],
        kind: ['Kind', 'Rebsorte'],
        quality: ['Quality', 'Qualität'],
        taste: ['Taste', 'Geschmack'],
        region: ['Region', 'Region'],
        articleGroup: ['Article Group', 'Artikelgruppe'],
        ingredients: ['Ingredients', 'Zutat'],
        sugar: ['Sugar', 'Zucker'],
        alcohol: ['Alcohol', 'Alkohol'],
        acid: ['Acid', 'Säure'],
        sulfits: ['Sulfits', 'Sulfite'],
        nuances: ['Nuances', 'Nuancen'],
        awards: ['Awards', 'Auszeichnung'],
        bottles: ['Bottles included', 'Flaschenanzahl'],
        category: ['Category', 'Kategorie'],
        allergens: ['Allergens', 'Allergene'],
        calories: ['Calories', 'Kalorien'],
        protein: ['Protein', 'Eiweiß'],
        area: ['Area', 'Anbaugebiet'],
        location: ['Location', 'Lage'],
        development: ['Development', 'Ausbau'],
        drinkingTemperature: ['Drinking Temperature', 'Trinktemperatur'],
        fat: ['Fat', 'Fettsäuren'],
        unsaturatedFat: ['Unsaturated fat', 'Ungesättigte Fettsäuren'],
        carbonhydrates: ['Carbonhydrates', 'Kohlenhydrate'],
        salt: ['Salt', 'Salz'],
        fiber: ['Fiber', 'Ballaststoffe'],
        vitamins: ['Vitamins', 'Vitamine']
    }

    constructor(repositoryFactory) {
        this.repositoryFactory = repositoryFactory;
    }

    async getPropertyGroup(name) {
        let propertyGroupRepository = this.repositoryFactory.create('property_group');
        const criteria = new Criteria();
        criteria.addFilter(Criteria.equals('name', name));
        return await propertyGroupRepository.search(criteria, Context.api).then((result) => {
            if (result.length) {
                return result[0];
            }
            return null;
        });
    }

    async createPropertyGroup(name){
        let propertyGroupRepository = this.repositoryFactory.create('property_group');
        let propertyGroup = await this.getPropertyGroup(name);
        if (null === propertyGroup) {
            let propertyGroup = propertyGroupRepository.create(Context.api);
            propertyGroup.id = Utils.createId();
            propertyGroup.name = name;
            await propertyGroupRepository.save(propertyGroup);
            return propertyGroup.id;
        }
    }
}