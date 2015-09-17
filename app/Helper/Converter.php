<?php
/**
 * Created by PhpStorm.
 * User: mao
 * Date: 2015/6/30
 * Time: 14:39
 */

namespace App\Helper;

use Request;


class Converter
{
    /**
     * 根据传入的image和
     * @param $imageResult
     * @param $imageArr
     * @param $mapResult
     * @param $isHead //标题
     * @return string
     */
    public static function createImageStr($imageResult, $mapResults, $isHead = true)
    {
        $imageArr = [];
        $imageHtmlstr = '';
        //获取image列表
        foreach ($imageResult as $image) {
            $image_temp = [];
            $image_temp['name'] = $image->name;
            $image_temp['sysmapid'] = $image->sysmapid;
            $image_temp['imageid'] = $image->imageid;
            $image_temp['image'] = 'data:image/png;base64,' . $image->image;
            //获取图片原有大小
            list($imageWidth, $imageHeight) = getimagesize($image_temp['image']);
            if (empty($imageWidth)) {
                $imageWidth = 200;
            }
            if (empty($imageHeight)) {
                $imageHeight = 200;
            }
            $image_temp['imageWidth'] = $imageWidth;
            $image_temp['imageHeight'] = $imageHeight;
            $image_temp['imagetype'] = $image->imagetype;
            $imageArr[$image->imageid] = $image_temp;
        }
        foreach ($mapResults as $mapResult) {
            //获取元素
            $selements = $mapResult->selements;
            //获取连线
            $links = $mapResult->links;
            $linestr = '';

            $lineCoordinate = []; //线的坐标
            $imagestr = '';
            $textstr = '';

            foreach ($selements as $selement) {
                $labelArr = explode("\r\n", $selement->label);
                $imageHeightstr = $imageArr[$selement->iconid_off]['imageHeight'];
                $y_offset = 0;
                $imageWidthstr = $imageArr[$selement->iconid_off]['imageWidth'];
                foreach ($labelArr as $label) {
                    //label坐标
                    //label_location -1 - (default) default location;0 - bottom; 1 - left; 2 - right;  3 - top.
                    switch ($selement->label_location) {
                        case '1': //left
                            $x = ($selement->x - 10);
                            $y = ($selement->y + $y_offset + 20);
                            $text_anchor = "end";
                            break;
                        case '2': //right
                            $x = ($selement->x + $imageWidthstr + 10);
                            $y = ($selement->y + $y_offset + 20);
                            $text_anchor = "start";
                            break;
                        case '3': //top
                            $x = ($selement->x + $imageWidthstr / 2);
                            $y = ($selement->y - 20);
                            $text_anchor = "middle";
                            break;
                        default : //bottom , default
                            $x = ($selement->x + $imageWidthstr / 2);
                            $y = ($selement->y + $imageHeightstr + 20);
                            $text_anchor = "middle";
                            break;
                    }
                    $textstr .= '
                <text
                x="' . $x . '"
                y="' . $y . '"
                font-size="13"
                text-anchor="' . $text_anchor . '"
                >' . $label . '
                </text>';
                    $imageHeightstr += 20;
                    $y_offset += 20;
                }
                $lineCoordinate_temp = [];
                $imageStr_temp = '<image elementid="' . $selement->elementid . '"  elementtype="' . $selement->elementtype . '"
                x="' . $selement->x . '"
                y="' . $selement->y . '"
                width="' . $imageArr[$selement->iconid_off]['imageWidth'] . '"
                height="' . $imageArr[$selement->iconid_off]['imageHeight'] . '"
                xlink:href="' . $imageArr[$selement->iconid_off]['image'] . '">
                </image>';
                $imagestr .= $imageStr_temp;

                $lineCoordinate_temp['x'] = $selement->x + ($imageArr[$selement->iconid_off]['imageWidth']) / 2;
                $lineCoordinate_temp['y'] = $selement->y + ($imageArr[$selement->iconid_off]['imageHeight']) / 2;
                $lineCoordinate[$selement->selementid] = $lineCoordinate_temp;

            }
            foreach ($links as $link) {
                $linestr .= '<line style="stroke:#' . $link->color . ';
                            stroke-width:1"
                            y1="' . $lineCoordinate[$link->selementid1]['y'] . '"
                            x1="' . $lineCoordinate[$link->selementid1]['x'] . '"
                            y2="' . $lineCoordinate[$link->selementid2]['y'] . '"
                            x2="' . $lineCoordinate[$link->selementid2]['x'] . '"
                        />';
            }
            $headstr = '';
            if ($isHead) {
                $headstr = '<div class="panel-heading" align="center">' . $mapResult->name . '</div>';
            }
            //在line 、image 和text的显示顺序需要注意
            $imageHtmlstr .= $headstr . '<div class="panel-body"><svg xmlns="http://www.w3.org/2000/svg" height="100%" width="100%"
                                viewbox="20 0 ' . $mapResult->width . ' ' . ($mapResult->height) . '">'
                . $linestr . $imagestr . $textstr . '
                             </svg></div>';
        }

        return $imageHtmlstr;
    }

