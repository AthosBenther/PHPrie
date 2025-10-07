<?php

namespace PHPrie\Controllers;

use PHPrie\Helpers\Console;
use PHPrie\Types\Roads\ArchRoads;
use PHPrie\Types\Roads\BaseRoads;

class ArchRoadsController
{
    public BaseRoads $archRoads;
    protected $defaultPath = $_ENV['ARCH_ROADS_PATH'] ?? "./archRoads.json";
    public function __construct(public ?string $filePath = null, bool $fixFile = true, bool $backup = true)
    {
        if ($backup) {
            $this->backupFile();
        }
        if ($fixFile) {
            $this->fixFile($this->filePath ?? $this->defaultPath);
        }
        $this->archRoads = new ArchRoads($filePath ?? $this->defaultPath);

    }

    public function fixFile(string $path)
    {
        $content = file_get_contents($path);

        //removes invalid characters
        $content = preg_replace('/[^A-Za-z0-9_\- \":.,\[\{\}\]\\\\]/', '', $content);

        //fixes road bojects links
        $content = str_replace("artshapesobjects", "/art/shapes/objects", $content);
        $content = str_replace("\\art\\shapes\\objects\\", "/art/shapes/objects", $content);

        $data = json_decode($content, true);

        // tries to load the file as Json to validate if file is invalid char free
        if ($data) {

            // Trims road uuids back to 36 characters
            foreach ($data['data']['roads'] as &$road) {
                $road['name'] = substr($road['name'], 0, 36);
            }

            file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
        } else {
            throw new \Exception("File fixing resulted in invalid JSON");
        }

    }

    public function backupFile(string $backupDir = $_ENV['ARCH_ROADS_BACKUP_PATH'] ?? "./backup")
    {
        $file = file_get_contents($this->defaultPath);
        $timestamp = date('Y-m-d-H-i-s');
        $backupName = "{$timestamp}-backup.json";
        file_put_contents($backupDir . $backupName, $file);
    }

    public function genSvg(string $outputPath = "output.svg")
    {
        $svg = $this->archRoads->toSvg();
        file_put_contents($outputPath, $svg);
    }

    public function slopes()
    {
        $data = $this->archRoads->fileArray;

        $roads = &$data['data']['roads'];

        $psmsRoads = [];

        foreach ($roads as &$road) {
            if (str_starts_with($road['displayName'], 'PSMS ')) {
                $psmsRoads[] = &$road;
            }
        }


        foreach ($psmsRoads as &$psmsRoad) {
            $nodes = &$psmsRoad['nodes'];

            $this->pleseSlopeMeSempai($psmsRoad);

            $psmsRoad['displayName'] = str_replace('PSMS', '', $psmsRoad['displayName']);
        }

        file_put_contents($this->filePath ?? $this->defaultPath, json_encode($data, JSON_PRETTY_PRINT));

        $psmsCount = count($psmsRoads);
        Console::log("Sloped {$psmsCount} roads.");
    }

    private function pleseSlopeMeSempai(&$road)
    {
        $nodes = &$road['nodes'];
        $cNodes = count($nodes);

        $roadLength = 0;
        $elevationDif = $nodes[count($nodes) - 1]['posZ'] - $nodes[0]['posZ'];

        $distances = [0];



        // Calculate total road length
        for ($i = 1; $i < $cNodes; $i++) {

            $currNode = $nodes[$i];
            $lastNode = $nodes[$i - 1];

            $dist = sqrt(pow($currNode['posX'] - $lastNode['posX'], 2) + pow($currNode['posY'] - $lastNode['posY'], 2));
            $distances[$i] = $dist;
            $roadLength += $dist;
        }

        $incRate = $elevationDif / $roadLength;

        $diffs = [];

        // Adjust elevations
        for ($i = 0; $i < $cNodes; $i++) {
            $currNode = &$nodes[$i];

            $currHeight = $currNode['posZ'];
            $distFromLast = 0;

            if ($i == 0) {
                $diffs[] = [
                    "Starting H" => $currHeight,
                    "New H" => $currNode['posZ'],
                    "Diff" => $currNode['posZ'] - $currHeight,
                    "Dist from Last" => $distFromLast,
                    "Dist from Start" => array_sum(array_slice($distances, 0, $i))
                ];
                continue;
            }

            if ($i > 0) {
                $lastNode = $nodes[$i - 1];
                $lastHeight = $lastNode['posZ'];
                $distFromLast = sqrt(pow($currNode['posX'] - $lastNode['posX'], 2) + pow($currNode['posY'] - $lastNode['posY'], 2));
                $currNode['posZ'] = $lastHeight + ($distFromLast * $incRate);
                $diffs[] = [
                    "Starting H" => $currHeight,
                    "New H" => $currNode['posZ'],
                    "Diff" => $currNode['posZ'] - $currHeight,
                    "Dist from Last" => $distFromLast,
                    "Dist from Start" => array_sum(array_slice($distances, 0, $i))
                ];
            }
        }
        file_put_contents("diffs.json", json_encode($diffs, JSON_PRETTY_PRINT));
    }
}