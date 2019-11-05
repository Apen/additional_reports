<?php

$EM_CONF['additional_reports'] = array(
    'title'            => 'Useful information in reports module',
    'description'      => 'Useful information in the reports module: xclass, ajax, cliKeys, eID, general status of the system (encoding, DB, php vars...), hooks, compare local and TER extension (diff), used content type, used plugins, ExtDirect... It can really help you during migration or new existing project (to have global reports of the system).',
    'category'         => 'misc',
    'version'          => '3.2.1',
    'state'            => 'stable',
    'uploadfolder'     => '',
    'createDirs'       => '',
    'clearcacheonload' => true,
    'author'           => 'CERDAN Yohann',
    'author_email'     => 'cerdanyohann@yahoo.fr',
    'author_company'   => '',
    'constraints'      =>
        array(
            'depends'   =>
                array(
                    'typo3' => '8.7.0-9.5.99',
                ),
            'conflicts' =>
                array(),
            'suggests'  =>
                array(),
        ),
);

