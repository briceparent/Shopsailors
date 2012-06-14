<?php
$i18n = array(
    'className' => 'Pages de contenus et listes',

    'rights_className' => 'Contenus et listes',
    'rights_show_all'=>'Affichage de toutes les pages de contenus',
    'rights_show_one'=>'Affichage de la page "[PAGE_NAME]"',
    'rights_shortList_all'=>'Affichage de toutes les listes de pages',
    'rights_shortList_one'=>'Affichage de la liste de pages "[PAGE_NAME]"',
    'rights_edit_all'=>'Modification de toutes les pages de contenus',
    'rights_edit_one'=>'Edition de la page "[PAGE_NAME]"',
    'rights_editShortList_all'=>'Modification de toutes les listes de pages',
    'rights_editShortList_one'=>'Edition de la liste de pages "[PAGE_NAME]"',
    
    

    'display' => 'Affichage : ',

    'title' => 'Titre de la page : ',
    'showtitle' => 'Afficher le titre',
    'showdate' => 'Afficher la date',
    'content' => 'Contenu de la page : ',
    'newAndNotActive' => 'La page a bien été enregistrée.<br />
Etant donné que son contenu n\'a pas été activé, vous n\'avez pas la possibilité
de l\'afficher directement. Vous ne pouvez que le modifier.<br />
Pour cela, accédez à la page "Tous les articles", et faites "modifier", ou suivez
le lien suivant :',
    'activated' => 'Activer le contenu ',
    'activated_descrition' => 'Un contenu non actif n\'est pas consultable par le public.',
    'isNews' => 'Cet article est une actualité',
    'isNews_explanations' => '<div>
        Si cet case est cochée, cet article sera automatiquement ajouté à la liste des actualités.<br />
        La date de l\'actualité est les date de l\'article.<br /><br />
        Dans la liste des actualités, c\'est la description ci-dessous qui sera affichée, avec un lien
        vers l\'article.
        </div>',
    'contentImage' => 'Image :',
    'contentImage_explanation' => '<div>
Vous pouvez ajouter une image pour cet article. Celle-ci sera utilisée entre autre dans les listes d\'articles. Si vous laissez 
l\'image par défaut, celle-ci ne sera tout simplement pas affichée dans ces pages.
</div>',
    'summary' => 'Description :',
    'summary_explanation' => '<div>
Entrez ici une description courte qui servira dans les listes d\'articles.</div>',
    'save' => 'Enregistrer',
    'action_show'=>'Article n°{id} : "{title}"',
    'edit_this_page'=>'Modifier cet article',
    'new_page_title'=>'Nouvel article',
    'editBoxTitle'=>'Paramètres',
    'showMethod'=>'lire',
    'show'=>'Afficher',
    'edit'=>'Modifier',
    'delete'=>'Supprimer',
    'previousVersions_title'=>'Versions précédentes',
    'previousVersions_action' => 'Afficher la version de ',
    'previousVersions_action_caution_title' => 'Attention!',
    'previousVersions_action_caution' => 'Les modifications que vous avez pu apporter seront perdues!',
    'previousVersions_restorate' => 'Afficher',
    'list_description' => 'Tous les articles du site.',
    'inactiveList' => 'Liste des articles inactifs.',
    'activeList' => 'Liste des articles actifs.',
    'inactiveList_description' => 'Pour les activer, cliquez sur "<RENDER_VALUE what="i18n>edit"/>",
    puis cochez la case "<RENDER_VALUE what="i18n>activated"/>" et appuyez sur le bouton
"<RENDER_VALUE what="i18n>save"/>"',
    'contentListEditor_generalTitle' => 'Paramètres',
    'contentListEditor_contentTitle' => 'Contenus',
    'contentlisteditor_list' => 'Cliquez sur les articles à afficher dans cette liste',
    'contentlisteditor_confirm'=>'Des modifications ont été apportées. \nSi vous cliquez sur OK, elle seront perdues...\nPour ne pas les perdre, cliquez sur Annuler, validez le formulaire (en bas), puis recliquez sur le lien.',
    'contentlisteditor_listsTitle' => 'Listes disponibles',
    'avalaible_contents' => 'Liste des articles disponibles :',
    'listName' => 'Nom de la liste: ',
    'listIntro' => 'Texte d\'introduction de la liste: ',
    'listIntro_explanation' => '<div>
    Ce champ est facultatif. Il vous permet si vous le désirez de décrire brievement les articles composants la liste 
    comme les thème abordés, etc... Cela vous permet d\'interpeller le visiteur, lui donner envie de visiter vos différents articles. 
    Vous pouvez, ici, appliquer quelques styles à votre texte (gras, italique) ou encore créer des liens pointant vers d\'autres pages du site 
    voir vers un site externe.
    </div>',
    'newShortList_title' => 'Nouvelle liste',
    'newShortList' => 'Créer une nouvelle liste',
    'action_editShortList'=>'Liste d\'articles n°{id} : "{title}"',
    'shortList_enableSubMenus' => 'Autoriser les sous menus',
    'shortList_enableSubMenus_explanations' => '<div>
        Si cette liste d\'articles est placée dans un menu, vous pouvez choisir d\'activer ou pas la génération de sous
        menus.<br />
        Pour avoir des sous menus, il vous suffit de cocher cette case. Une entrée dans le sous-menu sera ajoutée pour
        chacun des articles.
</div>',
    'action_shortList'=>'Liste d\'articles n°{id} : "{title}"',
    'contentlisteditor_deleteTitle'=>'Supprimer la liste',
    'contentlisteditor_deleteContent' => '
Pour éviter d\'avoir des liens qui pointent vers une page qui n\'existe
plus, il faut vérifier si des liens vers cette page ne sont pas présents dans :
<ul>
<li>les liens du menu</li>
<li>les liens dans d\'autres listes d\'articles</li>
<li>les éventuels liens que vous auriez pu insérer manuellement à l\'intérieur
d\'autres articles</li>
</ul>
Dans tous les cas, si des utilisateurs de votre site ont enregistré l\'adresse
de cette liste d\'articles, par exemple dans leurs favoris, s\'ils essaient à nouveau d\'y
accéder, une page signalant que la page recherchée n\'existe pas ou plus sera
affichée.',

    'search_contentsTitle' => 'Pages de contenus',

    'action_delete' => 'Supprimer',

    'deletePage_title' => 'Suppression d\\\'un article',
    'delete_alert' => 'Vous êtes sur le point de supprimer l\'article suivant : ',
    'delete_dateInfo' => 'Cet article date du&#160;: ',
    'deletePage_isActive' => 'Cet article est actif, c\'est à dire qu\'il est
accessible au public actuellement.<br />
Ainsi, pour éviter d\'avoir des liens qui pointent vers une page qui n\'existe
plus, il faut vérifier si des liens vers cette page ne sont pas présents dans :
<ul>
<li>les liens du menu</li>
<li>les liens dans les listes d\'articles</li>
<li>les éventuels liens que vous auriez pu insérer manuellement à l\'intérieur
d\'autres articles</li>
</ul>
Dans tous les cas, si des utilisateurs de votre site ont enregistré l\'adresse
de cette page, par exemple dans leurs favoris, s\'ils essaient à nouveau d\'y
accéder, une page signalant que la page recherchée n\'existe pas ou plus sera
affichée.',

    'seo_title' => 'Référencement',
    'seo_explanations' => 'Pour améliorer votre référencement, vous pouvez entrer ici
        des informations concernant le contenu (mots clés).',
    'seo_titleBar' => 'Titre de l\'onglet du navigateur : ',
    'seo_titleBar_explanations' => 'Si ce champs est vide, le titre de la page sera utilisé.',
    'seo_metaDescription' => 'Méta description : ',
    'seo_metaDescription_explanations' => 'Si ce champs est vide, la description de la page sera utilisée.',
    'seo_titleBar_listName_explanations' => 'Si ce champs est vide, le nom de la liste sera utilisé.',
    'seo_metaDescription_introText_explanations' => 'Si ce champs est vide, le texte d\'introduction de la page sera utilisée.',

    'facebook_shortLists' => 'Listes d\'articles',
    'facebook_articles' => 'Articles',

    'newsPage_title' => 'Fil d\'actualités',
    'publicationDate' => 'Publié le&#160;',
    'news_edit_boxTitle' => 'Edition du fil d\'actualité',
    'news_edit_title' => 'Titre du fil d\'actualité : ',
    'news_edit_intro' => 'Introduction du fil d\'actualité : ',
    'news_edit_numberByPage' => 'Nombre d\'actualités affichées par page : ',
    
    'readMore' => 'Lire la suite',
);
