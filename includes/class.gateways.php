<?php

/**
 * ASANAK Gateway Class Page
 *
 * PHP version 5.6.23 | 7.2.19
 *
 * @category  PLugins
 * @package   Wordpress
 * @author   Customized By Vahid Rezazadeh <vahid.rezazadeh1372@gmail.com>
 */

class WoocommerceIR_Gateways_SMS
{

    private static $_instance;

    /**
     * Gateways init function
     *
     * @return array Indicates the instance
     */
    public static function init()
    {
        if (!self::$_instance)
            self::$_instance = new WoocommerceIR_Gateways_SMS();
        return self::$_instance;
    }

    /**
     * Send SMS.
     *
     * @param sms_data[] $sms_data array structure of sms_data
     *
     * @return boolean
     */
    public function sendAsanak($sms_data)
    {

        $isTemplate = ps_sms_options('send_with_template', 'sms_main_settings');

        $message = $sms_data['sms_body'];
        $numbers1 = $sms_data['number'];

        if ($isTemplate == 'on') {
            $this->sendWithTemplate($message['template'], $message['data'], $numbers1);
            return true;
        } else {

            if ((strpos($message, '&#xfdfc;') != false) || (strpos($message, '&#x0627;') != false)) {
                $message = str_replace("&#xfdfc;", "ریال", $message);
                $message = str_replace("&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;", "تومان", $message);
                if (strpos($message, '&#x0631;') != false) {
                    $message = str_replace("&#x0631;&#xFBFE;&#x0627;&#x0644;", "ریال", $message);
                }
            }

            if ($numbers1) {
                foreach ($numbers1 as $numb) {
                    if (($this->isMobile($numb)) || ($this->isMobileWithz($numb))) {
                        $number[] = str_replace('+98', '0', $numb);
                    }
                }

                @$numbers = array_unique($number);

                if (is_array($numbers) && $numbers) {
                    foreach ($numbers as $value) {
                        $Messages[] = $message;
                    }
                }

                $SendMessage = $this->sendMessage($numbers, $Messages);
                if ($SendMessage == true) {
                    $response = true;
                } else {
                    $response = false;
                }
            } else {
                $response = false;
            }
        }
        return $response;
    }

    /**
     * Verification Code.
     *
     * @param string $template TemplateID
     * @param array $params Params
     * @param string $MobileNumber Mobile Number
     *
     */
    public function sendWithTemplate($template, $params, $MobileNumbers)
    {
        $apiKey = ps_sms_options('persian_woo_sms_username', 'sms_main_settings');
        $apiSecret = ps_sms_options('persian_woo_sms_apiSecret', 'sms_main_settings');
        if (!$apiSecret || !$apiKey) {
            return false;
        }

        foreach ($MobileNumbers as $number) {
            $data = array(
                "template_id" => $template,
                "destination" => str_replace('+98', '0', $number),
                "parameters" => $params
            );

            $this->_executeTemplate($data, $apiKey, $apiSecret);
        }
        return true;
    }

    /**
     * Executes the main method.
     *
     * @param postData[] $postData array of json data
     * @param string $url url
     *
     * @return string Indicates the curl execute result
     */
    private function _execute($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: text/html',
            'Connection: Keep-Alive',
            'Content-type: application/x-www-form-urlencoded;charset=UTF-8'
        ));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * Executes the main method.
     *
     * @param data[] $postData array of json data
     * @param apiKey $apiKey String Api Key
     * @param apiSecret $apiSecret string Api Secret
     */
    private function _executeTemplate($data, $apiKey, $apiSecret)
    {
        $url = "https://api.asanak.com/v1/sms/template";

        $headers = array(
            "api_key: " . trim($apiKey),
            "api_secret: " . trim($apiSecret),
            "cache-control: no-cache",
            "content-type: application/json",
        );
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0
        ));
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;

    }


    /**
     * Send sms.
     *
     * @param MobileNumbers[] $MobileNumbers array structure of mobile numbers
     * @param Messages[] $Messages array structure of messages
     * @param string $SendDateTime Send Date Time
     *
     * @return string Indicates the sent sms result
     */
    public function sendMessage($MobileNumbers, $Messages)
    {
        $username = ps_sms_options('persian_woo_sms_username', 'sms_main_settings');
        $password = ps_sms_options('persian_woo_sms_password', 'sms_main_settings');

        if (!$username || !$password) {
            return false;
        }


        $from = ps_sms_options('persian_woo_sms_sender', 'sms_main_settings');

        foreach ($MobileNumbers as $key => $mobileNumber) {
            if ($mobileNumber && isset($Messages[$key])) {
                $url = "https://panel.asanak.com/webservice/v1rest/sendsms?send_to_blacklist=1&username=$username&password=$password&source=$from&destination=$mobileNumber&message=" . urlencode(trim($Messages[$key]));
                $this->_execute($url);
            }
        }
        return true;
    }


    /**
     * Check if mobile number is valid.
     *
     * @param string $mobile mobile number
     *
     * @return boolean Indicates the mobile validation
     */
    public function isMobile($mobile)
    {
        if (preg_match('/^09(0[1-5]|1[0-9]|3[0-9]|2[0-2]|9[0-1])-?[0-9]{3}-?[0-9]{4}$/', $mobile)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if mobile with zero number is valid.
     *
     * @param string $mobile mobile with zero number
     *
     * @return boolean Indicates the mobile with zero validation
     */
    public function isMobileWithz($mobile)
    {
        if (preg_match('/^9(0[1-5]|1[0-9]|3[0-9]|2[0-2]|9[0-1])-?[0-9]{3}-?[0-9]{4}$/', $mobile)) {
            return true;
        } else {
            return false;
        }
    }
}