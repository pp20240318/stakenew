<template>
  <view class="page">
    <!-- 顶部导航栏 -->
    <view class="nav-bar">
      <view class="nav-left" @tap="goBack">
        <image  class="back-icon" src="/static/img/market/detail/backicon.png" ></image>
      </view>
      <text class="nav-title">{{ $t('公告') }}</text>
      <view class="nav-right"></view>
    </view>

    <!-- 公告列表 -->
    <scroll-view
      class="notice-list"
      scroll-y
      @scrolltolower="loadMore"
      :refresher-enabled="true"
      :refresher-triggered="isRefreshing"
      @refresherrefresh="onRefresh"
    >
      <view v-if="noticeList.length > 0">
        <view
          class="notice-item"
          v-for="(item, index) in noticeList"
          :key="item.id"
          @tap="goToDetail(item.id)"
        >
          <view class="notice-content">
            <text class="notice-title">{{ item.title }}</text>
            <view class="notice-info">
              <text class="notice-time">{{ item.formatted_time }}</text>
              <text class="notice-views">{{ $t('浏览') }} {{ item.view_count }}</text>
            </view>
          </view>
          <text class="notice-arrow">></text>
        </view>
      </view>

      <!-- 空状态 -->
      <view v-else-if="!loading" class="empty-state">
        <image class="empty-icon" src="/static/img/empty.png" mode="aspectFit" />
        <text class="empty-text">{{ $t('暂无公告') }}</text>
      </view>

      <!-- 加载更多 -->
      <view v-if="loading" class="loading-more">
        <text>{{ $t('加载中...') }}</text>
      </view>

      <!-- 没有更多数据 -->
      <view v-if="noMore && noticeList.length > 0" class="no-more">
        <text>{{ $t('没有更多数据了') }}</text>
      </view>
    </scroll-view>
  </view>
</template>

<script>
export default {
  data() {
    return {
      noticeList: [],
      page: 1,
      limit: 10,
      total: 0,
      loading: false,
      noMore: false,
      isRefreshing: false
    };
  },
  onLoad() {
    this.loadNoticeList();
  },
  methods: {
    // 加载公告列表
    loadNoticeList() {
      if (this.loading) return;

      this.loading = true;
      const t = this;

      t.req({
        url: 'notice/list',
        data: {
          page: t.page,
          limit: t.limit
        },
        success: function(res) {
          t.loading = false;
          t.isRefreshing = false;

          if (res.code == 1 && res.data) {
            const list = res.data.list || [];

            if (t.page === 1) {
              t.noticeList = list;
            } else {
              t.noticeList = t.noticeList.concat(list);
            }

            t.total = res.data.total || 0;
            t.noMore = t.noticeList.length >= t.total;
          }
        },
        fail: function(err) {
          t.loading = false;
          t.isRefreshing = false;
          console.error('获取公告列表失败:', err);
        }
      });
    },

    // 下拉刷新
    onRefresh() {
      this.isRefreshing = true;
      this.page = 1;
      this.noMore = false;
      this.loadNoticeList();
    },

    // 加载更多
    loadMore() {
      if (this.loading || this.noMore) return;
      this.page++;
      this.loadNoticeList();
    },

    // 跳转到公告详情
    goToDetail(id) {
      uni.navigateTo({
        url: `/pages/notice/detail?id=${id}`
      });
    },

    // 返回上一页
    goBack() {
      uni.navigateBack();
    }
  }
};
</script>

<style>
* {
  box-sizing: border-box;
}

.page {
  background-color: #1A2026;
  min-height: 100vh;
  width: 100%;
  max-width: 100vw;
  overflow-x: hidden;
  display: flex;
  flex-direction: column;
}

/* 导航栏 */
.nav-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20rpx 30rpx; 
  background-color: #1A2026;
}

.nav-left, .nav-right {
  width: 60rpx;
}

.back-icon {
  color: #FFFFFF;
  font-size: 40rpx;
}

.nav-title {
  color: #FFFFFF;
  font-size: 34rpx;
  font-weight: 500;
}

/* 公告列表 */
.notice-list {
  flex: 1;
  width: 100%;
  padding: 20rpx 30rpx;
}

.notice-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #252E3C;
  border-radius: 16rpx;
  padding: 30rpx;
  margin-bottom: 20rpx;
}

.notice-content {
  flex: 1;
}

.notice-title {
  color: #FFFFFF;
  font-size: 30rpx;
  font-weight: 500;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

.notice-info {
  display: flex;
  align-items: center;
  gap: 30rpx;
  margin-top: 16rpx;
}

.notice-time {
  color: #A0AEC0;
  font-size: 24rpx;
}

.notice-views {
  color: #A0AEC0;
  font-size: 24rpx;
}

.notice-arrow {
  color: #A0AEC0;
  font-size: 32rpx;
  margin-left: 20rpx;
}

/* 空状态 */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 100rpx 0;
}

.empty-icon {
  width: 200rpx;
  height: 200rpx;
  opacity: 0.5;
}

.empty-text {
  color: #A0AEC0;
  font-size: 28rpx;
  margin-top: 30rpx;
}

/* 加载更多 */
.loading-more, .no-more {
  text-align: center;
  padding: 30rpx 0;
}

.loading-more text, .no-more text {
  color: #A0AEC0;
  font-size: 24rpx;
}
</style>
