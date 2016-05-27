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

        'public_path' => 'public/',
        'webSiteTitle' => 'VisuLignes',
        'emailContactGala' => 'contact@xxx',


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
            //     ['level' => 1, 'name' => 'Membre', 'slug' => 'member'],
            //     ['level' => 0, 'name' => 'Non inscrit', 'slug' => 'non-inscrit']
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
            //     ]
            ]
        ]

    ]
];
