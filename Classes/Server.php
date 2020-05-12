<?php
/**
 * Simple OAI-PMH 2.0 Data Provider
 * Copyright (C) 2005 Heinrich Stamerjohanns <stamer@uni-oldenburg.de>
 * Copyright (C) 2011 Jianfeng Li <jianfeng.li@adelaide.edu.au>
 * Copyright (C) 2013 Daniel Neis Araujo <danielneis@gmail.com>
 * Copyright (C) 2017 Sebastian Meyer <sebastian.meyer@opencultureconsulting.com>
 * Copyright (C) 2020 Amaury BALMER <amaury@beapi.fr>
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

/**
 * This is an implementation of OAI Data Provider version 2.0.
 * @see http://www.openarchives.org/OAI/2.0/openarchivesprotocol.htm
 */
class Server
{

    public $errors = [];
    private $args = [];
    private $uri = '';
    private $verb = '';
    private $max_sets = 1;
    private $max_records = 100;
    private $token_prefix = '';
    private $token_valid = 86400;

    public function __construct($uri, $args, $identifyResponse, $callbacks, $config)
    {
        $this->uri = $uri;
        $verbs = ['Identify', 'ListMetadataFormats', 'ListSets', 'ListIdentifiers', 'ListRecords', 'GetRecord'];
        if (empty($args['verb']) || !in_array($args['verb'], $verbs, true)) {
            $this->errors[] = new Exception('badVerb');

            return;
        }
        $this->verb = $args['verb'];
        unset($args['verb']);
        $this->args = $args;
        $this->identifyResponse = $identifyResponse;
        $this->listMetadataFormatsCallback = $callbacks['ListMetadataFormats'];
        $this->listRecordsCallback = $callbacks['ListRecords'];
        $this->listSetsCallback = $callbacks['ListSets'];
        $this->getRecordCallback = $callbacks['GetRecord'];
        $this->max_records = $config['maxRecords'];
        $this->max_sets = $config['maxSets'];
        $this->token_prefix = $config['tokenPrefix'];
        $this->token_valid = $config['tokenValid'];
        $this->response = new Response($this->uri, $this->verb, $this->args);

        $this->{$this->verb}();
    }

    public function response()
    {
        if (empty($this->errors)) {
            return $this->response->doc;
        }

        $errorResponse = new Response($this->uri, $this->verb, $this->args);
        $oai_node = $errorResponse->doc->documentElement;
        foreach ($this->errors as $e) {
            /** @var $e Exception */
            $node = $errorResponse->addChild($oai_node, 'error', $e->getMessage());
            $node->setAttribute('code', $e->getOAI2Code());
        }

        return $errorResponse->doc;
    }

    public function Identify()
    {
        if (count($this->args) > 0) {
            foreach ($this->args as $key => $val) {
                $this->errors[] = new Exception('badArgument');
            }
        } else {
            $cmf = $this->response->addToVerbNode('Identify', null, true);

            foreach ($this->identifyResponse as $key => $val) {
                $this->response->addChild($cmf, $key, $val);
            }
        }
    }

    public function ListMetadataFormats()
    {
        $identifier = '';
        foreach ($this->args as $argument => $value) {
            if ($argument !== 'identifier') {
                $this->errors[] = new Exception('badArgument');
            } else {
                $identifier = $value;
            }
        }
        if (empty($this->errors)) {
            try {
                if ($formats = call_user_func($this->listMetadataFormatsCallback, $identifier)) {
                    foreach ($formats as $key => $val) {
                        $cmf = $this->response->addToVerbNode('metadataFormat');
                        $this->response->addChild($cmf, 'metadataPrefix', $key);
                        $this->response->addChild($cmf, 'schema', $val['schema']);
                        $this->response->addChild($cmf, 'metadataNamespace', $val['namespace']);
                    }
                } else {
                    $this->errors[] = new Exception('noMetadataFormats');
                }
            } catch (Exception $e) {
                $this->errors[] = $e;
            }
        }
    }

