<?php
/**
 * Created by PhpStorm.
 * User: mao
 * Date: 2015/7/9
 * Time: 15:28
 */

namespace App\Services;

use Crypt;
use Mockery;
use TestCase;
use Cookie;

function curl_exec($curl)
{
    return ApmSystemTest::$functions->curl_exec($curl);
}

class ApmSystemTest extends TestCase
{
    public static $functions;
    private $apm;
    private $authInfo;

    private function getKey($username, $password)
    {
        $password = Crypt::encrypt($password);
        $info = array('username' => $username, 'password' => $password);

        return json_encode($info);
    }

    public function setup()
    {
        parent::setUp();
        self::$functions = $this->getMockBuilder('Object')
            ->setMethods(array('curl_exec'))->getMock();
        $this->apm = new ZabbixSystem();
        $this->authInfo = $this->getKey('admin', '123456');
    }

    public function testLoginSuccess()
    {
        $return = '{
                            "jsonrpc": "2.0",
                            "result": "0424bd59b807674191e7d77572075f33",
                            "id": 1
                        }';
        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->login($this->authInfo);
        Cookie::shouldReceive()->once()->with('username', Crypt::encrypt('admin'));
        Cookie::shouldReceive()->once()->with('password', Crypt::encrypt('123456'));
        Cookie::shouldReceive()->once()->with('sessionid', Crypt::encrypt('admin'));

