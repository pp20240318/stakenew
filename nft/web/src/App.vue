<template>
    <el-config-provider :locale="lang">
        <router-view></router-view>
    </el-config-provider>
</template>
<script setup lang="ts">
import { onMounted, watch ,ref,reactive} from 'vue'
import { useI18n } from 'vue-i18n'
import iconfontInit from '/@/utils/iconfont'
import { useRoute } from 'vue-router'
import { setTitleFromRoute } from '/@/utils/common'
import { useConfig } from '/@/stores/config'
import { useTerminal } from '/@/stores/terminal'
import { ElNotification } from 'element-plus'
import createAxios from '/@/utils/axios'
import {useRouter} from 'vue-router'

// modules import mark, Please do not remove.

const config = useConfig()
const route = useRoute()
const router = useRouter()
const terminal = useTerminal()
const timer=ref();
const timer1=ref();
const timer2=ref();
const state1 = reactive({
                    collapseActiveName: ['create', 'dynamic-route', 'tabs'],
                    createRoute: {
                    id: '',
                    name:  '/admin/finance/recharge',
                    path:  '/admin/finance/recharge',
                    component: '/@/views/backend/finance/recharge/index.vue',
                    },
                    })

const state2 = reactive({
collapseActiveName: ['create', 'dynamic-route', 'tabs'],
createRoute: {
id: '',
name:  '/admin/finance/withdrawl',
path:  '/admin/finance/withdrawl',
component: '/@/views/backend/finance/withdrawl/index.vue',
},
})


// 初始化 element 的语言包
const { getLocaleMessage } = useI18n()
const lang = getLocaleMessage(config.lang.defaultLang) as any
onMounted(() => {
    iconfontInit()
    terminal.init()
    //timer.value = setInterval(()=>{creatExamine()},1*10*1000);
    timer1.value = setInterval(()=>{creatNotification()},3*60*1000);
    timer2.value = setInterval(()=>{creatNotification1()},3*58*1000);
    // timer1.value = setInterval(()=>{creatNotification()},1*60*1000);
    // Modules onMounted mark, Please do not remove.
})

// 监听路由变化时更新浏览器标题
watch(
    () => route.path,
    () => {
        setTitleFromRoute()
    }
)


function creatExamine() {
      createAxios({
            url: ('/admin/nft.Purchase/checkExpire'),
            method: 'post', 
 }).then((res)=>{
       
 })

}

var instanceNotification = Notification || window.Notification;
    console.log(instanceNotification);
    if (instanceNotification) {
        var permissionNow = instanceNotification.permission;
        if (permissionNow === 'granted') {//允许通知
            creatNotification();
        } else if (permissionNow === 'default') {
            setPermission();
        } else if (permissionNow === 'denied') {
            console.log('用户拒绝了你!!!');
        }  else {
            setPermission();
        }
    }

    function setPermission() {
        //请求获取通知权限
        instanceNotification.requestPermission(function (PERMISSION) {
            if (PERMISSION === 'granted') {
                console.log('用户允许通知了!!!');
                creatNotification();
            } else {
                console.log('用户拒绝了你!!!');
            }
        });
    }

    function creatNotification() {
      createAxios({
            url: ('/admin/finance.Recharge/searchNewRecharge'),
            method: 'get', 
 }).then((res)=>{
    
    if(res.data===true){
      
          if (!window.Notification) {
                alert("浏览器不支持通知！");
                return false;
            }
            console.log(window.Notification.permission);
            if (window.Notification.permission != 'granted') {
                console.log('用户未开启通知权限!!!');
                return false;
            }

            // var instanceNotification = new Notification("您有一条订单消息，请及时处理！", { "icon": "", "body": "快点击处理吧!","requireInteraction":true });
            var instanceNotification  =    ElNotification({
            title: '有新充值记录',
            message:  '点击查看',
            type: 'success',
            onClick:()=>{
            router.push({ path: state1.createRoute.path })
            instanceNotification.close()
            },
            onClose:()=>{
              console.log("通知关闭");
            }
          })
         
      }
   
 })

}




var instanceNotification = Notification || window.Notification;
    console.log(instanceNotification);
    if (instanceNotification) {
        var permissionNow = instanceNotification.permission;
        if (permissionNow === 'granted') {//允许通知
            creatNotification();
        } else if (permissionNow === 'default') {
            setPermission();
        } else if (permissionNow === 'denied') {
            console.log('用户拒绝了你!!!');
        }  else {
            setPermission();
        }
    }

    function creatNotification1() {
      createAxios({
            url: ('/admin/finance.withdrawl/searchNewWithdrawl'),
            method: 'get', 
 }).then((res)=>{
    
    if(res.data===true){
      
          if (!window.Notification) {
                alert("浏览器不支持通知！");
                return false;
            }
            console.log(window.Notification.permission);
            if (window.Notification.permission != 'granted') {
                console.log('用户未开启通知权限!!!');
                return false;
            }

            // var instanceNotification = new Notification("您有一条订单消息，请及时处理！", { "icon": "", "body": "快点击处理吧!","requireInteraction":true });
            var instanceNotification  =    ElNotification({
            title: '有新提现记录',
            message:  '点击查看',
            type: 'success',
            onClick:()=>{
            router.push({ path: state2.createRoute.path })
            instanceNotification.close()
            },
            onClose:()=>{
              console.log("通知关闭");
            }
          })
         
      }
   
 })

}









</script>