    public function ListSets()
    {
        $maxItems = $this->max_sets;
        $deliveredRecords = 0;

        if (isset($this->args['resumptionToken'])) {
            if (count($this->args) > 1) {
                $this->errors[] = new Exception('badArgument');
            } else {
                if (!file_exists($this->token_prefix . $this->args['resumptionToken'])) {
                    $this->errors[] = new Exception('badResumptionToken');
                } else {
                    if (filemtime($this->token_prefix . $this->args['resumptionToken']) + $this->token_valid < time()) {
                        $this->errors[] = new Exception('badResumptionToken');
                    } else {
                        if ($readings = $this->readResumptionToken(
                            $this->token_prefix . $this->args['resumptionToken']
                        )) {
                            list($deliveredRecords) = $readings;
                        } else {
                            $this->errors[] = new Exception('badResumptionToken');
                        }
                    }
                }
            }
        }

        if (empty($this->errors)) {
            try {
                if (!($records_count = call_user_func($this->listSetsCallback, true, $deliveredRecords, $maxItems))) {
                    throw new Exception('noSetHierarchy');
                }

                $records = call_user_func($this->listSetsCallback, false, $deliveredRecords, $maxItems);

                $cur_record = $this->response->addToVerbNode($this->verb, null, true);
                foreach ($records as $record) {
                    $this->addData($cur_record, $record['data']);
                }

                // Will we need a new ResumptionToken?
                if ($records_count - $deliveredRecords > $maxItems) {
                    $deliveredRecords += $maxItems;
                    $restoken = $this->createResumptionToken($deliveredRecords, 'sets');
                    $expirationDatetime = gmstrftime('%Y-%m-%dT%TZ', time() + $this->token_valid);
                } elseif (isset($this->args['resumptionToken'])) {
                    // Last delivery, return empty resumptionToken
                    $restoken = null;
                    $expirationDatetime = null;
                }
                if (isset($restoken)) {
                    $this->response->createResumptionToken(
                        $restoken,
                        $expirationDatetime,
                        $records_count,
                        $deliveredRecords - $maxItems
                    );
                }
            } catch (Exception $e) {
                $this->errors[] = $e;
            }
        }
    }

    public function GetRecord()
    {
        if (!isset($this->args['identifier']) || !isset($this->args['metadataPrefix'])) {
            $this->errors[] = new Exception('badArgument');
        } else {
            $metadataFormats = call_user_func($this->listMetadataFormatsCallback);
            if (!isset($metadataFormats[$this->args['metadataPrefix']])) {
                $this->errors[] = new Exception('cannotDisseminateFormat');
            }
        }
        if (empty($this->errors)) {
            try {
                if ($record = call_user_func(
                    $this->getRecordCallback,
                    $this->args['identifier'],
                    $this->args['metadataPrefix']
                )) {
                    $cur_record = $this->response->addChild($this->response->doc->documentElement, $this->verb);

                    $this->addData($cur_record, $record['metadata']);
                } else {
                    $this->errors[] = new Exception('idDoesNotExist');
                }
            } catch (Exception $e) {
                $this->errors[] = $e;
            }
        }
    }

    public function ListIdentifiers()
    {
        $this->ListRecords();
    }

    public function ListRecords()
    {
        $maxItems = $this->max_records;
        $deliveredRecords = 0;
        $metadataPrefix = isset($this->args['metadataPrefix']) ? $this->args['metadataPrefix'] : '';
        $from = isset($this->args['from']) ? $this->args['from'] : '';
        $until = isset($this->args['until']) ? $this->args['until'] : '';
        $set = isset($this->args['set']) ? $this->args['set'] : '';
        if (isset($this->args['resumptionToken'])) {
            if (count($this->args) > 1) {
                $this->errors[] = new Exception('badArgument');
            } else {
                if (!file_exists($this->token_prefix . $this->args['resumptionToken'])) {
                    $this->errors[] = new Exception('badResumptionToken');
                } else {
                    if (filemtime($this->token_prefix . $this->args['resumptionToken']) + $this->token_valid < time()) {
                        $this->errors[] = new Exception('badResumptionToken');
                    } else {
                        if ($readings = $this->readResumptionToken(
                            $this->token_prefix . $this->args['resumptionToken']
                        )) {
                            list($deliveredRecords, $metadataPrefix, $from, $until, $set) = $readings;
                        } else {
                            $this->errors[] = new Exception('badResumptionToken');
                        }
                    }
                }
            }
        } else {
            if (!isset($this->args['metadataPrefix'])) {
                $this->errors[] = new Exception('badArgument');
            } else {
                $metadataFormats = call_user_func($this->listMetadataFormatsCallback);
                if (!isset($metadataFormats[$this->args['metadataPrefix']])) {
                    $this->errors[] = new Exception('cannotDisseminateFormat');
                }
            }
            if (isset($this->args['from'])) {
                if (!$this->checkDateFormat($this->args['from'])) {
                    $this->errors[] = new Exception('badArgument');
                }
            }
            if (isset($this->args['until'])) {
                if (!$this->checkDateFormat($this->args['until'])) {
                    $this->errors[] = new Exception('badArgument');
                }
            }
        }
        if (empty($this->errors)) {
            try {
                if (!($records_count = call_user_func(
                    $this->listRecordsCallback,
                    $metadataPrefix,
                    $this->formatTimestamp($from),
                    $this->formatTimestamp($until),
                    $set,
                    true
                ))) {
                    throw new Exception('noRecordsMatch');
                }
                $records = call_user_func(
                    $this->listRecordsCallback,
                    $metadataPrefix,
                    $this->formatTimestamp($from),
                    $this->formatTimestamp($until),
                    $set,
                    false,
                    $deliveredRecords,
                    $maxItems
                );

                if ('ListIdentifiers' === $this->verb) {
                    $cur_record = $this->response->addToVerbNode($this->verb, null, true);

                    foreach ($records as $record) {
                        $this->addMetadata($cur_record, $record['data']);
                    }
                } else { // ListRecords
                    $cur_record = $this->response->addToVerbNode($this->verb, null, true);
                    foreach ($records as $record) {
                        $this->addData($cur_record, $record['data']);
                    }
                }

                // Will we need a new ResumptionToken?
                if ($records_count - $deliveredRecords > $maxItems) {
                    $deliveredRecords += $maxItems;
                    $restoken = $this->createResumptionToken(
                        $deliveredRecords,
                        $metadataPrefix,
                        $from,
                        $until,
                        $set
                    );
                    $expirationDatetime = gmstrftime('%Y-%m-%dT%TZ', time() + $this->token_valid);
                } elseif (isset($this->args['resumptionToken'])) {
                    // Last delivery, return empty resumptionToken
                    $restoken = null;
                    $expirationDatetime = null;
                }
                if (isset($restoken)) {
                    $this->response->createResumptionToken(
                        $restoken,
                        $expirationDatetime,
                        $records_count,
                        $deliveredRecords - $maxItems
                    );
                }
            } catch (Exception $e) {
                $this->errors[] = $e;
            }
        }
    }

