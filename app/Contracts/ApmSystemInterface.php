<?php
/**
 * Created by PhpStorm.
 * User: mao
 * Date: 2015/6/30
 * Time: 12:38
 */

namespace App\Contracts;


/**
 * Interface ApmSystemInterface
 * @package App\Contracts
 */
interface ApmSystemInterface
{

    /**
     * @param $username
     * @param $password
     * @return mixed
     */
    public function login($authInfo);

    /**
     * logout传入的参数是sessionid,所以不需要传入参数
     * @return mixed
     */
    public function logout($authInfo);

    /**
     * 查询主机列表
     * @param $param
     * @return mixed
     */
    public function queryHostList($authInfo, $param);

    /**
     * 查询主机数量
     * @param $param
     * @return mixed
     */

    public function queryHostCount($authInfo, $param);

    /**
     * 查询明细
     * @param $param
     * @return mixed
     */
    public function queryHostDetail($authInfo, $param);

    /**
     * 查询触发器列表
     * @param $param
     * @return mixed
     */
    public function queryEventList($authInfo, $param);

    /**
     * 查询触发器数量
     * @param $param
     * @return mixed
     */
    public function queryEventCount($authInfo, $param);

    /**
     * 查询event明细
     * @param $param
     * @return mixed
     */
    public function queryEventDetail($authInfo, $param);

    /**
     * 查询map列表
     * @param $param
     * @return mixed
     */
    public function queryMapList($authInfo, $param);

    public function queryMapCount($authInfo, $param);

    public function queryMapDetail($authInfo, $param);

    /**
     * 查询Iconmap,现有zabbix系统一直返回空数组
     * @param $authInfo
     * @param $param
     * @return mixed
     */
    public function queryIconmapList($authInfo, $param);

    public function queryIconmapCount($authInfo, $param);

    public function queryIconmapDetail($authInfo, $param);

    /**
     * 查询Image
     * @param $authInfo
     * @param $param
     * @return mixed
     */
    public function queryImageList($authInfo, $param);

    public function queryImageCount($authInfo, $param);

    public function queryImageDetail($authInfo, $param);


    /**
     * 查询监控项列表
     * @param $param
     * @return mixed
     */
    public function queryItemList($authInfo, $param);

    /**
     * 查询监控项数量
     * @param $param
     * @return mixed
     */
    public function queryItemCount($authInfo, $param);

    public function queryItemDetail($authInfo, $param);

//    //获取历史信息图的Url
    public function queryHistoryList($authInfo, $param);
}