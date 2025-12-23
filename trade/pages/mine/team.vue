<template>
	<link rel="stylesheet" type="text/css" href="/static/css/team.css ">
	<div   class="inviteFir">
		<div  class="van-nav-bar van-hairline--bottom">
			<div class="van-nav-bar__content">
				<div class="van-nav-bar__left"  @click="$goBack()"><i class="van-icon van-icon-arrow-left van-nav-bar__arrow"><!----></i>
				</div>
				<div class="van-nav-bar__title van-ellipsis">{{ $t('团队') }}</div>
				<div class="van-nav-bar__right"><span class="van-nav-bar__text">{{ $t('团队介绍') }}</span></div>
			</div>
		</div>
		<div  class="invite">
			<div  class="header">
				<div >{{ $t('团队总收益') }}</div>
				<h2  style="color: rgb(255, 255, 255);">0 USDT</h2>
			</div>
		</div>
		<div  class="flex alcenter between" style="margin: 15px 20px 0px;">
			<div  class="level_list">
				<div  style="text-align: left;">{{ $t('等级') }}1</div>
				<div  style="text-align: right;">0</div>
			</div>
			<div  class="level_list">
				<div  style="text-align: left;">{{ $t('等级') }}2</div>
				<div  style="text-align: right;">0</div>
			</div>
			<div  class="level_list">
				<div  style="text-align: left;">{{ $t('等级') }}3</div>
				<div  style="text-align: right;">0</div>
			</div>
		</div>
		<div  class="list_content">
			<div  class="list">
				<div  class="btm_list van-row van-row--flex van-row--justify-center">
					<div  style="border-top-left-radius: 6px;">{{ $t('账户') }}</div>
					<div >{{ $t('等级') }}</div><!---->
					<div  style="border-top-right-radius: 6px;"> {{ $t('注册时间') }} </div>
				</div>
				<div  class="van-empty">
					<div class="van-empty__image"><img src="/static/img/empty-image-default.png"></div>
					<p class="van-empty__description">{{ $t('暂无数据~') }}</p>
				</div>
			</div>
		</div><!---->
	</div>
</template>

<script>
</script>

<style>
</style>