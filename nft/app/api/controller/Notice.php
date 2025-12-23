<?php

namespace app\api\controller;

use think\facade\Db;
use think\Request;
use app\common\controller\Frontend;

/**
 * 公告接口
 */
class Notice extends Frontend
{
    // 不需要登录的接口
    protected array $noNeedLogin = ['list', 'detail'];

    /**
     * 获取公告列表
     */
    public function list(Request $request)
    {
        $page = $request->param('page', 1, 'intval');
        $limit = $request->param('limit', 10, 'intval');

        // 当前时间
        $now = time();

        // 构建查询条件
        $query = Db::name('notice')
            ->field('id, title, cover_image, view_count, create_time')
            ->where('status', 1)
            ->where(function ($query) use ($now) {
                // 时间段过滤：start_time 为空或小于等于当前时间
                $query->whereNull('start_time')
                    ->whereOr('start_time', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                // 时间段过滤：end_time 为空或大于等于当前时间
                $query->whereNull('end_time')
                    ->whereOr('end_time', '>=', $now);
            })
            ->order('sort', 'desc')
            ->order('create_time', 'desc');

        // 获取总数
        $total = $query->count();

        // 分页获取数据
        $list = $query->page($page, $limit)->select()->toArray();

        // 格式化数据
        foreach ($list as &$item) {
            $item['formatted_time'] = date('Y-m-d H:i', $item['create_time']);
        }

        return $this->success('', [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    /**
     * 获取公告详情
     */
    public function detail(Request $request)
    {
        $id = $request->param('id', 0, 'intval');

        if (!$id) {
            return $this->error('参数错误');
        }

        // 当前时间
        $now = time();

        // 获取公告详情
        $notice = Db::name('notice')
            ->where('id', $id)
            ->where('status', 1)
            ->where(function ($query) use ($now) {
                $query->whereNull('start_time')
                    ->whereOr('start_time', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_time')
                    ->whereOr('end_time', '>=', $now);
            })
            ->find();

        if (!$notice) {
            return $this->error('公告不存在或已过期');
        }

        // 更新浏览次数
        Db::name('notice')->where('id', $id)->inc('view_count')->update();

        // 格式化数据
        $notice['formatted_time'] = date('Y-m-d H:i', $notice['create_time']);

        return $this->success('', [
            'detail' => $notice
        ]);
    }
}
