<template>
	<link rel="stylesheet" type="text/css" href="/static/css/chunk-6d6f5e2c.6bf8ee1e.css">
	<div class="bg">
		<div class="mineView"><img src="/static/img/pic15.d4552dc1.jpg" class="headimg">
			<div class="headView">
				<div class="infoBgView">
					<div class="flex alcenter between">
						<div class="flex alcenter" style="width: calc(100% - 40px); overflow: hidden;">
							<div>
								<div v-if="!userid" class="flex alcenter" @click="$gopage('/pages/login/index')"><!---->
									<div class="login_title">{{ $t('请登录') }}</div>
								</div>
								<div class="flex alcenter" style="margin-top: 4px;">
									<div class="nick_name">{{userdata.username}}</div>
									<div class="user-level" v-if="userdata.level">{{userdata.level}}</div>
								</div>
							</div>
						</div><img @click="redirectToLink" src="/static/img/customre.c4517b63.png" class="imgs"
							style="width: 35px; margin-left: 10px;">
					</div>
				</div>
				<div class="withdraw_BgView">
					<div class="withdrawNumberView" @click="mygotopage('/pages/wallet/index')">
						<div class="textb">
							<div>{{ $t('总资金') }}:<span style="font-size: 20px; color: red;">$ {{formattedNumber(userdata.money)}}</span>
							</div><!---->
							<div> {{ $t('信用分') }}:{{userdata.credit}} </div>
						</div>
					</div>
					<div class="depositView">
						<div class="textb" @click="mygotopage('/pages/recharge/depositDetail')">
							<div class="text">{{ $t('充值') }}</div>
						</div>
						<div class="textb" @click="mygotopage('/pages/recharge/withdrawal')">
							<div class="text">{{ $t('提现') }}</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="Statistics">
			<div class="left">
				<div class="list">
					<div class="line1"><img
							src="data:image/png;base64,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"
							alt="" class="imgs"><span class="text">{{ $t('总收益') }}</span></div>
					<div class="line2"><span class="text1">{{moneydata.allmoney?moneydata.allmoney:0}}</span><span
							class="text2">USDT</span></div>
				</div>

			</div>
			<div class="left">
				<div class="list">
					<div class="line1"><img
							src="data:image/png;base64,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"
							class="imgs"><span class="text">{{ $t('今日收益') }}</span></div>
					<div class="line2"><span class="text1">{{moneydata.todaymoney?moneydata.todaymoney:0}}</span><span
							class="text2">USDT</span></div>
				</div>
			    <div  class="list">
						<div  class="line1"><img 
								src="data:image/png;base64,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"
								alt="" class="van-icon__image"><!----> 
						<div  class="van-cell__value van-cell__value--alone"><span
								>{{ $t('修改支付密码') }}</span></div><i 
						class="van-icon van-icon-arrow van-cell__right-icon"><!----></i>
					</div>
					<div class="whiteblock"></div>
					<div role="button" tabindex="0" @click="outlogin()"
						class="sectionThreeCell van-cell van-cell--clickable"><i class="van-icon van-cell__left-icon"><img
								src="data:image/png;base64,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"
								alt="" class="van-icon__image"><!----></i></div>
			<div class="van-tabbar-item__text"></div>
		</div>
					<div class="van-tabbar-item van-tabbar-item--active" style="color: rgb(0, 0, 0);" @click="$gopage('/pages/mine/index')">
			<div class="van-tabbar-item__icon"><img
								src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAMAAABg3Am1AAAARVBMVEUAAAD////////////////////////////////////////////8/P7///////+BmO+CmPD///+BmO6Cme+CmO7///+Bl+3///////+Al+6ktfKWqPL///////+BmO6pufS/y/eNovDy9f7h5/vG0PeTp/HP1/mhs/KHnO/s8P3l6vy2w/Xzon3FAAAAHXRSTlMAehHkCL5IVhmT9/GJ2MmfKM8tIWtfN2+bIgAAAXxJREFUSMfdVcuSxCAIXN8x0WTy4v8/dTtuKhKtiofZyww3lAahAX++SaTzJqqBKhlUNH6XhbkO9Cy91zd7Q00xHOFxMAq7dBKnRWzZvayYYRDY+3si8ZyiACLnsRPNraKMRI6/CAGaIfylIGXbAlikfSmR6FWQIoS7l34hipeiiDp2101/pZ9uh0TqUsAvc/dSdIpicSU4vxTc6ezqsB9DMAcix9BQOSC7muDKpjQReMrnJYCTaK/CkGwCHJHhZDUBgjVNAKH/D3AoEesA106aeNI9S/r9snLiTAhjIq4JqFujDUDz9ckezdcA1O3dBnD5NIC+lcltQmw2F4kNUD2iq4h0SgxdMaL1EpC+3sH1EoDL5VwmKpkZH4Q3Q2LbnvwjYL3I7EGxcadX7eYDbfMiK1flAvvB8VSdAmLhq5IPjYZDtd6LuQIRj4i421kFUogN3mBfIJDJhgB8mNLw0iwUYPWHAtMoRhj4N74sIHzfsA9lbLk/fbsYji+SX9E9MQeSghzAAAAAElFTkSuQmCC"
					class="imgs"><!----></div>
			<div class="van-tabbar-item__text"></div>
					</div>
		</div>
	</div>
	<gui-action-sheet ref="guiactionsheet" canCloseByShade="true" @selected="selected" :title="$t('语言')"
		:cancelBtnName="$t('取消')" :items="actionSheetItems"></gui-action-sheet>
		 
	</div>
