<?php namespace App\Http\Controllers;

use App\Contracts\ApmSystemInterface;
use App\Facades\Pagination;
use App\Http\Requests;
use Crypt;
use Request;
use Redirect;
use Session;
use Input;
use App\Helper\Converter;


/**
 * Class ZbxTopController
 * @package App\Http\Controllers
 */
class ZbxTopController extends Controller
{

    protected $apmStstemInterface;
    protected $auth_apm;

    function __construct(
        ApmSystemInterface $apmStstemInterface
    ) {
        $this->apmStstemInterface = $apmStstemInterface;

        $this->middleware('auth.zbx', ['except' => ['getLogin', 'postLogin', 'getLogout']]);

        $authInfo['username'] = '';
        $authInfo['password'] = '';
        $authInfo['sessionid'] = '';

        if (isset($_COOKIE['username'])) {
            $authInfo['username'] = Crypt::decrypt($_COOKIE['username']);
        }
        if (isset($_COOKIE['password'])) {
            $authInfo['password'] = Crypt::decrypt($_COOKIE['password']);
        }
        if (Session::has('sessionid')) {
            $authInfo['sessionid'] = Crypt::decrypt(Session::get('sessionid'));
        } elseif (isset($_COOKIE['sessionid'])) {
            $authInfo['sessionid'] = Crypt::decrypt($_COOKIE['sessionid']);
        }
        $this->auth_apm = json_encode($authInfo);

    }

    /**
     * 用户登录页面
     * @return \Illuminate\View\View
     */
    public function getLogin()
    {
        return view('auth.login');
    }

