<?php

$Module = array('name'            => 'ezClassSync',
                'variable_params' => true);

$ViewList = array();
$ViewList['dashboard'] = array(
    'functions'               => array('dashboard'),
    'script'                  => 'dashboard.php',
    'default_navigation_part' => 'ezclasssyncnavigationpart',
    'params'                  => array());

$ViewList['check'] = array(
    'functions'               => array('check'),
    'script'                  => 'check.php',
    'default_navigation_part' => 'ezclasssyncnavigationpart',
    'params'                  => array('file'));

$ViewList['sync'] = array(
    'functions'               => array('check'),
    'script'                  => 'sync.php',
    'default_navigation_part' => 'ezclasssyncnavigationpart',
    'params'                  => array('file'));

$FunctionList = array();
$FunctionList['dashboard'] = array();
$FunctionList['check'] = array();
