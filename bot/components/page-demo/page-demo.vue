<template>
	<view>
		<view class="demo-banner demo-bg gui-dark-bg-level-3 gui-border-radius"></view>
		<view class="demo-space"></view>
		<view class="gui-flex gui-row gui-nowrap gui-space-between">
			<view 
			v-for="(item, index) in [1,2,3,4,5]" 
			:key="index" 
			class="demo-grid demo-bg gui-dark-bg-level-3 gui-border-radius"></view>
		</view>
		<view class="demo-space"></view>
		<view class="gui-flex gui-row gui-nowrap gui-space-between">
			<view 
			class="demo-card demo-bg gui-dark-bg-level-3 gui-border-radius" 
			style="height:406rpx;"></view>
			<view>
				<view
				class="demo-card demo-bg gui-dark-bg-level-3 gui-border-radius"></view>
				<view 
				style="margin-top:30rpx;"
				class="demo-card demo-bg gui-dark-bg-level-3 gui-border-radius"></view>
			</view>
		</view>
		<view class="demo-space"></view>
		<view class="gui-flex gui-row gui-nowrap gui-space-between">
			<view>
				<view
				class="demo-card demo-bg gui-dark-bg-level-3 gui-border-radius"></view>
				<view 
				style="margin-top:30rpx;"
				class="demo-card demo-bg gui-dark-bg-level-3 gui-border-radius"></view>
			</view>
			<view 
			class="demo-card demo-bg gui-dark-bg-level-3 gui-border-radius" 
			style="height:406rpx;"></view>
		</view>
	</view>
</template>

<script>
	export default {
		name:"page-demo",
		data() {
			return {
				
			};
		}
	}
</script>

<style scoped>
.demo-bg{background-color:#EFF1F6;}
.demo-banner{height:288rpx;}
.demo-space{height:30rpx;}
.demo-grid{width:106rpx; height:106rpx;}
.demo-card{width:330rpx; height:188rpx;}
</style>