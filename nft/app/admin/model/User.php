<?php

namespace app\admin\model;

use ba\Random;
use think\Model;
use think\model\relation\BelongsTo;
use think\facade\Db;

/**
 * User
 */
class User extends Model
{
    // 表名
    protected $name = 'user';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;


    
      /**
     * 追加属性
     */
    protected $append = [
        'recharge_num',
        'withdrawl_num',
    ];

    public function getRechargeNumAttr($value, $row): float
    {
       
        return Db::name('finance_recharge')
                ->whereNotNull('salesman')
                ->where('salesman','<>','')
                ->where('status',1)
                ->where('user_id',$row['id'])
                ->sum('target_num'); 
    }
    public function getWithdrawlNumAttr($value, $row): float
    {
        return 0;
        // Db::name('finance_withdrawl')
        //         ->whereNotNull('salesman')
        //         ->where('salesman','<>','')
        //         ->where('status',1)
        //         ->where('user_id',$row['id'])
        //         ->sum('usdtold_num'); 
    }

    public function getMoneyAttr($value): float
    {
        return (float)$value;
    }

//    public function getCreateTimeAttr($value, $row): string
//    {
//        return strtotime($value);
//    }

    public function referenceTable(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'reference', 'id');
    }

    public function admin(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'agent', 'id');
    }

    /**
     * 重置用户密码
     * @param int|string $uid         用户ID
     * @param string     $newPassword 新密码
     * @return int|User
     */
    public function resetPassword(int|string $uid, string $newPassword): int|User
    {
        $salt   = Random::build('alnum', 16);
        $passwd = encrypt_password($newPassword, $salt);
        return $this->where(['id' => $uid])->update(['password' => $passwd, 'salt' => $salt]);
    }


    /**
     * 重置用户支付密码
     * @param int|string $uid         用户ID
     * @param string     $newPassword 新密码
     * @return int|User
     */
    public function resetPassword2(int|string $uid, string $newPassword): int|User
    {

        $salt   = $this->where(['id' => $uid])->value('salt');
        $passwd = encrypt_password($newPassword, $salt);


        return $this->where(['id' => $uid])->update(['password2' => $passwd]);

    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class, 'group_id');
    }

    public function getAvatarAttr($value): string
    {
        return full_url($value, false, config('buildadmin.default_avatar'));
    }

    public function setAvatarAttr($value): string
    {
        return $value == full_url('', false, config('buildadmin.default_avatar')) ? '' : $value;
    }
}