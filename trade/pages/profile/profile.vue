<template>
	<view class="page flex-col"> 

		<!-- Page Title -->
		<view class="page-title-wrapper flex-col">
			<text class="page-title">{{ $t('钱包') }}</text>
		</view>

		<!-- User Profile Section -->
		<view class="profile-section flex-row">
			<image class="avatar" :src="userInfo.avatar || '/static/img/default-avatar.png'" />
			<view class="profile-info flex-row">
				<view class="user-details flex-col">
					<text class="user-name">{{ userInfo.nickname || 'login' }}</text>
					<text class="user-id">UID: {{ userInfo.userid || '000000000' }}</text>
				</view>
				<image class="edit-icon" src="/static/img/edit-icon.png" @click="navigateTo('/pages/mine/truename')" />
			</view>
		</view>

		<!-- Balance Cards -->
		<view class="balance-cards-wrapper">
			<view class="balance-cards flex-row">
				<!-- 真实余额 -->
				<view class="balance-card flex-row" @tap="navigateTo('/pages/wallet/index')">
					<view class="card-icon-wrapper">
						<image class="card-icon-img" src="/static/img/market/detail/money.png"></image>
					</view>
					<view class="card-info flex-col">
						<text class="card-label">{{ $t('账户余额') }}</text>
						<text class="card-value">${{ userInfo.totalAssets || '0.00' }}</text>
					</view>
				</view>
				<!-- 虚拟余额 -->
				<view class="balance-card balance-card-demo flex-row" @tap="navigateTo('/pages/wallet/index')">
					<view class="card-icon-wrapper">
						<image class="card-icon-img" src="/static/img/market/detail/pig.png"></image>
					</view>
					<view class="card-info flex-col">
						<text class="card-label">{{ $t('模拟余额') }}</text>
						<text class="card-value">${{ userInfo.demoBalance || '0.00' }}</text>
					</view>
				</view>
			</view>
		</view>
		
		<!-- Menu Items -->
		<view class="menu-section flex-col">
			<!-- Deposit -->
			<view class="menu-item flex-row" @tap="navigateTo('/pages/recharge/depositDetail')">
				<view class="menu-item-left flex-row">
					<image class="menu-icon" src="/static/img/profile/deposit.png" />
					<text class="menu-text">{{ $t('充值') }}</text>
				</view>
				<image class="arrow-icon" src="/static/img/arrow-right.png" />
			</view>

			<!-- Withdrawal -->
			<view class="menu-item flex-row" @tap="navigateTo('/pages/recharge/withdrawal')">
				<view class="menu-item-left flex-row">
					<image class="menu-icon" src="/static/img/profile/withdrawal.png" />
					<text class="menu-text">{{ $t('提现') }}</text>
				</view>
				<image class="arrow-icon" src="/static/img/arrow-right.png" />
			</view>

			<!-- Google Authenticator -->
			<view class="menu-item flex-row" @tap="navigateTo('/pages/mine/truename')">
				<view class="menu-item-left flex-row">
					<image class="menu-icon" src="/static/img/profile-icon.png" />
					<text class="menu-text">{{ $t('谷歌验证器') }}</text>
				</view>
				<image class="arrow-icon" src="/static/img/arrow-right.png" />
			</view>

			<!-- Invite Friends -->
			<view class="menu-item flex-row" @tap="navigateTo('/pages/home/promote')">
				<view class="menu-item-left flex-row">
					<image class="menu-icon" src="/static/img/invite-icon.png" />
					<text class="menu-text">{{ $t('邀请好友') }}</text>
				</view>
				<image class="arrow-icon" src="/static/img/arrow-right.png" />
			</view>

			<!-- Language -->
			<view class="menu-item flex-row" @tap="showLanguageModal = true">
				<view class="menu-item-left flex-row">
					<image class="menu-icon" src="/static/img/language-icon.png" />
					<text class="menu-text">{{ $t('语言') }}</text>
				</view>
				<view class="menu-item-right flex-row">
					<text class="language-text">{{ actionSheetItems[showlanguageindex] || 'English' }}</text>
					<image class="arrow-icon" src="/static/img/arrow-right.png" />
				</view>
			</view>

			<!-- Settings -->
			<view class="menu-item flex-row" @tap="showPasswordModal = true">
				<view class="menu-item-left flex-row">
					<image class="menu-icon" src="/static/img/profile/changepassword.png" />
					<text class="menu-text">{{ $t('修改密码') }}</text>
				</view>
				<image class="arrow-icon" src="/static/img/arrow-right.png" />
			</view>
			<view class="menu-item flex-row" @tap="showPayPasswordModal = true">
				<view class="menu-item-left flex-row">
					<image class="menu-icon" src="/static/img/profile/changepassword.png" />
					<text class="menu-text">{{ $t('修改支付密码') }}</text>
				</view>
				<image class="arrow-icon" src="/static/img/arrow-right.png" />
			</view>
			<view class="menu-item flex-row" @tap="logout">
				<view class="menu-item-left flex-row">
					<image class="menu-icon" src="/static/img/profile/loginout.png" />
					<text class="menu-text">{{ $t('退出登录') }}</text>
				</view>
				<image class="arrow-icon" src="/static/img/arrow-right.png" />
			</view>
		</view>

		<!-- Language Selection Modal -->
		<view class="modal-overlay" v-if="showLanguageModal" @tap="showLanguageModal = false">
			<view class="modal-content" @tap.stop>
				<view class="modal-header">
					<text class="modal-title">{{ $t('选择语言') }}</text>
					<image class="modal-close" src="/static/img/close-icon.png" @tap="showLanguageModal = false" />
				</view>
				<view class="language-list">
					<view
						v-for="(item, index) in actionSheetItems"
						:key="index"
						class="language-item"
						:class="{'language-item-active': index === showlanguageindex}"
						@tap="selectLanguage(index, item)">
						<text class="language-name">{{ item }}</text>
						<image v-if="index === showlanguageindex" class="check-icon" src="/static/img/check-icon.png" />
					</view>
				</view>
			</view>
		</view>

		<!-- Change Password Modal -->
		<view class="modal-overlay-center" v-if="showPasswordModal" @tap="closePasswordModal">
			<view class="password-modal-content" @tap.stop>
				<view class="password-modal-header">
					<text class="password-modal-title">{{ $t('修改密码') }}</text>
				</view>
				<view class="password-modal-body">
					<view class="password-input-group">
						<text class="password-label">{{ $t('新密码') }}</text>
						<input
							class="password-input"
							type="password"
							v-model="newPassword"
							:placeholder="$t('请输入新密码')"
						/>
					</view>
					<view class="password-input-group">
						<text class="password-label">{{ $t('确认密码') }}</text>
						<input
							class="password-input"
							type="password"
							v-model="confirmPassword"
							:placeholder="$t('请再次输入新密码')"
						/>
					</view>
				</view>
				<view class="password-modal-footer">
					<view class="password-btn password-btn-cancel" @tap="closePasswordModal">
						<text class="password-btn-text">{{ $t('取消') }}</text>
					</view>
					<view class="password-btn password-btn-confirm" @tap="changePassword">
						<text class="password-btn-text">{{ $t('确认') }}</text>
					</view>
				</view>
			</view>
		</view>

		<!-- Change Pay Password Modal -->
		<view class="modal-overlay-center" v-if="showPayPasswordModal" @tap="closePayPasswordModal">
			<view class="password-modal-content" @tap.stop>
				<view class="password-modal-header">
					<text class="password-modal-title">{{ $t('修改支付密码') }}</text>
				</view>
				<view class="password-modal-body">
					<view class="password-input-group">
						<text class="password-label">{{ $t('新支付密码') }}</text>
						<input
							class="password-input"
							type="password"
							v-model="newPayPassword"
							:placeholder="$t('请输入新支付密码')"
						/>
					</view>
					<view class="password-input-group">
						<text class="password-label">{{ $t('确认支付密码') }}</text>
						<input
							class="password-input"
							type="password"
							v-model="confirmPayPassword"
							:placeholder="$t('请再次输入新支付密码')"
						/>
					</view>
				</view>
				<view class="password-modal-footer">
					<view class="password-btn password-btn-cancel" @tap="closePayPasswordModal">
						<text class="password-btn-text">{{ $t('取消') }}</text>
					</view>
					<view class="password-btn password-btn-confirm" @tap="changePayPassword">
						<text class="password-btn-text">{{ $t('确认') }}</text>
					</view>
				</view>
			</view>
		</view>

	</view>
