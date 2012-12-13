<?php

$i18n = array(
    'className' => 'Boutique',
    'mainCategory_name' => 'La boutique',
    'addToCart_embedded' => 'Ajout au panier',
    'mainCategory_description' => 'Vous trouverez ici toutes les catégories de produits en vente sur ce site.',
    'mainCategory_shortDescription' => 'Toutes les catégories de la boutique',
    'cartPlugin_content' => 'Votre panier : ',
    'cartPlugin_empty' => 'Votre panier est vide',
    'rights_className' => 'Boutique',
    'rights_showProduct_all' => 'Affichage de toutes les pages produits',
    'rights_editProduct_all' => 'Modification de toutes les fiches produits',
    'rights_showProduct_one' => 'Affichage du produit "[PAGE_NAME]"',
    'rights_editProduct_one' => 'Modification de la fiche produit "[PAGE_NAME]"',
    'rights_editProduct_0' => 'Création d\'une nouvelle fiche produit',
    'rights_showCategory_all' => 'Affichage de toutes les catégories de produits',
    'rights_editCategory_all' => 'Modification de toutes les catégories de produits',
    'rights_showCategory_one' => 'Affichage de la catégorie "[PAGE_NAME]"',
    'rights_editCategory_one' => 'Modification de la catégorie "[PAGE_NAME]"',
    'rights_editCategory_0' => 'Création d\'une nouvelle catégorie',
    'cart_save_title' => 'Enregistrement de votre panier',
    'cart_save_name' => 'Nom ou description du panier : ',
    'cart_save_name_info' => 'Si le champs est vide, le panier sera enregistré avec comme nom la date et
        l\'heure actuelles.',
    'cart_save_defaultName_before' => 'Panier du ',
    'cart_save_defaultName_after' => '',
    'cart_save_defaultName_middle' => ' à ',
    'cart_conditions_should_be_accepted' => 'Vous devez lire et accepter les 
        Conditions Générales de Vente, et le signaler en cochant la case au dessus du
        bouton <img src="images/shared/icons/picto_command.png" style="height:25px;vertical-align:top;"/>.
        ',
    'cart_accept_conditions' => 'J\'ai pris connaissance des <a href="{conditions:file}">Conditions Générales de Vente</a> et je les accepte',
    'cart_save_successfully' => 'Votre panier a été sauvegardé avec succès. Vous pouvez y accéder en allant dans "Mon compte" puis "Paniers enregistrés".',
    'saved_carts_tab' => 'Paniers enregistrés',
    'saved_cart_removed' => 'Le panier a été supprimé avec succès.',
    'savedCart_load_notFound' => 'Une erreur est survenue lors du chargement du panier enregistré. Celui-ci n\'a pas été trouvé.',
    'taxRate' => 'Taux de la taxe&#160;:&#160;',
    'command_previous_step' => 'Etape précédente',
    'command_next_step' => 'Etape suivante',
    'command_shipment' => 'Mode de livraison',
    'command_billing_address' => 'Facturation',
    'command_shipping_address' => 'Livraison',
    'supplyer_unavailable_minimum' => '[SHIPPER] : <br />
        Ce mode d\'expédition n\'est disponible qu\'à part de [MIN]€ d\'achats.<br />
        Nous vous remercions pour votre compréhension.',
    'command_extrenal_classes' => 'Informations complémentaires',
    'command_payment' => 'Paiement',
    'command_summary' => 'Résumé de la commande',
    'command_cancel_title' => 'Annulation du paiement',
    'command_cancel_content' => '<div class="left">
        Une erreur est survenue lors du processus de paiement en ligne.<br />
        Cela peut être dû à :
        <ul>
            <li>Une annulation de votre part</li>
            <li>Une erreur dans les numéros de cartes / vérification</li>
            <li>Une erreur sur les serveurs de votre banque</li>
        </ul>
        Ainsi, la commande n\'a pas été validée, et votre paiement n\'a pas été enregistré ni effectué.<br />
        Si vous n\'avez pas souhaité annuler votre commande, pour pouvez réessayer en suivant ce lien :
        <a href="{link:retry}"><RENDER_VALUE what="link:retry"/></a></div>',
    'billing_address' => 'Adresse de facturation :',
    'shipping_address' => 'Adresse de livraison :',
    'chooseProduct' => 'Produit&#160;:&#160; ',
    'chooseCategory' => 'Catégorie&#160;:&#160; ',
    'subLevel_title' => 'Affinez votre recherche dans les sous catégories&#160;:&#160; ',
    'navigator_title' => '',
    'list_view' => 'aperçu en liste',
    'table_view' => 'aperçu en tableau',
    'miniature_view' => 'aperçu en miniatures',
    'products_list' => 'Liste des articles de la catégorie&#160;:&#160; ',
    'categories_title' => 'Categories',
    'product_chooseVariant' => 'Vous devez choisir une variante du produit pour en connaitre le stock.',
    'product_nomorestock' => 'Les stocks sont épuisés.',
    'product_stocknotsufficient' => 'Il n\'y a pas assez d\'unités dans les stocks par rapport à la quantité demandée.<br />
Celle-ci a donc été abaissée à la quantité disponible.',
    'product_noMoreTakenOut' => 'Le produit n\'est plus disponible, il a été retiré de votre panier.',
    'product_added_message' => 'Le produit a été ajouté à votre panier avec succès!
    <br />
        Souhaitez-vous :
    <br />
    <br />
    <table cellpadding="4" cellspacing="0">
        <tr>
        <td>
        <span style="cursor:pointer" onclick="sh_popup.hide();">
        <img src="/images/icons/picto_previous.png" alt="buy more"/>
        </span>
    </td>
        <td>
        <span style="cursor:pointer" onclick="sh_popup.hide();">Continuer vos achats</span>
        </td>
        </tr>
    </table>
    <br />
    <table cellpadding="4" cellspacing="0">
        <tr>
        <td>
    <a href="{links:cart}">
        <span style="cursor:pointer" onclick="sh_popup.hide();">
        <img src="/images/icons/picto_cart.png" alt="go to cart"/>
        </span>
    </a>
    </td>
        <td>
    <a style="text-decoration:none;color:#000" href="{links:cart}">
        <span style="cursor:pointer" onclick="sh_popup.hide();">Poursuivre votre commande</span>
    </a>
        </td>
        </tr>
    </table>
    ',
    'submit_command' => 'Passer la commande',
    'command_confirm' => 'Confirmer la commande',
    'continue' => 'Poursuivre la commande',
    'command_billing' => 'Facturation et livraison',
    'command_justBilling' => 'Facturation',
    'billing_enterAdress' => 'Entrez l\'adresse de facturation',
    'command_billing_name' => 'Prénom et Nom :',
    'command_billing_name_ph' => 'Ex : Jean Durand',
    'command_billing_phone' => 'N° de téléphone :',
    'command_billing_phone_ph' => 'Ex : 0610203040',
    'command_billing_addressInput' => 'Adresse complète :',
    'command_billing_addressInput_ph' => 'Ex : 12 place de la poste',
    'command_billing_zipCode' => 'Code postal :',
    'command_billing_zipCode_ph' => 'Ex : 13001',
    'command_billing_city' => 'Ville :',
    'command_billing_city_ph' => 'Ex : Villeneuve',
    'command_billing_delivery' => 'Livraison :',
    'shipping_enterAdress' => 'Entrez l\'adresse de livraison',
    'shipping_youShouldPickOne' => 'Vous devez sélecionner un mode de livraison',
    'command_shipping_name' => 'Nom :',
    'command_shipping_phone' => 'N° de téléphone :',
    'command_yourEMail' => 'Adresse e-mail : ',
    'command_yourEMail_ph' => 'jean.durand@gmail.com',
    'command_yourEMail_explanations' => '<div>
        Vous devez remplir votre adresse email, car c\'est à cette adresse que sera envoyée la facture.
        </div>',
    'command_yourPhone' => 'Téléphone : ',
    'command_yourPhone_explanations' => '<div>
        Ce champs est obligatoire, car nous devons avoir un moyen de vous contacter directement à propos de cette commande.
        </div>',
    'command_shipping_addressInput' => 'Adresse complète :',
    'command_shipping_addressInput_more' => 'Complément d\'adresse :',
    'command_shipping_addressInput_more_prefill' => 'Lotissement :
Résidence :
Bâtiment : 
Escalier : 
Etage : 
Digicode : 
Sonnette : 
Autres informations :',
    'command_shipping_zipCode' => 'Code postal :',
    'command_shipping_city' => 'Ville :',
    'command_followingAddress' => 'Livrer à l\'adresse suivante&#160;:',
    'payment_title' => 'Modes de paiement disponibles',
    'payment_oneMode' => 'Le paiement est effectué par&#160;:',
    'payments_proposed' => 'Lors de la prise de commande vous pourrez choisir entre ces différents modes de paiment.',
    'payments_oneMode' => 'Les règlements des commandes sont effectués comme décrit ci-dessous:',
    'ship_getPrice_explanation' => '<br />Réduction appliquée en fonction du montant de la facture : ',
    'modify_billing_address' => 'Modifier ces coordonnées de facturation',
    'modify_shipping_address' => 'Modifier cette adresse de livraison',
    'editTitle' => 'Paramètres de la boutique',
    'activateShop' => 'Activer la boutique en ligne.',
    'sellingActivated' => 'Activer la vente en ligne. ',
    'sellingActivated_explanation' => '<div>
Si cette case n\'est pas cochée, votre boutique ne permettra pas à vos clients
de passer des commandes, mais servira uniquement de vitrine en ligne..
</div>',
    'activateMail' => 'Activer l\'envoi de la fiche produit par mail.',
    'activateCom' => 'Activer la rédaction de commentaire par les internautes.',
    'activateSendCost' => 'Activer les frais de port.',
    'sendCost' => 'Montant des frais de port&#160;:&#160; ',
    'showQuantity' => 'Afficher la quantité disponible pour les produits.',
    'forceUserToCheckConditions' => 'Obliger les clients à valider les CGV avant de pouvoir commander.',
    'conditionsFile' => 'Document téléchargeable des conditions générales de vente :',
    'conditionsFile_explanations' => '<div>
      Sélectionnez ici le document qui sera téléchargé par les utilisateurs si ils cliquent
      sur "Conditions Générales de Ventes" lors de la validation de la commande.<br /><br />
      Il est conseillé de choisir un fichier au format PDF, afin d\'éviter des modifications,
      et il est aussi souhaitable de conserver les anciennes versions.
</div>',
    'showDeliveriesMode' => 'Afficher les modes de livraisons.',
    'command_mail' => 'Indiquez la ou les adresses mail auxquelles vous desirez recevoir les ordres de commandes.',
    'command_mailNote' => 'Si vous saisissez plusieurs adresses, passez à la ligne pour chacunes d\'elles.',
    
    'share_settings_shop_active' => 'Activer le module',
    'share_settings_shop_active_explanations' => '<div>Ce module vous permet de présenter vos produits et peut vous permettre 
        de les mettre en vente sur votre site.<br />
        Il contient entre autres les fonctionnalités suivantes : arborescence complète des catégories et produits, lots
        de produits, promotions, variantes de produits, etc.</div>',
    'share_settings_shop_payment' => 'Activer le paiement en ligne',
    'share_settings_shop_payment_explanations' => '<div>Ce module requiert l\'activation du module précédent.<br />
        Il vous permet de vendre les produits de votre boutique.<br />
        Il contient un panier enregistrable, la gestion de plusieurs modes de paiements et modes de livraison,
        la création et l\'envoi automatique des factures, etc.</div>',
    
// Box Prices
    'prices_Title' => 'Affichage des prix',
    'prices_showCurrency' => 'Devise utilisée&#160;:',
    'prices_showPrice_explanation' => '<div>
C\'est ici que vous indiquez le mode d\'affichage des prix, Hors Taxes, ou
Toutes Taxes Comprises.<br />
Tous les prix du site, lorsque vous les entrez et quand ils sont affichés
pour vos clients, le seront avec le mode choisi.<br /><br />
Notez que les règlementations française et européenne stipulent que la
mention Hors Taxes doit être signalée explicitement, le cas chéant.</div>',
    'prices_showCurrency_explanation' => '<div>
C\'est ici que vous indiquez la devise que vous utilisez sur tout le site,
aussi bien dans les pages d\'administration (comme les pages d\'édition des
fiches produits), que dans les pages du site lui même (comme les fiches
techniques, le panier, les commandes, etc).</div>',
    'prices_monney_format' => 'Format d\'affichage des prix :&#160;',
    'prices_taxesMode' => 'Mode de saisie des prix&#160;:',
    'prices_showHT' => 'HT',
    'prices_showTTC' => 'TTC',
    'prices_taxRate' => 'Taux par défaut de la taxe : ',
    'prices_showTaxSymbol' => 'Afficher le symbole HT ou TTC après les prix',
    'prices_showTaxSymbol_explanation' => '<div>