        $this->assertTrue($response);
    }

    /**
     * 对异常情况进行测试
     */
    public function testLoginFailed_InvalidParams()
    {
        $return = '{
                        "jsonrpc": "2.0",
                        "error": {
                            "code": -32602,
                            "message": "Invalid params.",
                            "data": "No groups for host \"Linux server\"."
                        },
                        "id": 1
                    }';
        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->login($this->authInfo);
        $this->assertFalse($response);
    }

    public function testLoginFailed_NotAuthorized()
    {
        $return = '{
                      "jsonrpc": "2.0",
                      "error": {
                        "code": -32602,
                        "message": "Invalid params.",
                        "data": "Not authorized"
                      },
                      "id": "1"
                    }';
        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->login($this->authInfo);
        $this->assertFalse($response);
    }

    /**
     * 对异常情况进行测试
     * @expectedException App\Exceptions\CommonException
     * @expectedExceptionMessage apm.request_error
     */
    public function testLoginFailed_NOResponse()
    {
        $return = null;
        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->login($this->authInfo);
        $this->assertFalse($response);
    }

    /**
     * logout测试成功
     */
    public function testLogoutSuccess()
    {
        $return = '{
                        "jsonrpc": "2.0",
                        "result": true,
                        "id": 1
                    }';
        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->logout($this->authInfo);
        $this->assertTrue($response);
    }

    /**
     *查询主机测试成功
     */
    public function testQueryHostListSuccess()
    {
        $params = '{
                        "output": "extend"
                        }';
        $return = '{
                        "jsonrpc": "2.0",
                        "result": [
                            {
                                "maintenances": [],
                                "hostid": "10160",
                                "proxy_hostid": "0",
                                "host": "Zabbix server",
                                "status": "0",
                                "disable_until": "0",
                                "error": "",
                                "available": "0",
                                "errors_from": "0",
                                "lastaccess": "0",
                                "ipmi_authtype": "-1",
                                "ipmi_privilege": "2",
                                "ipmi_username": "",
                                "ipmi_password": "",
                                "ipmi_disable_until": "0",
                                "ipmi_available": "0",
                                "snmp_disable_until": "0",
                                "snmp_available": "0",
                                "maintenanceid": "0",
                                "maintenance_status": "0",
                                "maintenance_type": "0",
                                "maintenance_from": "0",
                                "ipmi_errors_from": "0",
                                "snmp_errors_from": "0",
                                "ipmi_error": "",
                                "snmp_error": "",
                                "jmx_disable_until": "0",
                                "jmx_available": "0",
                                "jmx_errors_from": "0",
                                "jmx_error": "",
                                "name": "Zabbix server"
                            },
                            {
                                "maintenances": [],
                                "hostid": "10167",
                                "proxy_hostid": "0",
                                "host": "Linux server",
                                "status": "0",
                                "disable_until": "0",
                                "error": "",
                                "available": "0",
                                "errors_from": "0",
                                "lastaccess": "0",
                                "ipmi_authtype": "-1",
                                "ipmi_privilege": "2",
                                "ipmi_username": "",
                                "ipmi_password": "",
                                "ipmi_disable_until": "0",
                                "ipmi_available": "0",
                                "snmp_disable_until": "0",
                                "snmp_available": "0",
                                "maintenanceid": "0",
                                "maintenance_status": "0",
                                "maintenance_type": "0",
                                "maintenance_from": "0",
                                "ipmi_errors_from": "0",
                                "snmp_errors_from": "0",
                                "ipmi_error": "",
                                "snmp_error": "",
                                "jmx_disable_until": "0",
                                "jmx_available": "0",
                                "jmx_errors_from": "0",
                                "jmx_error": "",
                                "name": "Linux server"
                            }
                        ],
                        "id": 1
                    }';

        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->queryHostList($this->authInfo, $params);
        $this->assertEquals(2, count($response));
        $this->assertEquals('10160', $response[0]->hostid);
        $this->assertEquals('Zabbix server', $response[0]->host);
        $this->assertEquals('10167', $response[1]->hostid);
        $this->assertEquals('Linux server', $response[1]->host);
    }

    /**
     * 查询主机数量查询成功
     */
    public function testQueryHostCountSuccess()
    {
        $params = '{
                        "output": "extend"
                   }';
        $return = '{
                        "jsonrpc": "2.0",
                        "result": [
                            {
                                "maintenances": [],
                                "hostid": "10160",
                                "proxy_hostid": "0",
                                "host": "Zabbix server",
                                "status": "0",
                                "disable_until": "0",
                                "error": "",
                                "available": "0",
                                "errors_from": "0",
                                "lastaccess": "0",
                                "ipmi_authtype": "-1",
                                "ipmi_privilege": "2",
                                "ipmi_username": "",
                                "ipmi_password": "",
                                "ipmi_disable_until": "0",
                                "ipmi_available": "0",
                                "snmp_disable_until": "0",
                                "snmp_available": "0",
                                "maintenanceid": "0",
                                "maintenance_status": "0",
                                "maintenance_type": "0",
                                "maintenance_from": "0",
                                "ipmi_errors_from": "0",
                                "snmp_errors_from": "0",
                                "ipmi_error": "",
                                "snmp_error": "",
                                "jmx_disable_until": "0",
                                "jmx_available": "0",
                                "jmx_errors_from": "0",
                                "jmx_error": "",
                                "name": "Zabbix server"
                            }
                        ],
                        "id": 1
                    }';

        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->queryHostCount($this->authInfo, $params);
        $this->assertEquals(1, $response);
    }

    /**
     * 查询主机明细测试成功
     */
    public function testQueryHostDetailSuccess()
    {
        $params = '{
                        "output": "extend"
                        }';
        $return = '{
                        "jsonrpc": "2.0",
                        "result": [
                            {
                                "maintenances": [],
                                "hostid": "10160",
                                "proxy_hostid": "0",
                                "host": "Zabbix server",
                                "status": "0",
                                "disable_until": "0",
                                "error": "",
                                "available": "0",
                                "errors_from": "0",
                                "lastaccess": "0",
                                "ipmi_authtype": "-1",
                                "ipmi_privilege": "2",
                                "ipmi_username": "",
                                "ipmi_password": "",
                                "ipmi_disable_until": "0",
                                "ipmi_available": "0",
                                "snmp_disable_until": "0",
                                "snmp_available": "0",
                                "maintenanceid": "0",
                                "maintenance_status": "0",
                                "maintenance_type": "0",
                                "maintenance_from": "0",
                                "ipmi_errors_from": "0",
                                "snmp_errors_from": "0",
                                "ipmi_error": "",
                                "snmp_error": "",
                                "jmx_disable_until": "0",
                                "jmx_available": "0",
                                "jmx_errors_from": "0",
                                "jmx_error": "",
                                "name": "Zabbix server"
                            }
                        ],
                        "id": 1
                    }';

        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->queryHostDetail($this->authInfo, $params);
        $this->assertTrue(count($response) == 1);
        $this->assertEquals('10160', $response->hostid);
        $this->assertEquals('Zabbix server', $response->host);
    }

    /**
     *查询Event测试成功
     */
    public function testQueryEventListSuccess()
    {
        $params = '{
                    "output": [
                        "triggerid",
                        "description",
                        "priority"
                    ],
                    "filter": {
                        "value": 1
                    },
                    "sortfield": "priority",
                    "sortorder": "DESC"
                }';
        $return = '{
                    "jsonrpc": "2.0",
                    "result": [
                        {
                            "functions": [
                                {
                                    "functionid": "13513",
                                    "itemid": "24350",
                                    "function": "diff",
                                    "parameter": "0"
                                }
                            ],
                            "triggerid": "14062",
                            "expression": "{13513}>0",
                            "description": "/etc/passwd has been changed on {HOST.NAME}",
                            "url": "",
                            "status": "0",
                            "value": "0",
                            "priority": "2",
                            "lastchange": "0",
                            "comments": "",
                            "error": "",
                            "templateid": "10016",
                            "type": "0",
                            "state": "0",
                            "flags": "0"
                        }
                    ],
                    "id": 1
                }';
        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->queryEventList($this->authInfo, $params);
        $this->assertEquals(1, count($response));
        $this->assertEquals('14062', $response[0]->triggerid);
    }

    /**
     * 测试Event数量成功
     */
    public function testQueryEventCountSuccess()
    {
        $params = '{
                    "output": [
                        "triggerid",
                        "description",
                        "priority"
                    ],
                    "filter": {
                        "value": 1
                    },
                    "sortfield": "priority",
                    "sortorder": "DESC"
                }';
        $return = '{
                    "jsonrpc": "2.0",
                    "result": [
                        {
                            "functions": [
                                {
                                    "functionid": "13513",
                                    "itemid": "24350",
                                    "function": "diff",
                                    "parameter": "0"
                                }
                            ],
                            "triggerid": "14062",
                            "expression": "{13513}>0",
                            "description": "/etc/passwd has been changed on {HOST.NAME}",
                            "url": "",
                            "status": "0",
                            "value": "0",
                            "priority": "2",
                            "lastchange": "0",
                            "comments": "",
                            "error": "",
                            "templateid": "10016",
                            "type": "0",
                            "state": "0",
                            "flags": "0"
                        }
                    ],
                    "id": 1
                }';
        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->queryEventCount($this->authInfo, $params);
        $this->assertTrue($response == 1);
    }

    /**
     * 测试查询Event详细信息成功
     */
    public function testQueryEventDetail()
    {
        $params = '{
                    "output": [
                        "triggerid",
                        "description",
                        "priority"
                    ],
                    "filter": {
                        "value": 1
                    },
                    "sortfield": "priority",
                    "sortorder": "DESC"
                }';
        $return = '{
                    "jsonrpc": "2.0",
                    "result": [
                        {
                            "functions": [
                                {
                                    "functionid": "13513",
                                    "itemid": "24350",
                                    "function": "diff",
                                    "parameter": "0"
                                }
                            ],
                            "triggerid": "14062",
                            "expression": "{13513}>0",
                            "description": "/etc/passwd has been changed on {HOST.NAME}",
                            "url": "",
                            "status": "0",
                            "value": "0",
                            "priority": "2",
                            "lastchange": "0",
                            "comments": "",
                            "error": "",
                            "templateid": "10016",
                            "type": "0",
                            "state": "0",
                            "flags": "0"
                        }
                    ],
                    "id": 1
                }';
        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->queryEventDetail($this->authInfo, $params);
        $this->assertEquals('14062', $response->triggerid);
    }

    public function testQueryMapListSuccess()
    {
        $params = '{
                    "output": "extend",
                    "selectSelements": "extend",
                    "selectLinks": "extend",
                    "sysmapids": "3"
                }';
        $return = '{
                    "jsonrpc": "2.0",
                    "result": [
                        {
                            "selements": [
                                {
                                    "selementid": "10",
                                    "sysmapid": "3",
                                    "elementid": "0",
                                    "elementtype": "4",
                                    "iconid_off": "1",
                                    "iconid_on": "0",
                                    "label": "Zabbix server",
                                    "label_location": "3",
                                    "x": "11",
                                    "y": "141",
                                    "iconid_disabled": "0",
                                    "iconid_maintenance": "0",
                                    "elementsubtype": "0",
                                    "areatype": "0",
                                    "width": "200",
                                    "height": "200",
                                    "viewtype": "0",
                                    "use_iconmap": "1",
                                    "urls": []
                                },
                                {
                                    "selementid": "11",
                                    "sysmapid": "3",
                                    "elementid": "0",
                                    "elementtype": "4",
                                    "iconid_off": "1",
                                    "iconid_on": "0",
                                    "label": "Web server",
                                    "label_location": "3",
                                    "x": "211",
                                    "y": "191",
                                    "iconid_disabled": "0",
                                    "iconid_maintenance": "0",
                                    "elementsubtype": "0",
                                    "areatype": "0",
                                    "width": "200",
                                    "height": "200",
                                    "viewtype": "0",
                                    "use_iconmap": "1",
                                    "urls": []
                                }
                            ],
                            "links": [
                                {
                                    "linkid": "23",
                                    "sysmapid": "3",
                                    "selementid1": "10",
                                    "selementid2": "11",
                                    "drawtype": "0",
                                    "color": "00CC00",
                                    "label": "",
                                    "linktriggers": []
                                }
                            ],
                            "sysmapid": "3",
                            "name": "Local nerwork",
                            "width": "400",
                            "height": "400",
                            "backgroundid": "0",
                            "label_type": "2",
                            "label_location": "3",
                            "highlight": "1",
                            "expandproblem": "1",
                            "markelements": "0",
                            "show_unack": "0",
                            "grid_size": "50",
                            "grid_show": "1",
                            "grid_align": "1",
                            "label_format": "0",
                            "label_type_host": "2",
                            "label_type_hostgroup": "2",
                            "label_type_trigger": "2",
                            "label_type_map": "2",
                            "label_type_image": "2",
                            "label_string_host": "",
                            "label_string_hostgroup": "",
                            "label_string_trigger": "",
                            "label_string_map": "",
                            "label_string_image": "",
                            "iconmapid": "0",
                            "expand_macros": "0",
                            "severity_min": "0"
                        }
                    ],
                    "id": 1
                }';
        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->queryMapList($this->authInfo, $params);
        $this->assertEquals(1, count($response));
        $this->assertEquals('3', $response[0]->sysmapid);
    }

    public function testQueryMapCountSuccess()
    {
        $params = '{
                    "output": "extend",
                    "selectSelements": "extend",
                    "selectLinks": "extend",
                    "sysmapids": "3"
                }';
        $return = '{
                    "jsonrpc": "2.0",
                    "result": [
                        {
                            "selements": [
                                {
                                    "selementid": "10",
                                    "sysmapid": "3",
                                    "elementid": "0",
                                    "elementtype": "4",
                                    "iconid_off": "1",
                                    "iconid_on": "0",
                                    "label": "Zabbix server",
                                    "label_location": "3",
                                    "x": "11",
                                    "y": "141",
                                    "iconid_disabled": "0",
                                    "iconid_maintenance": "0",
                                    "elementsubtype": "0",
                                    "areatype": "0",
                                    "width": "200",
                                    "height": "200",
                                    "viewtype": "0",
                                    "use_iconmap": "1",
                                    "urls": []
                                },
                                {
                                    "selementid": "11",
                                    "sysmapid": "3",
                                    "elementid": "0",
                                    "elementtype": "4",
                                    "iconid_off": "1",
                                    "iconid_on": "0",
                                    "label": "Web server",
                                    "label_location": "3",
                                    "x": "211",
                                    "y": "191",
                                    "iconid_disabled": "0",
                                    "iconid_maintenance": "0",
                                    "elementsubtype": "0",
                                    "areatype": "0",
                                    "width": "200",
                                    "height": "200",
                                    "viewtype": "0",
                                    "use_iconmap": "1",
                                    "urls": []
                                }
                            ],
                            "links": [
                                {
                                    "linkid": "23",
                                    "sysmapid": "3",
                                    "selementid1": "10",
                                    "selementid2": "11",
                                    "drawtype": "0",
                                    "color": "00CC00",
                                    "label": "",
                                    "linktriggers": []
                                }
                            ],
                            "sysmapid": "3",
                            "name": "Local nerwork",
                            "width": "400",
                            "height": "400",
                            "backgroundid": "0",
                            "label_type": "2",
                            "label_location": "3",
                            "highlight": "1",
                            "expandproblem": "1",
                            "markelements": "0",
                            "show_unack": "0",
                            "grid_size": "50",
                            "grid_show": "1",
                            "grid_align": "1",
                            "label_format": "0",
                            "label_type_host": "2",
                            "label_type_hostgroup": "2",
                            "label_type_trigger": "2",
                            "label_type_map": "2",
                            "label_type_image": "2",
                            "label_string_host": "",
                            "label_string_hostgroup": "",
                            "label_string_trigger": "",
                            "label_string_map": "",
                            "label_string_image": "",
                            "iconmapid": "0",
                            "expand_macros": "0",
                            "severity_min": "0"
                        }
                    ],
                    "id": 1
                }';
        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->queryMapCount($this->authInfo, $params);
        $this->assertEquals(1, $response);
    }

    public function testQueryMapDetailSuccess()
    {
        $params = '{
                    "output": "extend",
                    "selectSelements": "extend",
                    "selectLinks": "extend",
                    "sysmapids": "3"
                }';
        $return = '{
                    "jsonrpc": "2.0",
                    "result": [
                        {
                            "selements": [
                                {
                                    "selementid": "10",
                                    "sysmapid": "3",
                                    "elementid": "0",
                                    "elementtype": "4",
                                    "iconid_off": "1",
                                    "iconid_on": "0",
                                    "label": "Zabbix server",
                                    "label_location": "3",
                                    "x": "11",
                                    "y": "141",
                                    "iconid_disabled": "0",
                                    "iconid_maintenance": "0",
                                    "elementsubtype": "0",
                                    "areatype": "0",
                                    "width": "200",
                                    "height": "200",
                                    "viewtype": "0",
                                    "use_iconmap": "1",
                                    "urls": []
                                },
                                {
                                    "selementid": "11",
                                    "sysmapid": "3",
                                    "elementid": "0",
                                    "elementtype": "4",
                                    "iconid_off": "1",
                                    "iconid_on": "0",
                                    "label": "Web server",
                                    "label_location": "3",
                                    "x": "211",
                                    "y": "191",
                                    "iconid_disabled": "0",
                                    "iconid_maintenance": "0",
                                    "elementsubtype": "0",
                                    "areatype": "0",
                                    "width": "200",
                                    "height": "200",
                                    "viewtype": "0",
                                    "use_iconmap": "1",
                                    "urls": []
                                }
                            ],
                            "links": [
                                {
                                    "linkid": "23",
                                    "sysmapid": "3",
                                    "selementid1": "10",
                                    "selementid2": "11",
                                    "drawtype": "0",
                                    "color": "00CC00",
                                    "label": "",
                                    "linktriggers": []
                                }
                            ],
                            "sysmapid": "3",
                            "name": "Local nerwork",
                            "width": "400",
                            "height": "400",
                            "backgroundid": "0",
                            "label_type": "2",
                            "label_location": "3",
                            "highlight": "1",
                            "expandproblem": "1",
                            "markelements": "0",
                            "show_unack": "0",
                            "grid_size": "50",
                            "grid_show": "1",
                            "grid_align": "1",
                            "label_format": "0",
                            "label_type_host": "2",
                            "label_type_hostgroup": "2",
                            "label_type_trigger": "2",
                            "label_type_map": "2",
                            "label_type_image": "2",
                            "label_string_host": "",
                            "label_string_hostgroup": "",
                            "label_string_trigger": "",
                            "label_string_map": "",
                            "label_string_image": "",
                            "iconmapid": "0",
                            "expand_macros": "0",
                            "severity_min": "0"
                        }
                    ],
                    "id": 1
                }';
        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->queryMapDetail($this->authInfo, $params);
        $this->assertEquals('3', $response->sysmapid);
    }

    public function testQueryIconmapListSuccess()
    {
        $params = '{
                        "iconmapids": "3",
                        "output": "extend",
                        "selectMappings": "extend"
                    }';
        $return = '{
                    "jsonrpc": "2.0",
                    "result": [
                        {
                            "mappings": [
                                {
                                    "iconmappingid": "3",
                                    "iconmapid": "3",
                                    "iconid": "6",
                                    "inventory_link": "1",
                                    "expression": "server",
                                    "sortorder": "0"
                                },
                                {
                                    "iconmappingid": "4",
                                    "iconmapid": "3",
                                    "iconid": "10",
                                    "inventory_link": "1",
                                    "expression": "switch",
                                    "sortorder": "1"
                                }
                            ],
                            "iconmapid": "3",
                            "name": "Host type icons",
                            "default_iconid": "2"
                        }
                    ],
                    "id": 1
                }';
        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->queryIconmapList($this->authInfo, $params);
        $this->assertEquals(1, count($response));
        $this->assertEquals('2', count($response[0]->mappings));
        $this->assertEquals('3', $response[0]->iconmapid);
        $this->assertEquals('4', $response[0]->mappings[1]->iconmappingid);

    }

    public function testQueryIconmapCountSuccess()
    {
        $params = '{
                        "iconmapids": "3",
                        "output": "extend",
                        "selectMappings": "extend"
                    }';
        $return = '{
                    "jsonrpc": "2.0",
                    "result": [
                        {
                            "mappings": [
                                {
                                    "iconmappingid": "3",
                                    "iconmapid": "3",
                                    "iconid": "6",
                                    "inventory_link": "1",
                                    "expression": "server",
                                    "sortorder": "0"
                                },
                                {
                                    "iconmappingid": "4",
                                    "iconmapid": "3",
                                    "iconid": "10",
                                    "inventory_link": "1",
                                    "expression": "switch",
                                    "sortorder": "1"
                                }
                            ],
                            "iconmapid": "3",
                            "name": "Host type icons",
                            "default_iconid": "2"
                        }
                    ],
                    "id": 1
                }';
        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->queryIconmapCount($this->authInfo, $params);
        $this->assertEquals(1, $response);
    }

    public function testQueryIconmapDetailSuccess()
    {
        $params = '{
                        "iconmapids": "3",
                        "output": "extend",
                        "selectMappings": "extend"
                    }';
        $return = '{
                    "jsonrpc": "2.0",
                    "result": [
                        {
                            "mappings": [
                                {
                                    "iconmappingid": "3",
                                    "iconmapid": "3",
                                    "iconid": "6",
                                    "inventory_link": "1",
                                    "expression": "server",
                                    "sortorder": "0"
                                },
                                {
                                    "iconmappingid": "4",
                                    "iconmapid": "3",
                                    "iconid": "10",
                                    "inventory_link": "1",
                                    "expression": "switch",
                                    "sortorder": "1"
                                }
                            ],
                            "iconmapid": "3",
                            "name": "Host type icons",
                            "default_iconid": "2"
                        }
                    ],
                    "id": 1
                }';
        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->queryIconmapDetail($this->authInfo, $params);
        $this->assertEquals(1, count($response));
        $this->assertEquals('2', count($response->mappings));
        $this->assertEquals('3', $response->iconmapid);
        $this->assertEquals('4', $response->mappings[1]->iconmappingid);
    }

    public function testQueryImageListSuccess()
    {
        $params = '{
                        "output": "extend",
                        "select_image": true,
                        "imageids": "2"
                    }';
        $return = '{
                        "jsonrpc": "2.0",
                        "result": [
                            {
                                "imageid": "2",
                                "imagetype": "1",
                                "name": "Cloud_(24)",
                                "image": "iVBORw0KGgoAAAANSUhEUgAAABgAAAANCAYAAACzbK7QAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAACmAAAApgBNtNH3wAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAIcSURBVDjLrZLbSxRRHMdPKiEiRQ89CD0s+N5j9BIMEf4Hg/jWexD2ZEXQbC9tWUFZimtLhswuZiVujK1UJmYXW9PaCUdtb83enL3P7s6ss5f5dc7EUsmqkPuFH3M4/Ob7+V0OAgC0UyDENFEU03rh1uNOs/lFG75o2i2/rkd9Y3Tgyj3HiaezbukdH9A/rP4E9vWi0u+Y4fuGnMf3DRgYc3Z/84YrQSkD3mgKhFAC+KAEK74Y2Lj3MjPoOokQ3Xyx/1GHeXCifbfO6lRPH/wi+AvZQhGSsgKxdB5CCRkCGPbDgMXBMbukTc4vK5/WRHizsq7fZl2LFuvE4T0BZDTXHtgv4TNUqlUolsqQL2qQwbDEXzBBTIJ7I4y/cfAENmHZF4XrY9Mc+X9HAFmoyXS2ddy1IOg6/KNyBcM0DFP/wFZFCcOy4N9Mw0YkCTOfhdL5AfZQXQBFn2t/ODXHC8FYVcoWjNEQ03qqwTJ5FdI44jg/msoB2Zd5ZKq3q6evA1FUS60bYyyj3AJf3V72HiLZJQxTtRLk1C2IYEg4mTNg63hPd1mOJd7Ict911OMNlWEf0nFxpCt16zcshTuLpGSwDDuPIfv0xzNyQYVGicC0cgUUDLM6Xp02lvvW/V2EBssnxlSGmWsxljw0znV9XfPLjTCW84r+cn7Jc8c2eWrbM6Wbe6/aTJbhJ/TNkWc9/xXW592Xb9iPkKnUfH8BKdLgFy0lDyQAAAAASUVORK5CYII="
                            }
                        ],
                        "id": 1
                    }';
        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->queryImageList($this->authInfo, $params);
        $this->assertEquals(1, count($response));
        $this->assertEquals('2', $response[0]->imageid);
    }

    public function testQueryImageCountSuccess()
    {
        $params = '{
                        "output": "extend",
                        "select_image": true,
                        "imageids": "2"
                    }';
        $return = '{
                        "jsonrpc": "2.0",
                        "result": [
                            {
                                "imageid": "2",
                                "imagetype": "1",
                                "name": "Cloud_(24)",
                                "image": "iVBORw0KGgoAAAANSUhEUgAAABgAAAANCAYAAACzbK7QAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAACmAAAApgBNtNH3wAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAIcSURBVDjLrZLbSxRRHMdPKiEiRQ89CD0s+N5j9BIMEf4Hg/jWexD2ZEXQbC9tWUFZimtLhswuZiVujK1UJmYXW9PaCUdtb83enL3P7s6ss5f5dc7EUsmqkPuFH3M4/Ob7+V0OAgC0UyDENFEU03rh1uNOs/lFG75o2i2/rkd9Y3Tgyj3HiaezbukdH9A/rP4E9vWi0u+Y4fuGnMf3DRgYc3Z/84YrQSkD3mgKhFAC+KAEK74Y2Lj3MjPoOokQ3Xyx/1GHeXCifbfO6lRPH/wi+AvZQhGSsgKxdB5CCRkCGPbDgMXBMbukTc4vK5/WRHizsq7fZl2LFuvE4T0BZDTXHtgv4TNUqlUolsqQL2qQwbDEXzBBTIJ7I4y/cfAENmHZF4XrY9Mc+X9HAFmoyXS2ddy1IOg6/KNyBcM0DFP/wFZFCcOy4N9Mw0YkCTOfhdL5AfZQXQBFn2t/ODXHC8FYVcoWjNEQ03qqwTJ5FdI44jg/msoB2Zd5ZKq3q6evA1FUS60bYyyj3AJf3V72HiLZJQxTtRLk1C2IYEg4mTNg63hPd1mOJd7Ict911OMNlWEf0nFxpCt16zcshTuLpGSwDDuPIfv0xzNyQYVGicC0cgUUDLM6Xp02lvvW/V2EBssnxlSGmWsxljw0znV9XfPLjTCW84r+cn7Jc8c2eWrbM6Wbe6/aTJbhJ/TNkWc9/xXW592Xb9iPkKnUfH8BKdLgFy0lDyQAAAAASUVORK5CYII="
                            }
                        ],
                        "id": 1
                    }';
        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->queryImageCount($this->authInfo, $params);
        $this->assertEquals(1, $response);
    }

    public function testQueryImageDetailSuccess()
    {
        $params = '{
                        "output": "extend",
                        "select_image": true,
                        "imageids": "2"
                    }';
        $return = '{
                        "jsonrpc": "2.0",
                        "result": [
                            {
                                "imageid": "2",
                                "imagetype": "1",
                                "name": "Cloud_(24)",
                                "image": "iVBORw0KGgoAAAANSUhEUgAAABgAAAANCAYAAACzbK7QAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAACmAAAApgBNtNH3wAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAIcSURBVDjLrZLbSxRRHMdPKiEiRQ89CD0s+N5j9BIMEf4Hg/jWexD2ZEXQbC9tWUFZimtLhswuZiVujK1UJmYXW9PaCUdtb83enL3P7s6ss5f5dc7EUsmqkPuFH3M4/Ob7+V0OAgC0UyDENFEU03rh1uNOs/lFG75o2i2/rkd9Y3Tgyj3HiaezbukdH9A/rP4E9vWi0u+Y4fuGnMf3DRgYc3Z/84YrQSkD3mgKhFAC+KAEK74Y2Lj3MjPoOokQ3Xyx/1GHeXCifbfO6lRPH/wi+AvZQhGSsgKxdB5CCRkCGPbDgMXBMbukTc4vK5/WRHizsq7fZl2LFuvE4T0BZDTXHtgv4TNUqlUolsqQL2qQwbDEXzBBTIJ7I4y/cfAENmHZF4XrY9Mc+X9HAFmoyXS2ddy1IOg6/KNyBcM0DFP/wFZFCcOy4N9Mw0YkCTOfhdL5AfZQXQBFn2t/ODXHC8FYVcoWjNEQ03qqwTJ5FdI44jg/msoB2Zd5ZKq3q6evA1FUS60bYyyj3AJf3V72HiLZJQxTtRLk1C2IYEg4mTNg63hPd1mOJd7Ict911OMNlWEf0nFxpCt16zcshTuLpGSwDDuPIfv0xzNyQYVGicC0cgUUDLM6Xp02lvvW/V2EBssnxlSGmWsxljw0znV9XfPLjTCW84r+cn7Jc8c2eWrbM6Wbe6/aTJbhJ/TNkWc9/xXW592Xb9iPkKnUfH8BKdLgFy0lDyQAAAAASUVORK5CYII="
                            }
                        ],
                        "id": 1
                    }';
        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->queryImageDetail($this->authInfo, $params);
        $this->assertEquals('2', $response->imageid);
    }

    public function testQueryItemListSuccess()
    {
        $params = '{
                        "output": "extend",
                        "hostids": "10084",
                        "search": {
                            "key_": "system"
                        },
                        "sortfield": "name"
                    }';
        $return = '{
                        "jsonrpc": "2.0",
                        "result": [
                            {
                                "itemid": "23298",
                                "type": "0",
                                "snmp_community": "",
                                "snmp_oid": "",
                                "hostid": "10084",
                                "name": "Context switches per second",
                                "key_": "system.cpu.switches",
                                "delay": "60",
                                "history": "7",
                                "trends": "365",
                                "lastvalue": "2552",
                                "lastclock": "1351090998",
                                "prevvalue": "2641",
                                "state": "0",
                                "status": "0",
                                "value_type": "3",
                                "trapper_hosts": "",
                                "units": "sps",
                                "multiplier": "0",
                                "delta": "1",
                                "snmpv3_securityname": "",
                                "snmpv3_securitylevel": "0",
                                "snmpv3_authpassphrase": "",
                                "snmpv3_privpassphrase": "",
                                "formula": "1",
                                "error": "",
                                "lastlogsize": "0",
                                "logtimefmt": "",
                                "templateid": "22680",
                                "valuemapid": "0",
                                "delay_flex": "",
                                "params": "",
                                "ipmi_sensor": "",
                                "data_type": "0",
                                "authtype": "0",
                                "username": "",
                                "password": "",
                                "publickey": "",
                                "privatekey": "",
                                "mtime": "0",
                                "lastns": "564054253",
                                "flags": "0",
                                "filter": "",
                                "interfaceid": "1",
                                "port": "",
                                "description": "",
                                "inventory_link": "0",
                                "lifetime": "0"
                            },
                            {
                                "itemid": "23299",
                                "type": "0",
                                "snmp_community": "",
                                "snmp_oid": "",
                                "hostid": "10084",
                                "name": "CPU $2 time",
                                "key_": "system.cpu.util[,idle]",
                                "delay": "60",
                                "history": "7",
                                "trends": "365",
                                "lastvalue": "86.031879",
                                "lastclock": "1351090999",
                                "prevvalue": "85.306944",
                                "state": "0",
                                "status": "0",
                                "value_type": "0",
                                "trapper_hosts": "",
                                "units": "%",
                                "multiplier": "0",
                                "delta": "0",
                                "snmpv3_securityname": "",
                                "snmpv3_securitylevel": "0",
                                "snmpv3_authpassphrase": "",
                                "snmpv3_privpassphrase": "",
                                "formula": "1",
                                "error": "",
                                "lastlogsize": "0",
                                "logtimefmt": "",
                                "templateid": "17354",
                                "valuemapid": "0",
                                "delay_flex": "",
                                "params": "",
                                "ipmi_sensor": "",
                                "data_type": "0",
                                "authtype": "0",
                                "username": "",
                                "password": "",
                                "publickey": "",
                                "privatekey": "",
                                "mtime": "0",
                                "lastns": "564256864",
                                "flags": "0",
                                "filter": "",
                                "interfaceid": "1",
                                "port": "",
                                "description": "The time the CPU has spent doing nothing.",
                                "inventory_link": "0",
                                "lifetime": "0"
                            },
                            {
                                "itemid": "23300",
                                "type": "0",
                                "snmp_community": "",
                                "snmp_oid": "",
                                "hostid": "10084",
                                "name": "CPU $2 time",
                                "key_": "system.cpu.util[,interrupt]",
                                "delay": "60",
                                "history": "7",
                                "trends": "365",
                                "lastvalue": "0.008389",
                                "lastclock": "1351091000",
                                "prevvalue": "0.000000",
                                "state": "0",
                                "status": "0",
                                "value_type": "0",
                                "trapper_hosts": "",
                                "units": "%",
                                "multiplier": "0",
                                "delta": "0",
                                "snmpv3_securityname": "",
                                "snmpv3_securitylevel": "0",
                                "snmpv3_authpassphrase": "",
                                "snmpv3_privpassphrase": "",
                                "formula": "1",
                                "error": "",
                                "lastlogsize": "0",
                                "logtimefmt": "",
                                "templateid": "22671",
                                "valuemapid": "0",
                                "delay_flex": "",
                                "params": "",
                                "ipmi_sensor": "",
                                "data_type": "0",
                                "authtype": "0",
                                "username": "",
                                "password": "",
                                "publickey": "",
                                "privatekey": "",
                                "mtime": "0",
                                "lastns": "564661387",
                                "flags": "0",
                                "filter": "",
                                "interfaceid": "1",
                                "port": "",
                                "description": "The amount of time the CPU has been servicing hardware interrupts.",
                                "inventory_link": "0",
                                "lifetime": "0"
                            }
                        ],
                        "id": 1
                    }';
        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->queryItemList($this->authInfo, $params);
        $this->assertEquals(3, count($response));
        $this->assertEquals('23298', $response[0]->itemid);
        $this->assertEquals('23299', $response[1]->itemid);
        $this->assertEquals('23300', $response[2]->itemid);
    }

    public function testQueryItemCountSuccess()
    {
        $params = '{
                        "output": "extend",
                        "hostids": "10084",
                        "search": {
                            "key_": "system"
                        },
                        "sortfield": "name"
                    }';
        $return = '{
                        "jsonrpc": "2.0",
                        "result": [
                            {
                                "itemid": "23298",
                                "type": "0",
                                "snmp_community": "",
                                "snmp_oid": "",
                                "hostid": "10084",
                                "name": "Context switches per second",
                                "key_": "system.cpu.switches",
                                "delay": "60",
                                "history": "7",
                                "trends": "365",
                                "lastvalue": "2552",
                                "lastclock": "1351090998",
                                "prevvalue": "2641",
                                "state": "0",
                                "status": "0",
                                "value_type": "3",
                                "trapper_hosts": "",
                                "units": "sps",
                                "multiplier": "0",
                                "delta": "1",
                                "snmpv3_securityname": "",
                                "snmpv3_securitylevel": "0",
                                "snmpv3_authpassphrase": "",
                                "snmpv3_privpassphrase": "",
                                "formula": "1",
                                "error": "",
                                "lastlogsize": "0",
                                "logtimefmt": "",
                                "templateid": "22680",
                                "valuemapid": "0",
                                "delay_flex": "",
                                "params": "",
                                "ipmi_sensor": "",
                                "data_type": "0",
                                "authtype": "0",
                                "username": "",
                                "password": "",
                                "publickey": "",
                                "privatekey": "",
                                "mtime": "0",
                                "lastns": "564054253",
                                "flags": "0",
                                "filter": "",
                                "interfaceid": "1",
                                "port": "",
                                "description": "",
                                "inventory_link": "0",
                                "lifetime": "0"
                            },
                            {
                                "itemid": "23299",
                                "type": "0",
                                "snmp_community": "",
                                "snmp_oid": "",
                                "hostid": "10084",
                                "name": "CPU $2 time",
                                "key_": "system.cpu.util[,idle]",
                                "delay": "60",
                                "history": "7",
                                "trends": "365",
                                "lastvalue": "86.031879",
                                "lastclock": "1351090999",
                                "prevvalue": "85.306944",
                                "state": "0",
                                "status": "0",
                                "value_type": "0",
                                "trapper_hosts": "",
                                "units": "%",
                                "multiplier": "0",
                                "delta": "0",
                                "snmpv3_securityname": "",
                                "snmpv3_securitylevel": "0",
                                "snmpv3_authpassphrase": "",
                                "snmpv3_privpassphrase": "",
                                "formula": "1",
                                "error": "",
                                "lastlogsize": "0",
                                "logtimefmt": "",
                                "templateid": "17354",
                                "valuemapid": "0",
                                "delay_flex": "",
                                "params": "",
                                "ipmi_sensor": "",
                                "data_type": "0",
                                "authtype": "0",
                                "username": "",
                                "password": "",
                                "publickey": "",
                                "privatekey": "",
                                "mtime": "0",
                                "lastns": "564256864",
                                "flags": "0",
                                "filter": "",
                                "interfaceid": "1",
                                "port": "",
                                "description": "The time the CPU has spent doing nothing.",
                                "inventory_link": "0",
                                "lifetime": "0"
                            },
                            {
                                "itemid": "23300",
                                "type": "0",
                                "snmp_community": "",
                                "snmp_oid": "",
                                "hostid": "10084",
                                "name": "CPU $2 time",
                                "key_": "system.cpu.util[,interrupt]",
                                "delay": "60",
                                "history": "7",
                                "trends": "365",
                                "lastvalue": "0.008389",
                                "lastclock": "1351091000",
                                "prevvalue": "0.000000",
                                "state": "0",
                                "status": "0",
                                "value_type": "0",
                                "trapper_hosts": "",
                                "units": "%",
                                "multiplier": "0",
                                "delta": "0",
                                "snmpv3_securityname": "",
                                "snmpv3_securitylevel": "0",
                                "snmpv3_authpassphrase": "",
                                "snmpv3_privpassphrase": "",
                                "formula": "1",
                                "error": "",
                                "lastlogsize": "0",
                                "logtimefmt": "",
                                "templateid": "22671",
                                "valuemapid": "0",
                                "delay_flex": "",
                                "params": "",
                                "ipmi_sensor": "",
                                "data_type": "0",
                                "authtype": "0",
                                "username": "",
                                "password": "",
                                "publickey": "",
                                "privatekey": "",
                                "mtime": "0",
                                "lastns": "564661387",
                                "flags": "0",
                                "filter": "",
                                "interfaceid": "1",
                                "port": "",
                                "description": "The amount of time the CPU has been servicing hardware interrupts.",
                                "inventory_link": "0",
                                "lifetime": "0"
                            }
                        ],
                        "id": 1
                    }';
        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->queryItemCount($this->authInfo, $params);
        $this->assertEquals(3, $response);
    }

    public function testQueryItemDetailSuccess()
    {
        $params = '{
                        "output": "extend",
                        "hostids": "10084",
                        "search": {
                            "key_": "system"
                        },
                        "sortfield": "name"
                    }';
        $return = '{
                        "jsonrpc": "2.0",
                        "result": [
                            {
                                "itemid": "23298",
                                "type": "0",
                                "snmp_community": "",
                                "snmp_oid": "",
                                "hostid": "10084",
                                "name": "Context switches per second",
                                "key_": "system.cpu.switches",
                                "delay": "60",
                                "history": "7",
                                "trends": "365",
                                "lastvalue": "2552",
                                "lastclock": "1351090998",
                                "prevvalue": "2641",
                                "state": "0",
                                "status": "0",
                                "value_type": "3",
                                "trapper_hosts": "",
                                "units": "sps",
                                "multiplier": "0",
                                "delta": "1",
                                "snmpv3_securityname": "",
                                "snmpv3_securitylevel": "0",
                                "snmpv3_authpassphrase": "",
                                "snmpv3_privpassphrase": "",
                                "formula": "1",
                                "error": "",
                                "lastlogsize": "0",
                                "logtimefmt": "",
                                "templateid": "22680",
                                "valuemapid": "0",
                                "delay_flex": "",
                                "params": "",
                                "ipmi_sensor": "",
                                "data_type": "0",
                                "authtype": "0",
                                "username": "",
                                "password": "",
                                "publickey": "",
                                "privatekey": "",
                                "mtime": "0",
                                "lastns": "564054253",
                                "flags": "0",
                                "filter": "",
                                "interfaceid": "1",
                                "port": "",
                                "description": "",
                                "inventory_link": "0",
                                "lifetime": "0"
                            }
                        ],
                        "id": 1
                    }';
        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->queryItemDetail($this->authInfo, $params);
        $this->assertEquals(1, count($response));
        $this->assertEquals('23298', $response->itemid);
    }

    public function testQueryHistoryListSuccess()
    {
        $params = '{
                        "output": "extend",
                        "history": 0,
                        "itemids": "25475",
                        "sortfield": "clock",
                        "sortorder": "DESC",
                        "limit": 10
                    }';
        $return = '{
                        "jsonrpc": "2.0",
                        "result": [
                            {
                                "itemid": "25475",
                                "clock": "1442298482",
                                "value": "0.0006",
                                "ns": "155573574"
                            },
                            {
                                "itemid": "25475",
                                "clock": "1442298422",
                                "value": "0.0004",
                                "ns": "342389248"
                            },
                            {
                                "itemid": "25475",
                                "clock": "1442298362",
                                "value": "0.0004",
                                "ns": "67172835"
                            },
                            {
                                "itemid": "25475",
                                "clock": "1442298302",
                                "value": "0.0007",
                                "ns": "490520701"
                            },
                            {
                                "itemid": "25475",
                                "clock": "1442298242",
                                "value": "0.0006",
                                "ns": "796672651"
                            },
                            {
                                "itemid": "25475",
                                "clock": "1442298182",
                                "value": "0.0009",
                                "ns": "274219"
                            },
                            {
                                "itemid": "25475",
                                "clock": "1442298122",
                                "value": "0.0006",
                                "ns": "934167961"
                            },
                            {
                                "itemid": "25475",
                                "clock": "1442298062",
                                "value": "0.0008",
                                "ns": "820919648"
                            },
                            {
                                "itemid": "25475",
                                "clock": "1442298002",
                                "value": "0.0011",
                                "ns": "709060576"
                            },
                            {
                                "itemid": "25475",
                                "clock": "1442297942",
                                "value": "0.0020",
                                "ns": "595807756"
                            }
                        ],
                        "id": 1
                    }';
        self::$functions->method('curl_exec')->willReturn($return);
        $response = $this->apm->queryHistoryList($this->authInfo, $params);
        $this->assertEquals(10, count($response));
        $this->assertEquals('1442298482', $response[0]->clock);
    }
}