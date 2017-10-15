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

require_once('oai2exception.php');
require_once('oai2xml.php');

/**
 * This is an implementation of OAI Data Provider version 2.0.
 * @see http://www.openarchives.org/OAI/2.0/openarchivesprotocol.htm
 */
class OAI2Server {

    public $errors = array();
    private $args = array();
    private $verb = '';
    private $deleted_record = 'transient';
    private $max_records = 100;
    private $token_prefix = '/tmp/oai2-';
    private $token_valid = 86400;

    public function __construct($uri, $args, $identifyResponse, $callbacks, $config) {
        $verbs = array('Identify', 'ListMetadataFormats', 'ListSets', 'ListIdentifiers', 'ListRecords', 'GetRecord');
        if (empty($args['verb']) || !in_array($args['verb'], $verbs)) {
            $this->errors[] = new OAI2Exception('badVerb');
            return;
        }
        $this->uri = $uri;
        $this->verb = $args['verb'];
        unset($args['verb']);
        $this->args = $args;
        $this->identifyResponse = $identifyResponse;
        $this->listMetadataFormatsCallback = $callbacks['ListMetadataFormats'];
        $this->listRecordsCallback = $callbacks['ListRecords'];
        $this->getRecordCallback = $callbacks['GetRecord'];
        $this->deleted_record = $config['deletedRecord'];
        $this->max_records = $config['maxRecords'];
        $this->token_prefix = $config['tokenPrefix'];
        $this->token_valid = $config['tokenValid'];
        $this->response = new OAI2XMLResponse($this->uri, $this->verb, $this->args);
        call_user_func(array($this, $this->verb));
    }

    public function response() {
        if (empty($this->errors)) {
            return $this->response->doc;
        } else {
            $errorResponse = new OAI2XMLResponse($this->uri, $this->verb, $this->args);
            $oai_node = $errorResponse->doc->documentElement;
            foreach($this->errors as $e) {
                $node = $errorResponse->addChild($oai_node, 'error', $e->getMessage());
                $node->setAttribute('code', $e->getOAI2Code());
            }
            return $errorResponse->doc;
        }
    }

    public function Identify() {
        if (count($this->args) > 0) {
            foreach($this->args as $key => $val) {
                $this->errors[] = new OAI2Exception('badArgument');
            }
        } else {
            foreach($this->identifyResponse as $key => $val) {
                $this->response->addToVerbNode($key, $val);
            }
        }
    }

    public function ListMetadataFormats() {
        $identifier = '';
        foreach ($this->args as $argument => $value) {
            if ($argument != 'identifier') {
                $this->errors[] = new OAI2Exception('badArgument');
            } else {
                $identifier = $value;
            }
        }
        if (empty($this->errors)) {
            try {
                if ($formats = call_user_func($this->listMetadataFormatsCallback, $identifier)) {
                    foreach($formats as $key => $val) {
                        $cmf = $this->response->addToVerbNode('metadataFormat');
                        $this->response->addChild($cmf, 'metadataPrefix', $key);
                        $this->response->addChild($cmf, 'schema', $val['schema']);
                        $this->response->addChild($cmf, 'metadataNamespace', $val['namespace']);
                    }
                } else {
                    $this->errors[] = new OAI2Exception('noMetadataFormats');
                }
            } catch (OAI2Exception $e) {
                $this->errors[] = $e;
            }
        }
    }

    public function ListSets() {
        if (isset($this->args['resumptionToken'])) {
            if (count($this->args) > 1) {
                $this->errors[] = new OAI2Exception('badArgument');
            } else {
                $this->errors[] = new OAI2Exception('badResumptionToken');
            }
        } else {
            $this->errors[] = new OAI2Exception('noSetHierarchy');
        }
    }

    public function GetRecord() {
        if (!isset($this->args['identifier']) || !isset($this->args['metadataPrefix'])) {
            $this->errors[] = new OAI2Exception('badArgument');
        } else {
            $metadataFormats = call_user_func($this->listMetadataFormatsCallback);
            if (!isset($metadataFormats[$this->args['metadataPrefix']])) {
                $this->errors[] = new OAI2Exception('cannotDisseminateFormat');
            }
        }
        if (empty($this->errors)) {
            try {
                if ($record = call_user_func($this->getRecordCallback, $this->args['identifier'], $this->args['metadataPrefix'])) {
                    $cur_record = $this->response->addToVerbNode('record');
                    $this->response->createHeader($record['identifier'], $this->formatDatestamp($record['timestamp']), $record['deleted'], $cur_record);
                    if (!$record['deleted']) {
                        $this->addMetadata($cur_record, $record['metadata']);
                    }
                } else {
                    $this->errors[] = new OAI2Exception('idDoesNotExist');
                }
            } catch (OAI2Exception $e) {
                $this->errors[] = $e;
            }
        }
    }

    public function ListIdentifiers() {
        $this->ListRecords();
    }