</template>

<script>
	export default {
		data() {
			return {
				userInfo: {
					nickname: '',
					avatar: '',
					userid: '',
					level: this.$t('VIP 1'),
					totalAssets: '0.00',
					demoBalance: '0.00',
					totalProfit: 0,
					runningBots: 0,
					kefu: ''
				},
				showlanguagetxt: uni.getStorageSync('userlanguagetxt'),
				showlanguageindex: uni.getStorageSync('userlanguageindex') || 0,
				actionSheetItems: ['English','中文', 'Português','日本語'],
				actionSheetItemsvalue: ['en', 'zhHans', 'ptyy','ry'],
				showLanguageModal: false,
				showPasswordModal: false,
				newPassword: '',
				confirmPassword: '',
				showPayPasswordModal: false,
				newPayPassword: '',
				confirmPayPassword: ''
			}
		},
		onShow() {
			// 检查登录状态，未登录则跳转到登录页
			const token = uni.getStorageSync('token');
			const userid = uni.getStorageSync('userid');
			if (!token || !userid) {
				uni.showModal({
					title: this.$t('提示'),
					content: this.$t('请先登录'),
					confirmText: this.$t('登录'), 
					cancelText: this.$t('取消'),
					success: (res) => {
						if (res.confirm) {
							uni.navigateTo({
								url: '/pages/login/index'
							});
						}else{
							uni.switchTab({
								url: '/pages/market/detail'
							});
						}
					}
				});
				return;
			}
			this.fetchUserData();
		},
		methods: {
			navigateTo(url) {
				uni.navigateTo({
					url: url
				});
			},

			selectLanguage: function (index, val) {
				this.$i18n.locale = this.actionSheetItemsvalue[index];
				this.showlanguageindex = index;
				uni.setStorageSync('userlanguagetxt', val);
				uni.setStorageSync('userlanguage', this.actionSheetItemsvalue[index]);
				uni.setStorageSync('userlanguageindex', index);
				this.showLanguageModal = false;

				// 触发语言变化事件，更新tabBar
				uni.$emit('languageChanged');
			},

			closePasswordModal: function() {
				this.showPasswordModal = false;
				this.newPassword = '';
				this.confirmPassword = '';
			},

			closePayPasswordModal: function() {
				this.showPayPasswordModal = false;
				this.newPayPassword = '';
				this.confirmPayPassword = '';
			},

			changePayPassword: function() {
				const t = this;

				// 验证输入
				if (!t.newPayPassword || t.newPayPassword.trim() === '') {
					uni.showModal({
						title: t.$t('提示'),
						content: t.$t('请输入新支付密码'),
						showCancel: false,
						confirmText: t.$t('确定')
					});
					return;
				}

				if (t.newPayPassword.length < 6) {
					uni.showModal({
						title: t.$t('提示'),
						content: t.$t('支付密码长度不能少于6位'),
						showCancel: false,
						confirmText: t.$t('确定')
					});
					return;
				}

				if (!t.confirmPayPassword || t.confirmPayPassword.trim() === '') {
					uni.showModal({
						title: t.$t('提示'),
						content: t.$t('请输入确认支付密码'),
						showCancel: false,
						confirmText: t.$t('确定')
					});
					return;
				}

				if (t.newPayPassword !== t.confirmPayPassword) {
					uni.showModal({
						title: t.$t('提示'),
						content: t.$t('两次输入的支付密码不一致'),
						showCancel: false,
						confirmText: t.$t('确定')
					});
					return;
				}

				// 调用修改支付密码API
				t.req({
					url: "user/changePayPassword",
					data: {
						newpassword: t.newPayPassword
					},
					success: function(res) {
						if (res.code === 1) {
							uni.showModal({
								title: t.$t('提示'),
								content: t.$t('支付密码修改成功'),
								showCancel: false,
								confirmText: t.$t('确定'),
								success: function() {
									t.closePayPasswordModal();
								}
							});
						} else {
							uni.showModal({
								title: t.$t('提示'),
								content: res.msg || t.$t('支付密码修改失败'),
								showCancel: false,
								confirmText: t.$t('确定')
							});
						}
					},
					fail: function(err) {
						uni.showModal({
							title: t.$t('提示'),
							content: t.$t('支付密码修改失败'),
							showCancel: false,
							confirmText: t.$t('确定')
						});
					}
				});
			},

		changePassword: function() {
			const t = this;

			// 验证输入
			if (!t.newPassword || t.newPassword.trim() === '') {
				uni.showModal({
					title: t.$t('提示'),
					content: t.$t('请输入新密码'),
					showCancel: false,
					confirmText: t.$t('确定')
				});
				return;
			}

			if (t.newPassword.length < 6) {
				console.log('密码验证失败: 密码长度不足6位, 当前长度:', t.newPassword.length);
				const errorMsg = t.$t('密码长度不能少于6位');
				console.log('准备显示toast:', errorMsg);
				uni.showModal({
					title:t.$t('提示'),
					content: errorMsg,
					showCancel: false,
					confirmText: t.$t('确定')
				});
				console.log('toast调用完成');
				return;
			}

			if (!t.confirmPassword || t.confirmPassword.trim() === '') {
				uni.showModal({
					title: t.$t('提示'),
					content: t.$t('请输入确认密码'),
					showCancel: false,
					confirmText: t.$t('确定')
				});
				return;
			}

			if (t.newPassword !== t.confirmPassword) {
				console.log('密码验证失败: 两次输入的密码不一致');
				const errorMsg = t.$t('两次输入的密码不一致');
				console.log('准备显示toast:', errorMsg);
				uni.showModal({
					title: t.$t('提示'),
					content: errorMsg,
					showCancel: false,
					confirmText:t.$t('确定') 
				});
				console.log('toast调用完成');
				return;
			}

			// 调用新的changepassword API (使用token认证,不需要传userid)
			t.req({
				url: "user/changepassword",
				data: {
					newpassword: t.newPassword
				},
				success: function(res) {
					if (res.code === 1) {
						uni.showModal({
							title: t.$t('提示'),
							content: t.$t('密码修改成功'),
							showCancel: false,
							confirmText: t.$t('确定'),
							success: function() {
								t.closePasswordModal();
							}
						});
					} else {
						uni.showModal({
							title: t.$t('提示'),
							content: res.msg || t.$t('密码修改失败'),
							showCancel: false,
							confirmText: t.$t('确定')
						});
					}
				},
				fail: function(err) {
					uni.showModal({
						title: t.$t('提示'),
						content: t.$t('密码修改失败'),
						showCancel: false,
						confirmText: t.$t('确定')
					});
				}
			});
		},

			logout: function() {
				const t = this;
				uni.showModal({
					title: this.$t('提示'),
					content: this.$t('确定要退出登录吗?'),
					confirmText: t.$t('确定'),
					cancelText: this.$t('取消'),
					success: function(res) {
						if (res.confirm) {
							// 清除所有用户相关缓存
							uni.removeStorageSync('token');
							uni.removeStorageSync('userid');
							uni.removeStorageSync('userdata');
							// 清除游客相关缓存
							uni.removeStorageSync('device_id');
							uni.removeStorageSync('guest_userid');
							uni.removeStorageSync('guest_token');
							uni.removeStorageSync('guest_virtualmoney');
							uni.removeStorageSync('is_guest');
							uni.reLaunch({
								url: '/pages/login/index'
							});
						}
					}
				});
			},

			async fetchUserData() {
				try {
					this.req({
						url: "user/index", 
						success: (res) => {
							if (res.code === 1) {
								const userData = res.data;
								
								if (userData.userdata) {
									this.userInfo.nickname = userData.userdata.nickname || userData.userdata.username;
									this.userInfo.avatar = userData.userdata.avatar || '/static/img/default-avatar.png';
									this.userInfo.userid = userData.userdata.userid || '';
									this.userInfo.level = userData.userdata.level || this.$t('VIP 1');
									this.userInfo.kefu = userData.kefu;
									this.userInfo.totalAssets = userData.userdata.money || '0.00';
									this.userInfo.demoBalance = userData.userdata.virtualmoney || '0.00';
								}
								
								if (userData.profit_data) {
									this.userInfo.totalProfit = userData.profit_data.profit_rate || '0.00';
								}
							}
						},
						fail: (err) => {
							console.error(this.$t('获取用户数据失败:'), err);
							uni.showToast({
								title: this.$t('获取用户数据失败'),
								icon: 'none'
							});
						}
					});
				} catch (error) {
					console.error(this.$t('获取用户数据失败:'), error);
					uni.showToast({
						title: this.$t('获取用户数据失败'),
						icon: 'none'
					});
				}
			}
		}
	}
