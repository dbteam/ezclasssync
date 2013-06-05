<?php

$http = eZHTTPTool::instance();
$Result = array();
$tpl = eZTemplate::factory();

$filename = base64_decode($Params['file']);

if (!empty($filename) && file_exists($filename)) {

    $sync = new eZClassSync($filename);

    $results = $sync->sync();

    $tpl->setVariable(
        'result', (empty($results)) ? 'Nothing changed / Nothing to update.'
            : 'Result:<br/><br/>' . implode('.<br/>', $results)
    );
} else {
    $tpl->setVariable('result', 'Error: file not found.');
}

$Result['content'] = $tpl->fetch('design:classsync/result.tpl');
$Result['path'] = array(array('text' => 'Class Sync: Check'));