    public function ListRecords() {
        $maxItems = $this->max_records;
        $deliveredRecords = 0;
        $metadataPrefix = $this->args['metadataPrefix'];
        $from = isset($this->args['from']) ? $this->args['from'] : '';
        $until = isset($this->args['until']) ? $this->args['until'] : '';
        if (isset($this->args['resumptionToken'])) {
            if (count($this->args) > 1) {
                $this->errors[] = new OAI2Exception('badArgument');
            } else {
                if (!file_exists($this->token_prefix.$this->args['resumptionToken'])) {
                    $this->errors[] = new OAI2Exception('badResumptionToken');
                } else {
                    if (filemtime($this->token_prefix.$this->args['resumptionToken'])+$this->token_valid < time()) {
                        $this->errors[] = new OAI2Exception('badResumptionToken');
                    } else {
                        if ($readings = $this->readResumptionToken($this->token_prefix.$this->args['resumptionToken'])) {
                            list($deliveredRecords, $metadataPrefix, $from, $until) = $readings;
                        } else {
                            $this->errors[] = new OAI2Exception('badResumptionToken');
                        }
                    }
                }
            }
        } else {
            if (!isset($this->args['metadataPrefix'])) {
                $this->errors[] = new OAI2Exception('badArgument');
            } else {
                $metadataFormats = call_user_func($this->listMetadataFormatsCallback);
                if (!isset($metadataFormats[$this->args['metadataPrefix']])) {
                    $this->errors[] = new OAI2Exception('cannotDisseminateFormat');
                }
            }
            if (isset($this->args['from'])) {
                if (!$this->checkDateFormat($this->args['from'])) {
                    $this->errors[] = new OAI2Exception('badArgument');
                }
            }
            if (isset($this->args['until'])) {
                if (!$this->checkDateFormat($this->args['until'])) {
                    $this->errors[] = new OAI2Exception('badArgument');
                }
            }
            if (isset($this->args['set'])) {
               $this->errors[] = new OAI2Exception('noSetHierarchy');
            }
        }
        if (empty($this->errors)) {
            try {
                if (!($records_count = call_user_func($this->listRecordsCallback, $metadataPrefix, $this->formatTimestamp($from), $this->formatTimestamp($until), true))) {
                    throw new OAI2Exception('noRecordsMatch');
                }
                $records = call_user_func($this->listRecordsCallback, $metadataPrefix, $this->formatTimestamp($from), $this->formatTimestamp($until), false, $deliveredRecords, $maxItems);
                foreach ($records as $record) {
                    if ($this->verb == 'ListRecords') { // for ListIdentifiers, only headers will be returned.
                        $cur_record = $this->response->addToVerbNode('record');
                    }
                    $this->response->createHeader($record['identifier'], $this->formatDatestamp($record['timestamp']), $record['deleted'], $cur_record);
                    if (!$record['deleted'] && $this->verb == 'ListRecords') { // for ListIdentifiers, only headers will be returned.
                        $this->addMetadata($cur_record, $record['metadata']);
                    }
                }
                // Will we need a new ResumptionToken?
                if ($records_count - $deliveredRecords > $maxItems) {
                    $deliveredRecords +=  $maxItems;
                    $restoken = $this->createResumptionToken($deliveredRecords);
                    $expirationDatetime = gmstrftime('%Y-%m-%dT%TZ', time()+$this->token_valid);
                } elseif (isset($this->args['resumptionToken'])) {
                    // Last delivery, return empty ResumptionToken
                    $restoken = null;
                    $expirationDatetime = null;
                }
                if (isset($restoken)) {
                    $this->response->createResumptionToken($restoken, $expirationDatetime, $records_count, $deliveredRecords-$maxItems);
                }
            } catch (OAI2Exception $e) {
                $this->errors[] = $e;
            }
        }
    }

    private function addMetadata($cur_record, $file) {
        $meta_node =  $this->response->addChild($cur_record, 'metadata');
        $fragment = new DOMDocument();
        $fragment->load($file);
        $this->response->importFragment($meta_node, $fragment);
    }

    private function createResumptionToken($delivered_records) {
        list($usec, $sec) = explode(' ', microtime());
        $token = ((int)($usec*1000) + (int)($sec*1000));
        $file = fopen ($this->token_prefix.$token, 'w');
        if($file == false) {
            exit('Cannot write resumption token. Writing permission needs to be changed.');
        }
        $values = array(
            'deliveredRecords' => $delivered_records,
            'metadataPrefix' => isset($this->args['metadataPrefix']) ? $this->args['metadataPrefix'] : '',
            'from' => isset($this->args['from']) ? $this->args['from'] : '',
            'until' => isset($this->args['until']) ? $this->args['until'] : ''
        );
        fputs($file, $values['deliveredRecords'].'#');
        fputs($file, $values['metadataPrefix'].'#');
        fputs($file, $values['from'].'#');
        fputs($file, $values['until'].'#');
        fclose($file);
        return $token;
    }

    private function readResumptionToken($resumptionToken) {
        $rtVal = false;
        $file = fopen($resumptionToken, 'r');
        if ($file != false) {
            $filetext = fgets($file, 255);
            $textparts = explode('#', $filetext);
            fclose($file);
            unlink($resumptionToken);
            $rtVal = array_values($textparts);
        }
        return $rtVal;
    }

    private function formatDatestamp($timestamp) {
        return gmdate('Y-m-d\TH:i:s\Z', $timestamp);
    }

    private function formatTimestamp($datestamp) {
        if (is_array($time = strptime($datestamp, '%Y-%m-%dT%H:%M:%SZ')) || is_array($time = strptime($datestamp, '%Y-%m-%d'))) {
            return gmmktime($time['tm_hour'], $time['tm_min'], $time['tm_sec'], $time['tm_mon'] + 1, $time['tm_mday'], $time['tm_year']+1900);
        } else {
            return null;
        }
    }

    private function checkDateFormat($date) {
        $datetime = DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $date);
        if ($datetime === false) {
            $datetime = DateTime::createFromFormat('Y-m-d', $date);
        }
        return ($datetime !== false) && !array_sum($datetime->getLastErrors());
    }

}
