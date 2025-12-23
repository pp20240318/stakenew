<template>  
<link rel="stylesheet" type="text/css" href="/static/css/chunk-126d5f47.2501a65a.css"> 
				<div   class="home">
					<div  class="lanb" style="display: flex;
    justify-content: space-between;">
						<div >
							<span  class="text1"> {{ $t('今日收益') }} </span>
							<img  src="data:image/png;base64,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"
								class="img">
							<span  class="text2">{{money}} USDT</span>
						</div>
						<div style="float: right;"
							class="text2 gui-icons" @click="$gopage('/pages/home/alertlist')"> &#xe656;</div>
							</div>
					<div  class="con2_2">
						<view 
									class="gui-margin-top">
										<gui-swiper 
										:swiperItems="swiperItems" 
										indicatorType= "none"
										imgMode="scaleToFill" 
										:width="750" 
										:height="430"></gui-swiper>
									</view>
					 
						<div  class="butt"  @click="$gopage('/pages/robots/index')">{{ $t('开始交易') }}</div>
					</div>
					<div  class="con1">
						<div  class="con1swiper">
							<div  class="list bgcolor1">
								<div  class="text1 color1">137+</div>
								<div  class="text2">{{ $t('覆盖国家') }}</div>
							</div>
							<div  class="list bgcolor2">
								<div  class="text1 color2">{{getCurrentNumber()}}+</div>
								<div  class="text2">{{ $t('注册用户') }}</div>
							</div>
						</div>
						<div  class="con1swiper">
							<div  class="list bgcolor3">
								<div  class="text1 color3">{{money}}</div>
								<div  class="text2">{{ $t('已赚取收益') }}</div>
							</div>
							<!-- <div  class="list bgcolor4">
								<div  class="text1 color4">0</div>
								<div  class="text2">{{ $t('已发放佣金') }}</div>
							</div> -->
						</div>
					</div>
					<div  class="con3">
						<div  class="buttb">
							<span  class="butt"  @click="goSelect('1')">{{ $t('充值') }}</span>
							<span class="butt" @click="goSelect('2')">{{ $t('提现') }}</span>
							<!-- <span  class="butt" @click="goSelect('2')">{{ $t('团队') }}</span> -->
						</div>
					</div>
					<div  class="con6">
						<div  class="commontitle">
							<span  class="text2">{{ $t('什么是NFT') }}</span>
						</div>
						<div  class="textb">
							<div  class="text2">{{ $t('NFT（不可替代代币）是存储在区块链上的独特数字项目。NFT几乎可以代表任何东西，并作为数字记录所有权。') }}</div>
						</div>
					</div>
					<div  class="con6">
						<div  class="commontitle">
							<span  class="text2">{{ $t('关于我们') }}</span>
						</div>
						<div  class="textb">
							<div  class="text2">NFTEsame {{ $t('智能拍卖公司自2024年成立以来，便在美国加利福尼亚州旧金山这个金融科技的腹地发展壮大，迅速成为拍卖交易行业的佼佼者。') }}
							</div>
							<div  class="text2">{{ $t('公司凭借雄厚的资金背景和AI技术的深度挖掘，专注于探索区块链市场的未来趋势，并致力于利用先进的AI系统，为客户提供高效且精准的NFT的拍卖交易服务。') }}</div>
						</div><img  src="/static/img/pic34.8a98ead7.jpg" class="img1">
					</div>
					<div  class="con4">
						<div  class="title">{{ $t('探索NFT的世界') }}</div>
						<div  class="line1">
							<div  class="textb"><img 
									src="/static/img/pic32.592cfd1e.png" class="img1"><span 
									class="text1">{{ $t('可靠的安全保障') }}</span></div>
							<div  class="text2">{{ $t('我们的复杂安全措施和SAFU基金可保护您的数字资产免受所有风险.') }}</div>
						</div>
						<div  class="line1">
							<div  class="textb"><img 
									src="/static/img/pic33.bdd58247.png" class="img1"><span 
									class="text1">{{ $t('24*7小时客户服务支持') }}</span></div>
							<div  class="text2">{{ $t('24*7小时全天候运营模式,我们将尽快回答您的咨询.') }}</div>
						</div>
					</div>
					<div  class="con5"><img  src="/static/img/bitpay.bccc2689.png"
							class="img1"><img  src="/static/img/cybavo.d48f6b26.png" class="img1"><img
							 src="/static/img/gnosisSafe.2836c455.png" class="img1"><img
							 src="/static/img/keyring pro.a9cb78ca.png" class="img1"><img
							 src="/static/img/ledger.5d7dc63c.png" class="img1"><img 
							src="/static/img/mathwallet.e46d63f7.png" class="img1"><img 
							src="/static/img/metamask.88ff278d.png" class="img1"><img 
							src="/static/img/SafePal.c73007bf.png" class="img1"><img 
							src="/static/img/SparkSwap.d13a609c.png" class="img1"><img 
							src="/static/img/walletconnect.06f8ec65.png" class="img1"><img 
							src="/static/img/zelcore.bc33b023.png" class="img1"><img 
							src="/static/img/OpenSea.b15de72e.png" class="img1"></div><!----><!---->
					<div  style="height: 60px;"></div>
				</div><!----><!---->
				<div 
					class="tabbar van-hairline--top-bottom van-tabbar van-tabbar--fixed temp3menuac">
					<div  class="van-tabbar-item van-tabbar-item--active"  @click="$gopage('/pages/market/market')"
						style="color: rgb(0, 0, 0);">
						<div class="van-tabbar-item__icon"><img 
								src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAMAAABg3Am1AAAAqFBMVEUAAAD////////l5f////////////////+BmO6BmO7///////////////////////////////////////////////////////////+DmfD///////////+EmOz///+BmO6CmO7///////+BmO6CmO7///+Dme+Dl+7///////+AlfGpufTP2PmCl+////////+BmO7Ay/a7x/bf5fvl6fzN1viYq/GTpvC3PS7XAAAAL3RSTlMAgN8EkBC7CPjz6c6knot8c2JSSxkM9/PDfHdtPC4U07OtQ9+PhmlMLiQS98+7ZKKXXkYAAAGHSURBVEjH3VXneoJAEAyICjaaoliw15QBbO//ZrkVYuC4HPyN84e93ZltHxxvL4u6MzadenW6NgSDp/Wr8d0WUrScCvSaRtT2ttemp9Ys4wcNyuyS6ajMbARy/pFIy3o6y5Id1KOMv6Jpzd+zyY5D92/+pBB3PeayZOOqh7zzQD32aiL+gMYdFVbfH9HoTQGfAktBoNmhRIOCm/Jvxb0qVIPvasycE9kyTK5ACxjzvNx6W/kSU0DNe2YL3VjMfhaoAtNc2AY6uRX7esig+2kWNvgXP0Ivczx9hikWp4ejB1j8IpRMO+vwifWMjxcFeyPMQN+XCfyQgy8XbMICNjLBLhRgJxHMRYK5RKCLBIZEwML3OHogvj0VUkGMJ+IqghsyuJcLqMAlaelCJcoFERAlLLL+kcACNJmg8D3YQFcm6AB2/lIF2olliARGm/+m6wCc5OUTCd4Bj/uDdcFqdDRF+TgD5+iB1LpeL6CO8whUSKEWL92uJ+F3A9H1PbUtRYCJvRq8vRS+ATrNhEY5kjuBAAAAAElFTkSuQmCC"
								class="imgs"><!----></div>
						<div class="van-tabbar-item__text"></div>
					</div>
					<div  class="van-tabbar-item" @click="$gopage('/pages/main/bot')">
						<div class="van-tabbar-item__icon"><img 
								src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAMAAABg3Am1AAAASFBMVEUAAACrsMKqsMKpr8Cprr+rr8KqsMOrsMOrsMKgoL+qsMKorcGqq7+lsb+pr8CrsMOqr8Kqr8SosMGqsMKqsMOqsMKqr8Oqr8K87zdVAAAAF3RSTlMA9N0wI7CEYZIInjgZEVa70UhA6KfGdju0fwcAAAICSURBVEjH1VXbloMgDDQIeAGl1rb5/z/dBJYGRHf3nH3qPLQCCUwmIXSfgGmzQQEiKGO36VdzNwMWgLn/0bwP2MBcu0wjMtS49kvXLf06KmSMF8R0XK45uAfwFvrM/s5LRnc6r2offw2Hcj+3h6HTN8RbdLGINq4McOahaVYRG7InD5rw/KGTFIo89CFelZhqjMhfXqJTdeSkD/SdOAildAaQVpX+ZDTQv1BiUgWLgWZL+QyiiR856KXnVNQWoTpAgtJ6sSom0HoxYa7uPZoRH8XxuZpY5tJmfksEpbfl/A3ODZwxW7HIQm2ISvZP+cj6D1RhOxgiTIMt71lo5gGVfw8Ugg+RnGblrSiwFoT6So2d4rNA/FfRSRHp0eVvc1CTJ0ZKTS/EoyrpkCUnUCJ6TizRk5fge5Y133GfEgdX3Yc0nlFNnAlxWLZUQFvjwCExfwBxAHLwidPQUlq+04ZCSfE2O9q0UAedA73bWYIOvHuIoj2xllW0Z1qmSBz/6Fj3x8QVV8aWpaEB4YYIr2NpJFSlMQHzWHl3WK+Kz+Xik9L1r/GlL8v7EW3EG7Ws+XyBlrKrVGqEg5qnV9Q0TaBB0wTaNtNC2sxpI2shjey0VTaQVnnRjBtIM75q9yWk3V8xRXi4is0cM6L/+2TJhgYbhF/e0eOz6/7ysJv0sAd+2D8AX6Y0L4pH4jdwAAAAAElFTkSuQmCC"
								class="imgs"><!----></div>
						<div class="van-tabbar-item__text"></div>
					</div>
					<div  class="van-tabbar-item" @click="$gopage('/pages/index/index')">
						<div class="van-tabbar-item__icon"><img 
								src="data:image/png;base64,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"
								class="imgs"><!----></div>
						<div class="van-tabbar-item__text"></div>
					</div>
					<div  class="van-tabbar-item" @click="$gopage('/pages/mine/index')" >
						<div class="van-tabbar-item__icon"><img 
								src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAMAAABg3Am1AAAARVBMVEUAAACqsMOksL+qsMKfn7+qsMOpr8Gpr8GorL+qsMKqsMKqr8KrsMOrsMKqsMKqsMKqrL+qr8Kpr8Gorb+rsMKrsMOqr8I0I00EAAAAFnRSTlMAehHkCL5IVhmT9/GJ2MmfKM8tIWtfN2+bIgAAAXxJREFUSMfdVcuSxCAIXN8x0WTy4v8/dTtuKhKtiofZyww3lAahAX++SaTzJqqBKhlUNH6XhbkO9Cy91zd7Q00xHOFxMAq7dBKnRWzZvayYYRDY+3si8ZyiACLnsRPNraKMRI6/CAGaIfylIGXbAlikfSmR6FWQIoS7l34hipeiiDp2101/pZ9uh0TqUsAvc/dSdIpicSU4vxTc6ezqsB9DMAcix9BQOSC7muDKpjQReMrnJYCTaK/CkGwCHJHhZDUBgjVNAKH/D3AoEesA106aeNI9S/r9snLiTAhjIq4JqFujDUDz9ckezdcA1O3dBnD5NIC+lcltQmw2F4kNUD2iq4h0SgxdMaL1EpC+3sH1EoDL5VwmKpkZH4Q3Q2LbnvwjYL3I7EGxcadX7eYDbfMiK1flAvvB8VSdAmLhq5IPjYZDtd6LuQIRj4i421kFUogN3mBfIJDJhgB8mNLw0iwUYPWHAtMoRhj4N74sIHzfsA9lbLk/fbsYji+SX9E9MQeSgMhzAAAAAElFTkSuQmCC"
								class="imgs"><!----></div>
						<div class="van-tabbar-item__text"></div>
					</div>
				</div> 
		 <gui-modal 
		 			:customClass="['gui-bg-white', 'gui-dark-bg-level-3', 'gui-border-radius']" 
		 			ref="guimodal1" :isCloseBtn="false"
		 			:title="alerttitle">
		 				<template v-slot:content>
							<scroll-view 
											:show-scrollbar="false" 
											:scroll-y="true"
											style="width:100%; max-height:400px;" 
											 >
								<view class="gui-padding gui-bg-gray gui-dark-bg-level-2">
									<text 
									class="gui-block gui-text-center gui-text gui-color-gray" 
									style="line-height:100rpx; padding:10rpx;">{{alertcontext}}</text>
								</view>
							</scroll-view>
		 				</template>
		 				
		 				<!-- 利用 flex 布局 可以放置多个自定义按钮哦  -->
		 				<template v-slot:btns>
		 					<view 
		 					class="gui-flex gui-row gui-space-between">
							     <view
							     hover-class="gui-tap" 
							     class="modal-btns gui-flex1" 
							     style="margin-right:80rpx;">
							     	<text 
							     	class="modal-btns gui-color-gray" 
							     	@tap="history">{{ $t('历史记录') }}</text>
							     </view>
		 						<view 
		 						hover-class="gui-tap" 
		 						class="modal-btns gui-flex1"  >
		 							<text 
		 							class="modal-btns gui-color-gray" 
		 							@tap="close1">{{$t("确定")}}</text>
		 						</view> 
		 					</view>
		 				</template>
		 			</gui-modal>