En cochant cette case, les prix affichés sur le site contiendront tous la
mention HT (pour Hors Taxes) ou TTC (pour Toutes Taxes Comprises).
Notez que les règlementations française et européenne stipulent que la
mention Hors Taxes doit être signalée explicitement, le cas échéant.</div>',
    'payment_requireConnexion' => 'Forcer la connexion pour passer commande',
    'payment_requireConnexion_explanation' => '<div>
Si cette case est cochée, les utilisateurs devront se connecter pour pouvoir passer
des commandes sur votre site.<br />
Cette méthode vous permet de garantir la validité de l\'adresse email du client,
mais ajoute une légère contrainte pour les utilisateurs.<br />
Pour les sites ayant des commandes régulières, cette méthode est cependant
conseillée, car les clients qui reviennent n\'ont plus qu\'à se connecter
pour passer de nouvelles commandes (et non pas à remplir à nouveau le formulaire
de contact), et ils peuvent accéder à toutes leurs commandes et factures passée
sur votre site, ce qui simplifie votre relation avec la clientèle.
</div>',
    'activateCart_explanation' => '<div>
Si cette case n\'est pas cochée, le panier est inactif, il vous sera impossible
de prendre une commande, votre site n\'aura alors qu\'une fonction de vitrine
ou de présentation d\'activité.</div>',
    'emptyCart' => 'Votre panier est vide.',
    'emptyCategory' => 'Cette catégorie est vide.',
    'discount_text' => 'En promotion',
    'cart_show_title' => 'Votre panier',
    'cart_yourCommand' => 'Votre commande',
    'cart_removeProduct' => 'Retirer le produit de la liste',
    'cart_name' => 'Nom',
    'cart_shortDescription' => 'Description',
    'cart_stock' => 'Quantité disponible',
    'cart_quantity' => 'Quantité',
    'cart_price' => 'Prix <RENDER_VALUE what="constants:taxes"/>',
    'cart_productTotalPrice' => 'Total <RENDER_VALUE what="constants:taxes"/>',
    'cart_update' => 'Mettre à jour le formulaire',
    'cart_command' => 'Passer la commande',
    'cart_reference' => 'Reference',
    'cart_save' => 'Sauvegarder le panier',
    'changeAddresses' => 'Modifier la ou les adresses',
    'changeAddress' => 'Modifier l\'adresse',
    'cart_saved_successfully' => 'Votre panier a été sauvegardé avec succès.<br />