    /**
     * 单位转换
     * @param $value
     * @param $unit
     * @return string
     */
    public static function unitConversion($itemName, $value, $unit)
    {
        if ($unit == 'B' || $unit == '%' || $unit == 'Hz' || $unit == 's' || $unit == 'uptime' || $unit == 'Bps' || $unit == 'vps') {
            switch ($unit) {
                case 'Bps':
                case 'B':
                    $value_ret = self::binaryConversion($value, $unit);
                    break;
                case '%':
                case 'vps':
                    $value_ret = number_format($value, 2) . ' ' . $unit;
                    break;
                case 'Hz':
                    $value_ret = self::decimalConversion($value, $unit);
                    break;
                case 's':
                    $value_ret = $value * pow(10, 3) . ' ms';
                    break;
                case 'uptime':
                    $value_ret = self::timeConversion($value);
                    break;
                default:
                    $value_ret = $value . ' ' . $unit;
            }
        } else {
            switch ($itemName) {
                // ping status 1 To Up  other To Down
                case 'ICMP ping':
                case 'Agent ping':
                    if ($value == '1') {
                        $value_ret = 'Up';
                    } else {
                        $value_ret = 'Down';
                    }
                    //todo 需要确定除1外，是不是down
                    break;
                default :
                    $value_ret = $value . ' ' . $unit;
            }
        }

        return $value_ret;
    }

    /**
     * 二进制单位转换
     * @param $value
     * @param $unit
     * @return string
     */
    private static function binaryConversion($value, $unit)
    {
        $value_ret = $value . ' ' . $unit;
        if ($value <= pow(2, 10)) {
            $value_ret = number_format($value, 2) . ' ' . $unit;
        } elseif ($value > pow(2, 10) && $value <= pow(2, 20)) {
            $value_ret = number_format($value / pow(2, 10), 2) . ' K' . $unit;
        } elseif ($value > pow(2, 20) && $value <= pow(2, 30)) {
            $value_ret = number_format($value / pow(2, 20), 2) . ' M' . $unit;
        } elseif ($value > pow(2, 30)) {
            $value_ret = number_format($value / pow(2, 30), 2) . ' G' . $unit;
        }

        return $value_ret;
    }

    /**
     * 十进制单位转换
     * @param $value
     * @return string
     */
    private static function decimalConversion($value, $unit)
    {
        $value_ret = $value . ' ' . $unit;
        if ($value <= pow(10, 3)) {
            $value_ret = number_format($value, 2) . ' ' . $unit;
        } elseif ($value > pow(10, 3) && $value <= pow(10, 6)) {
            $value_ret = number_format($value / pow(10, 3), 2) . ' K' . $unit;
        } elseif ($value > pow(10, 6) && $value <= pow(10, 9)) {
            $value_ret = number_format($value / pow(10, 6), 2) . ' M' . $unit;
        } elseif ($value > pow(10, 9)) {
            $value_ret = number_format($value / pow(10, 9), 2) . ' G' . $unit;
        }

        return $value_ret;
    }

    /**
     * 时间 秒 转换成天
     * @param $value
     * @param $value_ret
     * @return string
     */
    private static function timeConversion($value)
    {
        $value_ret = '';
        //超过1天
        if ($value >= 24 * 60 * 60) {
            $value_ret .= floor($value / (24 * 60 * 60)) . ' days,';
            $value = $value % (24 * 60 * 60);
        }
        //转换成小时
        if ($value >= 60 * 60) {
            $value_ret .= floor($value / (60 * 60)) . ':';
            $value = $value % (60 * 60);
        } else {
            $value_ret .= '00:';
        }
        //转换成分钟
        if ($value >= 60) {
            $value_ret .= floor($value / 60) . ':';
            $value = $value % 60;
        } else {
            $value_ret .= '00:';
        }
        //转换成秒
        if ($value < 60) {
            $value_ret .= $value;
        }

        return $value_ret;
    }
}