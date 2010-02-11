<?php

$i18n = array(
    'className' => 'Boutique',
    'chooseProduct' => 'Produit&#160;:&#160; ',
    'chooseCategory' => 'Catégorie&#160;:&#160; ',

    'subLevel_title' => 'Affinez votre recherche dans les sous catégories&#160;:&#160; ',
    'navigator_title' => '',
    'list_view' => 'aperçu en liste',
    'table_view' => 'aperçu en tableau',
    'miniature_view' => 'aperçu en miniatures',
    'products_list' => 'Liste des articles de la catégorie&#160;:&#160; ',
    'categories_title' => 'Categories',
    'product_nomorestock' => 'Les stocks sont épuisés.',
    'product_stocknotsufficient' => 'Il n\'y a pas assez d\'unités dans les stocks par rapport à la quantité demandée.<br />
Celle-ci a donc été abaissée à la quantité disponible.',    
    'product_noMoreTakenOut' => 'Le produit n\'est plus disponible, il a été retiré de votre panier.',
    'submit_command'=>'Passer la commande',
    'command_confirm'=>'Confirmer la commande',
    'command_billing'=>'Facturation et livraison',
    'command_justBilling'=>'Facturation',
    'billing_enterAdress'=>'Entrez l\'adresse de facturation',
    'command_billing_name'=>'Nom :',
    'command_billing_address'=>'Adresse :',
    'command_billing_zipCode'=>'Code postal :',
    'command_billing_city'=>'Ville :',
    'command_billing_delivery'=>'Livraison :',
    'command_billingAddress'=>'Livrer à l\'adresse de facturation',
    'command_followingAddress'=>'Livrer à l\'adresse suivante&#160;:',
    'payment_title'=>'Mode de paiement',
    'payment_oneMode'=>'Le paiement est effectué par&#160;:',
    'payments_proposed'=>'Lors de la prise de commande vous pourrez choisir entre ces différents mode de paiment.',
    'payments_oneMode'=>'Les règlements des commandes sont effectués comme décrit ci-dessous:',

    'editTitle' => 'Paramètres de la boutique.',
    'activateShop' => 'Activer la boutique en ligne.',
    'activateCart' => 'Activer le panier. ',
    'activateMail' => 'Activer l\'envoi de la fiche produit par mail.',
    'activateCom' => 'Activer la rédaction de commentaire par les internautes.',
    'activateSendCost'=> 'Activer les frais de port.',
    'sendCost' => 'Montant des frais de port&#160;:&#160; ',
    'showQuantity' => 'Afficher la quantité disponible pour les produits.',
    'showDeliveriesMode' => 'Afficher les modes de livraisons.',
    'command_mail' => 'Indiquez la ou les adresses mail auxquelles vous desirez recevoir les ordres de commandes.',
    'command_mailNote' => 'Si vous saisissez plusieurs adresses, passez à la ligne pour chacunes d\'elles.',

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
    'prices_taxRate' => 'Taux de la taxe : ',
    'prices_showTaxSymbol' => 'Afficher le symbole HT ou TTC après les prix',
    'prices_showTaxSymbol_explanation' => '<div>
En cochant cette case, les prix affichés sur le site contiendront tous la
mention HT (pour Hors Taxes) ou TTC (pour Toutes Taxes Comprises).
Notez que les règlementations française et européenne stipulent que la
mention Hors Taxes doit être signalée explicitement, le cas échéant.</div>',
    
    'activateCart_explanation' => '<div>
Si cette case n\'est pas cochée, le panier est inactif, il vous sera impossible
de prendre une commande, votre site n\'aura alors qu\'une fonction de vitrine
ou de présentation d\'activité.</div>',
    'emptyCart' => 'Votre panier est vide.',
    'emptyCategory' => 'Cette catégorie est vide.',
    'cart_yourCommand' => 'Votre commande',
    'cart_removeProduct' => 'Retirer le produit de la liste',
    'cart_name'=>'Nom',
    'cart_shortDescription'=>'Description',
    'cart_stock'=>'Quantité disponible',
    'cart_quantity'=>'Quantité',
    'cart_price'=>'Prix unitaire <RENDER_VALUE what="constants>taxes"/>',
    'cart_productTotalPrice'=>'Prix total <RENDER_VALUE what="constants>taxes"/>',
    'cart_update'=>'Mettre à jour le formulaire',
    'cart_command'=>'Passer la commande',
    'cart_reference'=>'Reference',
    'cart_save'=>'Sauvegarder le panier',
    'cart_saved_successfully'=>'Votre panier a été sauvegardé avec succès.<br />
Vous pourrez le recharger lors d\'une prochaine visite en vous connectant à votre
compte, et en suivant le lien "<RENDER_VALUE what="i18n>cart_load"/>".',
    'cart_load'=>'Charger votre panier enregistré',
    'cart_alreadyExistingCart'=>'Vous avez déjà un panier enregistré.<br />
        Souhaitez vous :<br />',
    'totalht'=>'Total HT: ',
    'totalttc'=>'Total TTC: ',

    'pageName_cart_show'=>'Page d\'affichage du panier',

    'pageName_showShipModes'=>'Page d\'affichage des modes de livraisons',
    'bill_companyName' => 'Nom de la société&#160;:',
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
    
    'command_mail_headlineSender' => 'Bonjour.<br />Une commande a été passée sur le site&#160;:&#160;',
    'command_mail_headline' => 'Bonjour.<br />Vous avez passé une commande sur le site&#160;:&#160;',
    'command_mail_billSender' => 'Vous trouverez en pièce jointe la facture détaillée au format PDF.<br /><br />Cordialement,<br />L\'équipe de Websailors',
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
    
    'command_mailSent'=>'Votre commande a été bien prise en compte.<br />Une facture détaillée a été envoyée dans votre boîte mail. <br />Merci de la confiance que vous nous portez. ',
    
    'error_product_not_found_title'=>'Page introuvable',
    'error_product_not_found_content'=>'Le produit ou la catégorie <br /> à laquelle vous tentez d\'accéder <br /> n\'existe pas ou a été retiré(e).',
    'errorNocategory'=>'Une erreur est survenue lors de l\'ajout du produit.<br/>La produit n\'est associé à aucune catégorie',
    'productEditor_name'=>'Nom du produit&#160;:&#160;',
    'productEditor_reference'=>'Référence&#160;:&#160;',
    'productEditor_description'=>'Description détaillée&#160;:',
    'productEditor_activateProductExplanation'=>'<div>
Si la case d\'activation du produit n\'est pas cochée celui-ci n\'apparaîtra pas
dans votre boutique.<br />
Vous pouvez également activer ou désactiver un ou plusieurs produits via la
gestion des stocks.</div>',
    'productEditor_activateProduct'=>'Activer le produit  ',
    'productEditor_descriptionExplanation'=>'<div>
La description détaillée est celle qui apparaîtra dans la fiche du produit. <br />
Toutes les informations techniques seront présentes ici.</div>',
    'productEditor_shortDescription'=>'Courte description&#160;:',
    'productEditor_shortDescriptionExplanation'=>'<div>
La description courte est celle qui apparaîtra dans les listes de produits.<br />
Elle est très brève et renseigne sur les propriétés essentielles du produit.</div>',
    'productEditor_title'=>'Modifier un produit',
    'productEditor_price'=>'Prix&#160;:',
    'productEditor_priceNote'=>'Saisissez le montant uniquement, la devise sera ajoutée automatiquement.',
    'productEditor_stock'=>'Quantité disponible&#160;:',
    'productEditor_image'=>'Aperçu du produit&#160;:',
    'productEditor_smallImages'=>'Autres images du produit :',
    'productEditor_clickHere'=>'Cliquez ici pour choisir une image',
    'productEditor_imageExplanation'=>'<div>
Pour selectionner votre image, il suffit de cliquer sur l\'aperçu ci-dessous.
Vous accédez alors au navigateur de fichiers.<br />
Si l\'image que vous souhaitez utiliser est déjà présente, il suffit de cliquer
sur sa miniature pour la sélectionner. Si l\'image se trouve dans vos dossiers
personnels vous devez l\'ajouter (via le bouton "parcourir") puis "envoyer".
Une fois l\'image présente dans la liste, il vous suffira alors de cliquer sur
son aperçu pour la sélectionner.<br />
Elle sera automatiquement redimensionnée pour rentrer dans le format.</div>',
    'product_added_successfully'=>'Le produit a été ajouté avec succès.<br />
Vous pouvez continuer à en ajouter en remplissant à nouveau le formulaire, ou vous
pouvez accéder à la fiche du produit en cliquant sur le lien ci-dessous: ',
    'productEditor_custumProperties' => 'Propriétés personnalisées',
    
    'customPropertiesEditor_title' => 'Propriétés personnalisées',
    'addCustomPropertiesEditor_title' => 'Ajouter une propriété personnalisée',
    'customProperties_edit' => 'Modifier',
    'customProperties_delete' => 'Supprimer',
    'customProperties_confirmDelete' => 'Vous êtes sur le point de supprimer une propriété personnalisée. Voulez-vous continuer?',
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
    Texte libre sans traduction :<br />
    La valeur de la propriété peut être n\'importe quel texte ou nombre.
    Lorsque vous remplirez un tel champs, les valeurs précédemment entrées
    seront automatiquement proposées.<br />
    La valeur de ce champs est la même quelle que soit la langue d\'affichage.
    Ainsi, on l\'utilisera de préférence pour les valeurs numériques ou les
    normes, qui ne sont pas traduites.
</li>
<li>
    Texte libre avec traduction :<br />
    Ce type champs se comporte comme le précédent, avec en plus la liste
    déroulante proposant les drapeaux des langues actives, de manière à pouvoir
    traduire les valeurs.
</li>
<li>
    Oui ou non :<br />
    La valeur ne pourra être que "oui" ou "non". Lors de l\'édition de la fiche
    produit, une liste déroulante sera proposée avec ces deux propositions.
</li>
<li>
    Liste de valeurs :<br />
    La valeur ne pourra être qu\'une de celles contenues dans le champs suivant.
    Lors de l\'édition de la fiche produit, une liste déroulante sera proposée
    avec ces propositions.<br />
    S\'il y a la possibilité que ce champs ne soit pas remplissable, vous pouvez
    intégrer la valeur NC, pour non communiqué.
</li>
</ul></div>',
    'customPropertyType_text' => 'Texte libre sans traduction',// Présent dans customPropertyType_explanation
    'customPropertyType_i18nText' => 'Texte libre avec traduction',// Présent dans customPropertyType_explanation
    'customPropertyType_bool' => 'Oui ou non',// Présent dans customPropertyType_explanation
    'customPropertyType_list' => 'Liste de valeurs',// Présent dans customPropertyType_explanation
    'customPropertyList' => 'Liste des valeurs utilisables',
    'customPropertyList_explanation' => '<div>
Vous devez lister ici les différentes valeurs qui pourront être utilisées pour
remplir le champs.<br />
Passez à la ligne entre chaque valeur.</div>',
    'customPropertyActive' => 'Ce champs doit être actif par défaut',
    'customPropertyActive_explanation' => '<div>
Si vous souhaitez qu\'à la création d\'un nouveau produit, cette propriété soit
active, cochez cette case.<br />
Sinon, pour afficher cette propriété personnalisée dans la fiche produit, il
faudra cocher une case sur la fiche produit.</div>',
    'customProperty_new_linkName' => 'Nouvelle propriété personnalisée',

    'productEditor_categoriesTitle'=>'Choix des catégories',
    'productEditor_categoriesExplanantion'=>'<div>
Sélectionnez ici la ou les catégories dans lesquelles doit apparaître le produit.<br />
Vous devez impérativement sélectionner au moins une catégorie pour valider l\'ajout du produit.</div>',
    'categoryEditor_title'=>'Editeur de catégories',
    'categoryEditor_name'=>'Nom de la catégorie&#160;:',
    'categoryEditor_description'=>'Description détaillée:',
    'categoryEditor_activateCategoryExplanation'=>'<div>
Si la case d\'activation de la catégorie n\'est pas cochée
cellle-ci n\'apparaîtra pas dans votre boutique.</div>',
    'categoryEditor_activateCategory'=>'Activer la catégorie  ',
    'categoryEditor_descriptionExplanation'=>'<div>
La description détaillée est celle qui apparaîtra dans la fiche de la catégorie. <br />
Toutes les informations générales sur sont contenu seront présentes ici.</div>',
    'categoryEditor_shortDescription'=>'Courte description&#160;:',
    'categoryEditor_shortDescriptionExplanation'=>'<div>
La description courte est celle qui apparaîtra dans les listes de catégories.
Elle est très brève et renseigne sur les contenus principaux de la catégorie.</div>',
    'categoryEditor_image'=>'Aperçu de la catégorie&#160;:',
    'categoryEditor_clickHere'=>'Cliquez ici pour choisir une image',
    'categoryEditor_imageExplanation'=>'<div>
Pour selectionner votre image, il suffit de cliquer sur l\'aperçu ci-dessous.
Vous accédez alors au navigateur de fichiers.<br />
Si l\'image que vous souhaitez utiliser est déjà présente, il suffit de cliquer
sur sa miniature pour la sélectionner.<br />
Sinon, vous pouvez l\'envoyer à partir de là dans le dossier choisi. Une fois
l\'image présente dans la liste, il vous suffira alors de cliquer sur son aperçu
 pour la sélectionner.<br />
Elle sera automatiquement redimensionnée pour rentrer dans le format.</div>',
    'product_added_successfully'=>'Le produit a été ajouté avec succès.<br />
Vous pouvez continuer à en ajouter en remplissant à nouveau le formulaire, ou vous
pouvez accéder à la fiche du produit en cliquant sur le lien ci-dessous: ',
    'categoryEditor_categoriesTitle'=>'Choix des catégories',
    'categoryEditor_categoriesExplanantion'=>'<div>
Sélectionnez ici la ou les catégories dans lesquelles doit apparaître la
nouvelle catégorie.<br />
Vous ne pourrez pas ajouter une sous categorie dans une catégorie qui contient
un produit.<br />
Si aucune catégorie n\'est séléctionnée, la nouvelle catégorie sera positionnée
au premier niveau des catégories.</div>
',
    'category_added_successfully'=>'La catégorie a été ajoutée avec succès.<br />
Vous pouvez continuer à en ajouter en remplissant à nouveau le formulaire, ou vous
pouvez accéder à la fiche de la catégorie en cliquant sur le lien ci-dessous&#160;:&#160;',

    'cart_title'=>'Votre panier',
    'command_confirm_title'=>'Confirmez votre commande',
    
    'nav_next'=>'Page suivante',
    'nav_previous'=>'Page précédente',

    'bill_editBoxTitle'=>'Facturation',
    'bill_bottomText'=>'Bas de page&#160;:&#160;',
    'navigator_desc_name'=>'Description&#160;:&#160;',
    'navigator_stock_name'=>'Etat des stocks&#160;:&#160;',
    'access_category'=>'Accéder',
    
    'productsTable_name'=> 'Nom',
    'productsTable_desc'=> 'Description',
    'productsTable_stock'=> 'Stock',
    'productsTable_price'=> 'Prix',
    
    'reference_title'=> 'Reference&#160;:&#160;',
    
    'pictoAddToCart'=> 'Ajouter au panier',
    'pictoSendmail'=> 'Envoyer par e-mail',
    'pictoShow'=> 'Consulter la fiche produit',
    
    'selectAcategory'=> 'Choisissez une categorie&#160;:',
    'selectAproduct'=> 'Choisissez un produit&#160;:&#160;',
    'inSameCategory'=> 'Dans la même catégorie&#160;:&#160;',

    'showAllProducts_title' => 'Liste de tous les produits',
    'showAllProductsCategories_title' => 'Liste des catégories de produits',
    'chooseAcategory' => 'Après avoir sélectionné une catégorie, vous aurez accès à une seconde boîte au dessous<br />
comprenant la liste de tous les produits compris dans la catégorie sélectionnée.',
    'activate_products_from_list'=>'Si vous le souhaitez, vous pouvez activer ou désactiver vos fiches produits directement
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

    'payment_providerName' => 'Nom du prestataire :',
    'editPaymentModesTitle' => 'Modes de paiement',
    'pageName_showPaymentModes'=>'Page d\'affichage des modes de paiement',
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
    'payment_activateMode' => 'Activer ce mode de paiement',


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
    'shipMode_add' => 'Ajouter un mode de livraison',
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
37 bvd Jean Pierre
72814 Machin Les Bains

Ma boutique
84 place de la poste
37155 Chateauneuf

Ma boutique
...
</textarea></div>',
    'shipPriceRule0' => '
A partir de
<RENDER_VALUE what="constants>currencyBefore"/>
<input name="shipRulePrice0" value="{ship>rulePrice0}" style="width:50px;"/>&#160;
<RENDER_VALUE what="constants>currencyAfter"/>
d\'achats, la réduction est de 
<input name="shipRulediscount0" value="{ship>rulediscount0}" style="width:50px;"/>.',
    'shipPriceRule1' => '
A partir de
<RENDER_VALUE what="constants>currencyBefore"/>
<input name="shipRulePrice1" value="{ship>rulePrice1}" style="width:50px;"/>&#160;
<RENDER_VALUE what="constants>currencyAfter"/>
d\'achats, la réduction est de
<input name="shipRulediscount1" value="{ship>rulediscount1}" style="width:50px;"/>.',
    'shipPriceRule2' => '
A partir de
<RENDER_VALUE what="constants>currencyBefore"/>
<input name="shipRulePrice2" value="{ship>rulePrice2}" style="width:50px;"/>&#160;
<RENDER_VALUE what="constants>currencyAfter"/>
d\'achats, la réduction est de
<input name="shipRulediscount2" value="{ship>rulediscount2}" style="width:50px;"/>.',
    'ship_discountIntro' => 'En fonction du montant de votre facture vous bénéficierez de réductions sur les frais de livraison&#160;:',
    'ship_discount0' => 'A partir de <RENDER_VALUE what="discount0>price"/>&#160;:&#160;
<RENDER_VALUE what="discount0>discount"/>',
    'ship_discount1' => 'A partir de <RENDER_VALUE what="discount1>price"/>&#160;:&#160;
<RENDER_VALUE what="discount1>discount"/>',
    'ship_discount2' => 'A partir de <RENDER_VALUE what="discount2>price"/>&#160;:&#160;
<RENDER_VALUE what="discount2>discount"/>',
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
    <RENDER_VALUE what="comeTakeIt>price"/><RENDER_VALUE what="shipMode>price"/>
</span>.',
    'chooseShippingMode' => 'Pour l\'expédition, nous vous proposons les modes suivants :',
    'chooseToComeAndTakeIt' => 'Aller chercher la commande dans un dépot.',
    'chooseToComeAndTakeIt_chooseIt' => 'Choix du lieu où aller chercher
    la commande :',
    'comeTakeIt_youCanChooseAddress' => 'Vous pouvez choisir d\'aller chercher
votre commande à l\'une des adresses suivantes&#160;:',

    'command_changeShipModeLink'=>'Changer de mode d\'expédition',
    'command_comeTakeItTitle'=>'Disponibilité à l\'adresse :',
    'command_shippingTitle'=>'Livraison :',
    'command_requires_connection'=>'Vous devez être connecté pour passer votre commande',

    'paymentModes_title' => 'Modes de paiement acceptés',

    'breadCrumbs_sameCategoryText' => 'Dans la même catégorie : '
);
