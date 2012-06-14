<?php
$i18n = array(
    'atos_description'=>'Paiement sécurisé par carte bancaire.
Vous pouvez payer avec l\'une des cartes suivantes : <br />
<img src="/classes/sh_payment/banks/atos/logo/CB.gif"/>
<img src="/classes/sh_payment/banks/atos/logo/VISA.gif"/>
<img src="/classes/sh_payment/banks/atos/logo/MASTERCARD.gif"/>',   
'activated' => 'Activer le module',
'idPro' => 'N° de commerçant : ',
'country' => 'Pays : ',

    'activated_explanation' => '<div>Si le module est activé, la solution de paiement
sera disponible pour les différents modules ont l\'utilité de ce service
(comme le module de commande de la boutique en ligne).</div>',
    'certifTitle' => 'Certificat',
    'certificate' => 'Envoyer un nouveau certificat : ',
    'certificate_explanations' => '
<div>Pour disposer de ce moyen de paiement sur votre site, il vous faut un certificat
émis par votre banque.<br /><br />
Une fois que vous avez souscrit à cette offre dans votre banque, celle-ci
vous fournira un certificat.<br /><br />
Ce certificat est un fichier nommé <span class="nobr">certif.[pays].[num. de certificat]</span>.<br />
Exemple :<br />
certif.fr.123456789 pour le certificat numéro 123456789 situé en France.<br /><br />
Il ne vous reste alors qu\'à envoyer ce certificat grâce à ce champs pour l\'installer.</div>',
    'currency' => 'Devise : ',
    'currency_explanations' => '<div>
Définissez ici la devise utilisée par votre site.<br /><br />
ATTENTION : Celle-ci doit être la même que celle définie dans la boutique, si
vous l\'utilisez.
.</div>',
    'submitTitle' => 'Validation',
    'submit' => 'Envoyer',
    'errorTitle' => 'Erreur',
    'error_not_a_certificate' => 'Le fichier envoyé n\'est pas un certificat!<br />
Le certificat est un fichier nommé <span class="nobr">certif.[pays].[num. de certificat] .
</span>',
    'error_sending_certificate' => 'Il y a eu une erreur lors de l\'envoi du certificat.<br />
Veuillez essayer à nouveau.<br /><br />
Si le problème persiste, n\'hésitez pas à contacter l\'administrateur.',
);
