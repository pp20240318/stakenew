<template>
	<link rel="stylesheet" type="text/css" href="/static/css/loaninfo.css">
	<div   class="loan">
		<div  class="headB"><i  @click="$goBack()"
				class="img1 van-icon van-icon-arrow-left"><!----></i><span 
				class="text1">{{ $t('贷款') }}</span><span  class="text2"></span></div>
		<div  class="con">
			<div  class="title">{{ $t('预估贷款额度') }}</div>
			<div  class="line1"><span > {{gethaveloanmoney}} </span><span 
					class="text1">USD</span></div>
			<div  class="line2" v-if="getishaveverify!=1">{{ $t('验证您的身份并获得更多服务') }}</div>
			<div  class="line2" v-if="getishaveverify==1">{{ $t('贷款额度与客户信用分和资产存在关系') }}</div>
			<div  class="line3" v-if="getishaveverify!=1"  @click="$gopage('/pages/mine/truename')">{{ $t('开始验证') }}</div>
			<div  class="line4">
				<div  class="text1">{{ $t('金额') }}</div>
				<div  class="text2">
					<input  type="number" v-model="money" :placeholder=" $t('请输入金额') "  @input="moneychange()" class="text2-1">
					<span  class="text2-2" @click="maxclick">Max</span>
				</div>
			</div>
			<div  class="line5">
				<div  class="text1">{{ $t('贷款期限(天)') }}</div>
				<div  class="text2">
					<picker mode="selector" :range="getloanpick" :range-key="'title'" @change="pickerChange">
						<view  class="gui-flex gui-row gui-nowrap gui-space-between gui-align-items-center">
										<text class="gui-text gui-color-white gui-secondary-text">{{getloanpick[loanindex]?.title || $t('贷款期限(天)')}}</text>
										<text class="gui-form-icon gui-icons gui-text-center gui-color-white">&#xe603;</text>
						</view>
					</picker>
			    </div><!---->
			</div>
			<div  class="line6">
				<div  class="text1">
					<div  class="text1-1">{{ $t('基础')+" " }}{{ $t('日利率') }}</div>
					<div  class="text1-2">{{daypercent}}%</div>
				</div>
				<div  class="text1">
					<div  class="text1-1">{{ $t('总利息金额') }}</div>
					<div  class="text1-2">${{moneyp}}</div>
				</div>
			</div>
			<div  class="line7">{{ $t('申请贷款后与客服联系审批额度')+" " }}{{ $t('贷款后当天内无需支付利息，之后需支付利息') }}</div>
			<div v-if="getishaveverify==1" class="butt" @click="$refs.guimodal1.open()">{{ $t('现在借款') }}</div>
		</div>
		<div  class="con2">
			<div  class="title">{{ $t('借款记录') }}</div>
			<div  class="listb2" v-if="getthisloans.length==0">
				<div  class="text1">{{ $t('尚未借款') }}</div>
				<div  class="text2">{{ $t('找不到您的贷款信息') }}</div>
			</div>
			<view class="gui-list " >
				 
				<view class="gui-list-items gui-color-white"  v-for="(item, index) in getthisloans ">
					<view class="gui-list-body gui-border-b">
						<view class="gui-list-title">
							<text class="gui-list-title-text ">{{$t('贷款')}}:${{item.loan_amount}}</text>
							<text class="gui-list-title-desc ">{{$formatDate(item.create_time)}}</text>
						</view>
						<view class="gui-list-body-desc  gui-flex gui-row gui-space-between"> 
							<text style="width:50%;font-size: 0.6875rem;line-height: 0.9375rem;">
							{{ $t('总利息金额') }}:${{item.loan_interest}}
							</text> 
							 <view class=" gui-relative" style="width:50%;font-size: 0.6875rem;line-height: 0.9375rem;float:right;">
								<gui-tags 
								:text="item.status==0?$t('正在审核'):(item.status==1?$t('成功'):$t('失败'))" 
								:customClass="['abt-view gui-absolute-rt',
									item.status==0?'gui-bg-blue':(item.status==1?'gui-bg-green':'gui-bg-red'),
									'gui-color-white']" 
								:lineHeight="1.8" 
								:width="200" 
								:size="18"></gui-tags> 
							</view>
						</view> 
					</view>
				</view>
			</view>
		</div><!----><!----><!---->
	</div>
	<gui-modal 
				:customClass="['gui-bg-white', 'gui-dark-bg-level-3', 'gui-border-radius']" 
				ref="guimodal1" 
				:title="$t('提示')">
					<template v-slot:content>
						<view class="gui-padding gui-bg-gray gui-dark-bg-level-2">
							<text 
							class="gui-block gui-text-center gui-text gui-color-gray" 
							style="line-height:100rpx; padding:10rpx;">{{$t('确定')}}{{$t('贷款')}}:{{money}}？</text>
						</view>
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
								@tap="close1">{{$t('取消')}}</text>
							</view>
							<view 
							hover-class="gui-tap" 
							class="modal-btns gui-flex1" 
							style="margin-left:80rpx;">
								<text 
								class="modal-btns gui-primary-color" 
								@tap="confirm1">{{$t('确定')}}</text>
							</view>
						</view>
					</template>
				</gui-modal>
