<?php
/**
 * Simple OAI-PMH 2.0 Data Provider
 * Copyright (C) 2017 Sebastian Meyer <sebastian.meyer@opencultureconsulting.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// Make this script only executable via commandline interface!
if (php_sapi_name() !== 'cli') exit;

require_once('oai2config.php');

// Check mandatory cli arguments
if (empty($argc) || $argc != 3) {

    echo "Usage:\n";
    echo "  php update.php [sourceDir] [metadataPrefix]\n";
    echo "\n";
    echo "Example:\n";
    echo "  php update.php /tmp/import oai_dc\n";
    echo "\n";

    exit;

}

list(, $sourceDir, $metadataPrefix) = $argv;

// Check metadataPrefix
if (empty($config['metadataFormats'][$metadataPrefix])) {

    echo "Error: metadataPrefix $metadataPrefix not defined in oai2config.php\n";

    exit;

}

// Check sourceDir permissions
$sourceDir = rtrim($sourceDir, '/').'/';

if (!is_dir($sourceDir) || !is_readable($sourceDir)) {

    echo "Error: $sourceDir not readable\n";

    exit;

}

// Check dataDir permissions
$dataDir = rtrim($config['dataDirectory'], '/').'/'.$metadataPrefix.'/';

if (!is_dir($dataDir) || !is_writable($dataDir)) {

    echo "Error: $dataDir not writable\n";

    exit;

}

// Alright, let's start!
echo "Updating $dataDir from $sourceDir\n";

$todo = array ();

$oldFiles = glob($dataDir.'*.xml');

foreach ($oldFiles as $oldFile) {

    $todo[pathinfo($oldFile, PATHINFO_FILENAME)] = 'delete';

}

$newFiles = glob($sourceDir.'*.xml');

foreach ($newFiles as $newFile) {

    $todo[pathinfo($newFile, PATHINFO_FILENAME)] = 'update';

}

foreach ($todo as $identifier => $task) {

    if ($task === 'update') {

        if (md5_file($sourceDir.$identifier.'.xml') !== md5_file($dataDir.$identifier.'.xml')) {

            echo "  Updating record $identifier ...";

            // Replace file
            if (copy($sourceDir.$identifier.'.xml', $dataDir.$identifier.'.xml')) {

                echo " done!\n";

            } else {

                echo " failed!\n";

            }

        }

    } elseif ($task === 'delete') {

        if (filesize($dataDir.$identifier.'.xml') !== 0) {

            echo "  Deleting record $identifier ...";

            // Truncate file
            if (fclose(fopen($dataDir.$identifier.'.xml', 'w'))) {

                echo " done!\n";

            } else {

                echo " failed!\n";

            }

        }

    }

}
