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

require_once './Configuration/Main.php';

/**
 * Format output string
 *
 * @param string $text
 * @param string $format 'green' or 'red'
 *
 * @return string
 */
function format($text, $format = '') {
    switch ($format) {
        case 'green':
            $text = "\033[0;92m$text\033[0m";
            break;
        case 'red':
            $text = "\033[1;91m$text\033[0m";
            break;
        default:
            break;
    }
    return $text;
}

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
if (empty($config['metadataPrefix'][$metadataPrefix])) {
    echo "Error: metadataPrefix $metadataPrefix not defined in oai2config.php\n";
    exit;
}
// Check sourceDir permissions
if (!is_dir($sourceDir) || !is_readable($sourceDir)) {
    echo "Error: $sourceDir not readable\n";
    exit;
}
$sourceDir = rtrim($sourceDir, '/').'/';
// Prepend script's path if dataDir is not an absolute path
$dataDir = rtrim($config['dataDirectory'], '/').'/'.$metadataPrefix.'/';
if (strpos($dataDir, '/') !== 0) {
    $dataDir = dirname(__FILE__).'/'.$dataDir;
}
// Check dataDir permissions
if (!is_dir($dataDir) || !is_writable($dataDir)) {
    echo "Error: $dataDir not writable\n";
    exit;
}
// Alright, let's start!
echo "Updating $dataDir from $sourceDir\n";
$todo = array ();
$error = false;
$oldFiles = glob($dataDir.'*.xml');
foreach ($oldFiles as $oldFile) {
    $todo[pathinfo($oldFile, PATHINFO_FILENAME)] = 'delete';
}
$newFiles = glob($sourceDir.'*.xml');
foreach ($newFiles as $newFile) {
    $todo[pathinfo($newFile, PATHINFO_FILENAME)] = 'update';
}
foreach ($todo as $identifier => $task) {
    echo "  Checking record $identifier ... ";
    if ($task === 'update') {
        if (!file_exists($dataDir.$identifier.'.xml')) {
            // Add file
            if (copy($sourceDir.$identifier.'.xml', $dataDir.$identifier.'.xml')) {
                echo format('added', 'green')."\n";
            } else {
                echo format('addition failed', 'red')."\n";
                $error = true;
            }
        } elseif (md5_file($sourceDir.$identifier.'.xml') !== md5_file($dataDir.$identifier.'.xml')) {
            // Replace file
            if (copy($sourceDir.$identifier.'.xml', $dataDir.$identifier.'.xml')) {
                echo format('updated', 'green')."\n";
            } else {
                echo format('update failed', 'red')."\n";
                $error = true;
            }
        } else {
            echo "unchanged\n";
        }
    } elseif ($task === 'delete') {
        if (filesize($dataDir.$identifier.'.xml') !== 0) {
            // Truncate file
            if (fclose(fopen($dataDir.$identifier.'.xml', 'w'))) {
                echo format('deleted', 'green')."\n";
            } else {
                echo format('deletion failed', 'red')."\n";
                $error = true;
            }
        } else {
            echo "unchanged\n";
        }
    }
}
if ($error) {
    echo "Update completed, but errors occurred. Please check the logs!\n";
} else {
    echo "Update successfully completed!\n";
}
