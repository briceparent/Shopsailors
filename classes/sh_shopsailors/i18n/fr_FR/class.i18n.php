<?php
$i18n = array(
    'siteName' => 'Nom du site : ',
    'sitename_explanation' => '<div>
        Le nom du site sera enregistré dans une version simplifiée.<br />
        Cette simplification supprime les caractères spéciaux et les espaces.
        </div>',
    'domains' => 'Domaines : ',
    'i18n:domains_explanation' => '<div>
                        Veuillez entrer un nom de domaine par ligne.<br />
                        Si vous souhaitez faire pointer un domaine et tous ses sous-domaines vers ce site, entrez
                        <span class="bold">*.mon-domaine.com</span>.<br /><br />
                        <span style="color:red;">Attention : </span>Vous ne devez entrer que des noms de domaines
                        (ou sous-domaines) dont vous êtes propriétaire, ou pour lesquels le propriétaire vous a fourni les droits.<br />
                        Sans cela, votre site sera considéré comme une tentative de phishing (ou hameçonnage) et sera
                        fermé.<br /><br />
                        Une fois cette manipulation effectuée, il vous suffit de faire pointer les dns de ces domaines
                        vers ce serveur.<br /><br />
                        Ex :<br />
                        <textarea name="ex" style="width:95%;height:100px;">
*.domaine-principal.com
www.domaine-secondaire.com
user1.domaine-secondaire.com
user2.domaine-secondaire.com
user3.domaine-secondaire.com
                        </textarea>
                    </div>',
    'existing_topText' => 'Voici la liste des sites existants sur ce serveur :',
    'newSite' => 'Nouveau site',
    'sites' => 'Sites',
    'redirections' => 'Redirections',
);
