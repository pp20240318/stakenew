<?php

namespace app\api\controller;

use think\facade\Db;
use think\Request;
use app\common\controller\Frontend;

class TradePair extends Frontend
{
    protected array $noNeedLogin = ['list', 'detail'];

    protected array $noNeedPermission = ['*'];

    /**
     * 获取交易对列表
     */
    public function list(Request $request)
    {
        $type = $request->param('type', ''); // 可选：按类型筛选
        $status = $request->param('status', 1); // 默认只获取启用的

        // 构建查询
        $query = Db::name('soptcoin')
            ->where('status', $status);

        if (!empty($type)) {
            $query->where('type', $type);
        }

        // 获取列表
        $list = $query->order('id', 'asc')
            ->select()
            ->toArray();

        // 格式化数据
        foreach ($list as &$item) { 

            // 提取币种符号
            $item['symbol'] = explode('/', $item['name'])[0] ?? '';

            // 格式化时间
            if ($item['created_at']) {
                $item['created_at_formatted'] = date('Y-m-d H:i:s', strtotime($item['created_at']));
            }
        }

        return $this->success('', [
            'list' => $list,
            'total' => count($list)
        ]);
    }

    /**
     * 获取单个交易对详情
     */
    public function detail(Request $request)
    {
        $id = $request->param('id', 0);
        $code = $request->param('code', '');

        if (empty($id) && empty($code)) {
            return $this->error('ID or code is required');
        }

        // 构建查询
        $query = Db::name('soptcoin');

        if (!empty($id)) {
            $query->where('id', $id);
        } elseif (!empty($code)) {
            $query->where('code', $code);
        }

        $item = $query->find();

        if (!$item) {
            return $this->error('Trade pair not found');
        }

        // 格式化数据
        if (empty($item['icon'])) {
            $item['icon'] = '/static/img/coin/' . strtolower(explode('/', $item['name'])[0]) . '.png';
        }

        $item['symbol'] = explode('/', $item['name'])[0] ?? '';

        return $this->success('', [
            'item' => $item
        ]);
    }

    /**
     * 搜索交易对
     */
    public function search(Request $request)
    {
        $keyword = $request->param('keyword', '');

        if (empty($keyword)) {
            return $this->error('Keyword is required');
        }

        // 搜索交易对
        $list = Db::name('soptcoin')
            ->where('status', 1)
            ->where(function($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%')
                    ->whereOr('code', 'like', '%' . $keyword . '%')
                    ->whereOr('realname', 'like', '%' . $keyword . '%');
            })
            ->order('id', 'asc')
            ->select()
            ->toArray();

        // 格式化数据
        foreach ($list as &$item) {
            if (empty($item['icon'])) {
                $item['icon'] = '/static/img/coin/' . strtolower(explode('/', $item['name'])[0]) . '.png';
            }
            $item['symbol'] = explode('/', $item['name'])[0] ?? '';
        }

        return $this->success('', [
            'list' => $list,
            'total' => count($list)
        ]);
    }
}
