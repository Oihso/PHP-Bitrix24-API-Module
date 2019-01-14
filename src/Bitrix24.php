<?php
/**
 * Created by Rwaps Studio.
 * User: Oihso
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bitrix24API;

use Bitrix24API\AuthModule;

/**
 * Class Bitrix24
 * @author Oihso <ia@rwaps.com>
 * @package Bitrix24API
 */
class Bitrix24
{
    private $auth;
    private $config;

    /**
     * Bitrix24 constructor.
     * @param array $config Bitrix24 configuration.
     *  Configuration parameters example:
     *  array(
     *      'companyDomain' => 'example.bitrix24.com', //Bitrix24 company URL
     *      'scope' => 'crm,user,telephony', //Bitrix24 auth scopes. Available variants: https://training.bitrix24.com/rest_help/rest_sum/premissions_scope.php
     *
     *      //Auth data
     *      'auth' => array(
     *          //Bitrix24 User auth data
     *          'login'    => 'user@bitrix24.com',
     *          'password' => '1234',
     *
     *          //Bitrix24 App auth data
     *          'clientId' => 'local.55a6ca262e8482.12345678',
     *          'clientSecret' => 'eOk9XtOWbdTjUgQmBL1MYNpKl0Jwt11JLHYHIADX62f3c6PA29'
     *      ),
     *
     *      //Database config
     *      'database' => array(
     *          'settingsTableName' => 'config',
     *          'settingsKeyName' => 'key',
     *          'settingsValueName' => 'value'
     *      )
     *  )
     * @param array $mysqliConnection Mysqli connection array
     */
    public function __construct($config = array(), $mysqliConnection = array())
    {
        $this->auth = new AuthModule($config, $mysqliConnection);
        $this->config = $config;
    }

    /**
     * @param string $method For available methods see documentation: https://training.bitrix24.com/rest_help/rest_sum/index.php
     * @param array $data For available parameters see documentation: https://training.bitrix24.com/rest_help/rest_sum/index.php
     * @return mixed Data array from Bitrix24 REST API / null if nothing received
     */
    public function callMethod($method, $data = array())
    {
        $c = curl_init('https://' . $this->config['companyDomain'] . '/rest/' . $method . '.json?auth=' . $this->auth->getAuthCode());
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = json_decode(curl_exec($c), true);
        if (isset($response['result']))
            return $response['result'];
        else
            return null;
    }
}