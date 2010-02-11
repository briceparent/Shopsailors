<?php

$sites['`mail\.(.+\..+)`'] = 'roundCube';

$sites['`(.*\.)?websailors\.fr`'] = 'websailors';
$sites['`(.*\.)?shopsailors\.org`'] = 'shopsailors';
$sites['`(.*\.)?bigbandaix(enprovence)?\.com`'] = 'bigband';

$sites['`(.*\.)?client1.safetyrent-online\.com`'] = 'safetyrent_1';
$sites['`(.*\.)?client2.safetyrent-online\.com`'] = 'safetyrent_2';
$sites['`(.*\.)?client3.safetyrent-online\.com`'] = 'safetyrent_3';

$default = 'websailors';

$redirections['`(.*\.)?web-?sailor\.fr`'] = '$1websailors.fr';
$redirections['`(.*\.)?web-sailors\.fr`'] = '$1websailors.fr';