Vous pourrez le recharger lors d\'une prochaine visite en vous connectant à votre
compte, et en suivant le lien "<RENDER_VALUE what="i18n:cart_load"/>".',
    'cart_load' => 'Charger votre panier enregistré',
    'cart_alreadyExistingCart' => 'Vous avez déjà un panier enregistré.<br />
        Souhaitez vous :<br />',
    'totalht' => 'Total HT: ',
    'totalttc' => 'Total TTC: ',
    'pageName_cart_show' => 'Page d\'affichage du panier',
    'pageName_showShipModes' => 'Page d\'affichage des modes de livraisons',
    'bill_taxes_inclusive' => 'Total TTC',
    'bill_taxes_exclusive' => 'Total HT',
    'bill_number' => 'N° de facture : ',
    'bill_subject_beforeDate' => 'Facture du ',
    'bill_subject_middle' => ' pour ',
    'bill_subject_afterName' => '',
    'bill_customerId' => 'N° client:',
    'bill_author_prefix' => 'Websailors pour ',
    'bill_author_suffix' => '.',
    'bill_companyName' => 'Nom de la société&#160;:',
    'bill' => 'Facture',
    'bill_companyAddress' => 'Adresse de la société&#160;:',
    'bill_headline' => 'Intitulé des factures&#160;:',
    'bill_legal' => 'Mentions légales de bas de pages&#160;:',
    'bill_logo' => 'Logo de la société émettant la facture&#160;:',
    'bill_shippingTitle' => 'Livraison',
    'bill_comeTakeItText' => 'A l\'adresse ci-dessous',
    'bill_comeTakeItTextIntro' => 'Adresse de livraison :',
    'bill_customerService' => 'Service client :',
    'bill_customerServiceExplanation' =>
    '<div>Ce champs vous permet d\'ajouter jusqu\'a 3 lignes de texte dans l\'entête
des factures.<br /><br />
Le nombre de caractères est limité à une trentaine, environ (tous les caractères
ne prenant pas la même place. Si vous mettez plus de 25 caractères, n\'hésitez
pas à faire un test).
Ex :<br />
<textarea style="width:360px;">Service Client :
04 92 81 42 32
service-client@maboutique.com
</textarea>
</div>
',
    'bill_mailTitle' => ' - Confirmation de commande',
    'bill_color' => 'Couleur des factures :&#160;',
    'bill_colorExplanation' => '<div>
Une couleur est ajoutée à l\'arrière plan de certaines parties des factures.
Vous pouvez choisir cette couleur en la sélectionnant dans cette liste.</div>',
    'bill_table_ref' => 'Référence',
    'bill_table_product' => 'Produit',
    'bill_table_price' => 'PU',
    'bill_table_quantity' => 'Qté',
    'bill_table_totalPrice' => 'Total',
    'bill_AddressIntro' => 'Adresse de facturation :',
    'ship_AddressIntro' => 'Adresse de livraison :',
    'command_mail_headlineSender' => 'Bonjour.<br />Une commande a été passée sur le site&#160;:&#160;',
    'command_mail_headline' => 'Bonjour.<br />Vous avez passé une commande sur le site&#160;:&#160;',
    'command_mail_billSender' => 'Vous trouverez en pièce jointe la facture détaillée au format PDF.',
    'command_mail_signature' => 'Cordialement,<br />L\'équipe de Websailors',
    'command_mail_bill' => 'Vous trouverez en pièce jointe la facture détaillée au format PDF.<br /><br />Nous vous remercions de l\'attention que vous nous temoignez.',
    'bill_legalExplanation' => '<img src="/templates/global/admin/bill_legal_explanation.png" style="width:350px; height:93px;"/><br />
    Ici figurent les mention légales obligatoires à toute émission de facture.<br />Ces informations seront répétées sur toutes les pages.',
    'bill_headlineExplanation' => '<div>
<img src="/templates/global/admin/bill_headline_explanation.png" style="width:350px; height:218px;"/><br />
Vous indiquez ici des mentions telles que les délais de livraisons, de paiements
ou toute autre information que vous jugerez utiles.</div>',
    'bill_logoExplanation' => '<div>
<img src="/templates/global/admin/bill_logo_explanation.png" style="width:350px; height:218px;"/><br />
Choisissez ici le logo que vous souhaitez voir apparaître sur la facture.
Pour ce faire cliquez dans l\'encadré ci-dessous, puis selectionnez votre image.
Celle-ci sera automatiquement redimensionnée pour rentrer dans un format de 70x20mm
Si vous n\'en selectionnez aucune, cet espace apparaîtra blanc sur vos factures.</div>',
    'command_mailSent' => 'Votre commande a bien été prise en compte.<br />Une facture détaillée a été envoyée dans votre boîte mail. <br />Merci de la confiance que vous nous portez. ',
    'error_product_not_found_title' => 'Page introuvable',
    'error_product_not_found_content' => 'Le produit ou la catégorie <br /> à laquelle vous tentez d\'accéder <br /> n\'existe pas ou a été retiré(e).',
    'errorNocategory_title' => 'Erreur lors de l\\\'ajout du produit',
    'errorNocategory_content' => 'Une erreur est survenue lors de l\\\'ajout du produit.<br/>La produit n\\\'est associé à aucune catégorie',
    'productEditor_name' => 'Nom du produit&#160;:&#160;',
    'productEditor_reference' => 'Référence&#160;:&#160;',
    'productEditor_reference_explanation' => '<div>Quand les variantes sont activées, et si celles-ci gèrent les références,
        ce champs est indisponible. Les références doivent alors être entrées dans l\'onglet "Variantes du produit".</div>',
    'productEditor_description' => 'Description détaillée&#160;:',
    'productEditor_activateProductExplanation' => '<div>
Si la case d\'activation du produit n\'est pas cochée celui-ci n\'apparaîtra pas
dans votre boutique.<br />
Vous pouvez également activer ou désactiver un ou plusieurs produits via la
gestion des stocks.</div>',
    'productEditor_activateProduct' => 'Activer le produit  ',
    'productEditor_descriptionExplanation' => '<div>
La description détaillée est celle qui apparaîtra dans la fiche du produit. <br />
Toutes les informations techniques seront présentes ici.</div>',
    'productEditor_shortDescription' => 'Courte description&#160;:',
    'productEditor_shortDescriptionExplanation' => '<div>
La description courte est celle qui apparaîtra dans les listes de produits.<br />
Elle est très brève et renseigne sur les propriétés essentielles du produit.</div>',
    'productEditor_title' => 'Modifier un produit',
    'productEditor_price' => 'Prix&#160;:',
    'productEditor_price_explanation' => '
<diV>
    <span class="bold">Champs prix : </span> Vous ne devez entrer que le montant dans ce champs.<br />
    La monnaie utilisée, ainsi que la mention HT ou TTC est ajoutée automatiquement.<br />
    Quand les variantes sont activées, et si celles-ci gèrent les prix, ce champs est indisponible.
    Les prix doivent alors être entrés dans l\'onglet "Variantes du produit".<br /><br />
    <span class="bold">Taux de la taxe : </span> Shopsailors vous permet d\'avoir des taux de taxes différents
    suivant les produits que vous commercialisez.<br />
    Un taux général est choisi dans la page "Paramètres de la boutique". Pour utiliser un taux différent,
    saisissez le dans le champs présent ici.
</diV>',
    'productEditor_shipment' => 'Frais de port : ',
    'noShippingCost' => 'Ce produit n\'entraine aucun frais de port.',
    'noShippingCost_explanation' => '<div>
        Certains produits, comme les chèques cadeaux ou les invitations, peuvent ne pas entrainer de frais de ports
        supplémentaires.<br />
        Ainsi, si une commande qui ne contient que de tels produits est passée, l\'utilisateur n\'aura ni a choisir
        le mode d\'expédition, ni à régler les frais de ports correspondants.
        </div>',
    'productEditor_priceNote' => 'Saisissez le montant uniquement, la devise sera ajoutée automatiquement.',
    'productEditor_taxRate' => 'Taux de la taxe&#160;:',
    'productEditor_taxRateNote' => 'Saisissez-le en pourcentage (ex: 19.6 pour 19.6%).',
    'productEditor_stock' => 'Quantité disponible&#160;:',
    'productEditor_stock_explanation' => '
