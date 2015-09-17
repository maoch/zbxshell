<?php

namespace App;

use App\Exceptions\CommonException;
use Auth;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;

    public static $ruleMessages = array(
        'name.required' => '用户名字段是必填项',
        'name.unique' => '该用户已经存在，请重新输入',
        'old_password.required' => '旧密码字段是必填项',
        'password.required' => '密码字段是必填项',
        'password.min' => '密码至少6位，请重新输入',
        'password.confirmed' => '密码输入不一致，请重新输入',
    );
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'isadmin', 'status', 'password'];

    public function authInfo()
    {
        return $this->hasMany('App\AuthInfo', 'userid', 'id');
    }

    /**
     * 检查用户的启用，禁止，删除的情况
     * @param $type
     * @param $user
     */
    public static function checkUser($type, $user)
    {
        if (!isset($type)) {
            throw new CommonException('操作类型不能为空!');
        }
        if (Auth::user()->id == $user->id) {
            throw new CommonException('用户不能操作自身账号，请重新操作！');
        }
        if ($type == $user->status) {
            throw new CommonException('已经是当前状态，无需再次操作！');
        }
    }
}
