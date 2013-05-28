<?php

$http = eZHTTPTool::instance();
$Result = array();
$tpl = eZTemplate::factory();

$filename = base64_decode($Params['file']);

if (!empty($filename) && file_exists($filename)) {

    $compare = new eZClassSyncCompare();
    $differences = $compare->compare($filename);

    if ($differences) {
        $results = $compare->storeChanges();
    } else {
        $results = null;
    }

    $tpl->setVariable(
        'result', (empty($results)) ? 'Nothing changed / Nothing to update.'
            : 'Class updated:<br/>' . implode('.<br/>', $results)
    );
} else {
    $tpl->setVariable('result', 'Error: file not found.');
}

$Result['content'] = $tpl->fetch('design:result.tpl');
$Result['path'] = array(array('text' => 'Class Sync: Check'));