<diV>
    La gestion des stocks vous permet de ne vendre les produits que si vous les avez en stock.<br />
    Vous pouvez paramétrer la fonctionnalité dans "Paramètres de la boutique".<br />
    Quand les variantes sont activées, et si celles-ci gèrent les stocks comme quand les variantes des produits sont des produits
    physiquement différents (couleurs, capacités, etc), ce champs est indisponible.
    Les stcks doivent être alors entrés dans l\'onglet "Variantes du produit".
</diV>',
    'productEditor_image' => 'Aperçu du produit&#160;:',
    'productEditor_smallImages' => 'Autres images du produit :',
    'productEditor_clickHere' => 'Cliquez ici pour choisir une image',
    'productEditor_imageExplanation' => '<div>
Pour selectionner votre image, il suffit de cliquer sur l\'aperçu ci-dessous.
Vous accédez alors au navigateur de fichiers.<br />
Si l\'image que vous souhaitez utiliser est déjà présente, il suffit de cliquer
sur sa miniature pour la sélectionner. Si l\'image se trouve dans vos dossiers
personnels vous devez l\'ajouter (via le bouton "parcourir") puis "envoyer".
Une fois l\'image présente dans la liste, il vous suffira alors de cliquer sur
son aperçu pour la sélectionner.<br />
Elle sera automatiquement redimensionnée pour rentrer dans le format.</div>',
    'product_added_successfully' => 'Le produit a été ajouté avec succès.<br />
Vous pouvez continuer à en ajouter en remplissant à nouveau le formulaire, ou vous
pouvez accéder à la fiche du produit en cliquant sur le lien ci-dessous: ',
    'productEditor_variantsTitle' => 'Variantes',
    'productEditor_hasNoVariants' => 'Ce produit n\'a aucune variante.',
    'productEditor_hasVariants' => 'Ce produit a des variantes.',
    'productEditor_variantsChangePrice' => 'Les variantes ont des prix différents les unes des autres.',
    'productEditor_variantsChangeStock' => 'Les stocks sont différents d\'une variante à l\'autre.',
    'productEditor_variantsChangeRef' => 'Les références des produits sont différentes d\'une variante à l\'autre.',
    'hasVariants_explanation' => 'Les variantes  vous permettent de définir plusieurs versions (tailles, couleurs, ...) d\'un même produit.',
    'cutomProperties_used' => 'Pour ce produit, les variantes utilisent les propriétés personnalisées suivantes : ',
    'createCustomProperty' => 'Pour créer une propriété personnalisée, rendez vous sur la page d\'édition des propriétés personnalisées<br/>accessible par l\'onglet "boutique" de votre menu',
    'inactiveFields' => 'Si certains champs sont désactivés, c\'est qu\'ils sont utilisés dans les variantes du produit.',
    
    'Layout_new_linkName' => 'Nouvel habillage',
    'layout_edit' => 'Modifier',
    'layout_delete' => 'Supprimer',
    'layout_title' => 'Habillage',
    
    'productEditor_custumProperties' => 'Propriétés personnalisées',
    'customPropertiesEditor_title' => 'Propriétés personnalisées',
    'addCustomPropertiesEditor_title' => 'Ajouter une propriété personnalisée',
    'customProperties_edit' => 'Modifier',
    'customProperties_delete' => 'Supprimer',
    'customProperty_cant_deleted' => 'La propriété personnalisée ne peut être supprimée car elle est utilisée dans des fiches produits.',
    'customProperties_confirmDelete_title' => '\'Demande de confirmation\'',
    'customProperties_confirmDelete' => '\'Vous êtes sur le point de supprimer une propriété personnalisée. Voulez-vous continuer?\'',
    'editCustomPropertiesEditor_title' => 'Modifier une propriété personnalisée',
    'customProperties_notAny' => 'Il n\'y a pour le moment aucun propriété personnalisée de définie.',
    'customPropertyName' => 'Nom affiché :',
    'customPropertyName_explanation' =>
    '<div>Le nom que vous entrez dans ce champs sera celui affiché dans les fiches
