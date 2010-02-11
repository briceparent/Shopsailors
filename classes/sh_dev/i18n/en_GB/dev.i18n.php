<?php
$i18n = array(
    'debug_Title' => 'Code debug',
    'debug_Activated' => 'Activate the debug',
    'debug_path'=> 'Verification folder',
    'debug_path_explanation'=> '
This field allows you to define a test to verify that the server the script is<br />
running on is the one you would like to test.<br /><br />
This allows you to use axactly the same datas on a dev and on a prod servers, <br />
the first displaying debug while the second doesn\'t. The only difference between<br />
the two is the folder in which Shopsailors is installed.<br /><br />
The test verifies the existance of the folder specified in the following input.<br /><br />
If you don\'t want to use that filter, just enter here a folder name that always<br />
exists, such as /var on UNIX-like systems.',
    'codeCoverage_activated' => 'Activate code coverage',
    'codeCoverage_activated_explanation'=>'
Code coverage makes the system save the lines number that are executed. So it <br />
allows you to know which parts of the program are never executed.<br /><br />
Once this option activated, you should try all the functionalities of the class<br />
you want to clean.<br />
You then just have to go to the subfolder debugFiles/codeCoverage/ from the <br />
sh_dev class, and to open the file named like the class you want to clean. It<br />
contains all the lines that have been used since you\'ve activated the option.<br /><br />
The file has to be removed manually if you want to use this option after having<br />
done lots of changes, otherwise, the new results would be added to the old ones.',
    'trace_activated'=>'Activate the trace',
    'trace_activated_explanation'=>'
Activating the trace will create a trace file for every file that are called <br />
(html page, css file, image, script, etc...).<br /><br />
These trace files will allow you to know the way that the program really works,<br />
to know the time passed on each step, and to know the amount of memory used.<br /><br />
Caution : When you call a single html page, most of the time, there are many <br />
files that are called, so many trace files are created.<br />
You should only activate this option when you really need it.',
    'errors_activated'=>'Activate the displaying of the following errors :',
    'E_ALL_activated'=>'"E_ALL" errors (If activated, the following checked boxes
will deactivate the error.)',
    'E_STRICT_activated'=>'E_STRICT errors',
    'E_WARNING_activated'=>'E_WARNING errors',
    'E_NOTICE_activated'=>'E_NOTICE errors',


    'noFileSelected'=>'None of the files are selected!',
    'fileChangeDescription' => 'Modifications on the file',
    'globalChangeDescription' => 'Global description of the modifications',
    'futureRevisionNumber' => 'Next revision number : ',
    'noGlobalChangeDescription' => 'You have to enter a global description of the modification.',
    'noFileDescription' => 'Some files have no change description. Do you want to proceed anymay?',
    'noChanges' => 'No file has been modified, created, or deleted since the actual revision
(<RENDER_VALUE what="revisionNumber>actual"/>).',
);
