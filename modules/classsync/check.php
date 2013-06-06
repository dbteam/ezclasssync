<?php

$http = eZHTTPTool::instance();
$Result = array();
$tpl = eZTemplate::factory();

$filesToCompare = array();
$compareResults = array();

if (!$http->hasPostVariable('check')) {
    $filesToCompare[$Params['file']] = base64_decode($Params['file']);
} else {
    foreach ($http->postVariable('ExportIDArray') as $filename) {
        $filesToCompare[$filename] = base64_decode($filename);
    }
}

foreach ($filesToCompare as $key => $filename) {
    if (!empty($filename) && file_exists($filename)) {
        $sync = new eZClassSync($filename);
        $sync->compare();

        $compareResults[] = array(
            'exists'                 => true,
            'file'                   => $filename,
            'class'                  => $sync->getClassName(),
            'attrToAdd'              => implode(', ', $sync->attributesToAdd),
            'attrToDrop', implode(', ', $sync->attributesToDelete),
            'attrToUp'               => implode(', ', $sync->attributesToUpdate),
            'differences'            => $sync->getTotalDifferences(),
            'compareResultClass'     => $sync->getClassParamCompareResults(),
            'compareResultAttribute' => $sync->getClassAttributesCompareResults(),
            'formFileData'           => $key,
        );

    } else {
        $compareResults[] = array(
            'exists' => false,
            'file'   => $filename,
        );
    }
}

if (count($filename)) {

    $sync = new eZClassSync($filename);
    $sync->compare();

    $tpl->setVariable('compareResults', $compareResults);

    $Result['content'] = $tpl->fetch('design:classsync/check.tpl');
} else {
    $Result['content'] = $tpl->setVariable('result', 'Error: file not found.');
    $Result['content'] = $tpl->fetch('design:classsync/result.tpl');
}

$Result['path'] = array(array('text' => 'Class Sync: Check'));
