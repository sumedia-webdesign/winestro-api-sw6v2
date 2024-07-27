const { Utils, Context } = Shopware;
const { Criteria } = Shopware.Data;

export default class SumediaWinestroCustomFieldService {

    repositoryFactory = null;

    customFieldSets = {
        sumedia_winestro_product_details: {
            nameEN: 'Winestro Product Details',
            nameDE: 'Winestro Produktdetails',
            relations: [{entityName: 'product'}]
        },
        sumedia_winestro_product_switches: {
            nameEN: 'Winestro Product Switches',
            nameDE: 'Winestro Produktschalter',
            relations: [{entityName: 'product'}]
        },
        sumedia_winestro_order_details: {
            nameEN: 'Winestro Order Details',
            nameDE: 'Winestro Bestelldetails',
            relations: [{entityName: 'order'}]
        }
    };

    customFields = {
        sumedia_winestro_product_details: {
            apnr: {
                nameEN: 'APNR',
                nameDE: 'APNR',
                type: 'text',
                visible: true
            },
            article_number: {
                nameEN: 'Article Number',
                nameDE: 'Artikelnummer',
                type: 'text',
                visible: true
            },
            bottles: {
                nameEN: 'Numbers of bottles',
                nameDE: 'Flaschenanzahl',
                type: 'number',
                visible: false
            },
            best_before_date: {
                nameEN: 'Best Before Date',
                nameDE: 'Haltbarkeitsdatum',
                type: 'date',
                visible: true
            },
            shelf_life: {
                nameEN: 'Shelf Life',
                nameDE: 'Lagerfähigkeit',
                type: 'text',
                visible: true
            },
            e_label_free_text: {
                nameEN: 'E-Label Free Text',
                nameDE: 'E-Label Freitext',
                type: 'html',
                visible: true
            },
            description: {
                nameEN: 'Productdescription',
                nameDE: 'Produktbeschreibung',
                type: 'html',
                visible: true
            },
            shop_description: {
                nameEN: 'Shop Description',
                nameDE: 'Shopbeschreibung',
                type: 'html',
                visible: true
            },
            product_note: {
                nameEN: 'Producte Note',
                nameDE: 'Produktnotiz',
                type: 'html',
                visible: true
            },
            bundle: {
                nameEN: 'Package Items',
                nameDE: 'Sammelpaket Artikel',
                type: 'text',
                visible: false
            },
            stock_update_date: {
                nameEN: 'Last Stock Update',
                nameDE: 'Letzte Lagerbestandsaktualisierung',
                type: 'datetime',
                visible: true
            },
            winestro_connection_id: {
                nameEN: 'Winestro Connection ID',
                nameDE: 'Winestro Connection ID',
                type: 'text',
                visible: false
            }
        },
        sumedia_winestro_product_switches: {
            activestatus: {
                nameEN: 'Update Activestatus',
                nameDE: 'Aktivstatus aktualisieren',
                type: 'switch',
                visible: true
            },
            manufacturer: {
                nameEN: 'Update Manufacturer',
                nameDE: 'Hersteller aktualisieren',
                type: 'switch',
                visible: true
            },
            free_shipping: {
                nameEN: 'Update Freeshipping',
                nameDE: 'Kostenlosen Versand aktualisieren',
                type: 'switch',
                visible: true
            },
            description: {
                nameEN: 'Update Description',
                nameDE: 'Beschreibung aktualisieren',
                type: 'switch',
                visible: true
            }
        },
        sumedia_winestro_order_details: {
            order_number: {
                nameEN: 'Ordernumber',
                nameDE: 'Bestellnummer',
                type: 'text',
                visible: true
            },
            export_tries: {
                nameEN: 'Export tries',
                nameDE: 'Exportversuche',
                type: 'number',
                visible: false
            },
            billing_number: {
                nameEN: 'Billing Number',
                nameDE: 'Rechnungsnummer',
                type: 'text',
                visible: true
            }
        }
    }

    constructor(repositoryFactory) {
        this.repositoryFactory = repositoryFactory;
    }

    async upsertCustomFields() {
        for (let customFieldSetIdentifier in this.customFieldSets) {

            let customFieldSet = this.customFieldSets[customFieldSetIdentifier];
            let customFieldSetEntity = await this.getCustomFieldSetByIdentifier(customFieldSetIdentifier)
            let customFieldSetId = null;
            if (customFieldSetEntity) {
                customFieldSetId = customFieldSetEntity.id;
            } else {
                customFieldSetId = await this.createCustomFieldSet(customFieldSetIdentifier, customFieldSet.nameEN, customFieldSet.nameDE, customFieldSet.relations);
            }

            let pos = 1;
            for (let customFieldIdentifier in this.customFields[customFieldSetIdentifier]) {
                let customField = this.customFields[customFieldSetIdentifier][customFieldIdentifier];
                let identifier = customFieldSetIdentifier + '_' + customFieldIdentifier;
                if (! await this.getCustomFieldByIdentifier(identifier)) {
                    await this.createCustomField(customFieldSetId, identifier, customField.nameEN, customField.nameDE, customField.type, customField.visible, pos++);
                }
            }
        }
    }

    async getCustomFieldSetByIdentifier(identifier) {
        return await this.repositoryFactory.create('custom_field_set')
            .search((new Criteria).addFilter(Criteria.equals('name', identifier)), Context.api).then((result) => {
                if (result.length) {
                    return result[0];
                }
            });
    }

    async createCustomFieldSet(identifier, nameEN, nameDE, relations) {
        let customFieldSetRepository = this.repositoryFactory.create('custom_field_set');
        let customFieldSetId = Utils.createId();
        let entity = customFieldSetRepository.create(Context.api);
        entity.id = customFieldSetId;
        entity.name = identifier;
        entity.config = {
            label: {
                'en-GB': nameEN,
                'de-DE': nameDE
            }
        }

        await customFieldSetRepository.save(entity);

        for (let i in relations) {
            let customFieldSetRelationRepository = this.repositoryFactory.create('custom_field_set_relation');
            let customFieldSetRelation = customFieldSetRelationRepository.create(Context.api);
            customFieldSetRelation.id = Utils.createId();
            customFieldSetRelation.customFieldSetId = customFieldSetId;
            customFieldSetRelation.entityName = relations[i].entityName;
            await customFieldSetRelationRepository.save(customFieldSetRelation);
        }

        return customFieldSetId;
    }

    async getCustomFieldByIdentifier(identifier) {
        return await this.repositoryFactory.create('custom_field')
            .search((new Criteria).addFilter(Criteria.equals('name', identifier)), Context.api).then((result) => {
                if (result.length) {
                    return result[0];
                }
            });
    }

    async createCustomField(customFieldSetId, identifier, nameEN, nameDE, type, visible, position) {
        let customFieldRepository = this.repositoryFactory.create('custom_field');
        let customFieldId = Utils.createId();
        let entity = customFieldRepository.create(Context.api);
        entity.id = customFieldId;
        entity.customFieldSetId = customFieldSetId;
        entity.name = identifier;
        entity.type = type;
        entity.customFieldPosition = position;
        entity.customFieldVisible = visible;
        entity.config = {
            label: {
                'en-GB': nameEN,
                'de-DE': nameDE
            }
        }
        if (type === 'html') {
            entity.config.componentName = 'sw-text-editor';
            entity.config.customFieldType = 'html';
        }
        await customFieldRepository.save(entity);
        return customFieldId;
    }
}