    /**
     * 系统登录,目前直接调用zabbix的Login接口
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function postLogin()
    {
        $name = Request::Input('name');
        $password = Request::Input('password');
        if (empty($name) || empty($password)) {
            return redirect()->back()->withErrors('用户名和密码不能为空')->withInput();
        }
        //判断cookie是否存在，如果存在，则需要先logout
        if (isset($_COOKIE['sessionid'])) {
            $this->apmStstemInterface->logout($this->auth_apm);
        }

        $authInfo['username'] = $name;
        $authInfo['password'] = Crypt::encrypt($password);
        $loginResponse = $this->apmStstemInterface->login(json_encode($authInfo));
        if ($loginResponse) {
            Session::put('username', $name);

            return redirect()->intended('home');
        } else {
            return Redirect::to('/')->with('error', '用户名密码错误');
        }
    }

    /**
     * 用户loggou
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getLogout()
    {
        $this->apmStstemInterface->logout($this->auth_apm);

        return Redirect::to('/');
    }

    /**
     *概览首页
     *概览分成分成3部分，1-zbbbix健康百分比，2-CI数量统计，3-图
     */
    public function dashboardIndex()
    {
        //1-zabbix健康百分比
        $health_arr = ['normal' => 0, 'warning' => 0, 'error' => 0];
        $error_arr = [];
        $warning_arr = [];

        $hostids = '';
        $hostParams = '{
                        "output": "extend"
                        }';
        $hostresponse = $this->apmStstemInterface->queryHostList($this->auth_apm, $hostParams);
        //显示host返回数组的数量
        $hostcount = count($hostresponse);
        foreach ($hostresponse as $hostresult) {
            $hostids .= '"' . $hostresult->hostid . '",';
        }
        $hostidarray = substr($hostids, 0, strlen($hostids) - 1);
        $triggerParams = '{
                        "hostids":[' . $hostidarray . '],
                        "only_true": "1",
                        "min_severity":"2",
                        "output": "extend"
                    }';
        $triggerresponse = $this->apmStstemInterface->queryEventList($this->auth_apm, $triggerParams);
        //判断是否正确返回
        foreach ($triggerresponse as $triggerresult) {
            if ($triggerresult->priority == env('PRIORITY_WARNING')) {
                if (!array_search($triggerresult->hosts[0]->hostid, $error_arr)) {
                    //正常情况下，需要进行去重复的判断，在循环结束后，使用array_unique，所以此处不需要判断
                    $warning_arr[] = $triggerresult->hosts[0]->hostid;
                }
            } elseif ($triggerresult->priority >= env('PRIORITY_AVERAGE')) {
                $error_arr[] = $triggerresult->hosts[0]->hostid;
            }
        }
        $health_arr['warning'] = count(array_unique($warning_arr));
        $health_arr['error'] = count(array_unique($error_arr));
        $health_arr['normal'] = $hostcount - $health_arr['warning'] - $health_arr['error'];

        //2-CI数量统计
        $ciNames = json_decode(env('DASHBOARD_CINAME'));
        //找出.env中配置的mapid
        $cistr_temp = '';
        $cistr = '';

        //3-图，查询图的链接URL
        //查询所有的mapid和mapname

        $result = null;
        $sysmapidsStr = '';
        $maplistResultArr = [];
        $showMapidOrder = env('DASHBOARD_MAPID');
        $mapOrderids = json_decode($showMapidOrder);
        //找出.env中配置的mapid
        foreach ($mapOrderids as $mapOrderid) {
            $sysmapidsStr .= '"' . $mapOrderid->mapid . '",';
        }
        $sysmapidsStr = substr($sysmapidsStr, 0, strlen($sysmapidsStr) - 1);
        $sysmapidsStr = '"sysmapids":[' . $sysmapidsStr . '],';
        $maplistParam = '{
                        "output": "extend",
                         "selectSelements": "extend",
                        "selectLinks": "extend",
                        ' . $sysmapidsStr . '
                        "sortfield":"name"
                    }';
        //查询设备的名称列表
        $maplistResult = $this->apmStstemInterface->queryMapList($this->auth_apm, $maplistParam);
        //按照.env中的配置，进行排序
        foreach ($maplistResult as $result) {
            foreach ($mapOrderids as $mapOrderid) {
                if ($result->sysmapid == $mapOrderid->mapid) {
                    $maplistResultArr[$mapOrderid->order] = $result;
                }
            }
        }
        ksort($maplistResultArr);
        $imageParams = '{
                            "output": "extend",
                            ' . $sysmapidsStr . '
                            "select_image": true
                        }';
        $imageResult = $this->apmStstemInterface->queryImageList($this->auth_apm, $imageParams);

        $imagestr = Converter::createImageStr($imageResult, $maplistResultArr);

        return view('home', compact('health_arr', 'cistr', 'imagestr'));
    }

    /**
     * 设备首页
     * @param string $ciname
     * @return \Illuminate\View\View
     */
    public function deviceIndex($ciname = '')
    {
        $hostidarray = '';
        $deviceList = [];
        $deviceListArrays = [];
        $perPage = env('PERPAGE');
        $page = Input::get('page');
        if (empty($page)) {
            $page = 1;
        }
        if ($perPage <= 0) {
            //一页显示无限多条数据
            $perPage = 9999999999;
        }
        $devicename = Request::input('devicename');
        $paramstr = '';
        if (!empty($devicename)) {
            $paramstr .= '"search": {
                        "host":"' . $devicename . '"
                    },';
        }
        $hostparam = '{
                        "output": "extend",
                        "selectGroups": "extend", ' . $paramstr . '
                        "sortorder":"groupids"
                    }';
        $hostResponse = $this->apmStstemInterface->queryHostList($this->auth_apm, $hostparam);
        //需要先做分页处理
        $hostListArrays = array_slice($hostResponse, ($page - 1) * $perPage, $perPage);

        foreach ($hostListArrays as $response) {
            $hostidarray .= '"' . $response->hostid . '",';
            $deviceListArrays[$response->hostid]['name'] = $response->name;
            $deviceListArrays[$response->hostid]['state'] = '0';
            $deviceListArrays[$response->hostid]['groupname'] = $response->groups[0]->name;
            $deviceListArrays[$response->hostid]['hostid'] = $response->hostid;
        }
        $hostidarray = substr($hostidarray, 0, strlen($hostidarray) - 1); //hostid
        $eventParams = '{
                        "hostids":[' . $hostidarray . '],
                        "output": "extend",
                        "only_true": "1",
                        "min_severity":"2"
                    }';
        $eventResponses = $this->apmStstemInterface->queryEventList($this->auth_apm, $eventParams);

        foreach ($deviceListArrays as $deviceListArray) {
            if (array_key_exists('hostid', $deviceListArray)) {
                foreach ($eventResponses as $enentResponse) {
                    if ($deviceListArray["hostid"] == $enentResponse->hostid) {
                        if ($enentResponse->priority > $deviceListArray['state']) {
                            $deviceListArray['state'] = $enentResponse->priority;
                        }
                    }
                }
            }
            $deviceList[] = $deviceListArray;
        }
        $datas = Pagination::getPerpageDataByDatas($deviceList, $perPage, count($hostResponse));

        return view('devices.index', compact('datas', 'devicename'));
    }

    /**
     * 显示map
     * @param string $selectedId 对应的mapid
     * @return \Illuminate\View\View
     */
    public function mapIndex($selectedId = '')
    {
        $mapDatas = [];
        $mapname = [];
        $mapResult = '';
        $maplistParam = '{
                        "output": "extend",
                         "selectSelements": "extend",
                        "selectLinks": "extend",
                        "sortfield":"name"
                    }';
        //查询设备的名称列表
        $listResult = $this->apmStstemInterface->queryMapList($this->auth_apm, $maplistParam);
        foreach ($listResult as $result) {
            $mapname[$result->sysmapid] = $result->name;
            if (empty($selectedId)) {
                $mapDatas[$result->sysmapid] = $result->sysmapid;
                //增加排序
                ksort($mapDatas);
                if (!empty($mapDatas)) {
                    //获取key，因为$data是以mapid为key
                    $selectedId = key($mapDatas);
                }
            }
            if ($selectedId == $result->sysmapid) {
                $mapResult = $result;
            }

        }

        $imageParams = '{
                            "output": "extend",
                            "select_image": true,
                            "sysmapids":"' . $selectedId . '"
                        }';
        $imageResult = $this->apmStstemInterface->queryImageList($this->auth_apm, $imageParams);
        //两个参数都为数组
        $imagestr = Converter::createImageStr($imageResult, [$mapResult], false);

        return view('maps.show', compact('mapname', 'selectedId', 'imagestr'));

    }


    /**
     * 事件首页
     * @param string $hostid
     * @return \Illuminate\View\View
     */
    public function eventIndex($hostid = '')
    {
        $hostidstr = '';
        if ($hostid != '') {
            $hostidstr = '"hostids":"' . $hostid . '",';
        }

        $evevtlist_arr = [];
        $eventParams = '{
                                "output": "extend",
                                "only_true": "1",
                                "min_severity":"2",
                                ' . $hostidstr . '
                                "sortfield":"lastchange",
                                "sortorder": "DESC",
                                "selectFunctions": "extend",
                                "expandData":"1"
                            }';
        $eventResponses = $this->apmStstemInterface->queryEventList($this->auth_apm, $eventParams);
        foreach ($eventResponses as $eventResponse) {
            $event_arr = [];
            $event_arr['time'] = date('Y m/d H:i:s', $eventResponse->lastchange);
            $event_arr['name'] = $eventResponse->hostname; //
            $event_arr['hostid'] = $eventResponse->hostid;//
            $event_arr['state'] = $eventResponse->priority;
            $event_arr['description'] = $eventResponse->description;
            $evevtlist_arr[] = $event_arr;
        }
        $datas = Pagination::getPagedData($evevtlist_arr);

        return view('events.index', compact('datas'));
    }

    /**
     * @param $hostid - 主机编号
     * @return \Illuminate\View\View
     */
    public function eventShow($name = '', $hostid = 0)
    {
        $hostidstr = '';
        if ($hostid != '') {
            $hostidstr = '"hostids":"' . $hostid . '",';
        }

        $evevtlist_arr = [];
        $eventParams = '{
                                "output": "extend",
                                "only_true": "1",
                                "min_severity":"2",
                                ' . $hostidstr . '
                                "sortfield":"triggerid",
                                "selectFunctions": "extend",
                                "expandData":"1"
                            }';
        $eventResponses = $this->apmStstemInterface->queryEventList($this->auth_apm, $eventParams);
        foreach ($eventResponses as $eventResponse) {
            $event_arr = [];
            $event_arr['time'] = date('Y m/d H:i:s', $eventResponse->lastchange);
            $event_arr['name'] = $eventResponse->hostname; //
            $event_arr['hostid'] = $eventResponse->hostid;//
            $event_arr['state'] = $eventResponse->priority;
            $event_arr['description'] = $eventResponse->description;
            $evevtlist_arr[] = $event_arr;
        }
        $datas = Pagination::getPagedData($evevtlist_arr);

        return view('devices.detail.event', compact('datas', 'hostid', 'name'));
    }

    /**
     * @param $hostid : 主机编号
     * @return \Illuminate\View\View
     */
    public function monitorShow($name = '', $hostid = 0)
    {
        $itemlist_arr = [];
        $itemParams = '{
                        "output": "extend",
                        "hostids": "' . $hostid . '",
                        "selectApplications":"extend",
                        "selectHosts":"extend",
                        "sortfield": "itemid"
                    }';
        $responses = $this->apmStstemInterface->queryItemList($this->auth_apm, $itemParams);
        foreach ($responses as $response) {
            $item_arr = [];
            if ($response->value_type === '0' || $response->value_type === '3') {
                $item_arr['itemid'] = $response->itemid;
                if (empty($name) || $name == '""') {
                    $name = $response->hosts[0]->name;
                }
                $item_arr['name'] = $response->name;
                $item_arr['lastvalue'] = Converter::unitConversion($response->name,$response->lastvalue,$response->units);
                $item_arr['time'] = date('Y-m-d H:i:s', $response->lastclock);
                $itemlist_arr[$response->applications[0]->name][] = $item_arr;
            }
        }
        $datas = Pagination::getPagedData($itemlist_arr, -1, $hostid);

        return view('devices.detail.monitor', compact('datas', 'hostid', 'name'));
    }

    /**
     * @param $hostid - 查询历史信息
     * @return \Illuminate\View\View
     */
    public function historyShow($name = '', $hostid = 0, $itemid = 0, $itemName = '')
    {
        //时间戳 strtotime(); 'now' ,'-1 hours','-1 days','-1 week','-1 month'
        $historyStr = '';
        $historyParams = '{
                            "output": "extend",
                            "history": 0,
                            "itemids": "' . $itemid . '",
                            "time_from":"' . strtotime('-1 days') . '",
                            "sortfield": "clock",
                            "sortorder": "DESC"
                        }';
        $eventResponses = $this->apmStstemInterface->queryHistoryList($this->auth_apm, $historyParams);
        foreach ($eventResponses as $eventResponse) {
            $historyStr .= '[' . ($eventResponse->clock * 1000) . ',' . $eventResponse->value . '],';
        }
        $historyStr = '[' . substr($historyStr, 0, strlen($historyStr) - 1) . ']';
        return view('devices.detail.history', compact('hostid', 'name', 'itemid', 'itemName', 'historyStr'));
    }

}
