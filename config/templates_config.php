<?php
// Static template definitions using template_code
define('TEMPLATES', [
    'LPD' => [
        'template_code' => 'LPD',
        'template_name' => 'Lipid Substrate',
        'description' => null,
        'is_default' => true
    ],
    'LSB' => [
        'template_code' => 'LSB',
        'template_name' => 'Liquid Substrate',
        'description' => null,
        'is_default' => false
    ],
    'LYO' => [
        'template_code' => 'LYO',
        'template_name' => 'Lyophilized Peptide',
        'description' => null,
        'is_default' => false
    ],
    'SUB' => [
        'template_code' => 'SUB',
        'template_name' => 'Protein',
        'description' => null,
        'is_default' => false
    ],
    'ACT' => [
        'template_code' => 'ACT',
        'template_name' => 'Active Enzyme',
        'description' => null,
        'is_default' => false
    ],
    'RGT' => [
        'template_code' => 'RGT',
        'template_name' => 'Buffer Reagent',
        'description' => null,
        'is_default' => false
    ],
    'CPD' => [
        'template_code' => 'CPD',
        'template_name' => 'Compound',
        'description' => null,
        'is_default' => false
    ]
]);

// Static section definitions
define('SECTIONS', [
    1 => [
        'id' => 1,
        'section_name' => 'Description',
        'description' => 'Product description and source information',
        'default_order' => 1
    ],
    2 => [
        'id' => 2,
        'section_name' => 'Specifications',
        'description' => 'Product specifications including purity and formulation',
        'default_order' => 2
    ],
    3 => [
        'id' => 3,
        'section_name' => 'Preparation and Storage',
        'description' => 'Shipping, stability and storage instructions',
        'default_order' => 3
    ]
]);