    private function addMetadata($meta_node, $file)
    {
        if (!is_file($file) || !is_readable($file)) {
            return;
        }

        // TODO: For future, found an another way to extract header...
        $tmp_content = file_get_contents($file);
        preg_match('@<header>(.*)</header>@sm', $tmp_content, $matches);
        if (empty($matches)) {
            return;
        }

        $data = new \DOMDocument();
        $data->loadXML('<?xml version="1.0" encoding="UTF-8"?>' . $matches[0]);

        $this->response->importFragment($meta_node, $data);
    }

    private function addData($meta_node, $file)
    {
        if (!is_file($file) || !is_readable($file)) {
            return;
        }

        if (filesize($file) === 0) {
            $data = new \DOMDocument();
            $data->loadXML($this->getEmptyRecord($file));
        } else {
            $data = new \DOMDocument();
            $data->load($file);
        }

        $this->response->importFragment($meta_node, $data);
    }

    private function getEmptyRecord($file)
    {
        // Fake a empty document
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<record>';
        $xml .= '<header status="deleted">';
        $xml .= '<identifier>' . basename($file, '.xml') . '</identifier>';
        $xml .= '<datestamp>' . $this->formatDatestamp(filemtime($file)) . '</datestamp>';
        $xml .= '</header>';
        $xml .= '</record>';

        return $xml;
    }

    private function createResumptionToken($deliveredRecords, $metadataPrefix, $from = '', $until = '', $set = '')
    {
        list($usec, $sec) = explode(' ', microtime());
        $token = ((int)($usec * 1000) + (int)($sec * 1000)) . '_' . $metadataPrefix;
        $file = fopen($this->token_prefix . $token, 'wb');
        if ($file === false) {
            exit('Cannot write resumption token. Writing permission needs to be changed.');
        }
        fwrite($file, $deliveredRecords . '#');
        fwrite($file, $metadataPrefix . '#');
        fwrite($file, $from . '#');
        fwrite($file, $until . '#');
        fwrite($file, $set);
        fclose($file);

        return $token;
    }

    private function readResumptionToken($resumptionToken)
    {
        $rtVal = false;
        $file = fopen($resumptionToken, 'rb');
        if ($file !== false) {
            $filetext = fgets($file, 255);
            $textparts = explode('#', $filetext);
            fclose($file);
            unlink($resumptionToken);
            $rtVal = array_values($textparts);
        }

        return $rtVal;
    }

    private function formatDatestamp($timestamp)
    {
        return gmdate('Y-m-d\TH:i:s\Z', $timestamp);
    }

    private function formatTimestamp($datestamp)
    {
        if (is_array($time = strptime($datestamp, '%Y-%m-%dT%H:%M:%SZ')) || is_array(
                $time = strptime($datestamp, '%Y-%m-%d')
            )) {
            return gmmktime(
                $time['tm_hour'],
                $time['tm_min'],
                $time['tm_sec'],
                $time['tm_mon'] + 1,
                $time['tm_mday'],
                $time['tm_year'] + 1900
            );
        }

        return null;
    }

    private function checkDateFormat($date)
    {
        $datetime = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $date);
        if ($datetime === false) {
            $datetime = \DateTime::createFromFormat('Y-m-d', $date);
        }

        return ($datetime !== false) && !array_sum($datetime::getLastErrors());
    }

}
