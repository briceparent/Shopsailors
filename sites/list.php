<?php

$sites['`(.*\.)?websailors\.fr`'] = 'websailors';
$sites['`(.*\.)?shopsailors\.org`'] = 'shopsailors';
$sites['`(.*\.)?bigbandaix(enprovence)?\.com`'] = 'bigband';

$sites['`(.*\.)?client1.safetyrent-online\.com`'] = 'safetyrent_1';
$sites['`(.*\.)?client2.safetyrent-online\.com`'] = 'safetyrent_2';

$sites['`(.*\.)?lavilladeschefs\.com`'] = 'lvdc';

$default = 'websailors';

$redirections['`(.*\.)?web-?sailor\.fr`'] = '$1websailors.fr';
$redirections['`(.*\.)?web-sailors\.fr`'] = '$1websailors.fr';