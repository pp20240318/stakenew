<template>
	<view>
		<view style="height:16rpx;"></view>
		<view 
		:class="[center ? 'gui-justify-content-center' : '']" 
		class="gui-flex gui-nowrap gui-align-items-center">
			<text
			class="gui-block gui-primary-color logo-text gui-dark-text-level-2">Grace</text>
			<text 
			style="font-size:20rpx; margin-left:12rpx; padding-bottom:20rpx;" 
			class="gui-block gui-third-text gui-dark-text-level-2">{{title}}</text>
		</view>
		<view style="margin-top:18rpx;">
			<text 
			style="font-size:24rpx;" 
			:class="['gui-color-gray', 'gui-block', center ? 'gui-text-center' : '']">{{desc}}</text>
		</view>
	</view>
</template>
<script>
export default {
	name:"logo",
	data() {
		return {
			
		};
	},
	props:{
		title : {
			type    : String,
			default : 'V6.0' 
		},
		desc : { 
			type    : String,
			default : '一款优秀的前端框架 · 更丰富 · 更高效 · 更稳定' 
		},
		center : {
			type    : Boolean,
			default : false
		}
	}
}
</script>
<style scoped>
.logo-text{font-size:50rpx;}
</style>
