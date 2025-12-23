<template>
	<link rel="stylesheet" type="text/css" href="/static/css/share.css ">
	<div    class="shareView">
		<div  class="bg_share"><i   @click="$goBack()" class="left_btn van-icon van-icon-arrow-left"
				style="color: rgb(255, 255, 255); font-size: 25px;"><!----></i>
			<div  class="inviteOtherTitleView"> {{ $t('推荐计划') }} </div>
			<div  class="inviteTitleView">{{ $t('邀请朋友赚币') }}</div>
			<div  class="copyLinkBgView">
				<div  class="codeBgview">
					<div  id="qrcode"  ></div>
				</div>
				<div  class="my_code_title">{{ $t('我的邀请码') }}</div>
				<div  class="flex alcenter center" style="margin-top: 4px;">
					<div  class="nick_name"> {{invite_code}} </div><img @click="copyText(1)"
						src="data:image/png;base64,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"
						class="imgs" style="width: 24px; margin-left: 2px;">
				</div>
				<div  class="referView">{{ $t('推荐并获得奖励') }}</div>
				<div  class="referMsgView">{{ $t('分享您的推荐链接并开始赚钱') }}  </div>
				<div  class="copyBtnBgView">
					<div  class="linkAddress">{{windoworigin}}/#/pages/login/register?code={{invite_code}}</div>
					<div  class="copyBtn" @click="copyText(2)">{{ $t('复制') }}  </div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import QRCode from 'qrcode';
	export default {
		data: function() {
			return {
				invite_code:'',
				erc_addressur:'',
				erc_count:'',
				currentTime:'',
				windoworigin:'',
				// 记录需要上传的图片数据
				needPploadedImgs : [],
			}
		},
		onReady: function() {
	       
		},
		onLoad: function() {
		  var userid=uni.getStorageSync('userid');
		  if(!userid) this.$gopage("/pages/login/index");  
		  this.windoworigin= window.location.origin;
	      this.loadData();
		},
		onShow: function() {
	       
		},
		mounted() {  
		},
		methods: {
			change : function (e) {
						this.needPploadedImgs = e;
						console.log(e);
					},
			copyText(type){
				var t = this; 
				var thistxt="";
				if(type=='1'){
					thistxt=t.invite_code;
				}else{
					thistxt=window.location.origin+"/#/pages/login/register?code="+t.invite_code;
				}
				 navigator.clipboard.writeText(thistxt)
				        .then(() => {
				          t.$toast(t.$t('复制成功')); 
				        })
				        .catch((error) => { 
				        });
			} ,
	        loadData(){
	        	var t = this; 
				var userid=uni.getStorageSync('userid');
	        	t.req({
	        		url: "user/share", 
	        		Loading: !1,
					data:{'userid':userid},
	        		success: function (i) {  
	        			 t.invite_code=i.data.invite_code;
						 var thiscode=window.location.origin+"/#/pages/login/register?code="+t.invite_code;
						 var qrCodeCanvas =QRCode.toCanvas(thiscode)
						 .then((canvas) => {
						           document.getElementById('qrcode').append(canvas); 
						         })
						         .catch((error) => {
						           console.error(error);
						         });;
	        		}
	        	})
	        } 
		},
	}
</script>

<style>
</style>