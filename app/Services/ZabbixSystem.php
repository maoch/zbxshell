<?php
/**
 * Created by PhpStorm.
 * User: mao
 * Date: 2015/6/30
 * Time: 14:00
 */

namespace App\Services;

use App\Contracts\ApmSystemInterface;
use App\Exceptions\CommonException;
use Crypt;
use stdClass;
use Log;
use Session;
use Cookie;

/**
 * Class ZabbixSystem
 * @package App\Services
 */
class ZabbixSystem implements ApmSystemInterface
{
    /**
     * 调用zabbix接口
     * @param $operation (json)
     * @return Object
     */
    private function post($operation)
    {
        $url = env('ZABBIX_HOST') . '/raidmirror/api_jsonrpc.php';
        $header = array("Content-type: application/json-rpc", "charset=utf-8");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $operation);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * zabbix通用查询接口
     * @param AuthInfo $authInfo
     * @param $method
     * @param $param (json)
     * @param bool $auth 是否需要验证，如果是login,则不需要验证
     * @return Object
     * @throws CommonException
     */
    public function request($authInfo, $method, $param, $auth = true)
    {

        if (empty($authInfo)) {
            return json_encode('');
        }
        $authInfo_param = $authInfo;
        if (!$authInfo instanceof stdClass) {
            $authInfo = json_decode($authInfo);
        }
        $authStr = '';
        if ($auth) {
            if (property_exists($authInfo, 'sessionid')) {
                $sessionid = $authInfo->sessionid;
                $authStr = '"auth": "' . $sessionid . '",';
            }
        }
        $params = '{
                    "jsonrpc": "2.0",
                    "method": "' . $method . '",
                    "params":' . $param . ', ' . $authStr . '
                    "id": "1"
                }';
        $response = $this->post($params);
        $response = json_decode($response);
        if (empty($response)) {
            throw new CommonException('apm.request_error');
        }
        if (!property_exists($response, 'result')) {
            //是否正确的返回
            //需要排除logout,
            if ($method != 'user.logout'&&$method != 'user.login') {
                if (property_exists($response, 'error')) {
                    if ($response->error->data === '"Session terminated, re-login, please."'
                        || $response->error->data === 'Not authorised.'
                        || $response->error->data === 'Not authorized'
                    ) {

                        $this->logout($authInfo_param);
                        $this->login($authInfo_param);
                        //如果param中带有auth信息，则需要替换sessionid
                        $params_obj = json_decode($params);
                        //重新获取Cookie  sessionid
                        $params_obj->auth = Crypt::decrypt(Session::get('sessionid'));
                        $params = json_encode($params_obj);
                        $response = $this->post($params);
                        $response = json_decode($response);
                    }

                }

                if (property_exists($response,
                        'error') && $response->error->data == 'Login name or password is incorrect.'
                ) {
                    //如果用户名密码错误，需要重新设置用户名和密码
                    return new stdClass();
                }

                if (property_exists($response, 'error') && $response->error->data != 'Not authorized') {
                    throw new CommonException('apm.response_error');
                }
            }
        }