</template>

<script>
	export default {
		data: function() {
			return { 
				daypercent: '',
				getthisloans:[],
				getloanpick:[],
				loanindex:0,
				getishaveverify:0,
				money:'',
				moneyp:0,
				gethaveloanmoney:0,
				//actionSheetItems : ['عربي','English','日本語','한국인','Deutsch','हिंदी','Français','Italiano','Suomalainen','Português','Español','แบบไทย','Türkçe','українська','中文','繁体']
			}
		},
		onReady: function() {
	
		},
		onLoad: function() {
	
		},
		onShow: function() {
			 
			this.userid = uni.getStorageSync('userid');
	
			if (!this.userid) {
				this.$gopage("/pages/login/index");
			}
			this.loadData();
		},
		mounted() {
	
		},
		methods: {
			// picker 切换
			pickerChange : function (e) { 
				var t = this; 
				t.loanindex    = e.detail.value; 
				var thisselectdata=t.getloanpick[t.loanindex];
				if(t.money&&t.money>0&&t.money!='')
				  t.moneyp=(thisselectdata.type*(t.money*(t.daypercent/100))).toFixed(4);
			},
			maxclick:function(){
				var t = this;
				var thisselectdata=t.getloanpick[t.loanindex];
				t.money=thisselectdata.max;
				if(t.money&&t.money>0&&t.money!='')
				  t.moneyp=(thisselectdata.type*(t.money*(t.daypercent/100))).toFixed(4);
			},
			moneychange:function(){ 
				var t = this;
				var thisselectdata=t.getloanpick[t.loanindex];
				t.moneyp=(thisselectdata.type*(t.money*(t.daypercent/100))).toFixed(4);
			},
			loadData: function() {
				var t = this;
				t.req({
					url: "user/loanlist",
					data: {
						'userid': t.userid
					},
					Loading: !1,
					success: function(i) {
						t.getishaveverify=i.data.getishaveverify;
						t.daypercent=i.data.daypercent;
						t.getthisloans=i.data.getthisloans;
						t.getloanpick=i.data.getloanpick;
						t.gethaveloanmoney=i.data.gethaveloanmoney;
						for(var j=0;j<t.getloanpick.length;j++){
							var thisdata=t.getloanpick[j].title;
							t.getloanpick[j].title=thisdata.replace('天', t.$t('天'));
						}
						
						// if (t.userid) {
						// 	uni.setStorageSync('userid', i.data.userid);
						// 	uni.setStorageSync('userdata', i.data.userdata);
						// 	this.userdata = uni.getStorageSync('userdata');
						// 	t.moneydata = i.data.moneydata;
						// 	t.getsalecount = i.data.getsalecount;
						// }
						// t.kefuurl = i.data.kefu;
					}
				})
			},
			close1 : function () {
				this.$refs.guimodal1.close();
			},
			confirm1 : function () {
				this.borrownow();
				
			},
			borrownow: function() {
				var t = this;
				if(t.isclickflag){
					return t.$toast(t.$t('请不要重复提交'));
				}
				
				t.isclickflag=true; 
				if(t.getishaveverify==0){
					t.isclickflag = false; 
					return t.$toast(t.$t('请先验证身份信息'));
				}
				if(!t.money||t.money==0||t.money==''){
					t.isclickflag = false; 
					return t.$toast(t.$t('请输入金额'));
				}
				var thisselectdata=t.getloanpick[t.loanindex];
				if(t.money<thisselectdata.min){
					t.isclickflag = false; 
					return t.$toast(t.$t('低于')+t.$t('最小金额'));
				}
				if(t.money>thisselectdata.max){
					t.isclickflag = false; 
					return t.$toast(t.$t('高于')+t.$t('最大金额'));
				}
				if(t.money>t.gethaveloanmoney){
					t.isclickflag = false; 
					return t.$toast(t.$t('高于')+t.$t('预估贷款额度'));
				}
				t.req({
					url: "user/borrownow",
					data: {
						'userid': t.userid,
						'money':t.money,
						'day':thisselectdata.type,
						'daypercent':t.daypercent,
						'moneyp':t.moneyp
					},
					Loading: !1,
					success: function(i) { 
						window.location.reload();
						t.getthisloans=i.data.getthisloans; 
						t.$refs.guimodal1.close();
						setTimeout(() => {
						                t.isclickflag = false; 
						            }, 3000); 
					}
				})
			},
			 
		},
	}
</script>

<style>
	@import "@/Grace6/css/colors.css";
	.modal-btns{line-height:88rpx; font-size:26rpx; text-align:center; width:200rpx;}
</style>