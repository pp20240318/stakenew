<template>
	<picker-view 
	:style="{height:height}"
	:indicator-style="indicatorStyle" 
	class="graceDateTime-main" 
	:value="defaultValIn" 
	@change="change">
		<picker-view-column class="gui-flex1" v-if="isDate">
			<view class="gui-picker-item" 
			v-for="(item, index) in sDate[0]"
			:key="index">
				<text 
				class="gui-picker-item gui-block" 
				:style="indicatorStyle">{{item}}{{units[0]}}</text>
			</view>
		</picker-view-column>
		<picker-view-column v-if="isDate">
			<view
			class="gui-picker-item" 
			v-for="(item, index) in sDate[1]"
			:key="index">
				<text 
				class="gui-picker-item gui-block" 
				:style="indicatorStyle">{{item}}{{units[1]}}</text>
			</view>
		</picker-view-column>
		<picker-view-column v-if="isDate && isDay">
			<view class="gui-picker-item" 
			v-for="(item, index) in sDate[2]" 
			:key="index">
				<text 
				class="gui-picker-item gui-block" 
				:style="indicatorStyle">{{item}}{{units[2]}}</text>
			</view>
		</picker-view-column>
		<picker-view-column v-if="isTime">
			<view class="gui-picker-item" 
			v-for="(item, index) in sDate[3]" 
			:key="index">
				<text class="gui-picker-item gui-block" 
				:style="indicatorStyle">{{item}}{{units[3]}}</text>
			</view>
		</picker-view-column>
		<picker-view-column v-if="isTime">
			<view class="gui-picker-item" 
			v-for="(item, index) in sDate[4]" 
			:key="index">
				<text 
				class="gui-picker-item gui-block" 
				:style="indicatorStyle">{{item}}{{units[4]}}</text>
			</view>
		</picker-view-column>
		<picker-view-column v-if="isTime && isSecond">
			<view
			class="gui-picker-item" 
			v-for="(item, index) in sDate[5]" 
			:key="index">
				<text 
				class="gui-picker-item gui-block" 
				:style="indicatorStyle">{{item}}{{units[5]}}</text>
			</view>
		</picker-view-column>
	</picker-view>