produit.<br />
Ainsi, si vous entrez <input value="Poids" size="5"/>, la fiche produit
contiendra ceci :<br />
Poids : [poids entré dans la page d\'édition du produit]</div>',
    'customPropertyType' => 'Type de propriété :',
    'customPropertyType_explanation' =>
    '<div>Vous avez le choix entre plusieurs types de propriétés.
<ul>
<li>
    Texte libre :<br />
    La valeur de la propriété peut être n\'importe quel texte ou nombre.
    Lorsque vous remplirez un tel champs, les valeurs précédemment entrées
    seront automatiquement proposées.
</li>
<li>
    Liste de valeurs :<br />
    La valeur ne pourra être qu\'une de celles contenues dans le champs suivant.
    Lors de l\'édition de la fiche produit, une liste déroulante sera proposée
    avec ces propositions.<br />
    S\'il y a la possibilité que ce champs ne soit pas remplissable, vous pouvez
    intégrer la valeur NC, pour Non Communiqué.
</li>
</ul></div>',
    'customPropertyType_text' => 'Texte libre', // Présent dans customPropertyType_explanation
    'customPropertyType_list' => 'Liste de valeurs', // Présent dans customPropertyType_explanation
    'customPropertyList' => 'Liste des valeurs utilisables',
    'customPropertyList_explanation' => '<div>
Vous devez lister ici les différentes valeurs qui pourront être utilisées pour
remplir le champs.<br />
En plus des valeurs entrées ici, vous pourrez choisir de ne pas remplir le champs
(valeur non comuniquée), ou de ne pas afficher le champs du tout.</div>',
    'customPropertyActive' => 'Ce champs doit être actif par défaut',
    'customPropertyActive_explanation' => '<div>
Si vous souhaitez qu\'à la création d\'un nouveau produit, cette propriété soit
active, cochez cette case.<br />
Sinon, pour afficher cette propriété personnalisée dans la fiche produit, il
faudra cocher une case sur la fiche produit.</div>',
    'customProperty_new_linkName' => 'Nouvelle propriété personnalisée',
    'customProperty_deleted_successfully' => 'La propriété personnalisée a été supprimée avec succès.',
    'customProperty_saved_successfully' => 'La propriété personnalisée a été enregistrée avec succès.',
    'productEditor_categoriesTitle' => 'Catégories',
    'productEditor_categoriesExplanantion' => '<div>
Sélectionnez ici la ou les catégories dans lesquelles doit apparaître le produit.<br />
Vous devez impérativement sélectionner au moins une catégorie pour valider l\'ajout du produit.</div>',
    'categoryEditor_title' => 'Editeur de catégories',
    'categoryEditor_name' => 'Nom de la catégorie&#160;:',
    'categoryEditor_description' => 'Description détaillée:',
    'categoryEditor_is_root_no_disabling' => 'Cette catégorie étant la catégorie principale de votre
        boutique, elle ne peut être désactivée.<br />
        Si vous souhaitez désactiver votre boutique, rendez-vous sur la page 
        "Panneau d\'administration → Boutique → Gérer la boutique"',
    'categoryEditor_activateCategoryExplanation' => '<div>
Si la case d\'activation de la catégorie n\'est pas cochée
cellle-ci n\'apparaîtra pas dans votre boutique.<br />
Cela ne désactive pas pour autant son contenu, les produits et sous-catégories qu\'elle
contient seront toujours accessibles, particulièrement si ils appartiennent
à d\'autres catégories, mais cette page ne sera pas affichée directement, et ne sera
pas listée dans sa catégorie mère.<br /><br />
C\'est particulièrement utile pour créer une catégorie non affichée utilisée par
une promotion.</div>',
    'categoryEditor_activateCategory' => 'Activer la catégorie  ',
    'categoryEditor_descriptionExplanation' => '<div>
La description détaillée est celle qui apparaîtra dans la fiche de la catégorie. <br />
Toutes les informations générales sur sont contenu seront présentes ici.</div>',
    'categoryEditor_shortDescription' => 'Courte description&#160;:',
    'categoryEditor_shortDescriptionExplanation' => '<div>
La description courte est celle qui apparaîtra dans les listes de catégories.
Elle est très brève et renseigne sur les contenus principaux de la catégorie.</div>',
    'categoryEditor_image' => 'Aperçu de la catégorie&#160;:',
    'categoryEditor_clickHere' => 'Cliquez ici pour choisir une image',
    'categoryEditor_imageExplanation' => '<div>
Pour selectionner votre image, il suffit de cliquer sur l\'aperçu ci-dessous.
Vous accédez alors au navigateur de fichiers.<br />
Si l\'image que vous souhaitez utiliser est déjà présente, il suffit de cliquer
sur sa miniature pour la sélectionner.<br />
Sinon, vous pouvez l\'envoyer à partir de là dans le dossier choisi. Une fois
l\'image présente dans la liste, il vous suffira alors de cliquer sur son aperçu
 pour la sélectionner.<br />
Elle sera automatiquement redimensionnée pour rentrer dans le format.</div>',
    'product_added_successfully' => 'Le produit a été ajouté avec succès.<br />
Vous pouvez continuer à en ajouter en remplissant à nouveau le formulaire, ou vous
pouvez accéder à la fiche du produit en cliquant sur le lien ci-dessous: ',
    'categoryEditor_categoriesTitle' => 'Choix des catégories',
    'categoryEditor_noSubCategoryExplanation' => 'Cette catégorie est la catégorie
        principale du site.<br />Elle se trouve toujours tout en haut de l\'arborescence
        de la boutique.<br /><br />
        C\'est pourquoi vous ne pouvez pas la déplacer comme toutes les autres
        catégories.',
    'categories_isDescendant' => 'Cette catégorie est une sous-catégorie de celle que vous éditez. <br />
        Vous ne pouvez donc pas la sélectionner.',
    'categoryEditor_categoriesExplanantion' => '<div>
Sélectionnez ici la ou les catégories dans lesquelles doit apparaître la
nouvelle catégorie.<br />
Vous ne pourrez pas ajouter une sous categorie dans une catégorie qui contient
un produit.<br />
Si aucune catégorie n\'est séléctionnée, la nouvelle catégorie sera positionnée
au premier niveau des catégories.</div>
',
    'categoryEditor_discountsTitle' => 'Promotions',
    'discounts' => 'Promotions',
    'categoryEditor_discountsIntro' => 'Tous les produits contenus dans cette catégorie auront :',
    'categoryEditor_listDiscounts_none' => 'Aucune promotion',
    'categoryEditor_listDiscounts_those' => 'Les promotions suivantes : ',
    'categoryEditor_listDiscounts_those_intro' => 'Parmi les différentes promotions
        que vous aurez choisies ci-dessous, la promotion active la plus avantageuse
        pour le client sera utilisée.',
    'categoryEditor_discountNumber_before' => 'Promotions n°',
    'categoryEditor_discountNumber_after' => '&#160;:&#160;',
    'category_added_successfully' => 'La catégorie a été ajoutée avec succès.<br />
Vous pouvez continuer à en ajouter en remplissant à nouveau le formulaire, ou vous
pouvez accéder à la fiche de la catégorie en cliquant sur le lien ci-dessous&#160;:&#160;',
    'categoryShownBecauseAdmin' => 'Cette catégorie est désactivée. Elle ne s\'affiche que parce
        que vous êtes connecté en tant qu\'administrateur.<br />
        Les autres utilisateurs voient une page disant que cette catégorie n\'existe pas.',
    'cart_title' => 'Votre panier',
    'command_confirm_title' => 'Confirmez votre commande',
    'nav_next' => 'Page suivante',
    'nav_previous' => 'Page précédente',
    'bill_editBoxTitle' => 'Facturation',
    'bill_bottomText' => 'Bas de page&#160;:&#160;',
    'navigator_desc_name' => 'Description&#160;:&#160;',
    'navigator_variants_name' => 'Choix de la variante&#160;:&#160;',
    'navigator_stock_name' => 'Etat des stocks&#160;:&#160;',
    'product_price_from_before' => '<span class="product_price_before">A partir de </span>',
    'product_price_from_after' => '',
    'product_quantity' => 'Quantité :&#160;',
    'product_discount_text' => '&#160;avec promotions',
    'access_category' => 'Accéder',
    'listDiscounts_title' => 'Liste des promotions',
    'listDiscounts_new' => 'Nouvelle promotion',
    'productsTable_name' => 'Nom',
    'productsTable_desc' => 'Description',
    'productsTable_stock' => 'Stock',
    'productsTable_price' => 'Prix',
    'reference_title' => 'Reference&#160;:&#160;',
    'pictoAddToCart' => 'Ajouter au panier',
    'pictoAddToCart_selectVariant' => 'Sélectionnez une variante pour pouvoir l\'ajouter au panier',
    'pictoSendmail' => 'Envoyer par e-mail',
    'pictoShow' => 'Consulter la fiche produit',
    'pictoModify' => 'Modifier la fiche produit',
    'pictoActivate' => 'Activer / Désactiver le produit',
    'inactiveProducts_title' => 'Liste des produits désactivés',
    'inactiveProducts_empty' => 'Tous les produits de votre boutique sont actfs.',
    'selectAcategory' => 'Choisissez une categorie&#160;:',
    'selectAproduct' => 'Choisissez un produit&#160;:&#160;',
    'inSameCategory' => 'Dans la même catégorie&#160;:&#160;',
    'showAllProducts_title' => 'Liste de tous les produits',
    'showAllProductsCategories_title' => 'Liste des catégories de produits',
    'chooseAcategory' => 'Après avoir sélectionné une catégorie, vous aurez accès à une seconde boîte au dessous<br />
comprenant la liste de tous les produits compris dans la catégorie sélectionnée.',
    'activate_products_from_list' => 'Si vous le souhaitez, vous pouvez activer ou désactiver vos fiches produits directement
à partir d\'ici.<br />
Il suffit pour cela de cocher ou de décocher les cases précédant le nom du produit.<br />
Notez que lorsque vous désactivez un produit dans une catégorie donnée,<br />il disparaîtra de toute les autres catégories auxquelles il pourrait appartenir.<br />
Ce formulaire ne contient pas de bouton "enregistrer"; les modifications sont prises en compte immédiatement.',
    'showAllProducts_product' => 'Nom du produit',
    'showAllProducts_category' => 'Catégorie',
    'showAllProducts_Active' => 'Etat',
    'showAllProducts_link' => 'Lien',
    'showAllProducts_linktext' => 'Accéder à la fiche',
    'showAllProducts_activate_products' => 'Activez ou désactiver le ou les produits&#160;:',
    'hideNullQuantityProducts' => 'Cacher les produits qui ne sont pas en stock',
    'shopNotActive' => 'La boutique n\'est pas accessible.',
    'search_categoriesTitle' => 'Catégories de la boutique',
    'search_productsTitle' => 'Produits de la boutique',
    'search_shopTitle' => 'Catégories et produits de la boutique',
    'payment_providerName' => 'Type de prestation / Nom du prestataire :',
    'editPaymentModesTitle' => 'Modes de paiement',
    'show_PaymentModesTitle' => 'Modes de paiement',
    'pageName_showPaymentModes' => 'Page d\'affichage des modes de paiement',
    'payment_showPaymentInLegacy' => 'Afficher un lien dans les mentions légales',
    'payment_showPaymentInLegacy_explanation' => '<div>
Cette option vous permet d\'afficher un lien dans la ligne des mentions légales,
en bas de chaque page, et de générer la page associée, décrivant les modes de
paiement disponibles.<br /><br />
Pour une plus grande transparence, et pour répondre aux impératifs du commerce
en ligne, cette option doit être cochée.</div>',
    'paymentMode_add' => 'Ajouter un mode de paiement',
    'paymentMode_remove' => 'Supprimer ce mode de paiement',
    'paymentLogo' => 'Logo :',
    'paymentLogo_explanation' => '<div>Choisissez le logo du mode de paiement.
Cela permettra à vos clients de se repérer plus rapidement.<br />
Ce logo doit être placé dans le dossier nommé "small" que vous trouverez dans
votre explorateur d\'images.<br />
Vous pouvez y accéder directement en cliquant sur l\'image ci-après.</div>',
    'paymentDescription' => 'Description :',
    'paymentDescription_explanation' => '<div>
Vous pouvez entrez ici une courte description du service fourni.<br />
Par exemple:<br />
Banque Postale.</div>
',
    'payment_activateMode' => 'Accepter ce mode de paiement dans la boutique',
    'choose_paymentModes' => 'Cliquez sur le mode de paiement que vous voulez utiliser :',
    'payment_not_ready' => 'Ce mode de paiement n\'est pas paramétré, il ne peut donc être accepté dans la boutique.',
    'payment_manage' => 'Paramétrer ce mode de paiement',
    'shipping_not_needed' => 'Aucun produit de votre panier n\'entraine de frais de port.',
    'shipModes_title' => 'Modes de livraisons',
    'shipMode_newTitle' => 'Nouveau mode de livraison',
    'addShipMode' => 'Ajouter un mode de livraison',
    'ship_providerName' => 'Nom du prestataire :',
    'editShipModesTitle' => 'Modes d\'expédition',
    'activateShipping' => 'Activer le module d\'expédition',
    'activateShipping_explanation' => '<div>
Ce bouton vous permet d\'activer le module d\'envoi des commandes.<br />
Ce module est facultatif, mais si le module de panier est activé, il vous permet
de prendre en considération les modes d\'expédition ainsi que leurs coûts, qui
seront répercutés sur la facture du client.<br />
Si le module de panier est désactivé, ce module, activé ou pas, ne sera pas utilisé.</div>',
    'ship_showExpeditionInLegacy' => 'Afficher un lien dans les mentions légales',
    'ship_showExpeditionInLegacy_explanation' => '<div>
Cette option vous permet d\'afficher un lien dans la ligne des mentions légales,
en bas de chaque page, et de générer la page associée, décrivant les modes de
livraisons, ainsi que leur coûts pour le client.<br /><br />
Pour une plus grande transparence, et pour répondre aux impératifs du commerce
en ligne, cette option doit être cochée.<br /><br />
Si le module est désactivé (case à cocher "Activer le module d\'expédition"),
l\'option sera dans tous les cas désactivée.</div>
',
    'activateMode' => 'Activer ce mode de livraison',
    'shipPrice' => 'Montant des frais de port :',
    'ship_comeTakeIt_price' => 'Frais :',
    'ship_validMode' => 'Valider le mode de livraison',
    'shipPriceRules_title' => 'Réductions',
    'shipPriceRules' => 'Réductions sur l\'expédition accordées en fonction du
montant des factures :',
    'shipPriceRules_explanation' => '<div>
Vous avez la possibilité de faire varier les prix auxquels vous facturez la
livraison en fonction du montant de la facture du client.<br />
Ainsi, il est possible d\'avoir un tarif général de 20€ par envoi, de faire une
remise de 50% à partir de 80€ d\'achats, et d\'offrir intégralement les frais de
port pour les commandes dont le montant est supérieur ou égal à 150€.<br /><br />
Vous pouvez utiliser jusqu\'à 3 règles de prix pour chaque mode d\'exédition,
ce qui, avec le prix de base, vous permet d\'obtenir 4 tarifs différents.<br /><br />
Dans la 1ère case, entrez le montant minimal de commande pour obtenir la
réduction, et dans la 2ème case, entrez soit:
<ul><li>le montant de la réduction en chiffre avec éventuellement 
        le symbole de la monnaie, pour obtenir une réduction directe
        (par exemple 5€, ou juste 5)</li>
    <li>le pourcentage de réduction sur les frais de livraison, en
        chiffre, et se finissant par le caractère % (par exemple 50%).</li></ul>
</div>
',
    'shipMode_add' => 'Ajouter un nouveau mode de livraison :',
    'new' => 'Nouveau',
    'delete' => 'Supprimer',
    'shipMode_remove' => 'Supprimer ce mode de livraison',
    'shipLogo' => 'Logo :',
    'shipLogo_explanation' => '<div>Choisissez le logo du livreur.<br />
Cela permettra à vos clients de se repérer plus rapidement.<br />
Ce logo doit être placé dans le dossier nommé "small" que vous trouverez dans
votre explorateur d\'images.<br />
Vous pouvez y accéder directement en cliquant sur l\'image ci-après.</div>',
    'shipDescription' => 'Description :',
    'shipDescription_explanation' => '<div>
Vous pouvez entrez ici une courte description du service fourni.<br />
Par exemple:<br />
Livraison en 72h à votre domicile ou sur votre lieu de travail.</div>
',
    'ship_comeTakeIt' => 'Proposer au client de venir récupérer sa commande à l\'une
des adresses suivantes :',
    'ship_multiAddress_note' => 'Pour entrer plusieurs adresses, sautez une ligne entre chaque.',
    'ship_comeTakeIt_explanation' => '<div>
Vous pouvez entrer ici une liste d\'adresses auxquelles le client pourra venir
retirer sa commande.<br />
La liste s\'affichera dans le même ordre que celui que vous entrerez dans le
champs suivant.<br /><br />
Pour séparer les adresses, il vous suffit de laisser une ligne vide entre chaque.<br />
Exemple :<br />
<textarea style="width:95%;height:130px;">
Ma boutique
37 bvd République
75000 Paris

Ma boutique
84 place de la poste
37155 Chateauneuf

Ma boutique
...
</textarea></div>',
    'shipPriceRule0' => '
A partir de
<RENDER_VALUE what="constants:currencyBefore"/>
<input name="shipRulePrice0" value="{ship:rulePrice0}" style="width:50px;"/>&#160;
<RENDER_VALUE what="constants:currencyAfter"/>
d\'achats, la réduction est de 
<input name="shipRulediscount0" value="{ship:rulediscount0}" style="width:50px;"/>.',
    'shipPriceRule1' => '
A partir de
<RENDER_VALUE what="constants:currencyBefore"/>
<input name="shipRulePrice1" value="{ship:rulePrice1}" style="width:50px;"/>&#160;
<RENDER_VALUE what="constants:currencyAfter"/>
d\'achats, la réduction est de
<input name="shipRulediscount1" value="{ship:rulediscount1}" style="width:50px;"/>.',
    'shipPriceRule2' => '
A partir de
<RENDER_VALUE what="constants:currencyBefore"/>
<input name="shipRulePrice2" value="{ship:rulePrice2}" style="width:50px;"/>&#160;
<RENDER_VALUE what="constants:currencyAfter"/>
d\'achats, la réduction est de
<input name="shipRulediscount2" value="{ship:rulediscount2}" style="width:50px;"/>.',
    'ship_discountIntro' => 'En fonction du montant de votre facture vous bénéficierez de réductions sur les frais de livraison&#160;:',
    'ship_discount0' => 'A partir de <RENDER_VALUE what="discount0:price"/>&#160;:&#160;
<RENDER_VALUE what="discount0:discount"/>',
    'ship_discount1' => 'A partir de <RENDER_VALUE what="discount1:price"/>&#160;:&#160;
<RENDER_VALUE what="discount1:discount"/>',
    'ship_discount2' => 'A partir de <RENDER_VALUE what="discount2:price"/>&#160;:&#160;
<RENDER_VALUE what="discount2:discount"/>',
    'ship_discountNotAccumulative' => 'Les réductions sur les frais d\'expéditions
ne sont pas cumulables.',
    'ship_theBiggestdiscountMakesTheLaw' => 'Dans le cas où plusieurs réductions sont applicables, 
la plus grande sera appliquée.',
    'ship_chooseShipper_title' => 'Choix du mode de livraison',
    'comeTakeIt_chooseAddress' => 'Choisissez l\'adresse où récupérer votre commande :',
    'comeTakeIt_singleAddress' => 'Votre commande sera disponible à l\'adresse suivante :',
    'shipper_is' => 'La livraison est assurée par :',
    'free' => 'Gratuit',
    'comeTakeIt_free' => 'Cette prestation est gratuite.',
    'comeTakeIt_cost' => 'Cette prestation vous est facturée
<span class="bold" style="margin-top:10px;">
    <RENDER_VALUE what="comeTakeIt:price"/><RENDER_VALUE what="shipMode:price"/>
</span>.',
    'chooseShippingMode' => 'Pour l\'expédition, nous vous proposons les modes suivants :',
    'chooseToComeAndTakeIt' => 'Aller chercher la commande dans un dépot.',
    'chooseToComeAndTakeIt_chooseIt' => 'Choix du lieu où aller chercher
    la commande :',
    'comeTakeIt_youCanChooseAddress' => 'Vous pouvez choisir d\'aller chercher
votre commande à l\'une des adresses suivantes&#160;:',
    'command_changeShipModeLink' => 'Changer de mode d\'expédition',
    'command_comeTakeItTitle' => 'Disponibilité à l\'adresse :',
    'command_shippingTitle' => 'Livraison :',
    'command_requires_connection' => 'Vous devez être connecté pour passer votre commande',
    'paymentModes_title' => 'Modes de paiement acceptés',
    'breadCrumbs_sameCategoryText' => 'Dans la même catégorie&#160;: ',
    'billing_list' => 'Liste des factures',
    'billing_details' => 'Détails de la facture',
    'billing_empty' => 'Aucune facture n\'est enregistrée',
    'bill_link' => 'Voir les détails de la facture',
    'bill_pdf' => 'Télécharger PDF',
    'bill_delete' => 'Supprimer cette commande',
    
    'bill_delete_title' => 'Confirmation de la suppression d\'une facture',
    'bill_delete_message' => 'Attention! Cette opération ne pourra pas être annulée.<br />
        De plus, le fait de supprimer une facture n\'annulera pas les paiements effectués. Il faut
        pour cela contacter l\'organisme ou le système par lequel le paiement a été effectué.',
    'bill_delete_validation' => 'Supprimer',
    
    'billList_num' => 'N°',
    'billList_date' => 'Date',
    'billList_name' => 'Client',
    'billList_ht' => 'Total HT',
    'billList_ttc' => 'Total TTC',
    'customerid' => 'N° client',
    'billId' => 'Facture n°',
    'emission' => 'Emise le:&#160;',
    'customer' => 'Client&#160;:&#160;',
    'price' => 'Prix',
    'ref' => 'Référence',
    'stock' => 'Stock',
    'quantity' => 'Quantité',
    'total' => 'Total',
    'paymentmode' => 'Mode de paiement:&#160;',
    'billingaddress' => 'Adresse de facturation:&#160;',
    'billing_address_title' => 'Adresse de facturation',
    'billingaddress2' => 'Adresse de facturation',
    'shippingaddress' => 'Adresse de livraison:&#160;',
    'shipping_address_title' => 'Adresse de livraison',
    'shippingaddress2' => 'Adresse de livraison',
    'error_title' => 'Erreur - données manquantes',
    'error_intro' => 'Tous les champs n\'ont pas été remplis correctement.<br />
        La commande n\'a donc pas encore été validée.',
    'error_nopaymentmode' => 'Aucun mode de paiement n\'a été sélectionné.',
    'error_noBillingAddress' => 'Vous n\'avez pas entré votre adresse de facturation, ou celle-ci n\'est pas complète.
        Les champs obligatoires sont : Nom, Code Postal, et Ville.',
    'error_noShippingAddress' => 'Vous n\'avez pas entré votre adresse de livraison.',
    'error_noBillingMail' => 'Vous n\'avez pas entré votre adresse email d\'envoi de la facture,
        ou son format n\'est pas reconnu.',
    'error_noBillingPhone' => 'Vous n\'avez pas entré votre numéro de téléphone,
        ou son format n\'est pas reconnu.',
    'before_selling_disactivation_no_payment_mode' => '
<div>Attendtion : Ce moyen de paiement est le seul à être activé sur votre site.<br />
Si vous le désactivez, les modules de panier et de prise de commande de la boutique
seront désactivés aussi.<br />
Rien ne sera perdu pour autant, après avoit réactivé un mode de paiement, il vous
suffira de réactiver le panier dans les paramètres de la boutique.</div>',
    'selling_disactivated_no_payment_mode' => '
<div>Le panier et la prise de commande de la boutique ont été désactivés, car aucun moyen
de paiement n\'est activé sur le site.<br />
Pour réactiver ces fonctionnalités, il suffit d\'activer un mode de paiement,
et d\'aller réactiver la fonctionnalité dans les paramètres de la boutique.</div>',
    'ss' => "",
// SEO
    'seo_title' => 'Référencement',
    'seo_explanations' => 'Pour améliorer votre référecement, vous pouvez entrer ici
        des informations concernant le contenu (mots clés).',
    'seo_titleBar' => 'Titre de l\'onglet du navigateur : ',
    'seo_metaDescription' => 'Méta description : ',
    'seo_product_titleBar_explanations' => 'Si ce champs est vide, le nom du produit sera utilisé.',
    'seo_product_metaDescription_explanations' => 'Si ce champs est vide, la courte description de la page sera utilisée.',
    'seo_category_titleBar_explanations' => 'Si ce champs est vide, le nom de la catégorie sera utilisé.',
    'seo_category_metaDescription_explanations' => 'Si ce champs est vide, le texte d\'introduction de la page sera utilisée.',
    'billings_tab_title' => 'Commandes',
    'billings_list_title' => 'Liste des commandes / factures',
    'billings_list_year' => 'Année : ',
    'billings_list_month' => 'Mois : ',
    'billings_list_date' => 'Date : ',
    'billings_list_details' => '<a href="{elements:link}">Commande n°<RENDER_VALUE what="elements:id"/></a><br />
        Heure de la validation : <RENDER_VALUE what="elements:time"/>',
    'yourBillingList' => 'Voici la liste des commandes correspondant aux commandes que vous avez effectuées sur ce site.<br /><br />
        Vous pouvez accéder aux détails ou télécharger le pdf en cliquant sur le numéro de la facture en début de ligne.',
    'empty' => 'NC',
    'inactiveProducts' => 'Liste des produits inactifs',
    'accountCreation_getBackToCommand' => 'Une fois la création de votre compte validée, cliquez ici pour
        retourner à la page de votre commande',
    'accountCreation_getBackToCart' => 'Retourner à la page du panier',
    'discount_none' => 'Aucune promotion',
    'discount_existing' => 'Utiliser une promotion existante : ',
    'discount_type_title' => 'Type de promotion',
    'discount_type_title' => 'Type de promotion',
    'discount_title' => 'Promotion',
    'discount_texts_title' => 'Affichage',
    'discountEditor_texts_intro' => 'Dans les catégories de produits et les fiches
        produits pour lesquelles cette promotion est activée, une boite de description
        de la promotion sera affichée.',
    'discountEditor_title' => 'Titre de la promotion : ',
    'discountEditor_description_categories' => 'Description de la promotion pour l\'affichage dans les catégories : ',
    'discountEditor_description_product' => 'Description de la promotion pour l\'affichage dans les fiches produits : ',
    'discount_new' => 'Créer une nouvelle promotion : ',
    'discount_new_title' => 'Nouvelle promotion',
    'discount_new_name' => 'Nom de la nouvelle promotion : ',
    'discount_new_when' => 'Cette promotion s\'applique ',
    'discount_new_when_always' => 'tout le temps',
    'discount_new_when_from' => 'du&#160;',
    'discount_new_when_to' => '&#160;au&#160;',
    'discount_from' => 'Quantité minimale pour accéder à la promotion : ',
    'discount_percents_before' => '',
    'discount_percents_after' => '% de réduction sur ces produits',
    'discount_monney_before' => 'Réduction du montant total de&#160;',
    'discount_monney_after' => '€ sur le prix de ces produit',
    'discount_gift_beginning' => '',
    'discount_gift_middle' => ' produit(s) de la catégorie suivante offert :&#160;',
    'discount_gift_end' => '',
    'discount_gift_monney_before' => 'Pour&#160;',
    'discount_gift_monney_after' => '€ de plus,&#160;',
    'discount_gift_samples' => '<div>
        Exemples :<br />
        Pour <span class="bold">2</span>€ de plus, <span class="bold">2</span>
        produit(s) de la catégorie "<span class="bold">carte>boissons</span> " offert(s).<br />
        Pour <span class="bold">0</span>€ de plus, <span class="bold">1</span>
        produit(s) de la catégorie "<span class="bold">cadeaux>accessoires</span> " offert(s)
        (dans ce cas, sur la fiche produit, il sera écrit uniquement "1 produit 
        de la liste suivante offert pour l\'achat de X produits".
</div>',
    'discount_error_needName' => 'Afin de pouvoir la sélectionner, la promotion doit
        avoir un nom.<br />De plus, afin de ne pas les confondre, ce nom doit être unique.',
    'discount_error_toPast' => 'La date de fin de l\'offre est passée...',
    'discount_error_dayOfWeekNotSeletced' => 'Vous n\'avez pas sélectionné les jours
        de la semaine auxquels l\'offre est active.',
    'discount_error_needPercents' => 'Le pourcentage de réduction doit être compris entre 0 et 100.',
    'discount_error_needMonney' => 'Le montant de la réduction doit être un nombre supérieur à 0.',
    'discount_error_needGiftQuantity' => 'Le nombre de produits offerts doit être supérieur à 0.',
    'discount_days_title' => 'Pendant cette période, la promotion s\'applique :',
    'discount_everyday' => 'Tous les jours de la semaine',
    'discount_someDays' => 'En fonction du jour de la semaine : ',
    'buy' => 'Acheter',
    'packTitle' => 'Offre spéciale :&#160;',
    'packsList_title' => 'Liste des lots de produits',
    'packsList_active_tab' => 'Lots actifs',
    'packsList_inactive_tab' => 'Lots non actifs',
    'packsList_addPack' => 'Créer un nouveau lot',
    'packsList_editPack' => 'Modifier ce lot',
    'packsList_showPack' => 'Afficher ce lot',
    'packsList_empty' => 'Il n\'y a aucun lot à afficher.',
    'editPack_title' => 'Lots de produits',
    'editPack_firstTab' => 'Général',
    'editPack_name' => 'Nom du pack :',
    'editPack_name_explanations' => '<div>
    Le nom du lot est affiché sur la page sur laquelle les utilisateurs choisissent
    ou valident la composition de leur lot.<br />
    Ce nom n\'est pas celui affiché sur les fiches produits. En effet, le texte
    qui y est affiché peut être différent d\'un produit à l\'autre.<br />
    Exemple : Pack 1 écran + 1 unité centrale + 1 clavier + 1 souris :<br />
    Sur la page de l\'écran, on pourra lire :
    <div class="italic">Si vous achetez en plus cette unité centrale, votre clavier est votre
    souris ne vous couteront que 5€. <span class="underline">Acheter ce pack</span></div>
    </div>',
    'editPack_state' => 'Activer ce pack',
    'editPack_state_explanations' => '<div>Si le pack est activé, il sera accessible à partir
        de tous les produits listés dans "Produits principaux", sinon, il ne
        sera pas en vente dans la boutique.</div>',
    'editPack_addCost' => 'Cout supplémentaire :',
    'editPack_totalCost' => 'Cout total :',
    'editPack_secondTab' => 'Produits principaux',
    'editPack_secondTab_intro' => 'Les produits principaux sont ceux pour lesquels 
        la pack est affiché dans la fiche produit.<br />
        Vous pouvez créer des packs sur certains produits, ou sur tous les produits
        de certaines catégories.
        ',
    'editPack_prod_cat' => 'Produit / Catégorie',
    'editPack_quantity' => 'Qté',
    'editPack_gift' => 'Offert?',
    'editPack_gift_explanations' => '<div>
        <span class="bold">Si cette case est cochée</span>, le prix du produit sélectionné n\'entrera pas
        en compte pour le calcul du prix du lot. Ceci est utilisé pour les produits 
        offerts.<br />
        <span class="bold">Si cette case n\'est pas cochée</span>, le prix du lot sera augmenté du prix de ce produit.
        <hr />
        Exemple : Pour l\'achat d\'un ordinateur complet (écran + unité centrale), 
        une souris et un clavier sont proposés pour 5€. Dans ce cas, cette case est cochée pour
        le clavier et pour la souris, mais pas pour l\'écran, ni pour l\'unité centrale.<br />
        Le prix du pack sera donc : 5€ + prix de l\'écran + prix de l\'unité centrale.
</div>',
    'editPack_text' => 'Texte affiché sur la fiche produit',
    'editPack_link' => 'Lien?',
    'editPack_link_explanations' => 'Cochez cette case si vous voulez que dans la fiche produit, un lien soit fait vers ce pack.',
    'editPack_chooseProdOrCat' => 'Choix du produit ou de la catégorie',
    'editPack_addLine' => 'Ajouter',
    'editPack_thirdTab' => 'Produits supplémentaires',
    'editPack_thirdTab_intro' => 'Les produits secondaires sont ceux pour lesquels
        la pack n\'est pas affiché dans la fiche produit.',
    'editPack_' => '',
    'editPack_' => '',
// Facebook
    'facebook_categories' => 'Boutique en ligne - Pages d\'affichage des catégories',
    'facebook_products' => 'Boutique en ligne - Pages d\'affichage des produits',
    
// Layout adminPage
    'layoutEditor_title' => 'Habillage de la boutique',
    'defaultLayout' => '
        Cet habillage est l\'habillage par défaut. Il ne peut être supprimé, et est utilisé pour toutes
            les catégories et tous les produits du site, à condition qu\'ils n\'aient pas d\'autre habillage prévu.
        ',
    'layoutExplanations' => '
        L\'habillage vous permet de personnaliser chaque catégorie de votre boutique. 
        Créez ou modifiez vos habillages ici, puis rendez vous sur la page d\'administration de la catégorie 
        dans laquelle vous souhaitez afficher votre habillage. Puis sélectionnez-le dans l\'onglet "Habillage".
',
    'external_datas_title' =>'Informations complémentaires',
    'choose_payment_title' =>'Sélection du mode de paiement',
    
);
