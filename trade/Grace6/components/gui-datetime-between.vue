<template>
	<view>
		<view @tap.stop="open">
			<slot></slot>
		</view>
		<view 
		class="gui-dateBT-shade gui-flex gui-column gui-justify-content-end" 
		:style="{
			zIndex:zIndex, 
			left:show ? '0rpx' : '-1000rpx'
		}">
			<view class="gui-bg-white gui-dark-bg">
				<view 
				class="graceDateTime-header gui-flex gui-row gui-space-between gui-bg-gray gui-dark-bg-level-2">
					<text 
					class="graceDateTime-header-btn gui-color-gray" 
					@tap="close">{{cancelText}}</text>
					<text 
					class="graceDateTime-header-btn gui-primary-color" 
					style="text-align:right;"
					@tap="confirm">{{confirmText}}</text>
				</view>
				<view>
					<text 
					class="graceDateTimeBT-text gui-block-text gui-color-gray">{{titles[0]}}</text>
				</view>
				<!-- 起始时间 -->
				<view 
				style="overflow:hidden;">
					<gui-datetime-bt-base 
					:value="startValue" 
					@change="change1" 
					:indicatorStyle="indicatorStyle" 
					:isDate="isDate" 
					:isDay="isDay" 
					:isTime="isTime" 
					:isSecond="isSecond" 
					:startYear="startYear" 
					:endYear="endYear" 
					:height="height" 
					:units="units"></gui-datetime-bt-base>
				</view>
				<!-- 结束时间 -->
				<view class="gui-margin-top">
					<text class="graceDateTimeBT-text gui-block gui-color-gray">{{titles[1]}}</text>
				</view>
				<view style="overflow:hidden;">
					<gui-datetime-bt-base 
					:value="endValue" 
					@change="change2" 
					:indicatorStyle="indicatorStyle"
					:isDate="isDate" 
					:isDay="isDay" 
					:isTime="isTime" 
					:isSecond="isSecond" 
					:startYear="startYear" 
					:endYear="endYear" 
					:height="height"
					:units="units"></gui-datetime-bt-base>
				</view>
			</view>
		</view>
	</view>
</template>
<script>
export default {
	name  : "gui-datetime-between",
	props : {
		cancelText    : { type : String,  default : '取消' },
		confirmText   : { type : String,  default : '确定' },
		startValue    : { type : String , default : ''},
		endValue      : { type : String , default : ''},
		startYear     : { type : Number,  default : 1980},
		endYear       : { type : Number,  default : 2050},
		units         : { type : Array ,  default : function(){
			return new Array('年','月','日','时','分','秒');
		}},
		titles        : { type : Array ,  default : function(){
			return new Array('开始时间','结束时间');
		}},
		zIndex        : { type : Number,  default : 90 },
		indicatorStyle: { type : String,  default : 'height:36px; line-heiht:36px;'},
		height        : { type : String,  default : '300rpx'},
		isDate    : { type : Boolean, default : true},
		isDay     : { type : Boolean, default : true},
		isTime    : { type : Boolean, default : true},
		isSecond  : { type : Boolean, default : true},
	},
	data() {
		return {
			defaultVal     : [0,0,0,0,0,0],
			sDate:[[],[],[],[],[],[]],
			recDate:[[],[]],
			show : false
		}
	},
	methods:{
		open : function () {
			this.show = true;
		},
		close : function () {
			this.show = false;
		},
		confirm : function(){
			this.$emit('confirm', this.recDate);
			this.close();
		},
		change1 : function(res){
			this.recDate[0] = res;
		},
		change2 : function(res){
			this.recDate[1] = res;
		},
	},
	emits : ['confirm']
}
</script>
<style scoped>
.gui-dateBT-shade{width:750rpx; position:fixed; z-index:99; left:0; top:0; bottom:0; flex:1; overflow:hidden; background-color:rgba(0,0,0,0.7);}
.graceDateTime-header{padding:25rpx;}
.graceDateTime-header-btn{width:200rpx; line-height:38rpx; height:38rpx; font-size:28rpx;}
.graceDateTimeBT-text{padding:25rpx; line-height:80rpx; height:80rpx; font-size:26rpx;}
</style>