        return $response;
    }

    /**
     * zabbix登录
     * @param AutnInfo $authInfo
     * @return bool
     * @throws CommonException
     */
    public function login($authInfo)
    {
        $authobj = json_decode($authInfo);
        $username = $authobj->username;
        $password = $authobj->password;
        $param = '{
                    "user": "' . $username . '",
                    "password": "' . Crypt::decrypt($password) . '"
                }';
        $response = $this->request($authInfo, 'user.login', $param, false);
        if (property_exists($response, 'result')) {
            //登录成功后，设置用户名，密码，sessionid到cookie中
            Log::info('sessionid: ' . $response->result);
            //设置Cookie
            Cookie::queue('username', Crypt::encrypt($username));
            Cookie::queue('password', Crypt::encrypt($password));
            Cookie::queue('sessionid', Crypt::encrypt($response->result));

            Session::put('sessionid', Crypt::encrypt($response->result));

            return true;
        } else {
            return false;
        }
    }


    /**
     * loggou
     * @param AutnInfo $authInfo
     * @return bool
     * @throws CommonException
     */
    public function logout($authInfo)
    {
        $param = '[]';
        $response = $this->request($authInfo, 'user.logout', $param);

        unset($_COOKIE['username']);
        unset($_COOKIE['password']);
        unset($_COOKIE['sessionid']);

        if (property_exists($response, 'result') && $response->result == 'true') {
            return true;
        } elseif (property_exists($response, 'error') && $response->error->data == 'Not authorized') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 查询主机列表
     * @param AutnInfo $authInfo
     * @param $param
     * @return array
     * @throws CommonException
     */
    public function queryHostList($authInfo, $param)
    {
        $response = $this->request($authInfo, 'host.get', $param);
        if (property_exists($response, 'result')) {
            return $response->result;
        } else {
            return [];
        }
    }

    /**
     * 查询主机数量
     * @param AutnInfo $authInfo
     * @param $param
     * @return int
     */
    public function queryHostCount($authInfo, $param)
    {
        $response = $this->queryHostList($authInfo, $param);

        return count($response);
    }

    /**
     * 查询主机详细信息
     * @param AutnInfo $authInfo
     * @param $param
     * @return object
     */
    public function queryHostDetail($authInfo, $param)
    {
        $response = $this->queryHostList($authInfo, $param);
        if (count($response) > 0) {
            return $response[0];
        } else {
            return null;
        }
    }

    /**
     * 查询事件列表
     * @param AuthInfo $authInfo
     * @param $param
     * @return array
     * @throws CommonException
     */
    public function queryEventList($authInfo, $param)
    {
        $response = $this->request($authInfo, 'trigger.get', $param);
        if (property_exists($response, 'result')) {
            return $response->result;
        } else {
            return [];
        }
    }

    /**
     * 统计事件数量
     * @param AutnInfo $authInfo
     * @param $param
     * @return int
     */
    public function queryEventCount($authInfo, $param)
    {
        $response = $this->queryEventList($authInfo, $param);

        return count($response);
    }

    /**
     * 查询时间明细
     * @param AutnInfo $authInfo
     * @param $param
     * @return null
     */
    public function queryEventDetail($authInfo, $param)
    {
        $response = $this->queryEventList($authInfo, $param);
        if (count($response) > 0) {
            return $response[0];
        } else {
            return null;
        }
    }

    /**
     * 查询Map列表
     * @param AutnInfo $authInfo
     * @param $param
     * @return array
     * @throws CommonException
     */
    public function queryMapList($authInfo, $param)
    {
        $response = $this->request($authInfo, 'map.get', $param);
        if (property_exists($response, 'result')) {
            return $response->result;
        } else {
            return [];
        }

    }

    /**
     * 查询Map数量
     * @param AutnInfo $authInfo
     * @param $param
     * @return int
     */
    public function queryMapCount($authInfo, $param)
    {
        $response = $this->queryMapList($authInfo, $param);

        return count($response);
    }

    /**
     * 查询Map的详细信息
     * @param AutnInfo $authInfo
     * @param $param
     * @return Object
     */
    public function queryMapDetail($authInfo, $param)
    {
        $response = $this->queryMapList($authInfo, $param);
        if (count($response) > 0) {
            return $response[0];
        } else {
            return null;
        }
    }

    /**
     * 查询IconMap
     * @param AutnInfo $authInfo
     * @param $param
     * @return array
     * @throws CommonException
     */
    public function queryIconmapList($authInfo, $param)
    {
        $response = $this->request($authInfo, 'iconmap.get', $param);
        if (property_exists($response, 'result')) {
            return $response->result;
        } else {
            return [];
        }
    }

    /**
     * 查询IconMap数量
     * @param AutnInfo $authInfo
     * @param $param
     * @return int
     */
    public function queryIconmapCount($authInfo, $param)
    {
        $response = $this->queryIconmapList($authInfo, $param);

        return count($response);
    }

    /**
     * 查询IconMap详细信息
     * @param AutnInfo $authInfo
     * @param $param
     * @return Obeject
     */
    public function queryIconmapDetail($authInfo, $param)
    {
        $response = $this->queryIconmapList($authInfo, $param);
        if (count($response) > 0) {
            return $response[0];
        } else {
            return null;
        }
    }

    /**
     * 查询图片列表
     * @param AutnInfo $authInfo
     * @param $param
     * @return array
     * @throws CommonException
     */
    public function queryImageList($authInfo, $param)
    {
        $response = $this->request($authInfo, 'image.get', $param);
        if (property_exists($response, 'result')) {
            return $response->result;
        } else {
            return [];
        }
    }

    /**
     * 查询Image数量
     * @param AutnInfo $authInfo
     * @param $param
     * @return int
     */
    public function queryImageCount($authInfo, $param)
    {
        $response = $this->queryImageList($authInfo, $param);

        return count($response);
    }

    /**
     * 查询Image详细信息
     * @param AutnInfo $authInfo
     * @param $param
     * @return Object
     */
    public function queryImageDetail($authInfo, $param)
    {
        $response = $this->queryImageList($authInfo, $param);
        if (count($response) > 0) {
            return $response[0];
        } else {
            return null;
        }
    }


    /**
     * 查询Item列表
     * @param AutnInfo $authInfo
     * @param $param
     * @return array
     * @throws CommonException
     */
    public function queryItemList($authInfo, $param)
    {
        $response = $this->request($authInfo, 'item.get', $param);
        if (property_exists($response, 'result')) {
            $responseArray = $response->result;
            foreach ($responseArray as $key => $value) {
                if ($value->value_type != '3' && $value->value_type != '0') {
                    unset($responseArray[$key]);
                }
            }

            return array_values($responseArray);
        } else {
            return [];
        }
    }

    /**
     * 查询Item数量
     * @param AutnInfo $authInfo
     * @param $param
     * @return int
     */
    public function queryItemCount($authInfo, $param)
    {
        $response = $this->queryItemList($authInfo, $param);

        return count($response);
    }

    /**
     * 查询Item详细信息
     * @param AutnInfo $authInfo
     * @param $param
     * @return Object
     */
    public function queryItemDetail($authInfo, $param)
    {
        $response = $this->queryItemList($authInfo, $param);
        if (count($response) > 0) {
            return $response[0];
        } else {
            return null;
        }
    }

    /**
     * 获取历史信息
     * @param $authInfo
     * @param $param
     * @return array
     * @throws CommonException
     */
    public function queryHistoryList($authInfo, $param)
    {
        $response = $this->request($authInfo, 'history.get', $param);
        if (property_exists($response, 'result')) {
            return $response->result;
        } else {
            return [];
        }
    }

}
