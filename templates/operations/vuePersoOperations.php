<h1 class="page-header">Vue perso opearations</h1>
<p>
	On a rendu l'accès à cette page possible pour :
	<ul>
		<li>les utilisateurs du groupe <code>opearations</code></li>
		<li>l'utilisateur <code>user2@entreprise</code> aussi</li>
	</ul>
</p>
<pre>
'permissions' => [
    'forRole' => [
        'opearations' => [
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