<template>
  <view class="page">
    <!-- 顶部导航栏 -->
    <view class="nav-bar">
      <view class="nav-left" @tap="goBack">
        <image  class="back-icon" src="/static/img/market/detail/backicon.png" ></image>
      </view>
      <text class="nav-title">{{ $t('公告详情') }}</text>
      <view class="nav-right"></view>
    </view>

    <!-- 加载中 -->
    <view v-if="loading" class="loading-state">
      <text>{{ $t('加载中...') }}</text>
    </view>

    <!-- 公告内容 -->
    <scroll-view v-else-if="notice" class="notice-detail" scroll-y>
      <view class="notice-header">
        <text class="notice-title">{{ notice.title }}</text>
        <view class="notice-meta">
          <text class="notice-time">{{ notice.formatted_time }}</text>
          <text class="notice-views">{{ $t('浏览') }} {{ notice.view_count }}</text>
        </view>
      </view>

      <!-- 封面图片 -->
      <image
        v-if="notice.cover_image"
        class="notice-cover"
        :src="notice.cover_image"
        mode="widthFix"
      />

      <!-- 公告正文 -->
      <view class="notice-content">
        <rich-text :nodes="notice.content"></rich-text>
      </view>
    </scroll-view>

    <!-- 错误状态 -->
    <view v-else class="error-state">
      <text class="error-text">{{ $t('公告不存在或已过期') }}</text>
      <view class="error-btn" @tap="goBack">
        <text>{{ $t('返回') }}</text>
      </view>
    </view>
  </view>
</template>

<script>
export default {
  data() {
    return {
      noticeId: null,
      notice: null,
      loading: true
    };
  },
  onLoad(options) {
    if (options.id) {
      this.noticeId = options.id;
      this.loadNoticeDetail();
    } else {
      this.loading = false;
    }
  },
  methods: {
    // 加载公告详情
    loadNoticeDetail() {
      const t = this;
      t.loading = true;

      t.req({
        url: 'notice/detail',
        data: {
          id: t.noticeId
        },
        success: function(res) {
          t.loading = false;

          if (res.code == 1 && res.data && res.data.detail) {
            t.notice = res.data.detail;
          }
        },
        fail: function(err) {
          t.loading = false;
          console.error('获取公告详情失败:', err);
        }
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

/* 加载状态 */
.loading-state {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.loading-state text {
  color: #A0AEC0;
  font-size: 28rpx;
}

/* 公告详情 */
.notice-detail {
  flex: 1;
  padding: 30rpx;
  width:100%;
}

.notice-header {
  margin-bottom: 30rpx;
}

.notice-title {
  color: #FFFFFF;
  font-size: 36rpx;
  font-weight: 600;
  line-height: 1.5;
  display: block;
}

.notice-meta {
  display: flex;
  align-items: center;
  gap: 30rpx;
  margin-top: 20rpx;
}

.notice-time, .notice-views {
  color: #A0AEC0;
  font-size: 24rpx;
}

/* 封面图片 */
.notice-cover {
  width: 100%;
  border-radius: 16rpx;
  margin-bottom: 30rpx;
}

/* 公告正文 */
.notice-content {
  background: #252E3C;
  border-radius: 16rpx;
  padding: 30rpx;
}

.notice-content rich-text {
  color: #E2E8F0;
  font-size: 28rpx;
  line-height: 1.8;
}

/* 错误状态 */
.error-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 100rpx 30rpx;
}

.error-text {
  color: #A0AEC0;
  font-size: 28rpx;
  margin-bottom: 40rpx;
}

.error-btn {
  background: #40DFBF;
  padding: 20rpx 60rpx;
  border-radius: 40rpx;
}

.error-btn text {
  color: #1A2026;
  font-size: 28rpx;
  font-weight: 500;
}
</style>
