<?php

$http = eZHTTPTool::instance();
$Result = array();
$tpl = eZTemplate::factory();

$filename = base64_decode($Params['file']);

if (!empty($filename) && file_exists($filename)) {

    $compare = new eZClassSyncCompare();
    $differences = $compare->compare($filename);

    $tpl->setVariable('attrToAdd', implode(' ,', $compare->attributesToAdd));
    $tpl->setVariable('attrToDrop', implode(' ,', $compare->attributesToDrop));
    $tpl->setVariable('attrToUp', implode(' ,', $compare->attributesToUpdate));
    $tpl->setVariable('differences', $differences);
    $tpl->setVariable('compareResultClass', $compare->compareClassResults);
    $tpl->setVariable('compareResultAttribute', $compare->compareAttributesResults);

    $Result['content'] = $tpl->fetch('design:check.tpl');
} else {
    $Result['content'] = $tpl->setVariable('result', 'Error: file not found.');
}

$Result['path'] = array(array('text' => 'Class Sync: Check'));