</script>

<style scoped>
/* Page Layout */
.page {
  background-color: rgba(26, 32, 44, 1);
  position: relative;
  width: 100%;
  min-height: 100vh;
  overflow-x: hidden;
}

.flex-col {
  display: flex;
  flex-direction: column;
}

.flex-row {
  display: flex;
  flex-direction: row;
  align-items: center;
}

/* Status Bar */
.status-bar {
  background-color: rgba(27, 33, 38, 1);
  padding: 13px 22px 10px 12px;
  justify-content: space-between;
}

.time-text {
  color: rgba(255, 255, 255, 1);
  font-size: 14px;
  line-height: 17px;
  padding: 3px 17px 2px 16px;
}

.status-icon {
  width: 17px;
  height: 11px;
  margin-left: 5px;
}

.status-icon-battery {
  width: 24px;
  height: 11px;
  margin-left: 5px;
}

/* Page Title */
.page-title-wrapper {
  padding: 24px 0 22px 0;
  text-align: center;
}

.page-title {
  color: rgba(237, 242, 247, 1);
  font-size: 36rpx;
  font-weight: bold;
  line-height: 50rpx;
}

/* Profile Section */
.profile-section { 
  padding-left:15px;
  padding-right:15px;
  margin: 64rpx auto 0;
  justify-content: space-between;
}

