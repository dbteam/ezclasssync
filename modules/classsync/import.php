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

    } elseif (preg_match('/^.*?\.zip/', $originalFilename)) {
        $zip = new ZipArchive();
        $files = array();
        if ($zip = zip_open($filename)) {
            while ($entry = zip_read($zip)) {
                $subfile = zip_entry_name($entry);
                $files[] = $subfile;
                if (preg_match('/^.*?\.json$/', $subfile)) {
                    file_put_contents(
                        getcwd() . '/var/sync/' . $subfile, zip_entry_read($entry, zip_entry_filesize($entry))
                    );
                }
            }
        }

        $tpl->setVariable('result', count($files) . ' file(s) uploaded to var/sync/<br/><code>' . implode(', ', $files))
        . '</code>';

    } else {
        $tpl->setVariable('result', $originalFilename . ' isn\'t a json file!');
    }

    @unlink($filename);

    $Result['content'] = $tpl->fetch('design:classsync/result.tpl');
}