</template>

<script>
	export default {
		data() { 
			return {
				swiperItems:[],
				money:0,
				todayProfit: 0, // 今日收益
				initialNumber : 4000000, // 初始数字
				dailyIncrement : 233, // 每天增加的数字
				content_alter:[],
				alerttitle:'',
				alertcontext:'',
				alertindex:'0',
			}
		},
		onLoad: function () { 
			this.loadData();
			this.calculateTodayProfit(); // 计算今日收益
		},
		methods: {
			close1 : function () {
				var t = this;
				t.$refs.guimodal1.close();
				setTimeout(() => {
				    t.alertindex+=1;
				    if(t.content_alter.length>=t.alertindex+1){
				    	t.alertcontext=t.content_alter[t.alertindex].content_text;
				    	t.alerttitle=t.content_alter[t.alertindex].title;
				    	t.$refs.guimodal1.open();
				    }
				            }, 1000); 
				
				
			},
			history:function(){
				let ts = this; 
				ts.$gopage("/pages/home/alertlist");   
			},
			getCurrentNumber :function(){
				const initialDate = new Date('2024-01-01').getTime(); // 设置初始日期
				const currentDate = new Date().getTime(); // 当前时间戳
				const millisecondsInADay = 1000 * 60 * 60 * 24; // 一天的毫秒数
				
				      // 计算从初始日期到当前日期的天数
				const daysPassed = Math.floor((currentDate - initialDate) / millisecondsInADay);
				return this.initialNumber + daysPassed * this.dailyIncrement;
			},
			goSelect:function(type){
				let userid=uni.getStorageSync('userid') 
				let ts = this; 
				if(!userid){  
				  
				  ts.$gopage("/pages/login/index");   
				}	
			    if(type=='1') ts.$gopage("/pages/recharge/depositDetail");   
				else ts.$gopage("/pages/recharge/withdrawal");    
			},
			loadData:function(){
				var t = this;
				const userid=uni.getStorageSync('userid')  
				t.req({
					url: "index",
					data: {'userid':userid},
					Loading: !0,
					success: function (i) { 
						// 计算今日收益为当日卖出减去买入总额
						const todayStart = new Date();
						todayStart.setHours(0, 0, 0, 0);
						const todayTimestamp = Math.floor(todayStart.getTime() / 1000);
						
						// 如果后端已经提供了今日交易数据，就直接使用
						if (i.data.moneydata && i.data.moneydata.todaymoney !== undefined) {
							t.money = i.data.moneydata.todaymoney;
						} else if (userid) {
							// 否则，前端计算今日收益（卖出-买入）
							t.req({
								url: "user/transactions",
								data: {
									'userid': userid,
									'types': 'Buy,Sell'
								},
								success: function(res) {
									if (res.code == 1 && res.data.transactions) {
										let todayBuy = 0;
										let todaySell = 0;
										
										res.data.transactions.forEach(transaction => {
											// 检查是否是今天的交易
											if (transaction.create_time >= todayTimestamp) {
												if (transaction.business_type.includes('Buy')) {
													// 买入是负数，需要取绝对值
													todayBuy += Math.abs(parseFloat(transaction.money || 0));
												} else if (transaction.business_type.includes('Sell')) {
													// 卖出是正数
													todaySell += parseFloat(transaction.money || 0);
												}
											}
										});
										
										// 计算今日收益 = 卖出 - 买入
										t.money = (todaySell - todayBuy).toFixed(2);
									}
								}
							});
						} else {
							t.money = i.data.money || 0;
						}
						
						var advertlist=i.data.advert;
						for(var j=0;j<advertlist.length;j++){
							advertlist[j].img=t.H5BaseUrl+advertlist[j].img;
						}
						
						t.swiperItems=advertlist; 
						t.content_alter=i.data.content_alter; 
						if(t.content_alter.length>0){
							t.alerttitle=t.content_alter[0].title;
							t.alertcontext=t.content_alter[0].content_text;
							t.alertindex=0; 
							t.$refs.guimodal1.open();
						}
					}
				})
			},
			// 计算今日收益
			calculateTodayProfit() {
				const userid = uni.getStorageSync('userid');
				if (!userid) return;
				
				// 获取今天的日期，格式化为YYYY-MM-DD
				const today = new Date();
				const year = today.getFullYear();
				const month = String(today.getMonth() + 1).padStart(2, '0');
				const day = String(today.getDate()).padStart(2, '0');
				const dateStr = `${year}-${month}-${day}`;
				
				// 请求今日交易记录
				this.req({
					url: "nft/tradelist",
					method: "POST",
					data: {
						'userid': userid,
						'date': dateStr
					},
					Loading: !1,
					success: (res) => {
						if (res.code == 1 && res.data) {
							// 计算今日卖出和买入总额
							let sellTotal = 0;
							let buyTotal = 0;
							
							res.data.forEach(item => {
								if (item.type == 1) { // 假设type=1为买入
									buyTotal += parseFloat(item.amount || 0);
								} else if (item.type == 2) { // 假设type=2为卖出
									sellTotal += parseFloat(item.amount || 0);
								}
							});
							
							// 今日收益 = 卖出总额 - 买入总额
							this.todayProfit = (sellTotal - buyTotal).toFixed(2);
							if (this.todayProfit > 0) {
								// 如果后端已提供今日收益，则使用它
								this.money = this.todayProfit;
							}
						}
					}
				});
			},
		}
	}
</script>

<style>
	.icon {
		width: 1em;
		height: 1em;
		vertical-align: -0.15em;
		fill: currentColor;
		overflow: hidden;
	}
	.modal-btns{line-height:88rpx; font-size:26rpx; text-align:center; width:100%;}
</style>