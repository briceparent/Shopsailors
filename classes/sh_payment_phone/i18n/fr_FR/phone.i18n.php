<?php
$i18n = array(
    'phone_payment_name'=>'Paiement par téléphone',
    'describeMode'=>'Texte de description du moyen de paiement :',
    'extraTextForBill' => 'Texte à afficher sur les factures',
    'extraTextForBill_explanations' => '<div>
Lors d\'un paiement par téléphone, vous pouvez choisir d\'envoyer la commande dès qu\'elle a été prise, ou '.
'd\'attendre d\'avoir reçu le paiement (ou de l\'avoir encaissé) avant de faire l\'envoi.<br /><br />
Dans ce deuxième cas, il est important de le noter dans la facture, pour que votre client sache que c\'est à lui '.
'de faire quelque chose (vous appeler pour vous régler).<br /><br />
De plus, si vous comptez annuler les commandes qui n\'ont pas été validées par téléphone (par exemple au bout de 10 jours), '.
'il est très important de le signaler.<br />
Ex : <textarea style="width:380px;height: 180px;">Vous avez choisi de régler votre commande par téléphone.
Celle-ci sera traitée dans les plus brefs délais, mais ne sera expédiée qu\'à réception de votre paiement.
En l\'absence de votre paiement dans les 10 jours à compter de la commande, celle-ci sera annulée, et les produits '.
'seront remis dans le stock de la boutique, et à nouveau disponibles pour nos autres clients.

Nous vous remercions pour votre compréhension.</textarea>
</div>',
    'phone_number' => 'Numéro de téléphone à contacter : ',
    'email' => 'E-mail de demande de rappel : ',
    'mails' => 'E-mails',
    'mails_intro' => 'Lorsque les utilisateurs ne peuvent vous appeler (ligne occupée, hors plages horaires),
        les utilisateurs peuvent recevoir demander à recevoir un email contenant les instructions pour le règlement.',
    'mails_text_symbol_phone' => '[TELEPHONE]',
    'mails_text_symbol_command' => '[COMMANDE]',
    'mails_text_symbol_code' => '[CODE]',
    'mails_text_model' => 'Bonjour,
Vous avez passé la commande [COMMANDE] sur notre site, et nous vous en remercions.

Afin de procéder au règlement de cette commande par téléphone, il vous suffit de vous munir de votre carte bancaire, de composer le [TELEPHONE], et de donner le numéro de commande suivant : 
[CODE].
Votre commande sera traitée immédiatement.

Cordialement,
La boutique.
',
    'mails_text_symbols' => '
Dans ce texte, certains remplacements seront faits automatiquement afin de correspondre aux données à envoyer.<br />
Ainsi, si vous saisissez le mot "[TELEPHONE]", il sera remplacé par le numéro à appeler.<br />
Mots remplacés automatiquement : 
<ul>
<li>[TELEPHONE] -> Remplacé par le numéro de téléphone à appeler pour le règlement</li>
<li>[COMMANDE] -> Remplacé par le numéro de commande</li>
<li>[CODE] -> Remplacé par le code que l\'utilisateur devra donner par téléphone.</li>
</ul>
',
    'activated' => 'Activer le module',
    'activated_explanation' => '<div>Si le module est activé, la solution de paiement
sera disponible pour les différents modules ont l\'utilité de ce service
(comme le module de commande de la boutique en ligne).</div>',
    
);
