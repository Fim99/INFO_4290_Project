<?php
// Array of selected nutrient IDs
$selectedNutrientIds =
    [
        1003, // Protein
        1008, // Energy (kcal)
        1253, // Cholesterol
        1257, // Fatty acids, total trans
        2000, // Total Sugars
        1005, // Carbohydrate, by difference
        1258, // Fatty acids, total saturated
        1093, // Sodium, Na
        1004, // Total lipid (fat)
        1079, // Fiber, total dietary
        1092, // Potassium, K
        1114, // Vitamin D (D2 + D3)
        1087, // Calcium, Ca
        1235, // Sugars, added
        1089, // Iron, Fe
        1104, // Vitamin A, IU
        1162, // Vitamin C, total ascorbic acid
        1018, // Alcohol, ethyl
        1051, // Water
        1057, // Caffeine
        1058, // Theobromine
        1090, // Magnesium, Mg
        1091, // Phosphorus, P
        1095, // Zinc, Zn
        1098, // Copper, Cu
        1103, // Selenium, Se
        1105, // Retinol
        1106, // Vitamin A, RAE
        1107, // Carotene, beta
        1108, // Carotene, alpha
        1109, // Vitamin E (alpha-tocopherol)
        1120, // Cryptoxanthin, beta
        1122, // Lycopene
        1123, // Lutein + zeaxanthin
        1165, // Thiamin
        1166, // Riboflavin
        1167, // Niacin
        1175, // Vitamin B-6
        1177, // Folate, total
        1178, // Vitamin B-12
        1180, // Choline, total
        1185, // Vitamin K (phylloquinone)
        1186, // Folic acid
        1187, // Folate, food
        1190, // Folate, DFE
        1242, // Vitamin E, added
        1246  // Vitamin B-12, added
    ];

// Define nutrient types
// Define nutrient types
$nutrientTypes = [
    'macronutrients' => [
        'Protein' => 1003,
        'Energy (kcal)' => 1008,
        'Cholesterol' => 1253,
        'Carbohydrate, by difference' => 1005,
        'Total lipid (fat)' => 1004,
        'Fatty acids, total saturated' => 1258,
        'Fatty acids, total trans' => 1257,
        'Total Sugars' => 2000,
        'Fiber, total dietary' => 1079,
        'Alcohol, ethyl' => 1018,
        'Water' => 1051
    ],
    'minerals' => [
        'Sodium, Na' => 1093,
        'Potassium, K' => 1092,
        'Calcium, Ca' => 1087,
        'Magnesium, Mg' => 1090,
        'Phosphorus, P' => 1091,
        'Iron, Fe' => 1089,
        'Zinc, Zn' => 1095,
        'Copper, Cu' => 1098,
        'Selenium, Se' => 1103
    ],
    'vitamins' => [
        'Vitamin A, IU' => 1104,
        'Vitamin A, RAE' => 1106,
        'Vitamin C, total ascorbic acid' => 1162,
        'Vitamin D (D2 + D3)' => 1114,
        'Vitamin E (alpha-tocopherol)' => 1109,
        'Vitamin K (phylloquinone)' => 1185,
        'Folate, total' => 1177,
        'Folic acid' => 1186,
        'Folate, food' => 1187,
        'Folate, DFE' => 1190,
        'Vitamin B-6' => 1175,
        'Vitamin B-12' => 1178,
        'Vitamin E, added' => 1242,
        'Vitamin B-12, added' => 1246
    ],
    'other' => [
        'Caffeine' => 1057,
        'Theobromine' => 1058,
        'Carotene, beta' => 1107,
        'Carotene, alpha' => 1108,
        'Cryptoxanthin, beta' => 1120,
        'Lycopene' => 1122,
        'Lutein + zeaxanthin' => 1123,
        'Thiamin' => 1165,
        'Riboflavin' => 1166,
        'Niacin' => 1167,
        'Choline, total' => 1180
    ]
];

?>