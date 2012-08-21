<?php

$i18n = array(
    'className' => 'Forum',
    'action_manageGroup_0' => 'Nouveau groupe',
    'action_manageGroup' => 'Modifier le groupe {name}',
    'action_subject' => 'Sujet : {name}',
        
    'share_settings_forum_active' => 'Activer le module',
    'share_settings_forum_active_explanations' => '<div>Ce module met en place un forum administrable sur votre site.<br />
        Il contient entre autres les fonctionnalités suivantes : arborescence complète des catégories et sujets, 
        fiches de profiles, modération, mots interdits, messagerie privée, etc.</div>',
    
    'manage_general_title' => 'Général',
    'manage_activateForum' => 'Activer le forum',
    'manage_forceUserToCheckConditions' => 'Obliger les utilisateurs à valider les conditions d\'utilisation du forum',
    'manage_forum_is_public' => 'Autoriser les utilsateurs non connéctés à consulter le forum',
    'manage_forum_is_public_explanations' => '<div>
        Si cette case n\'est pas cochée, en aucun cas une personne non connectée ne pourra
        accéder à aucun des sujets du forum.<br />
        Si elle l\'est, et si la personne qui gère une rubrique accepte les personnes non connectées,
        celles-ci pourront consulter le sujet.
        </div>',
    'manage_allow_anonymous' => 'Autoriser les utilsateurs non connéctés à poster sur le forum',
    'manage_allow_anonymous_explanations' => '<div>
        Si cette case n\'est pas cochée, en aucun cas une personne non connectée ne pourra
        écrire dans le forum.<br />
        Si elle l\'est, et si la personne qui gère une rubrique accepte les personnes non connectées,
        celles-ci pourront y écrire.
        </div>',
    'usersList_byType' => 'Utilisateurs par groupes',
    'usersList_search' => 'Recherche d\'utilsateurs',

    'new_section_editProfile' => 'Afin de pouvoir ajouter une catégorie au forum, vous devez remplir votre profil.<br />
        Vous pouvez le faire en suivant le lien suivant : <br />',
    'new_topic_editProfile' => 'Afin de pouvoir créer un nouveau sujet, vous devez remplir votre profil.<br />
        Vous pouvez le faire en suivant le lien suivant : <br />',
    'new_post_editProfile' => 'Afin de pouvoir répondre à un sujet, vous devez remplir votre profil.<br />
        Vous pouvez le faire en suivant le lien suivant : <br />',
    
    'captchaError_message' => 'Il y a une erreur dans le texte à recopier.',
    'noConnected_captcha' => 'Merci de recopier le contenu de l\'image ci-dessous dans le champs à sa droite.<br />
        Pour éviter cette formalité à chaque fois, veuillez vous connecter ou vous créer un compte.',
    
    'subCat' => 'Sous-catégories&#160;: ',
    'topics' => 'Sujets&#160;: ',
    'newCat' => 'Créer une nouvelle catégorie',
    'catName' => 'Nom de la catégorie&#160;: ',
    'catText' => 'Description de la catégorie&#160;: ',
    'catImage' => 'Image de la catégorie&#160;: ',
    'catType' => 'Cette catégorie contient&#160;: ',
    'catTopics' => ' des sujets',
    'catSubCats' => 'des sous-catégories',

    'moderate_topic_title' => 'Modération d\'un sujet du forum',
    'moderate_topic_action' => 'Vous souhaitez : ',
    'moderate_topic_action_delete' => 'Supprimer ce sujet',
    'moderate_topic_action_modify' => 'Modifier le titre ou le contenu de ce sujet',
    'topic_delete_success' => 'Le sujet a été supprimé avec succès.',
    'topic_moderate_success' => 'Le sujet a été modifié avec succès.',
    
    'move_topic' => 'Déplacer le sujet',
    'topic_move_title' => 'Changement de catégorie d\'un sujet',
    'move_topic_intro' => 'Sujet à déplacer : ',
    'move_topic_to' => 'Catégorie de destination : ',
    'move_topic_action' => 'Déplacer',
    'topic_moved_successfully' => 'Le sujet a été déplacé avec succès.',
    
    'moderate_post_title' => 'Modération d\'un message du forum',
    'moderate_post_action' => 'Vous souhaitez : ',
    'moderate_post_action_delete' => 'Supprimer ce message',
    'moderate_post_action_modify' => 'Modifier le titre ou le contenu de ce message',
    'post_delete_success' => 'Le message a été supprimé avec succès.',
    'post_moderate_success' => 'Le message a été modifié avec succès.',
    
    'answer' => 'réponse',
    'answers' => 'réponses',
    'newTopic' => 'Nouveau sujet',
    'title' => 'Titre&#160;: ',
    'text' => 'Texte&#160;: ',
    'moderate_topic' => 'Modérer',
    'alertModerator' => 'Signaler aux modérateurs',
    'alert_moderator_mail_title' => 'Demande manuelle de modération pour le forum du site ',
    'alert_auto_moderator_mail_title' => 'Demande automatique de modération pour le forum du site ',
    'forbiddenWordsFound_message' => 'Votre sujet contient des mots qui ne peuvent être acceptés.<br />
        Veuillez, s\'il vous plait, vous confirmer aux conditions générales d\'utilisation de ce forum.<br /><br />
        Sachez qu\'en cas d\'utilisation répétée de termes ne correspondant pas à ces conditions, vous pourrez
        être exclu temporairement ou définitivement de ce forum.',
    
    'newAnswer' => 'Nouvelle réponse',
    'answerTo' => 'Répondre au commentaire n°',
    'mainTopic' => 'Sujet principal',
    'postNumber' => 'Post n° ',
    'answer2' => 'Répondre',
    'accept_conditions' => 'J\'ai lu, je comprends, et j\'accepte les <a href="{conditions:file}">conditions d\'utilisation du forum</a>',
    'please_accept_conditions' => 'Vous devez prendre connaissance des <span class="bold">conditions 
        d\'utilisation du forum</span>, et le signaler en cochant la case correspondante en bas du formulaire.',
    'a_title_is_required' => 'Le champs <span class="bold">Titre</span> est obligatoire.',
    'a_content_is_required' => 'Le champs <span class="bold">Texte</span> est obligatoire.',
    
    'notify_me' => 'S\'abonner à ce sujet',
    'dont_notify_me' => 'Arrêter mon abonnement à ce sujet',
    
    'added_to_notified_automatically' => 'Vous avez été abonné(e) à ce sujet automatiquement, conformément à vos préférences.',
    'added_to_notified' => 'Vous êtes maintenant abonné(e) à ce sujet.',
    'removed_from_notified' => 'Vous n\'êtes maintenant plus abonné(e) à ce sujet.',
    'notif_mail_title' => '[SITE] - Nouveau post dans le sujet "[TOPIC]"',
    'notif_mail_content' => '
        Bonjour,<br /><br />
        Vous êtes abonné(e) au sujet "<a href="[TOPIC_LINK]">[TOPIC]</a>" sur le site 
        <a href="[SITE]">[SITE]</a>.<br />
        Un nouveau message vient d\'y être posté. Vous pouvez y accéder avec le lien suivant :<br />
        <a href="[TOPIC_LINK]#[POST_ID]">[TOPIC_LINK]#[POST_ID]</a>.<br />
        <br />
        Pour vous désabonner de ce sujet, il vous suffit d\'y accéder grace au lien ci-dessus, de vous connecter
        avec vos identifiants, et de cliquer sur l\'enveloppe barrée située en haut du sujet.<br />
        <br />
        <hr />
        Il est inutile de répondre à ce mail, celui-ci étant envoyé par un robot, votre réponse serait perdue.
        ',
    
    'noForumRights' => 'Vous ne pouvez pas participer à ce sujet.<br />
Si vous désirez participer aux discussions         
',
    'connectionLink' => 'connectez vous ou créez un compte',
    'alert_title' => 'Signalement d\'un contenu offensant',
    'alert_messageTitle' => 'Titre du message : ',
    'alert_intro' => 'Vous souhaitez signaler à nos modérateurs que le message suivant est offensant ou n\'est
            pas en accord avec les conditions d\'utilisation du forum.',
    'alert_messageTitle'=>'Titre du message : ',
    'alert_messageContent'=>'Contenu du message : ',
    'alert_explanation'=>'Merci de donner ci-dessous la raison de ce signalement :',
    
    'user_not_found_title' => 'Erreur',
    'user_not_found' => 'L\\\'alias saisi ne correspond à aucun alias du site.<br />\
    Veuillez vérifier votre saisie.',

    'administrate_section'=>'Droits des groupes d\'utilisateurs',
    'apply_those_rights'=>'Appliquer les droits suivants à cette catégorie : ',
    'apply_those_rights_help'=>'<div>
        Ces droits seront appliqués à cette catégorie toute entière.<br />
        Chaque droit octroyé l\'est aussi pour toutes les sous catégories et les sujets qu\'elle contient.<br />
        Réciproquement, si un droit est donné dans une catégorie supérieure dans l\'arborescence du forum,
        le groupe aura ce droit, que vous cochiez la case correspondante ou pas.<br />
        Certains droits ne peuvent être retirés (notamment pour les administrateurs), et certains autres,
        pour des raisons de sécurité, ne peuvent être donnés (notamment pour les utilisateurs n\'appartenant à
        aucun groupe).<br /><br />
        Pour gérer les groupes d\'utilisateurs, contactez l\'administrateur du site, qui pourra le faire
        depuis son panneau d\'administration.
</div>',
    
    'administrate'=>'Administrer',
    'administrate_help'=>'<div>
        Ce droit ouvre la possiblitité pour chacune des personnes du groupe de remplir un forumulaire comme
        celui-ci pour chacune des sous-catégories de celle que vous gérez actuellement.
</div>',
    
    'moderate' => 'Modérer',
    'moderate_help' => '
        <div>
    Ce droit autorise tous les membres de ce groupe à modérer les particiations 
    dans n\'importe quelle catégorie.
            <br />
    Modérer des commentaires peut consister en :
            <br />
            <ul>
                <li>Masquer des commentaires des utilisateurs (sauf modérateurs)</li>
                <li>Supprimer des commentaires des utilisateurs (sauf modérateurs)</li>
                <li>Modifier des commentaires des utilisateurs (sauf modérateurs)</li>
            </ul>
            <br />
    De plus, une personne ayant ce droit recevra les
    éventuelles notifications quand quelqu\'un signale qu\'un post ne suit pas 
    les conditions générales d\'utilisation du forum.
            <br />
    Attention, dans le cas d\'un forum très fréquenté, il est possible qu\'avoir
    ce droit envoie de nombreux mails quotidiens aux personnes concernées.
        </div>',
    'post' => 'Poster',
    'post_help' => '
                <div>
            Ce droit autorise tous les membres de ce groupe à poster des réponses à des sujets existants dans n\'importe
            quelle catégorie.
                </div>',
    'bannish' => 'Bannir des posteurs',
    'bannish_help' => '
                <div>
                    Ce droit autorise tous les membres de ce groupe à bannir n\'importe quel 
                    utilisateur, que ce soit au niveau d\'un sujet, d\'un thème, ou du forum entier.
                    <br />
                    Exception : Il n\'est pas possible de bannir quelqu\'un qui a le pouvoir de bannir.
                </div>',
    'createTopics' => 'Créer des sujets',
    'createTopics_help' => '
                <div>
            Ce droit autorise tous les membres de ce groupe à créer des sujets dans n\'importe quelle catégorie.
                </div>',
    'createCat' => 'Créer des catégories',
    'createCat_help' => '
                <div>
            Ce droit autorise tous les membres de ce groupe à créer des catégories principales
            (à la racine du forum), ainsi que des sous-catégories dans n\'importe quelle catégorie.
                    <br />
                    <br />
            Pour conserver un forum propre, il est déconseillé de donner ce droit aux groupes "Tout les utilsateurs ...".
            Sinon, le forum risque de ne plus être compréhensible, et ce, très rapidement.
                </div>',
    'showForum' => 'Affichage du forum',
    'showForum_help' => '
                <div>
                    Ce droit autorise tous les membres de ce groupe à afficher le forum, ainsi
                    que tous ses sujets.
                    <br />
                    Attention : Il suffit qu\'un seul des autre droits soit donné pour que celui-ci
                    le soit aussi.
                </div>
                <div>
                    Ce droit autorise tous les membres de ce groupe à afficher le forum, ainsi
                    que tous ses sujets.
                    <br />
                    Attention : Il suffit qu\'un seul des autre droits soit donné pour que celui-ci
                    le soit aussi.
                </div>',
    'defaultRights' => 'Les droits inscrits ici sont les droits par défaut.',
    'defaultRights_help' => '
                    <div>
                    Les droits inscrits ici sont des droits généraux.<br/>Ils s\'appliquent au forum tout entier.
                        <br />
                    Si vous souhaitez donner des droits spécifiques pour une catégorie de sujets, ou pour certains
                    certains sujets uniquement, rendez vous sur la page qui les affiche. Vous pourrez alors gérer
                    ces droits.
                        <br />
                        <br />
                    Certains droits ne peuvent être retirés, sans quoi le forum pourrait ne plus fonctionner, et
                    certains autres ne peut être donnés, sans quoi le forum pourrait être utilisé à de mauvaises fins
                    par des utilisateurs mal intentionnés.
                    </div>',
    'adminTitle' => 'Administrateurs',
    'moderatorsTitle' => 'Modérateurs',
    'allUsersTitle' => 'Tous les utilisateurs connectés',
    'allUsersUnconnectedTitle' => 'Tous les utilisateurs non connectés',
    
    'mailsNotifs' => 'Emails auxquels les notifications de contenus inappropriés seront envoyées ',
    'mailsNotifs_help' => 'Entrez une adresse par ligne.',
    'forbidWords' => 'Mots interdits ',
    'forbidWords_help' => '<div>
                    Entrez ici la liste des mots qui ne pourront pas être utilisés sur le forum.
                    <br />
                    Vous devez passer à la ligne entre chaque mot/groupe de mots.<br /><br />
                    Les caractères spéciaux seront remplacés par leurs équivalents non spéciaux (à→a).<br /><br />
                    Vous pouvez de plus ajouter une étoile (*) à la fin des mots, pour signaler que quelle que 
                    soit la terminaison, le mot doit appartenir à cette liste (ex: arnaqu* marchera pour "arnaque",
                    "arnaques", "arnaquent", etc...).
                    </div>',
    'moderateWords' => 'Mots envoyant automatiquement une notification aux modérateurs',
    'moderateWords_help' => '
                    Ces mots sont des mots pouvant être interprétés de différentes manières qui ne sont pas 
                    toutes interdites ou toutes autorisées. 
                    <br />
                    Exemple, le mot "pédale" qui peut être soit innocent, soit insultant.
                    <br />
                    <br />
                    Ils ne sont donc pas interdits, mais une notification est envoyée aux modérateurs, afin
                    qu\'ils puissent retirer le message au plus tôt si besoin.<br />
                    Vous devez passer à la ligne entre chaque mot/groupe de mots.<br /><br />
                    Les caractères spéciaux seront remplacés par leurs équivalents non spéciaux (à→a).<br /><br />
                    Vous pouvez de plus ajouter une étoile (*) à la fin des mots, pour signaler que quelle que 
                    soit la terminaison, le mot doit appartenir à cette liste (ex: arnaqu* marchera pour "arnaque",
                    "arnaques", "arnaquent", etc...).
                    ',
    'usersGroups' => 'Groupes d\'utilisateurs :',
    'newGroup' => 'Nouveau groupe',
    'searchUsersTitle' => 'Rechercher les utilisateurs remplissant les conditions suivantes :',
    'searchUsersNameTitle' => 'Leurs noms contiennent : ',
    'searchUsersLastNameTitle' => 'Leurs prénoms contiennent : ',
    'searchUsersOpenTitle' => ' ayant ouvert au moins ',
    'searchUsersAnswerTitle' => ' ayant répondu à au moins ',
    'ofTopics' => ' sujets',

    'addGroup_title' => 'Nouveau groupe d\\\'utilisateurs',
    'addGroup_name' => 'Entrez le nom du nouveau groupe d\\\'utilisateurs :',
    'addGroup_default' => 'Nouveau groupe',
    
    'search_forumTitle' => 'Forum',

    'message_title' => 'Message privé',
    'message_should_be_connected_title' => 'Vous devez être connectés!',
    'message_should_be_connected' => '<br /><a href="/user/connect.php">Connectez-vous</a>,<br /><br />
    Ou <a href="/user/createAccount.php">Créez un compte</a>.',
    'message_should_be_connected_back' => 'Retourner à la page précédente',
    'message_titles' => 'Message privé de ',
    'message_intros' => 'Cette personne vous a laissé le message privé suivant : <br /><hr />',
    'message_answer' => '<hr /><br />Vous pouvez lui répondre en suivant le lien suivant : <br />',
    
    'profile_myprofile_title' => 'Mon profil',
    'profile_someprofile_title' => 'Profil de l\'utilisateur [ALIAS]',
    
    'lastTopics_title' => 'Les [COUNT] derniers sujets',
    'knowMore' => 'Accéder',
    'modifyThisCat' => 'Modifier cette catégorie',
);