</template>

<script>
	export default {
		data: function() {
			return {
				showlanguagetxt: uni.getStorageSync('userlanguagetxt'),
				actionSheetItems: ['English','繁體中文','العربية','Polski','Deutsch','Français','한국어','Português','日本語','ภาษาไทย'
				,'Türkçe','Українська','Español','Italiano','हिंदी'],
				actionSheetItemsvalue: ['en', 'zhHant','alby','bly','dy','fy','hy','ptyy','ry','ty','trqy','wkly','xbyy','ydly','ydy'],
				userid: '',
				verify: [],
				userdata: {
					'username': '',
					'money': 0,
					'credit': 0
				},
				moneydata: [],
				getsalecount: 0,
				kefuurl: '',
				//actionSheetItems : ['عربي','English','日本語','한국인','Deutsch','हिंदी','Français','Italiano','Suomalainen','Português','Español','แบบไทย','Türkçe','українська','中文','繁体']
			}
		},
		onReady: function() {

		},
		onLoad: function() {
			// Get user ID from storage if available
			this.userid = uni.getStorageSync('userid');
			
			// If user data is in storage, use it temporarily while new data loads
			if (uni.getStorageSync('userdata')) {
				this.userdata = uni.getStorageSync('userdata');
			}
			
			// Load fresh data from the server
			this.loadData();
		},
		onShow: function() {
			if (this.showlanguagetxt == '' || !this.showlanguagetxt) {
				this.showlanguagetxt = this.actionSheetItemsvalue[0];
			}
			this.userid = uni.getStorageSync('userid');

			if (uni.getStorageSync('userdata')) {
				this.userdata = uni.getStorageSync('userdata');  
			}
			this.loadData();
		},
		mounted() {

		},
		methods: {
			formattedNumber: function(value) {
				return new Intl.NumberFormat('en-US', {
				        style: 'decimal',
				        minimumFractionDigits: 2, // 最小小数位数
				        maximumFractionDigits: 2  // 最大小数位数
				      }).format(value);
			},
			loadData: function() {
				var t = this;
				t.req({
					url: "user/index",
					data: {
						'userid': t.userid
					},
					Loading: !1,
					success: function(i) {
						if (t.userid) {
							// 保存用户ID和用户数据到本地存储
							uni.setStorageSync('userid', i.data.userid);
							uni.setStorageSync('userdata', i.data.userdata);
							
							// 更新用户数据
							t.userdata = i.data.userdata;
							
							// 确保用户名称存在
							if (!t.userdata.username && t.userdata.nickname) {
								t.userdata.username = t.userdata.nickname;
							}
							
							// 确保用户等级存在，默认为"普通用户"
							if (!t.userdata.level) {
								t.userdata.level = "普通用户";
							}
							
							// 更新收益数据
							t.moneydata = i.data.moneydata;
							
							// 处理可能不存在的数据
							if (!t.moneydata) {
								t.moneydata = {
									allmoney: 0,
									todaymoney: 0
								};
							}
							
							t.getsalecount = i.data.getsalecount;
							t.verify = i.data.getreturnuser_verify;
							
							console.log("用户数据加载成功:", t.userdata);
							console.log("收益数据加载成功:", t.moneydata);
						} else {
							console.log("未登录状态，未加载用户数据");
						}
						
						t.kefuurl = i.data.kefu;
					},
					fail: function(error) {
						console.error("加载用户数据失败:", error);
						uni.showToast({
							title: t.$t('加载失败，请重试'),
							icon: 'none'
						});
					}
				})
			},
			redirectToLink: function() {
				//console.log(this.kefuurl);
				if (this.kefuurl&&this.kefuurl!='')
					window.location.href = this.kefuurl;
			},
			open: function() {
				this.$refs.guiactionsheet.open();
			},
			selected: function(index) {
				console.log(this.actionSheetItemsvalue[index]);
				this.$i18n.locale = this.actionSheetItemsvalue[index]; // 切换中英文
				uni.setStorageSync('userlanguagetxt', this.actionSheetItems[index])
				uni.setStorageSync('userlanguage', this.actionSheetItemsvalue[index])
				uni.setStorageSync('userlanguageindex', index)
				this.showlanguagetxt = this.actionSheetItems[index];
				
				// 刷新页面以应用tabBar的多语言翻译
				setTimeout(() => {
					uni.reLaunch({
						url: '/pages/mine/index'
					});
				}, 300);
				// 返回索引，可以根据索引获取文本数据
			},
			mygotopage: function(url) {
				let ts = this;
				let userid = uni.getStorageSync('userid')
				if (!userid) {
					ts.$gopage("/pages/login/index");
				} else {
					ts.$gopage(url);
				}

			},
			outlogin: function() {
				uni.setStorageSync('userid', '');
				uni.setStorageSync('userdata', '');
				this.$gopage("/pages/login/index");
			},
		},
	}
</script>

<style>
.user-level {
	font-size: 12px;
	color: #ff9500;
	background-color: rgba(255, 149, 0, 0.1);
	padding: 2px 6px;
	border-radius: 10px;
	margin-left: 8px;
}
</style>