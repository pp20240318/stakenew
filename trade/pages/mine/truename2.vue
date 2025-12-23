<template>
	<link rel="stylesheet" type="text/css" href="/static/css/truename2.css ">
	<div   id="depositDetail">
		<div   class="myhead3"><span  class="imgb"><img @click="$goBack()"
					
					src="data:image/png;base64,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"
					class="img1"></span><span  class="text1">{{ $t('高级认证') }}</span><span 
				class="text2"></span></div>
		<div  class="deposit_content"><!---->
			<div  class="titleOneNew ft14 mt20 text1" style="font-size: 14px;">
				{{ $t('请上传身份证正反面和手持身份证，第一张为正面，第二张为反面。') }} </div>
			<div  class="flex between">
				<div  class="img1b" style="text-align: center;">
					<div  class="mt10 van-uploader">
						<gui-upload-images
						btnName=""
										@change="change" 
										ref="uploadimgcom" 
										maxFileNumber="1"
										@uploaded="uploaded" 
										:uploadServerUrl="siteBaseUrl+'recharge/uploadimg'"></gui-upload-images>
					</div>
				</div>
				<div  class="img1b" style="text-align: center;">
					<div  class="mt10 van-uploader">
						<gui-upload-images
						btnName=""
										@change="change1" 
										ref="uploadimgcom1" 
										maxFileNumber="1"
										@uploaded="uploaded" 
										:uploadServerUrl="siteBaseUrl+'recharge/uploadimg'"></gui-upload-images>
					</div>
				</div>
			</div>
			<div  class="mt15 text1"> {{ $t(showtxt) }} </div>
			<div  class="Take_btn" v-if="!isReadOnly"  @click="submit()"> {{ $t('提交') }} </div>
		</div>
	</div>
</template>

<script>
	export default {
		data: function() {
			return {
				erc_address:'',
				erc_addressur:'',
				img_front:'',
				img_back:'', 
				// 记录需要上传的图片数据
				needPploadedImgs : [],
				needPploadedImgs1 : [],
				isReadOnly: false,
				saveimg:[],
				showtxt:'您还未高级认证,请去认证'
			}
		},
		onReady: function() {
	       
		},
		onLoad: function() {
		  var userid=uni.getStorageSync('userid');
		  if(!userid) this.$gopage("/pages/login/index");   
	      this.loadData();
		},
		onShow: function() {
	       
		},
		mounted() { 
		   
		    
		},
		methods: {
			change : function (e) {
						this.needPploadedImgs = e; 
					},
			change1 : function (e) {
						this.needPploadedImgs1 = e; 
					},
			uploaded : function (uploadedImgs) {
				var t=this;
				t.saveimg.push(uploadedImgs[0].result.result);
				if(t.saveimg.length==2){
					var userid=uni.getStorageSync('userid');
					t.req({
						url: "user/savetruename2",
						data: {'userid':userid,'img_front':t.saveimg[0],'img_back':t.saveimg[1]},
						Loading: !1,
						success: function (i) { 
							if(i.code==0){
								return t.$toast(t.$t(i.msg));
							} 
							t.$toast(t.$t('成功'));
							t.$gopage("/pages/mine/truename2");   
						}
					})
				}
				 
			}, 
			submit : function () { 
				var t=this;
				 
						// 判断图片选择数量
				if(this.needPploadedImgs.length < 1){
							uni.showToast({title:t.$t('请上传正面照片'), icon:"none"}); 
							return ;
				} 
				if(this.needPploadedImgs1.length < 1){
							uni.showToast({title:t.$t('请上传反面照片'), icon:"none"}); 
							return ;
				} 
				this.$refs.uploadimgcom.upload(); 
				this.$refs.uploadimgcom1.upload(); 
			},
	        loadData(){
	        	var t = this; 
				var userid=uni.getStorageSync('userid');
	        	t.req({
	        		url: "user/truename2", 
	        		Loading: !1,
					data:{'userid':userid},
	        		success: function (i) {  
						
	        			 t.img_front=t.H5BaseUrl+i.data.img_front;
						 t.img_back=t.H5BaseUrl+i.data.img_back;
						 if(i.data.status>0){
							 t.$refs.uploadimgcom.setItems([t.img_front]);
							 t.$refs.uploadimgcom1.setItems([t.img_back]);
						 }
						 //您以高级认证
						 t.isReadOnly=true;
						 if(i.data.status=='3')  t.showtxt="实名信息审核中";
						 else if(i.data.status=='1')  t.showtxt="您以高级认证"; 
						 else if(i.data.status=='2') {t.showtxt="审核失败请重新提交"; t.isReadOnly=false;} 
						 else {t.isReadOnly=false;t.showtxt="您还未高级认证,请去认证";}
	        		}
	        	})
	        } 
		},
	}
</script>

<style>
</style>