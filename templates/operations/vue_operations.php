<h1 class="page-header">Vue pour tous les oparations</h1>
<p>Tout utilisateur faisant parti du group des oparations peut voir cette page</p>
<p>l'usager <code>user2@entreprise</code> n'a pas accès à cette page</p>
<pre>
'allowedRoutes' => [
    'forRole' => [
        'oparation' => [
            'vue_operations',
            'vuePersoOperations'
        ]
    ],
    'forUser' => [
        'user2@entreprise' => [
            'vuePersoOperations'
        ]
    ]
],
</pre>
<p>Les administrateurs ont accès à cette page</p>