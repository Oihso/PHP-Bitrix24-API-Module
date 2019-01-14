<?php
/**
 * Created by Rwaps Studio.
 * User: Oihso
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bitrix24API;

/**
 * Class AuthModule
 * @author Oihso <ia@rwaps.com>
 * @package Bitrix24API
 */
class AuthModule
{
    private $config;
    private $DB;
    private $lastUpdateTime = 0;
    private $authCode;

    /**
     * Bitrix24>AuthModule constructor.
     * @param array $config Configuration array. See parameters in "Bitrix24" class.
     * @param array $mysqli Mysqli connection array.
     */
    public function __construct($config = array(), $mysqli = array())
    {
        $this->config = $config;
        $this->DB = mysqli_connect($mysqli[0], $mysqli[1], $mysqli[2], $mysqli[3], $mysqli[4]) or die(mysqli_error($this->DB));
    }

    /**
     * Auth module
     */
    public function auth()
    {
        $_url = 'https://' . $this->config['companyDomain'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $_url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($ch);
        $l = '';
        if (preg_match('#Location: (.*)#', $res, $r)) {
            $l = trim($r[1]);
        }

        curl_setopt($ch, CURLOPT_URL, $l);
        $res = curl_exec($ch);
        preg_match('#name="backurl" value="(.*)"#', $res, $math);
        $post = http_build_query([
            'AUTH_FORM' => 'Y',
            'TYPE' => 'AUTH',
            'backurl' => $math[1],
            'USER_LOGIN' => $this->config['auth']['login'],
            'USER_PASSWORD' => $this->config['auth']['password'],
            'USER_REMEMBER' => 'Y'
        ]);
        curl_setopt($ch, CURLOPT_URL, 'https://www.bitrix24.net/auth/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $res = curl_exec($ch);
        $l = '';
        if (preg_match('#Location: (.*)#', $res, $r)) {
            $l = trim($r[1]);
        }

        curl_setopt($ch, CURLOPT_URL, $l);
        $res = curl_exec($ch);
        $l = '';
        if (preg_match('#Location: (.*)#', $res, $r)) {
            $l = trim($r[1]);
        }

        curl_setopt($ch, CURLOPT_URL, $l);
        $res = curl_exec($ch);

        curl_setopt($ch, CURLOPT_URL, 'https://' . $this->config['companyDomain'] . '/oauth/authorize/?response_type=code&client_id=' . $this->config['auth']['clientId']);
        $res = curl_exec($ch);
        $l = '';
        if (preg_match('#Location: (.*)#', $res, $r)) {
            $l = trim($r[1]);
        }

        preg_match('/code=(.*)&do/', $l, $code);
        $code = $code[1];
        curl_setopt($ch, CURLOPT_URL, 'https://' . $this->config['companyDomain'] . '/oauth/token/?grant_type=authorization_code&client_id=' . $this->config['auth']['clientId'] . '&client_secret=' . $this->config['auth']['clientSecret'] . '&code=' . $code . '&scope=' . $this->config['scope']);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $res = curl_exec($ch);
        curl_close($ch);
        $resArr = explode(',', $res);
        $this->authCode = str_replace(array('{"access_token":"', '"'), array('', ''), $resArr[0]);
        $this->setAppCode();
    }

    /**
     * Stores auth code in database
     */
    private function setAppCode()
    {
        $data = mysqli_fetch_array(mysqli_query($this->DB,"SELECT {$this->config['database']['settingsValueName']} FROM {$this->config['database']['settingsTableName']} WHERE {$this->config['database']['settingsKeyName']}='php_bitrix24_auth_time'"), MYSQLI_ASSOC) or die(mysqli_error($this->DB));
        $this->lastUpdateTime = $data[$this->config['database']['settingsValueName']];
        if (time() - $this->lastUpdateTime >= 3000) {
            mysqli_query($this->DB,"UPDATE {$this->config['database']['settingsTableName']} SET {$this->config['database']['settingsValueName']}='{$this->app_code}' WHERE {$this->config['database']['settingsKeyName']}='php_bitrix24_auth_code'") or die(mysqli_error($this->DB));
            mysqli_query($this->DB,"UPDATE {$this->config['database']['settingsTableName']} SET {$this->config['database']['settingsValueName']}='{$this->lastUpdateTime}' WHERE {$this->config['database']['settingsKeyName']}='php_bitrix24_auth_time'") or die(mysqli_error($this->DB));
        }
    }

    /**
     * @return mixed Returns auth code
     */
    public function getAuthCode()
    {
        if (time() - $this->lastUpdateTime >= 3000) {
            $this->auth();
        }
        $data = mysqli_fetch_array(mysqli_query($this->DB,"SELECT {$this->config['database']['settingsValueName']} FROM {$this->config['database']['settingsTableName']} WHERE {$this->config['database']['settingsKeyName']}='php_bitrix24_auth_code'"), MYSQLI_ASSOC) or die(mysqli_error($this->DB));
        return $data[$this->config['database']['settingsValueName']];
    }
}