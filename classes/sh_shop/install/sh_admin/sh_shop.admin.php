<?php
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$adminMenu['Boutique'][] = array(
    'link'=>'shop/editParams/',
    'text'=>'Gérer la boutique',
    'icon'=>'picto_tool.png'
);
$shopClass = sh_links::getInstance()->shop;
if($shopClass->isActivated()){
    $adminMenu['Boutique'][] = array(
        'link'=>'shop/editProduct/0',
        'text'=>'Ajouter un produit',
        'icon'=>'picto_add.png'
    );
    $adminMenu['Boutique'][] = array(
        'link'=>'shop/editCategory/0',
        'text'=>'Ajouter une categorie',
        'icon'=>'picto_add.png'
    );
    $adminMenu['Boutique'][] = array(
        'link'=>'shop/showAllProducts/',
        'text'=>'Liste des produits',
        'icon'=>'picto_list.png'
    );
    $adminMenu['Boutique'][] = array(
        'link'=>'shop/editPaymentModes/',
        'text'=>'Modes de paiement',
        'icon'=>'payment_modes.png'
    );
    $adminMenu['Boutique'][] = array(
        'link'=>'shop/editShipModes/',
        'text'=>'Modes d\'expédition',
        'icon'=>'ship_modes.png'
    );
    $adminMenu['Boutique'][] = array(
        'link'=>'shop/showCommands/',
        'text'=>'Etat des commandes',
        'icon'=>'ship_modes.png'
    );
    $adminMenu['Boutique'][] = array(
        'link'=>'shop/editCustomProperty/0',
        'text'=>'Propriétés personnalisées des produits',
        'icon'=>'picto_modify.png'
    );
}