.avatar {
  width: 160rpx;
  height: 160rpx;
  border-radius: 80rpx;
}

.profile-info {
  flex: 1;
  margin-left: 48rpx;
  justify-content: space-between;
}

.user-details {
  flex: 1;
}

.user-name {
  color: rgba(237, 242, 247, 1);
  font-size: 36rpx;
  font-weight: bold;
  line-height: 42rpx;
  margin-bottom: 16rpx;
}

.user-id {
  color: rgba(113, 128, 150, 1);
  font-size: 24rpx;
  line-height: 34rpx;
}

.edit-icon {
  width: 28rpx;
  height: 28rpx;
  margin-left: 20rpx;
}

/* Balance Cards */
.balance-cards-wrapper {
  width: 670rpx;
  margin: 40rpx auto 0;
}

.balance-cards {
  display: flex;
  gap: 20rpx;
}

.balance-card {
  flex: 1;
  background-color: #1C180E;
  border-radius: 12rpx;
  padding: 24rpx;
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 16rpx;
}

.balance-card-demo {
  background-color: #1C0F19;
}

.card-icon-wrapper {
  width: 64rpx;
  height: 64rpx;
  border-radius: 12rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.card-icon-img {
  width: 48rpx;
  height: 48rpx;
}

.card-info {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  flex: 1;
}

.card-label {
  color: rgba(146, 151, 158, 1);
  font-size: 24rpx;
  line-height: 34rpx;
  margin-bottom: 4rpx;
}

.card-value {
  color: rgba(255, 255, 255, 1);
  font-size: 32rpx;
  font-weight: bold;
  line-height: 42rpx;
  word-break: break-all;
}

/* Menu Section */
.menu-section {
  width: 670rpx;
  margin: 40rpx auto 0;
}

.menu-item {
  background-color: rgba(42, 52, 66, 1);
  border-radius: 12rpx;
  padding: 24rpx 32rpx;
  margin-bottom: 20rpx;
  justify-content: space-between;
  cursor: pointer;
}

.menu-item:active {
  opacity: 0.8;
}

.menu-item-left {
  flex: 1;
}

.menu-icon {
  width: 48rpx;
  height: 48rpx;
  margin-right: 24rpx;
}

.menu-text {
  color: rgba(237, 242, 247, 1);
  font-size: 28rpx;
  line-height: 40rpx;
}

.menu-item-right {
  align-items: center;
}

.language-text {
  color: rgba(113, 128, 150, 1);
  font-size: 24rpx;
  line-height: 34rpx;
  margin-right: 12rpx;
}

.arrow-icon {
  width: 20rpx;
  height: 20rpx;
}

/* Bottom Navigation */
.bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background-color: rgba(27, 33, 38, 1);
  padding-bottom: env(safe-area-inset-bottom);
}

