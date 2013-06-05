<?php

$http = eZHTTPTool::instance();
$Result = array();
$tpl = eZTemplate::factory();

$filename = base64_decode($Params['file']);

if (!empty($filename) && file_exists($filename)) {

    $sync = new eZClassSync($filename);
    $sync->compare();

    $tpl->setVariable('class', $sync->getClassName());
    $tpl->setVariable('attrToAdd', implode(', ', $sync->attributesToAdd));
    $tpl->setVariable('attrToDrop', implode(', ', $sync->attributesToDelete));
    $tpl->setVariable('attrToUp', implode(', ', $sync->attributesToUpdate));
    $tpl->setVariable('differences', $sync->getTotalDifferences());
    $tpl->setVariable('compareResultClass', $sync->getClassParamCompareResults());
    $tpl->setVariable('compareResultAttribute', $sync->getClassAttributesCompareResults());

    $Result['content'] = $tpl->fetch('design:classsync/check.tpl');
} else {
    $Result['content'] = $tpl->setVariable('result', 'Error: file not found.');
}

$Result['path'] = array(array('text' => 'Class Sync: Check'));
