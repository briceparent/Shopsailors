<?php
$i18n = array(
    'bank_name' => 'CM-CIC P@iement - CIC',
    'bank_description'=>'Paiement par carte bancaire sécurisé par CIC.',   
    'activated' => 'Activer ce mode de paiement',
    'activated_explanation' => '<div>Si le module est activé, la solution de paiement
sera disponible pour les différents modules qui ont l\'utilité de ce service
(comme le module de commande de la boutique en ligne).</div>',
    'currency' => 'Devise : ',
    'currency_explanations' => '<div>
Définissez ici la devise utilisée par votre site.<br /><br />
ATTENTION : Celle-ci doit être la même que celle définie dans la boutique, si
vous l\'utilisez.
.</div>',
    'mode' => 'Mode :',
    'mode_explanations' => '<div>
        Afin d\'utiliser ce moyen de paiement avec de vrais utilisateurs, il vous faut un contrat signé avec le CIC.<br />
        Dès lors, vous pouvez choisir le mode "production". Lors de l\'utilisation de ce mode, les paiement sont réellement
        effectués.<br /><br />
        Pour essayer la solution de paiement sans effectuer de vrais paiement, choisissez le mode "test".
    </div>',
    'tpe' => 'Numéro de TPE : ',
    'tpe_explanations' => '<div>
        Cette donnée est fournie par votre banque. C\'est un numéro à 7 chiffres.<br />
        C\'est l\'identifiant de votre TPE virtuel.
    </div>',
    'mac' => 'Code MAC : ',
    'mac_explanations' => '<div>
        Cette donnée est fournie par votre banque. C\'est une suite de 40 chiffres et lettres.<br />
        C\'est elle qui permet d\'authentifier les paiements.
    </div>',
    'societe' => 'Code société : ',
    'societe_explanations' => '<div>
        Cette donnée est fournie par votre banque. C\'est un code alphanumérique vous permettant d\'utiliser le 
        même TPE Virtuel pour des sites différents (paramétrages distincts) se rapportant à la même activité.
    </div>',
    
);
