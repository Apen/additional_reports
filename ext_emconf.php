<?php

$EM_CONF['additional_reports'] = [
    'title' => 'Useful information in reports module',
    'description' => 'Useful information in the reports module: xclass, ajax, cliKeys, eID, general status of the system (encoding, DB, php vars...), hooks, compare local and TER extension (diff), used content type, used plugins, ExtDirect... It can really help you during migration or new existing project (to have global reports of the system).',
    'version' => '3.4.8',
    'state' => 'stable',
    'uploadfolder' => '',
    'createDirs' => '',
    'clearcacheonload' => true,
    'author' => 'CERDAN Yohann',
    'author_email' => 'cerdanyohann@yahoo.fr',
    'author_company' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
