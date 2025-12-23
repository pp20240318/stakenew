<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AddProfitRateToUserMoneyLog extends Migrator
{
    public function change()
    {
        $this->table('user_money_log')
            ->addColumn('profit_rate', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'default' => 0,
                'null' => false,
                'comment' => '收益率',
            ])
            ->update();
    }
} 