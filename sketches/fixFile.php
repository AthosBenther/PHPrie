<?php

$path = 'archRoads.json';

$data = file_get_contents($path);

file_put_contents('backup.json', $data);

$data = removeInvalidChars($data);

file_put_contents('cleaned.json', $data);

$data = json_decode($data, true);

if ($data === null) {
    die("\n\nError parsing JSON\n\n");
}

foreach ($data['data']['roads'] as &$road) {
    $road['name'] = fixName($road['name']);
}


function removeInvalidChars($data)
{
    return preg_replace('/[^A-Za-z0-9_\- \":.,\[\{\}\]\\\\]/', '', $data);

}
function fixName($name)
{
    $oName = substr($name, 0, 36);

    return $oName;
}

file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));