<?php

$http = eZHTTPTool::instance();
$Result = array();
$tpl = eZTemplate::factory();

$filesToCompare = array();
$syncResults = array();

if (!$http->hasPostVariable('sync')) {
    $filesToCompare[$Params['file']] = base64_decode($Params['file']);
} else {
    foreach ($http->postVariable('ExportIDArray') as $filename) {
        $filesToCompare[$filename] = base64_decode($filename);
    }
}


foreach ($filesToCompare as $key => $filename) {

    if (!empty($filename) && file_exists($filename)) {

        $sync = new eZClassSync($filename);
        $results = $sync->sync();

        $syncResults[] = '<strong>' . $sync->getClassName() . '</strong>:<br/>' . (empty($results)
            ? 'Nothing changed / Nothing to update.' : 'Result:<br/><br/>' . implode('.<br/>', $results) . '.');
    }
}

if (!empty($syncResults)) {
    $tpl->setVariable('result', implode('<br/><br/>', $syncResults));
} else {
    $tpl->setVariable('result', 'Error: file not found.');
}

$Result['content'] = $tpl->fetch('design:classsync/result.tpl');
$Result['path'] = array(array('text' => 'Class Sync: Check'));
