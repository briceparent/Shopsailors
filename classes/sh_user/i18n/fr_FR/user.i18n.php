<?php
$i18n = array(
    'requires_connection'=>'Ce site est privé.<br />
Pour y accéder vous devez posséder un compte Websailors.<br />
De plus, l\'administrateur du site doit autoriser votre compte
à accéder à ce site.<br /><br />
Si vous n\'avez pas d\'identifiants, nous vous conseillons donc:&#160;
<ul>
<li>De vous créer un compte Websailors</li>
<li>Puis d\'envoyer votre nouvel identifiant à l\'administrateur du site, de
manière à ce qu\'il vous ouvre l\'accès.</li>
</ul>',
    'connectionLinkText' => 'Se connecter',

    'myAccount'=>'Mon compte',
    'password'=>'Mot de passe',
    'verificationPhrase'=>'Phrase de vérification',

    'client_identification_title_step1' => 'Identification - Etape&#160;1:',
    'client_identification_title_step2' => 'Identification - Etape&#160;2:',
    'client_identification_passPhrase' => 'Pour que vous soyez certain que vous êtes bien
sur un site du réseau Websailors, voici votre phrase de vérification&#160;:',
    'client_username' => 'Identifiant:&#160;',
    'client_password'=>'Mot de passe:&#160;',
    'password_forgotten' => 'Mot de passe oublié?',
    'create_account' => 'Créer un compte',
    'noAccount' => 'Vous n\'avez pas encore de compte?',
    'WRONG_DATA'=> 'L\'identifiant et le mot de passe ne concordent pas.',
    'SITE_NOT_ALLOWED'=>'Ce site n\'a pas accès à la base de données clients de Websailors',
    'ERROR_USING_FORM'=>'Il y a eu une erreur.<br />Merci de contacter votre administrateur.',
    'WEBSAILORS_USERNAME_NOT_ALLOWED'=>'Votre identifiant Websailors n\'est pas autorisé à accéder à ce site',
    'ACCOUNT_NOT_ACTIVATED'=>'Votre compte n\'a pas été activé.<br /> Pour ce faire, vous devez confirmer en cliquant sur le lien du mail d\'activation<br />
    que avez reçu à l\'adresse que vous avez spécifiée lors de votre inscription.',
    'accountCreated' => 'Compte crée avec succès',
    'createAccount_error_captcha' => 'Le texte de vérification a mal été recopié.',
    'createAccount_error_name' => 'Le nom est un champs obligatoire.',
    'createAccount_error_lastname' => 'Le prénom est un champs obligatoire.',
    'createAccount_error_phone' => 'Le numéro de téléphone n\'est pas reconnu. Merci de le saisir sous la forme +123456789 ou directement 123456789.',
    'createAccount_error_mail' => 'L\'adresse email saisie n\'est pas correcte.',
    'createAccount_error_login_too_short' => 'Le nom d\'utilisateur doit faire au moins 3 caractères.',
    'createAccount_error_password_too_short' => 'Le mot de passe doit faire au moins 5 caractères.',
    'createAccount_error_passwords_different' => 'Les mots de passes doivent être identiques.',

    'passwordForgotten_response_title' => 'Mot de passe oublié',
    'passwordForgotten_response_text' => 'Un nouveau mot de passe a été généré.<br />
Il vous a a été communiqué par email à votre adresse.<br />
Si vous ne l\'utilisez pas dans les prochaines 48 heures, il sera détruit et seul l\'ancien
restera utilisable.<br /><br />
Dès lors que vous utiliserez ce nouveau mot de passe, l\'ancien sera détruit.',
    'passwordForgotten_response_text_notfound' => 'L\'adresse email que vous avez entrée
n\'est associée à aucun compte Websailors.',
    'passwordForgotten_title' => 'Mot de passe oublié',
    'passwordForgotten_email' => 'Entrez ici l\'adresse email associée à votre compte Websailors&#160;:',
    'mail_temporaryPassword_title' => 'Websailors - Nouveau mot de passe',
    'mail_temporaryPassword' => 'Bonjour,<br />
Vous avez demandé un nouveau mot de passe pour vous connecter aux sites du réseau Websailors.<br />
<br />
Votre nouveau mot de passe est: <RENDER_VALUE what="password:new"/><br />
Vous pouvez l\'utiliser dès maintenant.<br /><br />
Ce mot de passe n\'est valable que 48h. Passé ce délai, si vous ne l\'avez pas utilisé,
il vous faudra faire la demande à nouveau.<br/>
Notez que dès lors que vous aurez utilisé ce nouveau mot de passe une fois, il remplacera l\'ancien.<br /><br />
Nous vous remercions pour l\'intérêt que vous portez aux sites de notre réseau.<br />
Cordialement,<br />
L\'équipe de Websailors.',


    
    'hello' => 'Bonjour',
    'connected' => 'Vous êtes connecté.',
    'lastConnexion' => 'Dernière connexion : ',
    'lastConnexionDate' => 'Date : ',
    'lastConnexionTime' => 'Heure : ',
    'lastConnexionSite' => 'Site : ',
    'failuresTitle' => 'Tentatives de connexion infructueuses',
    'failuresDate' => 'Date',
    'failuresAttempts'=>'Nombre de tentatives : ',
    'failuresTime' => 'Heure',
    'failuresSite' => 'Site',
    'failuresIp' => 'Adresse IP',
    'connectedAsAdmin' => 'Vous êtes connecté en tant qu\'administrateur du site.',
    'connectedAsMaster' => 'Vous êtes connecté en tant qu\'administrateur du réseau de sites.',
    'loginNotFound' => '<span>Votre identifiant n\'est pas enregistré, ou le compte n\'a pas été validé
en cliquant sur le lien reçu par mail.</span><br /><br />
Veuillez donc vérifier:
<ul><li>Que votre compte Websailors existe bien et que l\'identifiant correspond bien
à celui qui vous est parvenu par mail lors de la création du compte.</li>
<li>Que vous avez bien suivi le lien le lien de confirmation présent dans ce même mail.</li></ul>',
    
    'connect_cookie' => 'Rester enregistré (cookie)',
    'connect_cookie_explanations' => '
        <div>
        Cette fonctionnalité permet de placer un cookie (fichier) sur votre ordinateur faisant en sorte
        que vous n\'ayez pas à saisir vos identifiants à chaque passage sur ce site.<br />
        Ce cookie a une durée de vie limitée. Si il n\'est pas utilisé pendant une période continue de 3 mois,
        il est automatiquement supprimé.<br />
        Si vous cliquez sur le lien de déconnexion, le cookie sera aussi supprimé.
        </div>
',
    'connected_using_cookie' => '
        Vous êtes connecté en tant que <span class="bold">[NAME]</span>.<br />
        Si ce n\'est pas vous, veuillez <a href="/user/disconnect.php">cliquer ici</a> pour vous déconnecter.
',

    'privateAccess' => 'Accès privé',
    'privateAccess_input' => 'Rendre privé l\'accès au site',
    'privateAccess_explanation' => '
<div>
Rendre l\'accès au site privé vous permettra de contrôler les utilisateurs (ou clients) qui auront accès au site. 
Leurs identifiants websailors ne pourront être autorisés par l\'administrateur de ce site.
<br /> Si vous ne cochez pas cette case il est inutile de remplir le champ ci-dessous.
</div>',
    'userList' => 'Liste des utilisateurs autorisés&#160;:',
    'userList_explanation' => '
<div>
Dans ce champ, tous les utilisateurs autorisés sont recensés. Pour modifier cette liste, il vous suffit d\'y ajouter 
ou d\'y supprimer une adresse (une adresse par ligne).
</div>',

    'createAccount' => 'Créer un compte',
    'alertAccount' => 'Une erreur est survenue lors de l\'inscription&#160;: <br />Vérifiez que tous les champs soient correctement remplis. ',
    'stepOne' => 'Etape 1&#160;: Vos coordonnées&#160;',
    'stepTwo' => 'Etape 2&#160;: Choix de vos codes d\'accès&#160;',
    'accountName' => 'Nom&#160;:&#160;',
    'accountCity' => 'Ville&#160;:&#160;',
    'accountZipCode' => 'Code&#160;Postal&#160;:&#160;',
    'accountLastname' => 'Prénom&#160;:&#160;',
    'accountPhone' => 'Téléphone&#160;:&#160;',
    'accountAddress' => 'Adresse&#160;:&#160;',
    'optional' => 'Ce champs est facultatif.',
    'accountEmail' => 'E-mail&#160;:&#160;',
    'login' => 'Choisissez un identifiant&#160;:&#160;',
    'yourLogin' => 'Identifiant&#160;:&#160;',
    'password' => 'Choisissez un mot&#160;de&#160;passe&#160;:&#160;',
    'notification' => '(5 caractères minimum)',
    'passwordConfirm' => ' Confirmez le mot&#160;de&#160;passe&#160;:&#160;',
    'verification' => 'Phrase de vérification&#160;:&#160;',
    'backToAccount' => 'Retour à l\'espace client',

    'createAccount_name' => 'Entrez ici votre nom de famille.',
    'createAccount_lastName' => 'Entrez ici votre prénom.',
    'createAccount_phone' => 'Entrez ici votre numéro de téléphone.<br />
Celui-ci ne sera en aucun cas utilisé à des fins commerciales, revendu ou donné<br />
à des organismes ou personnes externe sans votre accord explicite.',
    'createAccount_email' => 'Entrez ici votre adresse email.<br />
Celle-ci ne sera en aucun cas utilisée à des fins commerciales, revendue ou <br />
donnée à des organismes ou personnes externe sans votre accord explicite.',
    'createAccount_address' => 'Entrez ici votre adresse postale.<br />
Ce champs est facultatif. Cependant, si vous le remplissez, lors de vos achats<br />
sur un des sites du réseau Websailors, vous n\'aurez pas à la saisir à nouveau.<br />
Celle-ci ne sera en aucun cas utilisée à des fins commerciales, revendue ou <br />
donnée à des organismes ou personnes externe sans votre accord explicite.',
    'createAccount_login' =>
'Entrez ici un dientifiant qui vous permettra de vous connecter sur les sites du<br />
réseau Websailors.<br />
Cet identifiant doit comporter au moins 5 caractères, et au plus 50.<br />
Les caractères spéciaux seront automatiquement remplacés par leurs équivalents.',
    'createAccount_password' =>
'Entrez dans les deux champs suivants le mot de passe que vous souhaitez utiliser<br />
pour vous connecter aux sites du réseau Websailors.<br />
Celui-ci doit comporter au moins 5 caractères alphanumériques, et au plus 50.<br />
<br />
Par sécurité, il est conseillé de ne pas utiliser les mêmes mots de passe pour tous les<br />
services que vous utilisez sur internet (banque, messagerie électronique, comptes<br />
sur des sites commerçants, blogs, etc).',
    'createAccount_verification' =>
'Ce champs vous permet de lutter contre le piratage, et tout particulièrement <br />
le <a href="http://fr.wikipedia.org/wiki/Hame%C3%A7onnage">hameçonnage</a>.<br />
Pour vous garantir que le site sur lequel vous vous connectez est bien le site original<br />
et non un site contrefait, la phrase que vous entrez ici vous sera répétée à chaque<br />
fois que votre mot de passe vous sera demandé sur le réseau Websailors.<br />
Si cette phrase n\'est pas affichée au moment où l\'on vous demande votre mot de passe,<br />
annulez votre action et vérifiez bien l\'adresse du site internet sur lequel vous êtes.<br /><br />
Vous pouvez donc inscrire ici la phrase de votre choix, il vous suffira de la reconnaitre<br />
au moment de la connexion pour être sûr que le site n\'est pas contrefait.',

    'sendMailConfirm' => ' Vous recevrez un e-mail confirmant votre inscription.<br /><br />
Websailors<br />
Les informations recueillies font l\'objet d\'un traitement informatique destiné
à vous permettre de vous connecter à l\'ensemble du réseau de sites Websailors.<br />
L\'unique destinataire des données est la société Websailors qui héberge ces sites.<br />
Conformément à la loi «informatique et libertés» du 6 janvier 1978, vous
bénéficiez d\'un droit d\'accès et de rectification aux informations qui vous
concernent. Si vous souhaitez exercer ce droit et obtenir communication des
informations vous concernant, veuillez vous adresser à <span>i<span>l</span>&#64;</span>websailors.fr .',
    'mailConfirmSent' => 'Un mail de confirmation a été envoyé à l\'adresse suivante&#160;:&#160;',
    'accountCreationCongratulation' => 'Felicitations ',
    'accountCreationValidated' => 'Votre compte a été créé avec succès.<br />Vous pouvez dès à présent vous connecter sur tout le réseau Websailors.',
    'accountCreationFailed' => ' Un problème est survenu lors de la validation.<br /> Soit votre compte a déjà été validé, soit il y a un problème dans le lien d\'activation.<br />
Vous pouvez donc essayer de vous connecter avec vos identifiants, et si cela ne fonctionne pas, il vous faudra créer un nouveau compte.',
    'wayToConfirm' => ' Pour activer votre compte, il vous suffit de cliquer sur le lien présent dans le mail.',
    'websailorsThanksYou' => 'Nous vous remercions pour l\'intêret que vous nous portez.<br />L\'équipe de Websailors',
    'mail_already_used' => 'Votre email est déjà associé à un compte Websailors.<br/>
Si vous ne vous souvenez pas de vos données, nous pouvons vous renvoyer votre identifiant, et
générer un nouveau mot de passe , pour ce faire veuillez nous contacter via contact@websailors.fr<br /><br />',
    'login_already_used' => 'Votre identifiant est déjà pris.<br/>
Veuillez en choisir un autre.<br /><br />',

    'mail_confirmation_title'=>'Websailors - Confirmation de la création de votre compte',
    'error_sending_mail'=>'L\'envoi du mail de confirmation a échoué.<br />
Merci de bien vouloir contacter l\'administrateur du site.',

    'mail_authorization_title'=>'Ouverture de votre accès au site ',
    'mail_authorization_content'=>
'Un accès a été ouvert pour vous sur le site <RENDER_VALUE what="client>site"/><br />
Pour vous y connecter, vous devez d\'abord <a href="{websailors>createAccountPage}?mail={dest>mail}">créer un compte Websailors</a>.<br />
Pour pouvoir être reconnu(e) sur le site, vous devez utiliser cette adresse e-mail (<RENDER_VALUE what="dest>mail"/>).<br />
Dès la confirmation de la création de votre compte Websailors, vous pourrez vous
connecter sur le site <a href="{client>site}"><RENDER_VALUE what="client>site"/></a> avec
les identifiants Websailors que vous aurez créés.<br /><br />
Cordialement,<br />
L\'équipe de Websailors',
    'mail_existingAccount_authorization_content' => 
'Un accès a été ouvert pour vous sur le site <RENDER_VALUE what="client>site"/><br />
Pour vous y connecter, il vous suffit d\'accéder au site  <a href="{client>site}"><RENDER_VALUE what="client>site"/></a>
et de vous connecter avec vos identifiants Websailors.<br /><br />
Cordialement,<br />
L\'équipe de Websailors',

    'editProfile_rights' => '<div>
Les données recueillies ici sont destinées à Websailors, qui se charge de leur traitement.<br />
Ces données sont conservées pendant une durée d\'un an après votre dernière connexion.
Elles ne sont diffusées aux sites du réseau que lorsque vous vous connectez sur un de leur
site et que vous souscrivez à des services (achat en ligne, inscription à une newsletter, etc).<br />
Cette page vous permet de faire valoir vos droits de rectification de vos données personnelles.<br />
Pour supprimer complètement votre compte, n\'hésitez pas à contacter Websailors.
</div>',

    'WRONG_PASSWORD' => 'Le mot de passe entré est incorrect.',
    'WRONG_PASSWORD_FORMAT' => 'Le nouveau mot de passe doit être composé d\'au moins 6 caractères.',
    'WRONG_PASSWORD_COPY' => 'Le nouveau mot de passe a mal été recopié.',

    'notifTitle' => 'Message',
    'yourDatasTabTitle' => 'Vos coordonnées',
    'connectionTabTitle' => 'Connexion',
    'favoritesTabTitle' => 'Favoris',
    
    'whatIsVerificationPhrase' => '<div>Vos identifiants de connexion étant utilisables depuis de multiples
sites, nous vous donnons ici un moyen de vous assurer que le site sur lequel
vous vous connectez appartient bien au réseau, et n\'est donc pas en train
d\'essayer de récupérer vos identifiants.<br />
Chaque fois que vous aurez à entrer votre mot de passe, la phrase ci-dessous
vous sera afichée.<br />
Si ce n\'est pas le cas, n\'entrez pas votre mot de passe avant d\'avoir bien vérifié que ce site est le bon
(nom de domaine).
</div>',
    'editPassphrase' => 'Modifier votre phrase de vérification',
    'editPassWord' => 'Modifier votre mot de passe',
    'phoneNotProvided' => 'Vous n\'avez pas fourni votre numéro de téléphone',
    'addressNotProvided' => 'Vous n\'avez pas fourni votre adresse',
    'editDatas' => 'Modifier vos coordonnées',
    'noFavorites' => 'Vous n\'avez enregistré aucun favoris pour ce site.',
    'modificationsSaved' => 'Vos modifications ont bien été prises en compte.',
    'password_allSitesModif' => 'Note : Lorsque vous modifiez votre mot de passe sur un des sites du réseau Websailors,
                        celui-ci sera modifié pour l\'ensemble des sites.',
    'datas_allSitesModif' => 'Note : Lorsque vous modifiez vos coordonnées sur un des sites du réseau Websailors,
                        celles-ci seront modifiées pour l\'ensemble des sites.<br /><br />
                        Informatique et Libertés : Ce site respecte entièrement les disposition de la CNIL.',

);
