<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\facade\Db;

class Dashboard extends Backend
{
    public function initialize(): void
    {
        parent::initialize();
    }

    public function index(): void
    {
        $this->success('', [
            'remark' => get_route_remark()
        ]);
    }

    public function countData(): void
    {
        $UserDayCount  = Db::query("SELECT  COUNT(*) AS users_count FROM     ba_user  WHERE create_time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY)");
        $UserMonthCount  = Db::query("SELECT  COUNT(*) AS users_count FROM     ba_user  WHERE create_time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 MONTH)");
        $UserThrMonthCount  = Db::query("SELECT  COUNT(*) AS users_count FROM     ba_user  WHERE create_time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 3 MONTH)");
        $userYearCount  = Db::query("SELECT  COUNT(*) AS users_count FROM     ba_user  WHERE create_time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 YEAR)");


        $newUserDay  = Db::query("SELECT FROM_UNIXTIME(create_time, '%Y-%m-%d') AS date,     COUNT(*) AS new_users_count FROM     ba_user  WHERE create_time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY)  GROUP BY FROM_UNIXTIME(create_time, '%Y-%m-%d')");
        $newUserMonth= Db::query("SELECT FROM_UNIXTIME(create_time, '%Y-%m-%d') AS date,     COUNT(*) AS new_users_count FROM     ba_user  WHERE create_time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 MONTH)  GROUP BY FROM_UNIXTIME(create_time, '%Y-%m-%d')");
        $newUserThrMonth= Db::query("SELECT FROM_UNIXTIME(create_time, '%Y-%m') AS date,     COUNT(*) AS new_users_count FROM     ba_user  WHERE create_time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 3 MONTH)  GROUP BY FROM_UNIXTIME(create_time, '%Y-%m')");
        $newUserYear = Db::query("SELECT FROM_UNIXTIME(create_time, '%Y-%m') AS date,     COUNT(*) AS new_users_count FROM     ba_user  WHERE create_time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 YEAR)  GROUP BY FROM_UNIXTIME(create_time, '%Y-%m')");


//        $withdrawCountDay= Db::query("SELECT FROM_UNIXTIME(create_time, '%Y-%m-%d') AS date,SUM(money) AS daily_recharge_amount FROM ba_user_money_log GROUP BY FROM_UNIXTIME(create_time, '%Y-%m-%d');");
//        $rechargeMonth= Db::query("SELECT FROM_UNIXTIME(create_time, '%Y-%m') AS date,SUM(money) AS monthly_recharge_amount FROM ba_user_money_log GROUP BY FROM_UNIXTIME(create_time, '%Y-%m');");
//
//        $rechargeAmountUserDay= Db::query("SELECT FROM_UNIXTIME(create_time, '%Y-%m-%d') AS date, count(DISTINCT(user_id)) AS daily_recharge_user FROM     ba_user_money_log GROUP BY     FROM_UNIXTIME(create_time, '%Y-%m-%d');");
//        $rechargeAmountUserMonth= Db::query("SELECT  FROM_UNIXTIME(create_time, '%Y-%m') AS date, count(DISTINCT(user_id)) AS monthly_recharge_user FROM     ba_user_money_log GROUP BY     FROM_UNIXTIME(create_time, '%Y-%m');");
//
//        $payment_rate = Db::query("SELECT CONCAT((SELECT COUNT(DISTINCT user_id) AS daily_recharge_user FROM ba_user_money_log) / (SELECT COUNT(id) FROM ba_user) * 100,'%') AS payment_rate");

//        $day1_retention_rate = Db::query("SELECT
//    FROM_UNIXTIME(create_time, '%Y-%m-%d') AS reg_date,
//    COUNT(*) AS new_users,
//    COUNT(DISTINCT CASE WHEN (last_login_time - create_time) >= 86400 THEN id END) AS day1_retained_users,
//    COUNT(DISTINCT CASE WHEN (last_login_time - create_time) >= 86400 THEN id END) / COUNT(*) AS day1_retention_rate
//FROM
//    ba_user
//GROUP BY
//    FROM_UNIXTIME(create_time, '%Y-%m-%d');");


        $this->success('', [
            'user' => $newUserDay,
            'userMonth' => $newUserMonth,
            'userThrMonth' => $newUserThrMonth,
            'userYear' => $newUserYear,
            'UserDayCount'   =>$UserDayCount,
            'UserMonthCount'   =>$UserMonthCount,
            'userThrMonthCount'   =>$UserThrMonthCount,
            'userYearCount'   =>$userYearCount,
//            'rechargeDay' => $rechargeDay,
//            'rechargeMonth' => $rechargeMonth,
//            'rechargeAmountUserDay' => $rechargeAmountUserDay,
//            'rechargeAmountUserMonth' => $rechargeAmountUserMonth,
//            'payment_rate' => $payment_rate,
//
//            '$day1_retention_rate' => $day1_retention_rate,
        ]);
    }

    public function countfundData(): void
    {


        $rechargeCountDay  = Db::query("SELECT  SUM(source_num) AS recharge FROM ba_finance_recharge  WHERE create_time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY)");
        $rechargeCountMonth  = Db::query("SELECT  SUM(source_num) AS recharge FROM ba_finance_recharge  WHERE create_time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 MONTH)");
        $rechargeCountThrMonth  = Db::query("SELECT  SUM(source_num) AS recharge FROM ba_finance_recharge  WHERE create_time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 3 MONTH)");
        $rechargeCountYear  = Db::query("SELECT  SUM(source_num) AS recharge FROM ba_finance_recharge  WHERE create_time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 YEAR)");

        $withdrawCountDay  = Db::query("SELECT  SUM(target_num) AS withdraw   FROM ba_finance_withdrawl  WHERE create_time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY)");
        $withdrawCountMonth  = Db::query("SELECT  SUM(target_num) AS withdraw   FROM ba_finance_withdrawl  WHERE create_time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 MONTH)");
        $withdrawCountThrMonth  = Db::query("SELECT  SUM(target_num) AS withdraw   FROM ba_finance_withdrawl  WHERE create_time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 3 MONTH)");
        $withdrawCountYear = Db::query("SELECT  SUM(target_num) AS withdraw   FROM ba_finance_withdrawl  WHERE create_time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 YEAR)");


        $this->success('', [
            'rechargeCountDay'   =>$rechargeCountDay,
            'rechargeCountMonth'   =>$rechargeCountMonth,
            'rechargeCountThrMonth'   =>$rechargeCountThrMonth,
            'rechargeCountYear'   =>$rechargeCountYear,
            'withdrawCountDay'   =>$withdrawCountDay,
            'withdrawCountMonth'   =>$withdrawCountMonth,
            'withdrawCountThrMonth,'   =>$withdrawCountThrMonth,
            'withdrawCountYear'   =>$withdrawCountYear,
        ]);
    }
}