.nav-items {
  padding: 20rpx 0;
  justify-content: space-around;
}

.nav-item {
  align-items: center;
  justify-content: center;
  flex: 1;
}

.nav-icon {
  width: 48rpx;
  height: 48rpx;
  margin-bottom: 8rpx;
}

.nav-text {
  color: rgba(113, 128, 150, 1);
  font-size: 20rpx;
  line-height: 28rpx;
}

.nav-item-active .nav-text,
.nav-text-active {
  color: rgba(237, 242, 247, 1);
}

.home-indicator {
  width: 268rpx;
  height: 10rpx;
  margin: 16rpx auto;
  display: block;
}

/* Language Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.6);
  z-index: 9999;
  display: flex;
  align-items: flex-end;
}

.modal-content {
  background-color: rgba(42, 52, 66, 1);
  border-radius: 24rpx 24rpx 0 0;
  width: 100%;
  max-height: 70vh;
  animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
  from {
    transform: translateY(100%);
  }
  to {
    transform: translateY(0);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 32rpx 32rpx 24rpx;
  border-bottom: 1rpx solid rgba(113, 128, 150, 0.2);
}

.modal-title {
  color: rgba(237, 242, 247, 1);
  font-size: 32rpx;
  font-weight: bold;
}

.modal-close {
  width: 40rpx;
  height: 40rpx;
  cursor: pointer;
}

.language-list {
  padding: 16rpx 0;
  max-height: 60vh;
  overflow-y: auto;
}

.language-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 28rpx 32rpx;
  cursor: pointer;
  transition: background-color 0.2s;
}

.language-item:active {
  background-color: rgba(113, 128, 150, 0.1);
}

.language-item-active {
  background-color: rgba(113, 128, 150, 0.15);
}

.language-name {
  color: rgba(237, 242, 247, 1);
  font-size: 28rpx;
}

.language-item-active .language-name {
  color: rgba(64, 223, 191, 1);
  font-weight: bold;
}

.check-icon {
  width: 36rpx;
  height: 36rpx;
}

/* Change Password Modal */
.modal-overlay-center {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.6);
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: center;
}

