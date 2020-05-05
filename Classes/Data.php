<?php
/**
 * Simple OAI-PMH 2.0 Data Provider
 * Copyright (C) 2005 Heinrich Stamerjohanns <stamer@uni-oldenburg.de>
 * Copyright (C) 2011 Jianfeng Li <jianfeng.li@adelaide.edu.au>
 * Copyright (C) 2013 Daniel Neis Araujo <danielneis@gmail.com>
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

namespace OCC\OAI2;

class Data
{
    private static $instance;

    private $records = [];
    private $deleted = [];
    private $timestamps = [];
    private $sets = [];

    private $earliest;

    /**
     * @return Data
     */
    public static function getInstance(): Data
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->earliest = time();
    }

    private function __clone()
    {
    }

    public function getDirectoryByMeta($prefix)
    {
        $config = Config::getInstance();

        $directory = rtrim($config->getConfigValue('dataDirectory'), '/') . '/' . $prefix;

        // Prepend script's path if dataDir is not an absolute path
        if (strpos($directory, '/') !== 0) {
            $directory = ABSPATH . $directory;
        }

        return realpath($directory);
    }

    public function populateSets()
    {
        $config = Config::getInstance();

        foreach ($config->getConfigValue('metadataPrefix') as $metadataPrefix => $uris) {
            $directory = $this->getDirectoryByMeta($metadataPrefix);

            $all_files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            $xml_files = new \RegexIterator(
                $all_files,
                '/' . preg_quote($config->getConfigValue('setDefinition'), '/') . '$/'
            );

            foreach ($xml_files as $fileInfo) {
                // Build set name
                $setName = str_replace($directory, '', $fileInfo->getPath());
                $setName = trim($setName, '/');
                $setName = str_replace('/', ':', $setName);

                $this->sets[] = $setName;
            }
        }
    }

    public function resetRecords()
    {
        $this->records = array();
        $this->deleted = array();
        $this->timestamps = array();
        $this->earliest = time();
    }

    public function populateRecords($set = '')
    {
        $this->resetRecords();

        $config = Config::getInstance();

        foreach ($config->getConfigValue('metadataPrefix') as $metadataPrefix => $uris) {
            $directory = $this->getDirectoryByMeta($metadataPrefix);

            $all_files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            $xml_files = new \RegexIterator($all_files, '/\.xml$/');

            foreach ($xml_files as $fileInfo) {
                $filePath = $fileInfo->getPathname();
                $fileName = $fileInfo->getBasename();

                if (basename($filePath) === $config->getConfigValue('setDefinition')) {
                    continue;
                }

                // Translate path to setSpec URI identifier
                $setName = str_replace($directory, '', $fileInfo->getPath());
                $setName = trim($setName, '/');
                $setName = str_replace('/', ':', $setName);

                // Filter to a set
                if (!empty($set) && $setName !== $set) {
                    continue;
                }

                // If a record exist on multiple folder, because multisets, keep last not empty file in case or deletion
                if (isset($this->records[$metadataPrefix][$fileName]) && !filesize($filePath)) {
                    continue;
                }

                $this->records[$metadataPrefix][$fileName] = $filePath;
                $this->deleted[$metadataPrefix][$fileName] = !filesize($filePath);
                $this->timestamps[$metadataPrefix][$fileName] = filemtime($filePath);

                if (filemtime($filePath) < $this->earliest) {
                    $this->earliest = filemtime($filePath);
                }
            }

            if (isset($this->records[$metadataPrefix])) {
                ksort($this->records[$metadataPrefix]);
                reset($this->records[$metadataPrefix]);
                asort($this->timestamps[$metadataPrefix]);
                reset($this->timestamps[$metadataPrefix]);
            }
        }
    }

    /**
     * @return array
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * @return array
     */
    public function getDeleted(): array
    {
        return $this->deleted;
    }

    /**
     * @return array
     */
    public function getTimestamps(): array
    {
        return $this->timestamps;
    }

    /**
     * @return int
     */
    public function getEarliest(): int
    {
        return $this->earliest;
    }

    /**
     * @return array
     */
    public function getSets(): array
    {
        return $this->sets;
    }

    public function getSetFile(string $set)
    {
        $config = Config::getInstance();

        $setDefinition = $config->getConfigValue('setDefinition');

        // Translate setSpec URL identifier to path
        $set = str_replace(':', '/', $set);

        foreach ($config->getConfigValue('metadataPrefix') as $prefix => $uris) {
            $file = $this->getDirectoryByMeta($prefix) . "/$set/$setDefinition";
            if (is_file($file)) {
                return $file;
            }
        }

        return false;
    }
}
