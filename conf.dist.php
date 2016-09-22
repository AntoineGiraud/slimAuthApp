<?php
// Exemple de ce qui est attendu dans votre fichier de configuration conf.php
return [
    'settings' => [
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

        'public_url' => '/simpleSlimAppWithAuthentification/public/',
        'public_path' => 'public/',
        'webSiteTitle' => 'SlimAuthApp',

        'confSQL' => [
            'sql_host' => "localhost",
            'sql_db'   => "visulignes",
            'sql_user' => "root",
            'sql_pass' => ""
        ],

        'Auth' => [
            'casUrl' => 'https://cas.icam.fr/cas/',
            // Si vous voulez utiliser une configuration locale des roles & users, décommentez roles & users. Sinon, vous devez avoir en base de données les tables équivalentes ! confer db_creation !
            // 'roles' => [
            //     ['level' => 2, 'name' => 'Administrateur', 'slug' => 'admin'],
            //     ['level' => 1, 'name' => 'Oparation', 'slug' => 'oparation'],
            //     ['level' => 1, 'name' => 'Membre', 'slug' => 'member'],
            //     ['level' => 0, 'name' => 'Non inscrit', 'slug' => 'non-inscrit']
            // ],
            // 'permissions' => [
            //     'forRole' => [ // droits spécifiques pour un groupe d'utilisateurs donné
            //         'allUsers' => [
            //             'home',
            //             'login',
            //             'logout',
            //             'about',
            //             'account'
            //         ],
            //         'opearation' => [
            //             'operations/vue_operations',
            //             'operations/vuePersoOperations'
            //         ]
            //         // ...
            //     ],
            //     'forUser' => [ // droits spécifiques pour un utilisateur donné
            //         'user2@entreprise' => [
            //             'operations/vuePersoOperations'
            //         ]
            //         // ...
            //     ]
            // ],
            // 'users' => [
            //     [
            //         'email' => 'antoine.giraud@xxx',
            //         'password' => 'xxx',
            //         'nom' => 'Giraud',
            //         'prenom' => 'Antoine',
            //         'online' => '1',
            //         'name' => 'Administrateur',
            //         'slug' => 'admin',
            //         'level' => '2'
            //     ],
            //     [
            //         'email' => 'user1@oparation',
            //         'password' => 'xxx',
            //         'nom' => 'User',
            //         'prenom' => 'opérations #1',
            //         'online' => '1',
            //         'name' => 'Oparation',
            //         'slug' => 'oparation',
            //         'level' => '1'
            //     ],
            //     [
            //         'email' => 'user2@entreprise',
            //         'password' => 'xxx',
            //         'nom' => 'User',
            //         'prenom' => 'normal #2',
            //         'online' => '1',
            //         'name' => 'Membre',
            //         'slug' => 'member',
            //         'level' => '1'
            //     ]
            // ]
        ]

    ]
];