.password-modal-content {
  background-color: rgba(42, 52, 66, 1);
  border-radius: 24rpx;
  width: 600rpx;
  animation: zoomIn 0.3s ease-out;
}

@keyframes zoomIn {
  from {
    transform: scale(0.8);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}

.password-modal-header {
  padding: 40rpx 32rpx 24rpx;
  border-bottom: 1rpx solid rgba(113, 128, 150, 0.2);
  text-align: center;
}

.password-modal-title {
  color: rgba(237, 242, 247, 1);
  font-size: 32rpx;
  font-weight: bold;
}

.password-modal-body {
  padding: 40rpx 32rpx;
}

.password-input-group {
  margin-bottom: 32rpx;
}

.password-input-group:last-child {
  margin-bottom: 0;
}

.password-label {
  display: block;
  color: rgba(237, 242, 247, 1);
  font-size: 28rpx;
  margin-bottom: 16rpx;
}

.password-input {
  background-color: rgba(26, 32, 44, 1);
  border: 1rpx solid rgba(113, 128, 150, 0.3);
  border-radius: 12rpx;
  padding: 0rpx 20rpx;
  width: 100%;
  height:34px;
  color: rgba(237, 242, 247, 1);
  font-size: 28rpx;
  box-sizing: border-box;
}

.password-input:focus {
  border-color: rgba(64, 223, 191, 1);
}

.password-modal-footer {
  display: flex;
  gap: 20rpx;
  padding: 24rpx 32rpx 40rpx;
}

.password-btn {
  flex: 1;
  padding: 24rpx;
  border-radius: 12rpx;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
}

.password-btn:active {
  opacity: 0.8;
  transform: scale(0.98);
}

.password-btn-cancel {
  background-color: rgba(113, 128, 150, 0.2);
  border: 1rpx solid rgba(113, 128, 150, 0.3);
}

.password-btn-confirm {
  background-color: rgba(64, 223, 191, 1);
}

.password-btn-text {
  color: rgba(237, 242, 247, 1);
  font-size: 28rpx;
  font-weight: bold;
}

.password-btn-confirm .password-btn-text {
  color: rgba(255, 255, 255, 1);
}
</style>
