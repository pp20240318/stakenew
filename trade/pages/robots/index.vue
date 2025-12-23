<template>
 <link rel="stylesheet" type="text/css" href="/static/css/chunk-31ff03d6.8db6dbfa.css"> 
			 
				<div   class="robotPage">
					<div  class="lanb">
						<span class="text1"></span>
						<span class="text2"></span>
							<span  class="text3"><a
								  @click="$gopage('/pages/robots/myxbot2')" class=""><img 
									src="/static/img/myorder.c4a1e9da.png" class="img"></a>
							</span>
					</div>
					<div  class="con1">
						<div  class="textb">
							<div  class="text1">{{ $t('账户总额') }}</div>
							<div  class="text2">{{money}}</div>
						</div>
						<div  class="textb">
							<div  class="text1">{{ $t('冻结金额') }}</div>
							<div  class="text2">{{lockmoney}}</div>
						</div>
						<div  class="textb">
							<div  class="text1">{{ $t('今日收益') }}</div>
							<div  class="text2">{{symoney}}</div>
						</div>
					</div>
					<div  class="botlist" style="margin-top: 20px;"><!---->
						<div  class="list" @click="$gopage('/pages/robots/list?id='+item.id)"  v-for="(item, index) in list ">
							<div  class="left"><img :src="H5BaseUrl+item.image"  class="img1"></div>
							<div  class="right">
								<div  class="text1">{{item.name}}</div>
								<div  class="text2">{{item.begin_price}} - {{item.end_price}} USDT</div>
								<div  class="text3">{{ $t('点击进入') }} &gt;&gt;</div>
								<img :src="'/static/img/level'+(index+1)+'.png'" src="/static/img/pic06.95176865.png" class="img2">
							</div>
						</div>
						 
					</div>
					<div  style="height: 60px;"></div>
				</div><!----><!---->
				  <div  class="tabbar van-hairline--top-bottom van-tabbar van-tabbar--fixed">
				  	<div  class="van-tabbar-item" @click="$gopage('/pages/market/market')">
				  		<div class="van-tabbar-item__icon"><img 
				  				src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAMAAABg3Am1AAAAP1BMVEUAAACprcGnrb+rsMOrsMKqsMKlpb+qr8KrsMKrr8KqsMKpr8GrssOqrr+qsMKprr+qsMKqsMKqr7+qr7+qr8J0mv7fAAAAFHRSTlMAgBR1jfMJ4KHBzD9iUrpLq20vJDRCiekAAAEWSURBVEjH7ZTbboQwDERx7gSy3Ob/v7Uxqko3zrpIfeqq84JJ5hDbyBneVqZYKua+3XlUeXcTWU77iZQ7fsfW7Fzmpws/pvOotrhwWGINH0b3H2xazSe8Mnxo/onTp+uduJDltX8790UDrFZu3J/X9sild+2Byx1FjWbk0kPHzxtrb4NLH0P3+9RPlZhoF21d3LRmUHOAb5ohzvDNDwDioCgC09PCDCQNSMDcluA0wLUZk2yR2P8Hfgcclk7Z/R5g8SV7B9jxTYcGXAdkOpUBqwEi5OgPATwPGsDzICZOAeTETUDWgNzOtAFQXgMF8EZMOXJyRDQCY31ckUsZ8o4wEaqivHSTV/yp+oXCNFvqaJunMLyVPgCPKBwmH+OKHgAAAABJRU5ErkJggg=="
				  				class="imgs"><!----></div>
				  		<div class="van-tabbar-item__text"></div>
				  	</div>
				  	<div  class="van-tabbar-item van-tabbar-item--active" style="color: rgb(0, 0, 0);" @click="$gopage('/pages/main/bot')">
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
		 
</template>

<script>
	export default {
		data: function() {
			return { 
				list:[],
				money:0,
				symoney:0,
				lockmoney:0,
			}
		},
		onReady: function() {
	
		},
		onLoad: function() {
			
		},
		onShow: function() { 
		  let userid=uni.getStorageSync('userid');
		  if(userid){
		  	
		  }
		  this.loadData();
		},
		mounted() {
	
		},
		methods: {
			loadData:function(){
				var t = this; 
				let userid=uni.getStorageSync('userid'); 
				t.req({
					url: "robot/index", 
					Loading: !1,
					data:{'userid':userid},
					success: function (i) {    
						 t.list=i.data.list;
						 t.money=i.data.money;
						 t.lockmoney=i.data.getlockmoney;
						 t.symoney=i.data.symoney;
					}
				})
			},
			 
		},
	}
</script>

<style>
</style>