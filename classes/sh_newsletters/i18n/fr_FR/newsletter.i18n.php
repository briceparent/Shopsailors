<?php
$i18n = array(
    'className' => 'Newsletters',
    'action_show' => 'Newsletter n°{id} : "{title}"',
    'action_showList' => 'Liste des newsletters',
    'action_subscribe' => 'Inscription à la newsletter',

    'newsletters_sent' => 'Newsletters déjà envoyées : ',
    'newsletters_planned' => 'Newsletters dont l\'envoi est planifié : ',
    'newsletters_notPlanned' => 'Newsletters dont l\'envoi n\'est pas encore planifié : ',
    
    'nl_dateTitle' => 'Newsletter du&#160;',

    'monthAndYear' => '<RENDER_VALUE what="i18n>month_{monthes>month}"/>&#160;
<RENDER_VALUE what="monthes>year"/>',
    'month_01' => 'Janvier',
    'month_02' => 'Février',
    'month_03' => 'Mars',
    'month_04' => 'Avril',
    'month_05' => 'Mai',
    'month_06' => 'Juin',
    'month_07' => 'Juillet',
    'month_08' => 'Août',
    'month_09' => 'Septembre',
    'month_10' => 'Octobre',
    'month_11' => 'Novembre',
    'month_12' => 'Décembre',
    'news_suscribe' => 'Pour recevoir notre newsletter,&#160;',
    'news_unsuscribe' => 'Pour vous désabonner de notre newsletter,&#160;',
    'news_backToList' => 'Retour à la liste des newsletters',
    'subscribe_here' => 'inscrivez vous ici',
    'unsubscribe_here' => 'suivez ce lien',
    'newsList' => 'Liste des newsletters',
    'chooseList' => 'Choisissez une ou plusieurs listes de diffusion&#160;:',
    'autoChoosenList' => 'Vous êtes sur le point de vous abonner à la liste de diffusion suivante&#160;:',
    'noneSent' => 'Aucune newsletter n\'a été envoyée pour le moment...',


    'test_subjectBeginning' => 'Envoi test - ',
    'test_sending' => 'Envoi du test...<br /> Merci de patienter quelques instants.',
    'test_sentSuccessfully' => 'Mail de test envoyé avec succès.',
    'test_intro' => '<div style="text-align:center;font-size:small;">Ceci est un test de newsletter.<br />
Un lien "Si cette newsletter ne s\'affiche pas correctement, cliquez ici." apparaitra ici.</div>',
    'test_outro' => '<div style="text-align:center;font-size:small;">
Les mentions légales, ainsi que celles de la société se chargeant de l\'envoi,
apparaîtront ici.
</div>',

    'editNewsletter_title' => 'Modifier une newsletter',
    'createNewsletter_title' => 'Créer une newsletter',
    'editNewsletter_params' => 'Paramètres de la newsletter',
    'newsletterTitle' => 'Titre de la newsletter :',
    'newsletterTitle_explanations' => '
<div>Entrez ici le titre de la newsletter.<br />
Celui-ci sera utilisé comme sujet du mail, ainsi que comme titre de la 
page d\'affichage de la newsletter sur le site.</div>',
    'newsletterType' => 'Choix de la liste de diffusion de la newsletter : ',
    'select1AtLeast' => 'Vous devez choisir au moins une liste de diffusion pour cette newsletter.',
    'createNewsletter' => 'Créer la newsletter',
    'newsletterDate' => 'Date d\'envoi :',
    'newsletterDate_explanations' => '<div>
Vous devez choisir une date d\'envoi dans le mois qui vient.<br /><br />
La newsletter commencera à être envoyée à partir de 0h, le jour choisi.
Cet envoi se poursuivra jusqu\'à l\'envoi de cette newsletter à chacun
des destinataires.<br /><br />
Si vous planifiez cette newsletter pour plus tard, vous pourrez revenir
éditer aussi bien son titre ou son contenu que sa date d\'envoi, et ce
jusqu\'à l\'envoi aux premiers destinataires.</div>',
    'editNewsletter_contentTitle' => 'Contenu de la newsletter',
    'editNewsletter_content' => 'Contenu de la newsletter',
    'test_newsletter' => 'Tester',
    'titleIsEmpty' => 'Vous devez fournir un titre à la newsletter. Celui-ci apparaîtra en sujet du mail.',
    'noNewsletterToList' => 'Vous n\'avez envoyé aucune newsletter pour
l\'instant, ou celles-ci ont été supprimées.',
    'sendNewsletter' => 'Autoriser l\'envoi',
    'sendNewsletter_explanations' => '<div>
Pour qu\'à la date choisie au-dessus, la newsletter soit envoyée, il faut que
cette case soit cochée.<br />
Ainsi, vous pouvez commencer à écrire la newsletter sans savoir encore à quelle
date vous souhaitez l\'envoyer.
</div>',


    'newsletter_hasBeenSent' => 'Cette newsletter a été envoyée avec succès.',
    'newsletter_hasBeenSent_date' => 'Date de l\'envoi : ',
    'newsletter_hasBeenSent_dest' => 'Liste de destinataires ayant reçu la newsletter : ',

    'newsletter_list' => 'Newsletters non affichées',
    'newsletters_notAlreadySent' => 'Newsletters non envoyées :',
    'newsletters_alreadySent' => 'Newsletters déjà envoyées mais masquées :',

    'manage_title' => 'Paramètres généraux',
    'manage_activate' => 'Activer le module',

    'editSubscribePage_title' => 'Page d\'inscription',
    'editSubscribePage_intro' => 'Texte d\'introduction à l\'inscription : ',
    'editSubscribePage_intro_explanations' => '<div>
Ce texte sera affiché en haut de la page sur laquelle les utilisateurs pourront
s\'inscrire à vos newsletters.<br /><br />
Vous pouvez donc par exemple y entrer le texte suivant :<br />
<textarea style="width:380px;height:110px;">
L\'inscription à notre newsletter est gratuite, et vous pouvez vous en désabonner quand vous le souhaitez.
En aucun cas nous ne vendons ou donnons les adresses de nos abonnés à des tiers.

Nous vous remercions pour votre interêt.
</textarea><br />
Si vous ne souhaitez pas afficher de texte d\'introduction, laissez tout simplement
le champs vide (auquel cas, vérifiez bien qu\'il soit vide dans chacune des langues
que vous proposez sur votre site).
</div>',

    'editNewslettersList_title' => 'Liste des newsletters',
    'pageTitle' => 'Grand titre de page :',
    'subscribPageTitle_explanations' => '<div>Il sagit du titre de la page sur laquelle vos visiteurs s\'inscriront à votre newsletter.</div>',
    'NL_PageTitle_explanations' => '<div>Il sagit du titre de la page sur laquelle vos visiteurs pourront lire les newsletters déjà envoyées.</div>',
    'showTitle' => 'Afficher le titre de la page',
    'showIntro' => 'Afficher l\'introduction de la page',
    'edit_newsList_intro' => 'Texte de présentation des newsletter :',
    'edit_newsList_intro_explanation' => '<div>Vous pouvez résumer en quelques mots l\'interêt qu\'offrent vos news, cela incitera le visiteur à les consulter, voir à s\'inscrire.</div>',
    
    'editLink' => 'Modifier',
    'showLink' => 'Afficher',
    'deleteLink' => 'Supprimer',

    'error_nothingSelected' => 'Vous n\'avez selectionné aucune newsletter.',
    'error_mail' => 'L\'adresse email n\'est pas valide.',
    'error_captchaError' => 'Le texte de vérification a mal été recopié.',

    'unsubscription_title'=>'Newsletter - Désinscription',
    'unsubscription_inputMail'=>'Entrez ici l\'adresse email que vous souhaitez
désinscrire : ',
    'unsubscription_selectML_intro'=>'Choisissez la ou les newsletters desquelles
vous souhaitez désabonner l\'adresse <span style="font-weight:bold"><RENDER_VALUE what="mail>value"/></span> :',
    'unsubscription_intro'=>'Cette page vous permet de vous désabonner des
newsletters de notre site.<br />
Sachez qu\'une fois votre désinscription effectuée, nos services ne vous enverront
plus aucun mail, à moins que vous ne vous inscriviez à nouveau.<br />
Votre adresse email sera donc supprimée de notre base de données.',
    'error_noSelection'=>'Vous n\'avez pas séctionné les newsletters desquelles
vous souhaitez vous désabonner.<br />
Veuillez donc les sélectionner et valider le formulaire à nouveau.',
    'error_noSubscription'=>'L\'adresse email que vous avez entrée n\'est abonnée
à aucune de nos listes de diffusion.<br /> Peut-être n\'avez vous pas entré
l\'adresse avec laquelle vous vous êtes abonné.',
    'unsubscription_successfull'=>'Votre désinscription a été effectuée avec succès.<br />
Vous ne recevrez plus cette/ces newsletters.',

    'newsSubscribe_title' => 'Inscription à la newsletter',
    'inputName' => 'Nom :',
    'inputFirstName' => 'Prénom :',
    'inputMail' => 'Entrez ici votre adresse mail :',

    'subscription_successfull' => '
Vous avez été inscrit avec succès!<br />
Vous recevrez un mail de confirmation dans les minutes qui viennent, il vous
suffira alors de suivre le lien qu\'il contient pour que votre abonnement soit
effectif.<br /><br />
Sachez que vous pourrez vous désabonner à tout moment en suivant le lien présent
en bas de chaque envoi.',
    
    'thereWasSomeChanges' => 'Des modifications ont été apportées à la liste de diffusion.\nSi vous souhaitez les enregistrer, cliquez sur Annuler et enregistrez le formulaire. Sinon, cliquez sur OK',

    'removeList_title'=>'Suppression d\'une liste de diffusion',
    'confirmDeletion'=>'Supprimer',
    'removingTheList'=>'Vous êtes sur le point de supprimer la liste de diffusion suivante&#160;:',
    'removingTheMails'=>'
Si vous ne l\'avez pas déjà fait, avant de supprimer la liste, vous pouvez 
envoyer une dernière newsletter pour signaler aux utilisateurs que ce service 
s\'arrête.<br /><br />
Toutes les adresses email de cette liste seront effacées.<br />
Si vous êtes sûr de vouloir supprimer cette liste de diffusion, il suffit de
cliquer sur le bouton "<RENDER_VALUE what="i18n>confirmDeletion"/>" ci-dessous.',
    
    'nl_newDiffusionList' => 'Nouvelle liste de diffusion',
    'newDiffusionListLink' => 'Nouvelle liste de diffusion',
    'nl_editDiffusionList' => 'Modifier la liste de diffusion',
    'nl_diffusionList_name' => 'Nom de la liste :',
    'nl_diffusionList_desc' => 'Description courte de la liste :',
    'nl_diffusionList_explanation' => '<div>
Lorsqu\'un internaute décidera de s\'abonner à votre newsletter il aura la 
possibilité de choisir une liste de diffusion que vous aurez préalablement 
déterminée. Ainsi si vous créez plusieurs listes, vous pourrez, lors de la 
création d\'une newsletter, choisir à quel type d\'abonnés vous souhaitez 
l\'envoyer.<br />
Notez que vous disposez d\'une liste par défaut.<br />
<br />
La nécessité de création d\'une nouvelle liste dépend uniquement du fait 
de la diversité de vos abonnés (centres d\'interêt, langue pratiquée, etc...).
Si certains de vos abonnés sont présents sur plusieurs de vos listes, ils ne 
recevront qu\'une fois la newsletter.</div>',
    'nl_diffusionList_desc_explanation' => '<div>
Vous indiquerez ici par exemple si les abonnés de la liste reçoivent une
newsletter chaque mois, chaque semaines, etc, ou encore si la liste est
anglophone, francophone, etc</div>.',
    'nl_diffusionList' => 'Listes existantes',

    'confirmSubscriptionMail_title' => 'Validation de votre inscription à la newsletter',
    'confirm_subscription_validated' => '
Votre inscription à la newsletter a bien été prise en compte.<br />
Nous vous remercions pour l\'intérêt que vous nous témoignez.<br /><br />
Cordialement,<br />
L\'équipe de <RENDER_VALUE what="site>base"/>',
    'confirm_subscription_alreadyValidated' => '
Votre inscription à la newsletter a déjà été validée.<br />
Nous vous remercions pour l\'intérêt que vous nous témoignez.<br /><br />
Cordialement,<br />
L\'équipe de <RENDER_VALUE what="site>base"/>',
    'confirm_subscription_dateOver' => '
La date limite pour valider votre inscription à notre newsletter est passée.<br />
Pour vous inscrire, vous devez remplir à nouveau le formulaire d\'inscription
disponible sur <a href="{links>subscribe}">cette page</a>.<br /><br />
Cordialement,<br />
L\'équipe de <RENDER_VALUE what="site>base"/>',
    'confirm_subscription_error' => '
Nous n\'avons pas pu trouver votre demande d\'inscription à la newsletter.<br />
Cela peut être dû à plusieurs choses :
<ul>
<li>Votre inscription a déjà été validée, vous recevrez donc notre newsletter
sans problème.<br />
Dans le doute, n\'hésitez pas à vous réinscrire sur <a href="{links>subscribe}">
cette page</a>. Vous ne recevrez en aucun cas la même newsletter 2 fois à la
même adresse.</li>
<li>Votre demande a expiré car elle a été effectuée il y a plus d\'un mois.<br />
Vous devez dans ce cas remplir à nouveau le formulaire d\'inscription
disponible sur <a href="{links>subscribe}">cette page</a>.</li>
<li>Le lien qui vous a mené sur cette page n\'est pas complet.<br />
Si vous avez cliqué sur le lien, c\'est qu\'il y a vraissemblablement une erreur
dans celui-ci, auquel cas, copiez l\'adresse présente en bas du mail, et collez
la dans votre navigateur.<br />
Si cela ne résoud pas le problème, n\'hésitez pas à vous réinscrire pour
recevoir un lien propre.</li>
</ul>

Cordialement,<br />
L\'équipe de <RENDER_VALUE what="site>base"/>',
    'newsletter_saved_successfully'=>'La newsletter a été enregistrée avec succès.'
);
