<?php
// Exemple de ce qui est attendu dans votre fichier de configuration conf.php
return [
    'settings' => [
        'displayErrorDetails' => true,
        'addContentLengthHeader' => false,
        'displayErrorDetails' => true,

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/templates/'
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/logs/app.log'
        ],

        'public_url' => '/slimAuthApp/public/',
        'public_path' => 'public/',
        'webSiteTitle' => 'SlimAuthApp',

        'confSQL' => [
            'sql_host' => "localhost",
            'sql_db'   => "database",
            'sql_user' => "user",
            'sql_pass' => "pswd"
        ],

        'Auth' => [
            'canEditUserId' => false,
            'id' => 'monNomDeSite',
            'casUrl' => 'https://cas.icam.fr/cas/',
            'addUserFromCasIfNotExists' => true,
            'extensionMailIfNotExists' => 'icam.fr',
            'userActiveIfNotExists' => false,
            'ldapUrl' => 'biximontreal.net',
            'sourceConfig' => 'database', // database or file
            // Pour la configuration avec une base de données, allez voir /db_creation/db_model.sql et /db_creation/db_value_sample.sql
            'allUserRole' => 'member',
            // 'roles' => [
            //     ['id' => 1, 'name' => 'Super administrateur', 'slug' => 'superadmin'],
            //     ['id' => 2, 'name' => 'Tout utilisateur', 'slug' => 'member'],
            //     ['id' => 3, 'name' => 'Opérations', 'slug' => 'operations']
            // ],
            // 'permissions' => [
            //     'forRole' => [ // droits spécifiques pour un groupe d'utilisateurs donné
            //         'member' => [
            //             'allowed' => [
            //                 '/',
            //                 'login',
            //                 'logout',
            //                 'about',
            //                 'account'
            //             ],
            //             'not_allowed' => []
            //         ],
            //         'operations' => [
            //             'allowed' => [
            //                 'operations/vue_operations',
            //                 'operations/vuePersoOperations'
            //             ],
            //             'not_allowed' => []
            //         ]
            //         // ...
            //     ],
            //     'forUser' => [ // droits spécifiques à un utilisateur donné
            //         'user2@entreprise' => [
            //             'allowed' => [
            //                 'operations/vue_operations'
            //             ],
            //             'not_allowed' => []
            //         ]
            //         // ...
            //     ]
            // ],
            // 'users' => [
            //     [
            //         'email' => 'antoine.giraud@2015.icam.fr',
            //         'password' => '$2y$10$vF8C9hwamm2/srzgsjI2NOlBk1zHo39g/RGLL.gcum7hB5/s5I9tq',
            //         'last_name' => 'Giraud',
            //         'first_name' => 'Antoine',
            //         'online' => 1,
            //         'roles' => ['superadmin']
            //     ],
            //     [
            //         'email' => 'user1@operations',
            //         'password' => 'motdepasse',
            //         'last_name' => 'User',
            //         'first_name' => 'opérations #1',
            //         'online' => 1,
            //         'roles' => ['operations']
            //     ],
            //     [
            //         'email' => 'user2@entreprise',
            //         'password' => 'motdepasse',
            //         'last_name' => 'User',
            //         'first_name' => 'normal #2',
            //         'online' => 1,
            //         'roles' => []
            //     ],
            //     [
            //         'email' => 'horsligne@entreprise',
            //         'password' => 'motdepasse',
            //         'last_name' => 'User',
            //         'first_name' => 'horsligne',
            //         'online' => 0,
            //         'roles' => []
            //     ]
            // ]
        ]

    ] // end ['settings']
];
