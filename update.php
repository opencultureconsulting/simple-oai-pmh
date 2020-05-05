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

define('ABSPATH', __DIR__ . '/');

use OCC\OAI2\Config;
use OCC\OAI2\Helper;
use OCC\OAI2\Data;

// Make this script only executable via commandline interface!
if (PHP_SAPI !== 'cli') {
    exit;
}

// Check mandatory cli arguments
if (empty($argc) || $argc !== 3) {
    echo 'Usage:' . PHP_EOL;
    echo '  php update.php [sourceDir] [metadataPrefix]' . PHP_EOL . PHP_EOL;
    echo 'Example:' . PHP_EOL;
    echo '  php update.php /tmp/import oai_dc' . PHP_EOL;
    exit;
}

// Register PSR-4 autoloader
require ABSPATH . 'vendor/autoload.php';

// Init config manager
$config = Config::getInstance();

// Init data manager
$data = Data::getInstance();

// Get data from CLI arguments
list(, $sourceDir, $metadataPrefix) = $argv;

// Remove endslash for sourceDir
$sourceDir = rtrim($sourceDir, '/');

// Check metadataPrefix
if (!$config->metadataPrefixExists($metadataPrefix)) {
    Helper::cliError("Error: metadataPrefix $metadataPrefix not defined in configuration file");
}

// Check sourceDir permissions
if (!is_dir($sourceDir) || !is_readable($sourceDir)) {
    Helper::cliError("Error: $sourceDir not readable");
}

// Get dataDir from config
$dataDir = Data::getInstance()->getDirectoryByMeta($metadataPrefix);

// Check dataDir permissions
if (!is_dir($dataDir) || !is_writable($dataDir)) {
    Helper::cliError("Error: $dataDir not writable");
}

// Alright, let's start!
echo "Updating $dataDir from $sourceDir" . PHP_EOL;

$todo = array();
$error = false;

// Mark all existing files for "DELATION"
$_files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dataDir));
$oldFiles = new \RegexIterator($_files, '/\.xml$/');
foreach ($oldFiles as $fileInfo) {
    // Determine relative path
    $relativePath = str_replace($dataDir, '', $fileInfo->getPathname());

    $todo[$relativePath] = 'delete';
}

// Change flag for "UPDATE", if file exist on source
$_files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($sourceDir));
$newFiles = new \RegexIterator($_files, '/\.xml$/');
foreach ($newFiles as $fileInfo) {
    // Determine relative path
    $relativePath = str_replace($sourceDir, '', $fileInfo->getPathname());

    $todo[$relativePath] = 'update';
}

foreach ($todo as $relativeFilePath => $task) {
    echo "  Checking record $relativeFilePath ... ";
    if ('update' === $task) {
        if (!is_file($dataDir . $relativeFilePath)) {
            // Add file
            if (copy($sourceDir . $relativeFilePath, $dataDir . $relativeFilePath)) {
                echo Helper::CliFormat('added', 'green') . PHP_EOL;
            } else {
                echo Helper::CliFormat('addition failed', 'red') . PHP_EOL;
                $error = true;
            }
        } elseif (md5_file($sourceDir . $relativeFilePath) !== md5_file($dataDir . $relativeFilePath)) {
            // Replace file
            if (copy($sourceDir . $relativeFilePath, $dataDir . $relativeFilePath)) {
                echo Helper::CliFormat('updated', 'green') . PHP_EOL;
            } else {
                echo Helper::CliFormat('update failed', 'red') . PHP_EOL;
                $error = true;
            }
        } else {
            echo 'unchanged' . PHP_EOL;
        }
    } elseif ($task === 'delete') {
        if (filesize($dataDir . $relativeFilePath) !== 0) {
            // Truncate file
            if (fclose(fopen($dataDir . $relativeFilePath, 'wb'))) {
                echo Helper::CliFormat('deleted', 'green') . PHP_EOL;
            } else {
                echo Helper::CliFormat('deletion failed', 'red') . PHP_EOL;
                $error = true;
            }
        } else {
            echo 'unchanged' . PHP_EOL;
        }
    }
}

if ($error) {
    Helper::cliError('Update completed, but errors occurred. Please check the logs!');
} else {
    Helper::cliSuccess('Update successfully completed!');
}
