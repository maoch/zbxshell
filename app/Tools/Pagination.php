<?php
/**
 * Created by PhpStorm.
 * User: mao
 * Date: 2015/6/30
 * Time: 14:46
 */

namespace App\Tools;


use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Input;
use Request;

define ("PERPAGE", env('PERPAGE'));

class Pagination
{
    /**
     * $datas传的为数据的总数
     * 在view中也要注意，取数据是datas
     * @param array $datas
     * @param string $perPage
     * @param bool $is_lengthAware
     * @return array|LengthAwarePaginator|Paginator
     */
    public function getPagedData(Array $datas, $perPage = PERPAGE, $path = '')
    {
        $page = Input::get('page');
        if (empty($page)) {
            $page = 1;
        }
        if ($perPage <= 0) {
            //一页显示无限多条数据
            $perPage = 9999999999;
        }
        $total = count($datas);
        $datas = array_slice($datas, ($page - 1) * $perPage, $perPage);
        $datas = new LengthAwarePaginator($datas, $total, $perPage);
        if (empty($path)) {
            $path = Request::path();
        }
        $datas->setPath($path); //设置URL的路径
        return $datas;
    }

    /**
     * $datas传递的数据为每页显示的数据，例如：数据总共有25条，每页显示10条，则$datas只传递10条，
     * 需要在函数调用前，就已经吧数据分页完成
     * @param array $datas
     * @param string $perPage
     * @param string $total
     * @param string $path
     * @return array|LengthAwarePaginator
     */
    public function getPerpageDataByDatas(Array $datas, $perPage = PERPAGE, $total = '', $path = '')
    {


        if (empty($total)) {
            $total = count($datas);
        }

        $datas = new LengthAwarePaginator($datas, $total, $perPage);

        if (empty($path)) {
            $path = Request::path();
        }
        $datas->setPath($path); //设置URL的路径
        return $datas;
    }
}