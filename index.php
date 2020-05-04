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

define('ABSPATH', __DIR__ . '/');

use OCC\OAI2\Config;
use OCC\OAI2\Data;
use OCC\OAI2\Exception;
use OCC\OAI2\Server;
use OCC\OAI2\Helper;

// Register PSR-4 autoloader
require __DIR__ . '/vendor/autoload.php';

// Init config manger
$config = Config::getInstance();

// Init data manger
$data = Data::getInstance();

// Get all available records and their respective status and timestamps
$data->populateRecords();
$data->populateSets();

// Build the Identify response
$identifyResponse = [
    'repositoryName' => $config->getConfigValue('repositoryName'),
    'baseURL' => Helper::getBaseURL(),
    'protocolVersion' => '2.0',
    'adminEmail' => $config->getConfigValue('adminEmail'),
    'earliestDatestamp' => gmdate('Y-m-d\TH:i:s\Z', $data->getEarliest()),
    'deletedRecord' => $config->getConfigValue('deletedRecord'),
    'granularity' => 'YYYY-MM-DDThh:mm:ssZ'
];

$oai2 = new Server(
    Helper::getBaseURL(),
    $_REQUEST,
    $identifyResponse,
    [
        'GetRecord' => function ($identifier, $metadataPrefix) {
            $records = Data::getInstance()->getRecords();
            $deleted = Data::getInstance()->getDeleted();
            $timestamps = Data::getInstance()->getTimestamps();

            $identifier .= '.xml';

            if (empty($records[$metadataPrefix][$identifier])) {
                return [];
            }

            return [
                'identifier' => $identifier,
                'timestamp' => $timestamps[$metadataPrefix][$identifier],
                'deleted' => $deleted[$metadataPrefix][$identifier],
                'metadata' => $records[$metadataPrefix][$identifier]
            ];
        },
        'ListRecords' => function (
            $metadataPrefix,
            $from = null,
            $until = null,
            $set = null,
            $count = false,
            $deliveredRecords = 0,
            $maxItems = 100
        ) {
            if (!empty($set)) {
                Data::getInstance()->populateRecords($set);
            }
            $records = Data::getInstance()->getRecords();
            $deleted = Data::getInstance()->getDeleted();
            $timestamps = Data::getInstance()->getTimestamps();

            $resultSet = [];
            if (isset($timestamps[$metadataPrefix])) {
                foreach ((array)$timestamps[$metadataPrefix] as $identifier => $timestamp) {
                    if ((is_null($from) || $timestamp >= $from) && (is_null($until) || $timestamp <= $until)) {
                        $resultSet[] = [
                            'identifier' => $identifier,
                            'timestamp' => filemtime($records[$metadataPrefix][$identifier]),
                            'deleted' => $deleted[$metadataPrefix][$identifier],
                            'data' => $records[$metadataPrefix][$identifier]
                        ];
                    }
                }
            }

            if ($count) {
                return count($resultSet);
            }

            return array_slice($resultSet, $deliveredRecords, $maxItems);
        },
        'ListSets' => function (
            $count = false,
            $deliveredRecords = 0,
            $maxItems = 100
        ) {
            $sets = Data::getInstance()->getSets();

            $resultSet = [];

            foreach ($sets as $set) {
                $resultSet[] = [
                    'identifier' => $set,
                    'data' => Data::getInstance()->getSetFile($set),
                ];
            }

            if ($count) {
                return count($resultSet);
            }

            return array_slice($resultSet, $deliveredRecords, $maxItems);
        },
        'ListMetadataFormats' => function ($identifier = '') {
            $records = Data::getInstance()->getRecords();
            $config = Config::getInstance()->getConfig();

            if (!empty($identifier)) {
                $formats = [];
                foreach ($records as $format => $record) {
                    if (!empty($record[$identifier])) {
                        $formats[$format] = $config['metadataPrefix'][$format];
                    }
                }
                if (!empty($formats)) {
                    return $formats;
                } else {
                    throw new Exception('idDoesNotExist');
                }
            }

            return $config['metadataPrefix'];
        }
    ],
    $config->getConfig()
);

$response = $oai2->response();

if (isset($return)) {
    return $response;
} else {
    $response->formatOutput = true;
    $response->preserveWhiteSpace = false;
    header('Content-Type: text/xml');

    $xml_string = $response->saveXML();
    $xml_string = preg_replace('/(?:^|\G)  /um', "\t", $xml_string);
    echo $xml_string;
}