</template>
<script>
export default {
	name  : "gui-datetime-bt-base",
	props : {
		value     : { type : String , default:''},
		isTime    : { type : Boolean, default : true},
		isSecond  : { type : Boolean, default : true},
		isDate    : { type : Boolean, default : true},
		isDay     : { type : Boolean, default : true },
		startYear : { type : Number, default : 1980},
		endYear   : { type : Number, default : 2050},
		units     : { type : Array , default:function(){
			return new Array('年','月','日','时','分','秒');
		}},
		height    : { type : String, default : '300rpx' },
		indicatorStyle : { type : String,  default : 'height:36px; line-heiht:36px;'}
	},
	data() {
		return {
			defaultVal     : [0,0,0,0,0,0],
			defaultValIn   : [0,0,0,0,0,0],
			sDate:[[],[],[],[],[],[]],
			nowStr : "",
		}
	},
	created() {
		this.nowStr = this.now();
		this.init();
	},
	methods: {
		now : function () {
			var date = new Date();
			var y = date.getFullYear();
			var m = date.getMonth() + 1;
			m = m < 10 ? ('0' + m) : m;
			var d = date.getDate();
			d = d < 10 ? ('0' + d) : d;
			var h = date.getHours();
			h = h < 10 ? ('0' + h) : h;
			var minute = date.getMinutes();
			var second = date.getSeconds();
			minute = minute < 10 ? ('0' + minute) : minute;
			second = second < 10 ? ('0' + second) : second;
			return y + '-' + m + '-' + d + ' '+ h +':' + minute + ':' + second;
		},
		arrayIndexOf : function(arr, needFind){
			var index = -1; 
			for(let i = 0; i < arr.length; i++){if(arr[i] == needFind){index = i; return i;}}
			return index;
		},
		setValue : function (val) {
			if(val == ''){
				val = this.now();
			}else if(val != this.nowStr){
				// 没有日期
				if(!this.isDate){
					val = this.nowStr.substring(0, 11)+val;
				}else{
					// 有日期没有day
					if(!this.isDay){
						val = val.substring(0,7) + "-01"+val.substring(7, val.length)
					}
				}
				// 没有秒
				// 没有时间
				if(!this.isTime){
					val = val + " 00:00:00";
				}else{
					if(!this.isSecond){
						val = val + ":00"
					}
				}
			}
			var reg = /^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/;
			var res = val.match(reg);
			if(res == null){
				this.setValue(this.nowStr);
				return ;
			}else{
				this.setDefaults([res[1],res[2],res[3],res[4],res[5],res[6]]);
			}
		},
		setDefaults : function (res) {
			var dIn = [];
			for(let i = 0; i < res.length; i++){
				var index = this.arrayIndexOf(this.sDate[i], res[i]);
				if(index == -1){index = 0;}
				dIn.push(index);
			}
			// 条件
			var ddIn= [];
			if(!this.isDate){
				//没有秒
				if(!this.isSecond){
					ddIn=[dIn[3], dIn[4]];
				}else{
					ddIn=[dIn[3], dIn[4], dIn[4]];
				}
			}else{
				// 有天
				if(this.isDay){
					ddIn=[dIn[0], dIn[1], dIn[2], dIn[3], dIn[4]];
					// 有秒
					if(this.isSecond){
						dIn[5] = res[5];
						ddIn.push(res[5]);
					}
				}else{
					ddIn=[dIn[0], dIn[1], dIn[3], dIn[4]];
					// 有秒
					if(this.isSecond){
						ddIn.push(dIn[5]);
					}
				}
			}
			this.changeBase(ddIn);
		},
		// 初始化组件
		init:function(){
			if(this.endYear < this.startYear){
				this.endYear = this.startYear + 10;
			}
			var years     = new Array();
			for(let i = this.startYear; i <= this.endYear; i++){
				years.push(i);
			}
			var months     = new Array();
			for(let i = 1; i <= 12; i++){
				if(i < 10){
					months.push('0'+i);
				}else{
					months.push(i);
				}
			}
			var days     = new Array();
			for(let i = 1; i <= 31; i++){
				if(i < 10){
					days.push('0'+i);
				}else{
					days.push(i);
				}
			}
			var hours     =  new Array();
			for(let i = 0; i < 24; i++){
				if(i < 10){
					hours.push('0'+i);
				}else{
					hours.push(i);
				}
			}
			var minutes  =  new Array();
			var seconds  =  new Array();
			for(let i = 0; i < 60; i++){
				if(i < 10){
					minutes.push('0'+i); seconds.push('0'+i);
				}else{
					minutes.push(i); 
					seconds.push(i);
				}
			}
			this.sDate = [years, months, days, hours, minutes, seconds];
			this.$nextTick(()=>{
				this.setValue(this.value);
			})
		},
		change : function (res) {
			this.changeBase(res.detail.value);
		},
		changeBase:function(res){
			var dIn = [0,0,0,0,0,0];
			var ddIn = [];
			// 没有日期
			if(!this.isDate){
				//没有秒
				if(!this.isSecond){
					dIn[3] = res[0];
					dIn[4] = res[1];
					ddIn=[res[0], res[1]];
				}else{
					dIn[3] = res[0];
					dIn[4] = res[1];
					dIn[5] = res[2];
					ddIn=[res[0], res[1], res[2]];
				}
			}else{
				dIn[0] = res[0];
				dIn[1] = res[1];
				// 有天
				if(this.isDay){
					dIn[2] = res[2];
					dIn[3] = res[3];
					dIn[4] = res[4];
					ddIn=[res[0], res[1], res[2], res[3], res[4]];
					// 有秒
					if(this.isSecond){
						dIn[5] = res[5];
						ddIn.push(Number(res[5]));
					}
				}else{
					dIn[2] = 0;
					dIn[3] = res[2];
					dIn[4] = res[3];
					ddIn=[res[0], res[1], res[2], res[3]];
					// 有秒
					if(this.isSecond){
						dIn[5] = res[4];
						ddIn.push(Number(res[4]));
					}
				}
			}
			this.defaultVal = dIn;
			this.defaultValIn = ddIn;
			this.$emit('change', new Array(
				this.sDate[0][this.defaultVal[0]],
				this.sDate[1][this.defaultVal[1]],
				this.sDate[2][this.defaultVal[2]],
				this.sDate[3][this.defaultVal[3]],
				this.sDate[4][this.defaultVal[4]],
				this.sDate[5][this.defaultVal[5]])
			);
		},
		confirm:function () {
			this.$emit('confirm', new Array(
				this.sDate[0][this.defaultVal[0]],
				this.sDate[1][this.defaultVal[1]],
				this.sDate[2][this.defaultVal[2]],
				this.sDate[3][this.defaultVal[3]],
				this.sDate[4][this.defaultVal[4]],
				this.sDate[5][this.defaultVal[5]])
			);
		}
	},
	emits : ['change', 'confirm']
}
</script>
<style scoped>
.graceDateTime-main{width:750rpx; height:300rpx; }
</style>