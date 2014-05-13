<?php

/**
 * NamecheapRegistrarApi
 */
class NamecheapRegistrarApi
{
    public static $url = "https://api.namecheap.com/xml.response";
    public static $testUrl = "https://api.sandbox.namecheap.com/xml.response";

    private static $_phoneCountryCodes = array(
        1, 7, 20, 27, 30, 31, 32, 33, 34, 36, 39, 40, 41, 43, 44, 45, 46, 47, 48, 49, 51, 52, 54, 55, 56, 57, 58, 60,
        61, 62, 63, 64, 65, 66, 81, 82, 84, 86, 90, 91, 92, 93, 94, 95, 98, 212, 213, 216, 220, 221, 222, 224, 225, 226,
        227, 228, 229, 230, 231, 232, 233, 234, 235, 236, 237, 238, 239, 240, 241, 242, 243, 244, 245, 246, 248, 249,
        250, 251, 252, 253, 254, 255, 256, 257, 258, 260, 261, 262, 263, 264, 265, 266, 267, 268, 269, 290, 291, 297,
        298, 299, 340, 350, 351, 352, 353, 354, 355, 356, 357, 358, 359, 370, 371, 372, 373, 374, 375, 376, 377, 378,
        380, 381, 382, 385, 386, 387, 389, 420, 421, 423, 500, 501, 502, 503, 504, 505, 506, 507, 508, 509, 590, 591,
        592, 593, 594, 595, 596, 597, 598, 599, 618, 670, 672, 673, 674, 675, 676, 677, 678, 679, 680, 681, 682, 683,
        684, 686, 687, 688, 689, 690, 691, 692, 850, 852, 853, 855, 856, 872, 880, 886, 960, 961, 962, 963, 965, 966,
        967, 968, 970, 971, 972, 973, 974, 975, 976, 977, 992, 993, 994, 995, 996, 998
    );
    private $_apiUser;
    private $_apiKey;

    private $_testMode = true;

    public function  __construct($apiUser, $apiKey, $testMode = true)
    {
        $this->_apiUser = $apiUser;
        $this->_apiKey  = $apiKey;

        $this->setTestMode($testMode);
    }

    /**
     * parseResponse
     * @param string $response
     * @return array
     */
    public function parseResponse($response)
    {
        if (false === ($xml = simplexml_load_string($response))) {
            throw new NamecheapRegistrarApiException("Unable to parse response");
        }
        $result = $this->_xml2Array($xml);
        if ("ERROR" == $result['@attributes']['Status']) {
            $errors = isset($result['Errors']['Error'][0]) ? $result['Errors']['Error'] : array($result['Errors']['Error']);

            $err = $errors[count($errors) - 1];
            $err_msg = sprintf("[%s] %s", $err['@attributes']['Number'], $err['@value']) ;
            throw new NamecheapRegistrarApiException($err_msg, $err['@attributes']['Number']);
        }
        return $result['CommandResponse'];
    }

    /**
     * request
     * @param string $command
     * @param array $params
     * @return string
     */
    public function request($command, array $params)
    {
        $result = false;

        $url = $this->_getApiUrl($command, $params);

        $curl_error = false;
        if (extension_loaded("curl") && ($ch = curl_init()) !== false)
        {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
            // we set peer verification of namecheap server to false - else the process will fail
            // if the host server doesn't have an accurate ca bundle.
            // can turn this on, when you place an up to date ca bundle at your host server.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $result = curl_exec($ch);
            $curl_error = curl_error($ch);

            curl_close($ch);
        }

        // if we didn't get a result from curl, or curl had encountered an error, do it through fsockopen.
        if (!$result || $curl_error) {
            $result = @file_get_contents($url);
        }
        if (!$result) {
            throw new NamecheapRegistrarApiException($curl_error ? $curl_error : "Unable to request data from " . $url);
        }
        return $result;
    }

    /**
     * setTestMode
     * @param boolean $flag
     */
    public function setTestMode($flag)
    {
        $this->_testMode = (bool)$flag;
    }

    // private methods

    /**
     * formatPhone
     * @param string $phone
     * @return string
     */
    private function _formatPhone($phone)
    {
        /**
         * Namecheap API phone format requirement is +NNN.NNNNNNNNNN
         */

        // strip all non-digit characters
        $phone = preg_replace('/[^\d]/', '', $phone);

        // check country code
        $phone_code = "";
        foreach (self::$_phoneCountryCodes as $v) {
            if (preg_match("/^$v\d+$/", $phone)) {
                $phone_code = $v;
                break;
            }
        }
        if (!$phone_code) {
            throw new NamecheapRegistrarApiException("Invalid phone number or phone country code");
        }
        // add '+' and dot to result phone number
        $phone = preg_replace("/^$phone_code/", "+{$phone_code}.", $phone);
        return $phone;
    }

    /**
     * _getApiUrl
     * @param string $command
     * @param array $params
     * @return string
     */
    private function _getApiUrl($command, array $params)
    {
        // set necessary params
        $params['ApiUser'] = $this->_apiUser;
        $params['ApiKey']  = $this->_apiKey;
        $params['Command'] = $command;
        if (!in_array('UserName', $params)) {
            $params['UserName'] = $params['ApiUser'];
        }
        if (!in_array('ClientIp', $params)) {
            $params['ClientIp'] = $this->_getClientIp();
        }
        // format phone/fax fields
        foreach ($params as $k => &$v) {
            if (preg_match('/(Phone|Fax)/i', $k)) {
                $v = $this->_formatPhone($v);
            }
        }
        // force EPPCode to be base64 encoded
        if (array_key_exists('EPPCode', $params)) {
            $params['EPPCode'] = "base64:" . base64_encode($params['EPPCode']);
        }
        return ($this->_testMode ? self::$testUrl : self::$url) . '?' . http_build_query($params);
    }

    /**
     * _getClientIp
     * @return string
     */
    private function _getClientIp()
    {
        $clientip = $_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        return $clientip ? $clientip : "10.11.12.13";
    }

    /**
     * _xml2Array
     * @param string $xml
     * @return array
     */
    private function _xml2Array($xml)
    {
        if (!($xml instanceof SimpleXMLElement)) {
            throw new NamecheapRegistrarApiException("Not a SimpleXMLElement object");
        }
        $result = array();
        foreach ($xml->attributes() as $attrName => $attr) {
            $result['@attributes'][$attrName] = (string)$attr;
        }
        foreach ($xml->children() as $childName => $child) {
            if (array_key_exists($childName, $result)) {
                if (!is_array($result[$childName]) || !isset($result[$childName][1])) {
                    $result[$childName] = array($result[$childName]);
                }
                $result[$childName][] = $this->_xml2Array($child);
            } else {
                $result[$childName] = $this->_xml2Array($child);
            }
        }
        $value = trim((string)$xml);
        if (array_keys($result)) {
            if ($value) {
                $result['@value'] = $value;
            }
        } else {
            $result = $value;
        }
        return $result;
    }
}

/**
 * NamecheapRegistrarApiException
 */
class NamecheapRegistrarApiException extends Exception {}
