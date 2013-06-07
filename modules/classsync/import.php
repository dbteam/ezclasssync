<?php

$http = eZHTTPTool::instance();
$Result = array();
$tpl = eZTemplate::factory();

if (!eZHTTPFile::canFetch("json_file")) {
    $Result['content'] = $tpl->fetch('design:classsync/import.tpl');
} else {
    $file = eZHTTPFile::fetch("json_file");
    /* @var $file eZHTTPFile */
    $file->store();

    $filename = $file->attribute('filename');
    $originalFilename = $file->attribute('original_filename');
    if (preg_match('/^.*?\.json$/', $originalFilename)) {
        rename($filename, getcwd() . '/var/sync/' . $originalFilename);
        $tpl->setVariable('result', $originalFilename . ' uploaded to var/sync/');
    } else {
        $tpl->setVariable('result', $originalFilename . ' isn\'t a json file!');
    }

    @unlink($filename);

    $Result['content'] = $tpl->fetch('design:classsync/result.tpl');
}
