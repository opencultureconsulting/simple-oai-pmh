<?php
/**
 * Simple OAI-PMH 2.0 Data Provider
 * Copyright (C) 2011 Jianfeng Li
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

require_once('oai2config.php');
require_once('oai2server.php');

// Get all available records and their respective timestamps
$records = array();
$timestamps = array();

$files = glob('data/*.xml');
foreach($files as $file) {
  $records[pathinfo($file, PATHINFO_FILENAME)] = $file;
  $timestamps[filemtime($file)][] = pathinfo($file, PATHINFO_FILENAME);
};

ksort($records);
reset($records);

ksort($timestamps);
reset($timestamps);

// Build the Identify response
$identifyResponse = array(
  'repositoryName' => $config['repositoryName'],
  'baseURL' => $config['baseURL'],
  'protocolVersion' => '2.0',
  'adminEmail' => $config['adminEmail'],
  'earliestDatestamp' => gmdate('Y-m-d\TH:i:s\Z', key($timestamps)),
  'deletedRecord' => 'no',
  'granularity' => 'YYYY-MM-DDThh:mm:ssZ'
);

$oai2 = new OAI2Server(
  'http://'.$_SERVER['HTTP_HOST'].parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH),
  $_GET,
  $identifyResponse,
  array(
    'GetRecord' =>
    function($identifier, $metadataPrefix) {
      if (empty($records[$identifier])) {
        return array();
      } else {
        return array(
          'identifier' => $identifier,
          'timestamp' => filemtime($records[$identifier]),
          'metadata' => $records[$identifier]
        );
      }
    },
    'ListRecords' =>
    function($metadataPrefix, $from = null, $until = null, $count = false, $deliveredRecords = 0, $maxItems = 100) {
      $resultSet = array();
      foreach($timestamps as $timestamp => $identifiers) {
        if ((is_null($from) || $timestamp >= $from) && (is_null($until) || $timestamp <= $until)) {
          foreach($identifiers as $identifier) {
            $resultSet[] = array(
              'identifier' => $identifier,
              'timestamp' => filemtime($records[$identifier]),
              'metadata' => $records[$identifier]
            );
          }
        }
      }
      if ($count) {
        return count($resultSet);
      } else {
        return array_slice($resultSet, $deliveredRecords, $maxItems);
      }
    },
    'ListMetadataFormats' =>
    function($identifier = '') {
      if (!empty($identifier) && empty($records[$identifier]) {
        throw new OAI2Exception('idDoesNotExist');
      } else {
        return array(
          $config['metadataFormat'] => array (
            'metadataPrefix' => $config['metadataFormat'],
            'schema'=> $config['metadataSchema'],
            'metadataNamespace' => $config['metadataNamespace']
          )
        );
      }
    }
  ),
  $config
);

$response = $oai2->response();

if (isset($return)) {
  return $response;
} else {
  $response->formatOutput = true;
  $response->preserveWhiteSpace = false;
  header('Content-Type: text/xml');
  echo $response->saveXML();
}
