<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer;

class ParserService
{
    /**
     * This function parses xenserver result to an array. But this function removes RO or RW values.
     *
     * @param $result
     * @return array
     */
    public static function parseResult($result) : ?array {
        $exploded = explode(PHP_EOL, $result);

        $data = [];

        foreach ($exploded as $line) {
            $tempLine = explode(':', $line);

            $key = trim($tempLine[0]);
            $key = str_replace('( RO)', '', $key);
            $key = str_replace('(SRO)', '', $key);
            $key = str_replace('( RW)', '', $key);
            $key = str_replace('(SRW)', '', $key);
            $key = str_replace('(MRO)', '', $key);
            $key = str_replace('(MRW)', '', $key);
            $key = trim($key);

            unset($tempLine[0]);
            $params = implode(':', $tempLine);

            switch ($key) {
                case 'allowed-operations':
                case 'current-operations':
                case 'VDIs':
                case 'PBDs':
                case 'sm-config':
                    $params = explode(';', $params);
                    break;
                case 'device-config':
                    $params = self::parseDeviceConfigParameters($params);
                    break;
                default:
                    $params = trim($params);
            }

            if(is_array($params)) {
                $tempParams = [];

                foreach ($params as $param) {
                    $tempParams[] = trim($param);
                }

                $params = $tempParams;
            }

            if($key != '')
                $data[$key] = $params;
        }

        return $data;
    }

    public static function parseDeviceConfigParameters($connectionParameters) :array {
        if(is_array($connectionParameters))
            return $connectionParameters;

        $array = explode(';', $connectionParameters);

        $parameters = [];

        foreach ($array as $item) {
            $item = trim($item);

            $explodedItem = explode(':', $item);

            $key = $explodedItem[0];

            unset($explodedItem[0]);
            $parameter = implode(':', $explodedItem);

            $parameters[$key] = trim($parameter);
        }

        return $parameters;
    }

    public static function parseListResult($result) : array {
        $lines = explode(PHP_EOL, $result);

        $array = [];

        $data = '';

        foreach ($lines as $line) {
            //  We are looking at this because if there is no data in line, this means that
            //  this is the end of that array
            if($line != '') {
                $data .= $line . PHP_EOL;
            }

            //  If we see empty line, that array is finished.
            if($line == '') {
                //  We are trying to parse. If we have an object we add it to the result list.
                $tempData = self::parseResult($data);

                //  Returns null or empty array if we cannot find data to parse
                if($tempData)
                    $array[] = $tempData;

                $data = '';
            }
        }

        //  In the end of the response, there is no empty line, that is why we convert the last data here.
        $array[] = self::parseResult($data);

        return $array;
    }

    public static function isError($result) : bool {
        if($result == 'Error: No matching VMs found') {
            return true;
        }

        if(str_contains($result, 'The uuid you supplied was invalid.')) {
            logger()->error('[XenServerService@isError] Cannot find the uuid you provide. Either there is no
object like this, or the uuid is different. I highly suggest you to delete this and start the sync.');

            return true;
        }

        return false;
    }
}
