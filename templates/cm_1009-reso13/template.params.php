<?php

/**
 * Params file
 *
 * Params file version : 0.2
 * Licensed under LGPL
 */
if (!defined('SH_MARKER')) {
    header('location: directCallForbidden.php');
}

$this->version = '0.2';

$this->values = array(
    'width' => 950,
    'menusNumber' => 1,
    'menusDescription' => array(
        0 => 'principal'
    ),
    'palettes' => array(
        'headline' => 'headline.php',
        'title' => 'title.php',
        'tables_border' => 'tables_border.php',
    ),
    'menuButtons' =>
    array(
        1 =>
        array(
            'maxWidth' => true,
            'totalWidth' => 548,
            'textHeight' => 18,
            'expand' => true,
            'type' => 'btn_reso13-2',
            'font' => 'FreeFontBold.ttf',
            'hasSubmenus' => true,
            'renderFile' => 'horizontal',
            'fonts' =>
            array(
                'Aarvark_Cafe.ttf' => SH_FONTS_FOLDER . 'Aarvark_Cafe.ttf',
                'Abduction2002.ttf' => SH_FONTS_FOLDER . 'Abduction2002.ttf',
                'Alpine_Regular.ttf' => SH_FONTS_FOLDER . 'Alpine_Regular.ttf',
                'designer.ttf' => SH_FONTS_FOLDER . 'designer.ttf',
                'FreeFont.ttf' => SH_FONTS_FOLDER . 'FreeFont.ttf',
                'FreeFontBold.ttf' => SH_FONTS_FOLDER . 'FreeFontBold.ttf',
                'FreeFontBoldOblique.ttf' => SH_FONTS_FOLDER . 'FreeFontBoldOblique.ttf',
                'FreeFontOblique.ttf' => SH_FONTS_FOLDER . 'FreeFontOblique.ttf',
                'FreeFontSerif.ttf' => SH_FONTS_FOLDER . 'FreeFontSerif.ttf',
                'FreeFontSerifBold.ttf' => SH_FONTS_FOLDER . 'FreeFontSerifBold.ttf',
                'FreeFontSerifBoldOblique.ttf' => SH_FONTS_FOLDER . 'FreeFontSerifBoldOblique.ttf',
                'FreeFontSerifOblique.ttf' => SH_FONTS_FOLDER . 'FreeFontSerifOblique.ttf',
                'LatiniaBlack.ttf' => SH_FONTS_FOLDER . 'LatiniaBlack.ttf',
                'MiddleSaxonyText.ttf' => SH_FONTS_FOLDER . 'MiddleSaxonyText.ttf',
                'Vera.ttf' => SH_FONTS_FOLDER . 'Vera.ttf',
                'VeraBold.ttf' => SH_FONTS_FOLDER . 'VeraBold.ttf',
                'VeraBoldOblique.ttf' => SH_FONTS_FOLDER . 'VeraBoldOblique.ttf',
                'VeraSe.ttf' => SH_FONTS_FOLDER . 'VeraSe.ttf',
                'VeraSeBd.ttf' => SH_FONTS_FOLDER . 'VeraSeBd.ttf',
            ),
        ),
    ),
    'renderFiles' => array(
        'sh_contact_show' => 'sh_contact/show.rf.xml',
        'sh_content_shortList_show' => 'sh_content/shortList_show.rf.xml',
        'sh_searcher_searchEngine' => 'sh_searcher/searchEngine.rf.xml',
        'sh_legacy_legacyLine' => 'sh_legacy/legacyLine.rf.xml',
        'sh_forum_post' => 'sh_forum/post.rf.xml',
        'sh_forum_render_usersList' => 'sh_forum/render_usersList.rf.xml',
        'sh_forum_render_lastTopics' => 'sh_forum/render_lastTopics.rf.xml',
        'sh_forum_profile' => 'sh_forum/profile.rf.xml',
        'sh_forum_show' => 'sh_forum/show.rf.xml',
        'sh_forum_topic' => 'sh_forum/topic.rf.xml',
        'sh_forum_moderate_post' => 'sh_forum/moderate_post.rf.xml',
        'sh_forum_moderate_topic' => 'sh_forum/moderate_topic.rf.xml',
        'sh_forum_message_write' => 'sh_forum/message_write.rf.xml',
        'sh_forum_topic_move' => 'sh_forum/topic_move.rf.xml',
        'sh_forum_forumMenu_categories' => 'sh_forum/forumMenu_categories.rf.xml',
        'sh_forum_create_topic' => 'sh_forum/create_topic.rf.xml',
        'sh_forum_last_topics' => 'sh_forum/last_topics.rf.xml',
        'sh_forum_render_lastTopics_page' => 'sh_forum/render_lastTopics_page.rf.xml',
        'sh_forum_message_youshouldbeconnected' => 'sh_forum/message_youshouldbeconnected.rf.xml',
        'sh_forum_modify_section' => 'sh_forum/modify_section.rf.xml',
        'sh_user_showProfile' => 'sh_user/showProfile.rf.xml',
        'sh_user_connected' => 'sh_user/connected.rf.xml',
    ),
    'sh_shop' => array(
        'categoriesListing' => array(
            'categoriesNumber' => 10
        ),
        'productsListing' => array(
            'list' => array(
                'productsNumber' => 12
            ),
            'table' => array(
                'productsNumber' => 20
            ),
            'miniature' => array(
                'productsNumber' => 12
            ),
            'default' => 'miniature'
        ),
        'product' => array(
            'productsNumber' => 4
        )
    ),
    'sh_i18n' => array(
        'sh_user' => array(
            'client_identification_passphrase|fr_FR' => 'Pour que vous soyez certain que vous êtes bien
sur le site de Réso13, voici votre phrase de vérification&#160;:',
            'passwordforgotten_email|fr_FR'=>'Entrez ici l\'adresse email associée à votre compte Réso 13',
            'mail_temporarypassword_title|fr_FR' =>'Réso 13 - Nouveau mot de passe',
            'mail_temporarypassword|fr_FR' => 'Bonjour,<br />
Vous avez demandé un nouveau mot de passe pour vous connecter au site Réso 13.<br />
<br />
Votre nouveau mot de passe est: <RENDER_VALUE what="password:new"/><br />
Vous pouvez l\'utiliser dès maintenant.<br /><br />
Ce mot de passe n\'est valable que 48h. Passé ce délai, si vous ne l\'avez pas utilisé,
il vous faudra faire la demande à nouveau.<br/>
Notez que dès lors que vous aurez utilisé ce nouveau mot de passe une fois, il remplacera l\'ancien.<br /><br />
Nous vous remercions pour l\'intérêt que vous nous portez.<br />
Cordialement,<br />
L\'équipe de Réso 13.',
    'loginnotfound|fr_FR' => '<span>Votre identifiant n\'est pas enregistré, ou le compte n\'a pas été validé
en cliquant sur le lien reçu par mail.</span><br /><br />
Veuillez donc vérifier:
<ul><li>Que votre compte Réso 13 existe bien et que l\'identifiant correspond bien
à celui qui vous est parvenu par mail lors de la création du compte.</li>
<li>Que vous avez bien suivi le lien le lien de confirmation présent dans ce même mail.</li></ul>',
            'createaccount_address|fr_FR' => 'Entrez ici votre adresse postale.<br />
Ce champs est facultatif. Celle-ci ne sera en aucun cas utilisée à des fins commerciales, revendue ou <br />
donnée à des organismes ou personnes externe sans votre accord explicite.',
    'createaccount_login|fr_FR' =>
'Entrez ici un dientifiant qui vous permettra de vous connecter sur le site.<br />
Cet identifiant doit comporter au moins 5 caractères, et au plus 50.<br />
Les caractères spéciaux seront automatiquement remplacés par leurs équivalents.',
    'createaccount_password|fr_FR' =>
'Entrez dans les deux champs suivants le mot de passe que vous souhaitez utiliser<br />
pour vous connecter au site.<br />
Celui-ci doit comporter au moins 5 caractères alphanumériques, et au plus 50.<br />
<br />
Par sécurité, il est conseillé de ne pas utiliser les mêmes mots de passe pour tous les<br />
services que vous utilisez sur internet (banque, messagerie électronique, comptes<br />
sur des sites commerçants, blogs, etc).',
    'createaccount_verification|fr_FR' =>
'Ce champs vous permet de lutter contre le piratage, et tout particulièrement <br />
le <a href="http://fr.wikipedia.org/wiki/Hame%C3%A7onnage">hameçonnage</a>.<br />
Pour vous garantir que le site sur lequel vous vous connectez est bien le site original<br />
et non un site contrefait, la phrase que vous entrez ici vous sera répétée à chaque<br />
fois que votre mot de passe vous sera demandé sur le site.<br />
Si cette phrase n\'est pas affichée au moment où l\'on vous demande votre mot de passe,<br />
annulez votre action et vérifiez bien l\'adresse du site internet sur lequel vous êtes.<br /><br />
Vous pouvez donc inscrire ici la phrase de votre choix, il vous suffira de la reconnaitre<br />
au moment de la connexion pour être sûr que le site n\'est pas contrefait.',

    'sendMailconfirm|fr_FR' => ' Vous recevrez un e-mail confirmant votre inscription.<br /><br />
Réso 13<br />
Les informations recueillies font l\'objet d\'un traitement informatique destiné
à vous permettre de vous connecter au site.<br />
L\'unique destinataire des données est la société Websailors qui héberge ces sites.<br />
Conformément à la loi «informatique et libertés» du 6 janvier 1978, vous
bénéficiez d\'un droit d\'accès et de rectification aux informations qui vous
concernent. Si vous souhaitez exercer ce droit et obtenir communication des
informations vous concernant, veuillez vous adresser à <span>i<span>l</span>&#64;</span>websailors.fr .',
    'websailorsthanksyou|fr_FR' => 'Nous vous remercions pour l\'intêret que vous nous portez.<br />L\'équipe de Réso 13',
    'mail_already_used|fr_FR' => 'Votre email est déjà associé à un compte Réso 13.<br/>
Si vous ne vous souvenez pas de vos données, nous pouvons vous renvoyer votre identifiant, et
générer un nouveau mot de passe , pour ce faire veuillez nous contacter via contact@websailors.fr<br /><br />',
    'mail_confirmation_title|fr_FR'=>'Réso 13 - Confirmation de la création de votre compte',
            'password_allSitesModif' => '',
            'data_allSitesModif' => 'Informatique et Libertés : Ce site respecte entièrement les disposition de la CNIL.',
        ),
    )
);