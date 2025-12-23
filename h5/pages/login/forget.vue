<template>
	<link rel="stylesheet" type="text/css" href="/static/css/forget.css ">
	<div class="ChangePswView">
		<div class="head">
			<img  @click="$goBack()"
				src="data:image/png;base64,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"
				class="img1">
			<span class="text">{{ $t('忘记密码') }}</span>
			<span></span>
		</div><!---->

		<div class="imgb"><img src="/static/img/pic17.f70b6947.png" class="img1"></div>
		<div class="logob"><img src="/static/img/logo.png" class="logo">
			<div class="text"></div>
		</div>
		<div class="viewBg">
			<div class="changePsw_BgView">
				<div class="tab">
					<span class="text" @click="selecttype=1" :class="selecttype==1?'active':''"> {{ $t('邮箱') }}</span>
					<span class="text" @click="selecttype=2" :class="selecttype==2?'active':''"> {{ $t('手机号') }}</span>
				</div>
				<div  class="inputb" v-if="selecttype==2">
					<span  class="textb">
						<select class="text1">
							<option  value="+234"> +234(NG)</option>
							<option  value="+81"> +81(JP)</option>
							<option  value="+82"> +82(RKSK)</option>
							<option  value="+91"> +91(Ind)</option>
							<option  value="+65"> +65(SG)</option>
							<option  value="+92"> +92(PK)</option>
							<option  value="+233"> +233(GHA)</option>
							<option  value="+225"> +225(CIV)</option>
							<option  value="+1"> +1(US)</option>
							<option  value="+55"> +55(BR)</option>
							<option  value="+44"> +44(UK)</option>
							<option  value="+254"> +254(KE)</option>
							<option  value="+27"> +27(ZA)</option>
							<option  value="+250"> +250(RW)</option>
							<option  value="+90"> +90(TR)</option>
							<option  value="+971"> +971(AE)</option>
							<option  value="+260"> +260(ZM)</option>
							<option  value="+237"> +237(CM)</option>
							<option  value="+7"> +7(RU)</option>
							<option  value="+256"> +256(UG)</option>
							<option  value="+218"> +218(LBY)</option>
							<option  value="+62"> +62(IDN)</option>
							<option  value="+34"> +34(ES)</option>
							<option  value="+39"> +39(IT)</option>
							<option  value="+226"> +226(BF)</option>
							<option  value="+220"> +220(GM)</option>
							<option  value="+49"> +49(DE)</option>
							<option  value="+966"> +966(SA)</option>
							<option  value="+43"> +43(AT)</option>
							<option  value="+40"> +40(RO)</option>
							<option  value="+229"> +229(BJ)</option>
							<option  value="+351"> +351(pt)</option>
							<option  value="+20"> +20(EG)</option>
							<option  value="+855"> +855(KH)</option>
							<option  value="+61"> +61(AU)</option>
							<option  value="+66"> +66(THA)</option>
						</select><input  type="text"  style="float: right;" :placeholder=" $t('请输入手机号') " class="text2"></span>
				</div>
				<div class="inputb" v-else><input type="text" :placeholder=" $t('请输入邮箱') "  class="input1"></div><!---->
				<!-- <div class="inputb"><input type="text" :placeholder=" $t('请输入验证码') " class="input1"><span
						class="code"><span>{{$t('获取验证码')}}</span> </span></div> -->
				<div class="inputb"><input type="text" :placeholder=" $t('请输入密码') " class="input1"></div>
				<div class="confirmBtnBg">
					<div class="Take_btn"> {{$t('提交')}} </div>
				</div>
			</div>
		</div>
		 
	</div><!----><!----><!---->
</template>

<script>
	export default {
		data: function() {
			return {
				selectforgettype: 1,
				selecttype: 1, 
			}
		},
		onReady: function() {

		},
		onLoad: function() {

		},
		onShow: function() {

		},
		mounted() {

		},
		methods: {
			goBack: function() {
				window.history.back();
			},

		},
	}
</script>

<style>
</style>