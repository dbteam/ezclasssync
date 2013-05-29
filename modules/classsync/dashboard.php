<?php

$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();

$files = glob('extension/*/sync/*.json');
$fileList = array();

foreach ($files as $k => $v) {

    $file = json_decode(file_get_contents($v), true);

    $fileList[] = array(
        'filename'        => basename($v),
        'filehash'        => base64_encode($v),
        'identifier'      => $file['identifier'],
        'attribute_count' => count($file['attributes']),
    );
}

$tpl->setVariable('fileList', $fileList);

$Result = array();
$Result['content'] = $tpl->fetch('design:classsync/dashboard.tpl');
$Result['path'] = array(array('text' => 'Class Sync'));
//$Result['ui_context'] = 'dashboard';
//$Result['content_info'] = array();
