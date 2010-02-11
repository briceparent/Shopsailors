<?php
$i18n = array(
    'debug_Title' => 'Débogage du code',
    'debug_Activated' => 'Activer le débogage',
    'debug_path'=> 'Dossier de vérification',
    'debug_path_explanation'=> '
Ce champs vous permet de définir un test vérifiant que le serveur <br />
sur lequel vous fonctionnez est bien celui que vous voulez tester.<br /><br />
Cela vous permet ainsi d\'utiliser exactement les mêmes données sur<br />
un serveur de développement et sur un serveur de production, en <br />
ayant comme unique différence le chemin d\'installation.<br /><br />
Le test est une vérification de l\'existence du dossier du champs <br />
suivant.<br /><br />
Si vous ne souhaitez pas utiliser ce filtre, entrez ici le nom d\'un<br />
dossier qui existe toujours, comme "/var" sur les systemes UNIX.',
    'codeCoverage_activated' => 'Activer la couverture du code',
    'codeCoverage_activated_explanation'=>'
La couverture du code permet d\'enregistrer les numéros de lignes<br />
éxecutées. Cela permet donc de trouver les morceaux de codes inutilisés.<br /><br />
Une fois cette option activée, il faut utiliser toutes les fonctionnalités<br />
de la classe à nettoyer.<br />
Ensuite, il vous suffit d\'aller dans le sous-dossier debugFiles/codeCoverage<br />
de la classe sh_dev, et d\'ouvrir le fichier portant le nom de celui que vous<br />
testez.<br /><br />
Il contient l\'ensemble des numéros de lignes utilisées depuis l\'activation<br />
de la fonction.<br /><br />
Ainsi, si des modifications importantes sont apportées au fichier testé,<br />
il faut penser à supprimer tous les fichier de debugFiles/codeCoverage<br />
pour éviter que les résultats ne soient faussés.
',
    'trace_activated'=>'Activer la trace de l\'execution',
    'trace_activated_explanation'=>'
Le fait de tracer l\'execution créera un fichier de trace pour chaque <br />
fichier appelé (page html, fichier css, image, script, etc...).<br /><br />
Ces fichiers de trace vous permettront de connaitre le chemin parcouru <br />
lors de l\'exécution, de connaitre le temps passé à chaque étape, et de <br />
connaître la quantité de mémoire utilisée.<br /><br />
Attention: lors de l\'affichage d\'une page html, de multiples fichier sont<br />
appelés, ce qui fait que de multiples fichiers de trace sont créés.<br />
Il convient donc de n\'activer cette fonction que si vous le souhaitez<br />
vraiment.',
    'errors_activated'=>'Activer l\'affichage des erreurs suivantes:',
    'E_ALL_activated'=>'Erreurs de type "E_ALL" (si activé, les erreurs cochées suivantes
seront désactivées)',
    'E_STRICT_activated'=>'Erreurs de type E_STRICT',
    'E_WARNING_activated'=>'Erreurs de type E_WARNING',
    'E_NOTICE_activated'=>'Erreurs de types E_NOTICE',


    'noFileSelected'=>'Ancun fichier n\\\'est sélectionné!',
    'fileChangeDescription' => '',
    'globalChangeDescription' => 'Description globale des modifications',
    'futureRevisionNumber' => 'Numéro de la prochaine révision : ',
    'noGlobalChangeDescription' => 'Vous devez entrer une description globale',
    'noFileDescription' => 'Certaines modifications n\'ont pas de description. Voulez-vous continuer tout de même?',
    'noChanges' => 'Aucun fichier n\'a été modifié, créé ou supprimé depuis la
révision actuelle (<RENDER_VALUE what="revisionNumber>actual"/>).',
);
