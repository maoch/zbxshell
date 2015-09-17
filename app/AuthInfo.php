<?php

namespace App;

use App\Exceptions\CommonException;
use Illuminate\Database\Eloquent\Model;

class AuthInfo extends Model
{
    public static $ruleMessages = array();
    protected $primaryKey = 'authInfoId';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'authinfo';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['userid', 'flag', 'key'];

    /**
     * 保存authInfo，如果存在则更新，如果不存在，则保存
     * @param $authInfo
     * @throws CommonException
     */
    public static function saveAuthInfo($authInfo)
    {
        if (!isset($authInfo)) {
            throw new CommonException('参数不为空！');
        }
        $auth = AuthInfo::where('userid', $authInfo->userid)->where('flag', $authInfo->flag)->first();
        if (isset($auth)) {
            $auth->key = $authInfo->key;
        } else {
            $auth = $authInfo;
        }
        $auth->save();
    }
}
