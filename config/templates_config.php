<?php
// Static template definitions using template_code
define('TEMPLATES', [
    'LPD' => [
        'template_code' => 'LPD',
        'template_name' => 'Lipid Substrate'
    ],
    'LSB' => [
        'template_code' => 'LSB',
        'template_name' => 'Liquid Substrate'
    ],
    'LYO' => [
        'template_code' => 'LYO',
        'template_name' => 'Lyophilized Peptide'
    ],
    'SUB' => [
        'template_code' => 'SUB',
        'template_name' => 'Protein'
    ],
    'ACT' => [
        'template_code' => 'ACT',
        'template_name' => 'Active Enzyme'
    ],
    'RGT' => [
        'template_code' => 'RGT',
        'template_name' => 'Buffer Reagent'
    ],
    'CPD' => [
        'template_code' => 'CPD',
        'template_name' => 'Compound'
    ]
]);

// Static section definitions
define('SECTIONS', [
    1 => [
        'id' => 1,
        'section_name' => 'Description'
    ],
    2 => [
        'id' => 2,
        'section_name' => 'Specifications'
    ],
    3 => [
        'id' => 3,
        'section_name' => 'Preparation and Storage'
    ]
]);

// Template fields mapping - which fields belong to which template/section
define('TEMPLATE_FIELDS', [
    // Template LPD - Lipid Substrate
    'LPD' => [
        1 => [ // Section 1 - Description
            ['field_name' => 'Detail', 'field_source' => 'catalog', 'field_order' => 1, 'db_field' => 'detail'],
            ['field_name' => 'Predicted Molecular Mass', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'predictedMolMass']
        ],
        2 => [ // Section 2 - Specifications
            ['field_name' => 'Formulation', 'field_source' => 'catalog', 'field_order' => 1, 'db_field' => 'formulation']
        ],
        3 => [ // Section 3 - Preparation and Storage
            ['field_name' => 'Concentration', 'field_source' => 'lot', 'field_order' => 1, 'db_field' => 'concentration'],
            ['field_name' => 'Shipping', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'shipping'],
            ['field_name' => 'Stability & Storage', 'field_source' => 'catalog', 'field_order' => 3, 'db_field' => 'stability']
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
            ['field_name' => 'Stability & Storage', 'field_source' => 'catalog', 'field_order' => 3, 'db_field' => 'stability']
        ]
    ],
    
    // Template LYO - Lyophilized Peptide
    'LYO' => [
        1 => [
            ['field_name' => 'Source', 'field_source' => 'catalog', 'field_order' => 1, 'db_field' => 'source'],
            ['field_name' => 'Predicted Molecular Mass', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'predictedMolMass']
        ],
        2 => [
            ['field_name' => 'Purity', 'field_source' => 'lot', 'field_order' => 1, 'db_field' => 'purity'],
            ['field_name' => 'Formulation', 'field_source' => 'lot', 'field_order' => 2, 'db_field' => 'formulation']
        ],
        3 => [
            ['field_name' => 'Reconstitution', 'field_source' => 'catalog', 'field_order' => 1, 'db_field' => 'reconstitution'],
            ['field_name' => 'Shipping', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'shipping'],
            ['field_name' => 'Stability & Storage', 'field_source' => 'catalog', 'field_order' => 3, 'db_field' => 'stability']
        ]
    ],
    
    // Template SUB - Protein
    'SUB' => [
        1 => [
            ['field_name' => 'Source', 'field_source' => 'catalog', 'field_order' => 1, 'db_field' => 'source'],
            ['field_name' => 'Predicted N terminal', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'predictedNTerminal'],
            ['field_name' => 'Predicted Molecular Mass', 'field_source' => 'catalog', 'field_order' => 3, 'db_field' => 'predictedMolMass'],
            ['field_name' => 'Observed Molecular Mass', 'field_source' => 'catalog', 'field_order' => 4, 'db_field' => 'observedMolMass']
        ],
        2 => [
            ['field_name' => 'Concentration', 'field_source' => 'lot', 'field_order' => 1, 'db_field' => 'concentration'],
            ['field_name' => 'Formulation', 'field_source' => 'lot', 'field_order' => 2, 'db_field' => 'formulation']
        ],
        3 => [
            ['field_name' => 'Purity', 'field_source' => 'lot', 'field_order' => 1, 'db_field' => 'purity'],
            ['field_name' => 'Shipping', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'shipping'],
            ['field_name' => 'Stability & Storage', 'field_source' => 'catalog', 'field_order' => 3, 'db_field' => 'stability']
        ]
    ],
    
    // Template ACT - Active Enzyme
    'ACT' => [
        1 => [
            ['field_name' => 'Source', 'field_source' => 'catalog', 'field_order' => 1, 'db_field' => 'source'],
            ['field_name' => 'Predicted N terminal', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'predictedNTerminal'],
            ['field_name' => 'Predicted Molecular Mass', 'field_source' => 'catalog', 'field_order' => 3, 'db_field' => 'predictedMolMass'],
            ['field_name' => 'Observed Molecular Mass', 'field_source' => 'catalog', 'field_order' => 4, 'db_field' => 'observedMolMass']
        ],
        2 => [
            ['field_name' => 'Activity', 'field_source' => 'lot', 'field_order' => 1, 'db_field' => 'activity'],
            ['field_name' => 'Purity', 'field_source' => 'lot', 'field_order' => 2, 'db_field' => 'purity'],
            ['field_name' => 'Formulation', 'field_source' => 'lot', 'field_order' => 3, 'db_field' => 'formulation']
        ],
        3 => [
            ['field_name' => 'Concentration', 'field_source' => 'lot', 'field_order' => 1, 'db_field' => 'concentration'],
            ['field_name' => 'Shipping', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'shipping'],
            ['field_name' => 'Stability & Storage', 'field_source' => 'catalog', 'field_order' => 3, 'db_field' => 'stability']
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
            ['field_name' => 'Stability & Storage', 'field_source' => 'catalog', 'field_order' => 3, 'db_field' => 'stability']
        ]
    ],
    
    // Template CPD - Compound
    'CPD' => [
        1 => [
            ['field_name' => 'CAS', 'field_source' => 'catalog', 'field_order' => 1, 'db_field' => 'cas'],
            // ['field_name' => 'Molecular Formula', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'molFormula'], // asked to be removed by Donna
            ['field_name' => 'Predicted Molecular Mass', 'field_source' => 'catalog', 'field_order' => 3, 'db_field' => 'predictedMolMass']
        ],
        2 => [
            ['field_name' => 'Purity', 'field_source' => 'lot', 'field_order' => 1, 'db_field' => 'purity'],
            ['field_name' => 'Formulation', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'formulation']
        ],
        3 => [
            ['field_name' => 'Reconstitution', 'field_source' => 'catalog', 'field_order' => 1, 'db_field' => 'reconstitution'],
            ['field_name' => 'Shipping', 'field_source' => 'catalog', 'field_order' => 2, 'db_field' => 'shipping'],
            ['field_name' => 'Stability & Storage', 'field_source' => 'catalog', 'field_order' => 3, 'db_field' => 'stability']
        ]
    ]
]);
?>