// Template fields mapping - which fields belong to which template/section
define('TEMPLATE_FIELDS', [
    // Template LPD - Lipid Substrate
    'LPD' => [
        1 => [ // Section 1 - Description
            ['field_name' => 'Source', 'field_source' => 'catalog', 'field_order' => 1, 'db_field' => 'source'],
            ['field_name' => 'Predicted Molecular Mass', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'predicted_molecular_mass']
        ],
        2 => [ // Section 2 - Specifications
            ['field_name' => 'Formulation', 'field_source' => 'catalog', 'field_order' => 1, 'db_field' => 'formulation']
        ],
        3 => [ // Section 3 - Preparation and Storage
            ['field_name' => 'Concentration', 'field_source' => 'lot', 'field_order' => 1, 'db_field' => 'concentration'],
            ['field_name' => 'Shipping', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'shipping'],
            ['field_name' => 'Stability & Storage', 'field_source' => 'catalog', 'field_order' => 3, 'db_field' => 'stability_storage']
        ]
    ],
    
    // Template LSB - Liquid Substrate
    'LSB' => [
        1 => [ // Description
            ['field_name' => 'Source', 'field_source' => 'catalog', 'field_order' => 1, 'db_field' => 'source']
        ],
        2 => [ // Specifications
            ['field_name' => 'Purity', 'field_source' => 'lot', 'field_order' => 1, 'db_field' => 'purity'],
            ['field_name' => 'Formulation', 'field_source' => 'lot', 'field_order' => 2, 'db_field' => 'formulation']
        ],
        3 => [ // Preparation and Storage
            ['field_name' => 'Concentration', 'field_source' => 'lot', 'field_order' => 1, 'db_field' => 'concentration'],
            ['field_name' => 'Shipping', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'shipping'],
            ['field_name' => 'Stability & Storage', 'field_source' => 'catalog', 'field_order' => 3, 'db_field' => 'stability_storage']
        ]
    ],
    
    // Template LYO - Lyophilized Peptide
    'LYO' => [
        1 => [
            ['field_name' => 'Source', 'field_source' => 'catalog', 'field_order' => 1, 'db_field' => 'source'],
            ['field_name' => 'Predicted Molecular Mass', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'predicted_molecular_mass']
        ],
        2 => [
            ['field_name' => 'Purity', 'field_source' => 'lot', 'field_order' => 1, 'db_field' => 'purity'],
            ['field_name' => 'Formulation', 'field_source' => 'lot', 'field_order' => 2, 'db_field' => 'formulation']
        ],
        3 => [
            ['field_name' => 'Reconstitution', 'field_source' => 'catalog', 'field_order' => 1, 'db_field' => 'reconstitution'],
            ['field_name' => 'Shipping', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'shipping'],
            ['field_name' => 'Stability & Storage', 'field_source' => 'catalog', 'field_order' => 3, 'db_field' => 'stability_storage']
        ]
    ],
    
    // Template SUB - Protein
    'SUB' => [
        1 => [
            ['field_name' => 'Source', 'field_source' => 'catalog', 'field_order' => 1, 'db_field' => 'source'],
            ['field_name' => 'Predicted N terminal', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'predicted_n_terminal'],
            ['field_name' => 'Predicted Molecular Mass', 'field_source' => 'catalog', 'field_order' => 3, 'db_field' => 'predicted_molecular_mass'],
            ['field_name' => 'Observed Molecular Mass', 'field_source' => 'catalog', 'field_order' => 4, 'db_field' => 'observed_molecular_mass']
        ],
        2 => [
            ['field_name' => 'Concentration', 'field_source' => 'lot', 'field_order' => 1, 'db_field' => 'concentration'],
            ['field_name' => 'Formulation', 'field_source' => 'lot', 'field_order' => 2, 'db_field' => 'formulation']
        ],
        3 => [
            ['field_name' => 'Purity', 'field_source' => 'lot', 'field_order' => 1, 'db_field' => 'purity'],
            ['field_name' => 'Shipping', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'shipping'],
            ['field_name' => 'Stability & Storage', 'field_source' => 'catalog', 'field_order' => 3, 'db_field' => 'stability_storage']
        ]
    ],
    
    // Template ACT - Active Enzyme
    'ACT' => [
        1 => [
            ['field_name' => 'Source', 'field_source' => 'catalog', 'field_order' => 1, 'db_field' => 'source'],
            ['field_name' => 'Predicted N terminal', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'predicted_n_terminal'],
            ['field_name' => 'Predicted Molecular Mass', 'field_source' => 'catalog', 'field_order' => 3, 'db_field' => 'predicted_molecular_mass'],
            ['field_name' => 'Observed Molecular Mass', 'field_source' => 'catalog', 'field_order' => 4, 'db_field' => 'observed_molecular_mass']
        ],
        2 => [
            ['field_name' => 'Activity', 'field_source' => 'lot', 'field_order' => 1, 'db_field' => 'activity'],
            ['field_name' => 'Purity', 'field_source' => 'lot', 'field_order' => 2, 'db_field' => 'purity'],
            ['field_name' => 'Formulation', 'field_source' => 'lot', 'field_order' => 3, 'db_field' => 'formulation']
        ],
        3 => [
            ['field_name' => 'Concentration', 'field_source' => 'lot', 'field_order' => 1, 'db_field' => 'concentration'],
            ['field_name' => 'Shipping', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'shipping'],
            ['field_name' => 'Stability & Storage', 'field_source' => 'catalog', 'field_order' => 3, 'db_field' => 'stability_storage']
        ]
    ],
    
    // Template RGT - Buffer Reagent
    'RGT' => [
        1 => [
            ['field_name' => 'Detail', 'field_source' => 'catalog', 'field_order' => 1, 'db_field' => 'detail']
        ],
        2 => [
            ['field_name' => 'Formulation', 'field_source' => 'catalog', 'field_order' => 1, 'db_field' => 'formulation']
        ],
        3 => [
            ['field_name' => 'Reconstitution', 'field_source' => 'catalog', 'field_order' => 1, 'db_field' => 'reconstitution'],
            ['field_name' => 'Shipping', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'shipping'],
            ['field_name' => 'Stability & Storage', 'field_source' => 'catalog', 'field_order' => 3, 'db_field' => 'stability_storage']
        ]
    ],
    
    // Template CPD - Compound
    'CPD' => [
        1 => [
            ['field_name' => 'CAS', 'field_source' => 'catalog', 'field_order' => 1, 'db_field' => 'cas'],
            ['field_name' => 'Molecular Formula', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'molecular_formula'],
            ['field_name' => 'Predicted Molecular Mass', 'field_source' => 'catalog', 'field_order' => 3, 'db_field' => 'predicted_molecular_mass']
        ],
        2 => [
            ['field_name' => 'Purity', 'field_source' => 'lot', 'field_order' => 1, 'db_field' => 'purity'],
            ['field_name' => 'Formulation', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'formulation']
        ],
        3 => [
            ['field_name' => 'Reconstitution', 'field_source' => 'catalog', 'field_order' => 1, 'db_field' => 'reconstitution'],
            ['field_name' => 'Shipping', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'shipping'],
            ['field_name' => 'Stability & Storage', 'field_source' => 'catalog', 'field_order' => 3, 'db_field' => 'stability_storage']
        ]
    ]
]);
?>