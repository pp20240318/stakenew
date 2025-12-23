<template>
    
    <div class="default-main">
        
        <!-- <div id="testid" style="width:800px;height:200px;"></div> -->
        <div class="small-panel-box">
            <el-row :gutter="20">
                
                <!-- <el-col :sm="12" :lg="6">
                   
                    <div class="small-panel user-reg suspension">
                        <div class="small-panel-title">{{ t('dashboard.Member registration') }}</div>
                        <div class="small-panel-content">
                            
                            <div class="content-left">
                                
                                <Icon color="#8595F4" size="20" name="fa fa-line-chart" />
                                <el-statistic :value="userCount" :value-style="statisticValueStyle" />
                            </div>
                            
                        </div>
                    </div>
                </el-col> -->
                <el-col :sm="12" :lg="6">
                    <div class="small-panel user-reg suspension" >
                        <div class="small-panel-title"><el-select
                                v-model="fundvalue"
                                placeholder="Select"
                                size="medium"
                                style="width: 120px;"
                                @change="countfundData()"
                                >
                                    <el-option
                                        v-for="item in options"
                                        :key="item.value"
                                        :label="item.label"
                                        :value="item.value"
                                    />
                                </el-select></div>
                        <div class="small-panel-content" v-loading="fundLoading">
                            
                            
                                <div id="pie" style="width:100%;height:200px;"></div>
                        </div>
                    </div>
            
                    <!-- <el-card shadow="hover"  v-loading="fundLoading">
                       
                        <el-select
                                v-model="fundvalue"
                                placeholder="Select"
                                size="small"
                                style="width: 90px;"
                                @change="countfundData()"
                                >
                                    <el-option
                                        v-for="item in options"
                                        :key="item.value"
                                        :label="item.label"
                                        :value="item.value"
                                    />
                                </el-select>
                        <div id="pie" style="width:100%;height:200px;"></div>
                        
                    </el-card> -->
                </el-col>




                <!-- <el-col :sm="12" :lg="6">
                    <div class="small-panel users suspension">
                        <div class="small-panel-title">{{ t('dashboard.Total number of members') }}</div>
                        <div class="small-panel-content" >
                            <div class="content-left">
                                <Icon color="#74A8B5" size="20" name="fa fa-users" />
                                <span id="users_number">{{allUserCount}}</span>
                            </div>
                            <div class="content-right"></div>
                        </div>
                    </div>
                </el-col>
                
                <el-col :sm="12" :lg="6">
                    <div class="small-panel users suspension">
                        <div class="small-panel-title">{{ t('dashboard.Total number of recharge') }}</div>
                        <div class="small-panel-content" >
                            <div class="content-left">
                                <Icon color="#74A8B5" size="20" name="fa fa-users" />
                                <span id="users_number">{{allRecharge}}</span>
                            </div>
                            <div class="content-right"></div>
                        </div>
                    </div>
                </el-col>

                <el-col :sm="12" :lg="6">
                    <div class="small-panel users suspension">
                        <div class="small-panel-title">{{ t('dashboard.Total number of withdrawl') }}</div>
                        <div class="small-panel-content" >
                            <div class="content-left">
                                <Icon color="#74A8B5" size="20" name="fa fa-users" />
                                <span id="users_number">{{allWithdrawl}}</span>
                            </div>
                            <div class="content-right"></div>
                        </div>
                    </div>
                </el-col> -->
                

            </el-row>
        </div>
        <div class="growth-chart">
            <el-row :gutter="20">
                <el-col class="lg-mb-20" :xs="24" :sm="24" :md="24" :lg="24">
                    
                    
                    <el-card shadow="hover" :header="t('dashboard.Membership growth')" v-loading="userLoading">
                        <template #header>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="align-items: ;">{{t('dashboard.Membership growth')}}</span>
                                
                                
                                <el-select
                                v-model="value"
                                placeholder="Select"
                                size="medium"
                                style="width: 120px;"
                                @change="countuserData()"
                                >
                                    <el-option
                                        v-for="item in options"
                                        :key="item.value"
                                        :label="item.label"
                                        :value="item.value"
                                    />
                                </el-select>
                            </div>
                        </template>
                        <!-- <div class="user-growth-chart" :ref="chartRefs.set"></div> -->
                        
                        <div id="line" style="width:100%;height:200px;"></div>
                        
                    </el-card>
                </el-col>
                
            </el-row>
        </div>

       
    </div>
</template>

<script setup lang="ts">
import { useEventListener, useTemplateRefsList, useTransition } from '@vueuse/core'
import * as echarts from 'echarts'
import { CSSProperties, nextTick, onActivated, onBeforeMount, onMounted, onUnmounted, reactive, toRefs, watch,ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { index } from '/@/api/backend/dashboard'
import coffeeSvg from '/@/assets/dashboard/coffee.svg'
import headerSvg from '/@/assets/dashboard/header-1.svg'
import { useAdminInfo } from '/@/stores/adminInfo'
import { WORKING_TIME } from '/@/stores/constant/cacheKey'
import { useNavTabs } from '/@/stores/navTabs'
import { fullUrl, getGreet } from '/@/utils/common'
import { Local } from '/@/utils/storage'
import createAxios from '/@/utils/axios'
let workTimer: number

defineOptions({
    name: 'dashboard',
})

const d = new Date()
const { t } = useI18n()
const navTabs = useNavTabs()
const adminInfo = useAdminInfo()
const chartRefs = useTemplateRefsList<HTMLDivElement>()

const newUserCount = ref({date:[],new_users_count:[]})
const newUserCountMonth = ref({date:[],new_users_count:[]})
const newUserCountThrMonth = ref({date:[],new_users_count:[]})
const newUserCountYear = ref({date:[],new_users_count:[]})

const value = ref('0')
const userPievalue = ref('0')
const fundvalue = ref('0')
const options = [
  {
    value: '0',
    label: '最近一周',
  },
  {
    value: '1',
    label: '最近一月',
  },
  {
    value: '2',
    label: '最近三月',
  },
  {
    value: '3',
    label: '最近一年',
  },
  
]

var myChart:any
var pieChart:any
var option:any
const allUserCount = ref('0')
const allRecharge= ref('0')
const allWithdrawl = ref('0')
const userCount = ref('')
const rechargeNum = ref('')
const withdrawlNum = ref('')
const fundLoading =ref(false)
const userpieLoading =ref(false)
const userLoading =ref(false)
function countuserData(){
    userLoading.value = true
    createAxios({
        url: '/admin/dashboard/countData',
        method: 'get',
    }).then((res)=>{
         myChart = echarts.init(document.getElementById('line'));
         pieChart = echarts.init(document.getElementById('pie'));
         //用户注册图表数据
        newUserCount.value.date = res.data.user.map((item:any )=> item.date )
        newUserCount.value.new_users_count = res.data.user.map((item:any)=>item.new_users_count)
        newUserCountMonth.value.date = res.data.userMonth.map((item:any )=> item.date )
        newUserCountMonth.value.new_users_count = res.data.userMonth.map((item:any)=>item.new_users_count)
        newUserCountThrMonth.value.date = res.data.userThrMonth.map((item:any )=> item.date )
        newUserCountThrMonth.value.new_users_count = res.data.userThrMonth.map((item:any)=>item.new_users_count)
        newUserCountYear.value.date = res.data.userMonth.map((item:any )=> item.date )
        newUserCountYear.value.new_users_count = res.data.userMonth.map((item:any)=>item.new_users_count)
        console.log('res',res.data)
        

        if(value.value=='0'){
            userCount.value = res.data.UserDayCount[0].users_count   
            const option = {
                title: {
        text: '会员注册量: '+userCount.value, 
        left: 'right'
    },
                tooltip: {
                trigger: 'axis',
                axisPointer: { type: 'cross' }
                },
                    grid: {
                left: '10%',
                right: '10%',
                bottom: '10%',
                containLabel: true
            },
                xAxis: {
                    type: 'category',
                    data:newUserCount.value.date
                    
                },
                yAxis: {
                    type:'value',
                    
                    min: 0,
                    max: 10,
                    position: 'left',
                },
                legend: {
                    data: [ t('dashboard.Registration volume')],
                    textStyle: {
                        color: '#73767a',
                    },
                },
                series: [
                    
                    {
                        name: t('dashboard.Registration volume'),
                        data: newUserCount.value.new_users_count,
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            color: '#F48595',
                            opacity: 0.5,
                        },
                    },
                ],
            }

                myChart.setOption(option)
             
    }
    else if(value.value=='1'){
        userCount.value = res.data.UserMonthCount[0].users_count
            const option = {
                title: {
        text: '会员注册量: '+userCount.value, 
        left: 'right'
    },
                tooltip: {
                trigger: 'axis',
                axisPointer: { type: 'cross' }
                },
                    grid: {
                left: '10%',
                right: '10%',
                bottom: '10%',
                containLabel: true
            },
            
                xAxis: {
                    type: 'category',
                    data:newUserCountMonth.value.date
                    
                },
                yAxis: {
                    type:'value',
                    
                    min: 0,
                    max: 10,
                    position: 'left',
                },
                legend: {
                    data: [ t('dashboard.Registration volume')],
                    textStyle: {
                        color: '#73767a',
                    },
                },
                series: [
                    
                    {
                        name: t('dashboard.Registration volume'),
                        data: newUserCountMonth.value.new_users_count,
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            color: '#F48595',
                            opacity: 0.5,
                        },
                    },
                ],
            }
                myChart.setOption(option)
    }       
    else if(value.value=='2'){
        
        userCount.value = res.data.userThrMonthCount[0].users_count
            const option = {
                title: {
        text: '会员注册量: '+userCount.value, 
        left: 'right'
    },
                tooltip: {
                trigger: 'axis',
                axisPointer: { type: 'cross' }
                },
                    grid: {
                left: '10%',
                right: '10%',
                bottom: '10%',
                containLabel: true
            },
                xAxis: {
                    type: 'category',
                    data:newUserCountYear.value.date
                    
                },
                yAxis: {
                    type:'value',
                    
                    min: 0,
                    max: 10,
                    position: 'left',
                },
                legend: {
                    data: [ t('dashboard.Registration volume')],
                    textStyle: {
                        color: '#73767a',
                    },
                },
                series: [
                    {
                        name: t('dashboard.Registration volume'),
                        data: newUserCountYear.value.new_users_count,
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            color: '#F48595',
                            opacity: 0.5,
                        },
                    },
                ],
            }
                myChart.setOption(option)
    }        
    else if(value.value=='3'){
        userCount.value = res.data.userYearCount[0].users_count
            const option = {
                title: {
        text: '会员注册量: '+userCount.value, 
        left: 'right'
    },
                tooltip: {
                trigger: 'axis',
                axisPointer: { type: 'cross' }
                },
                    grid: {
                left: '10%',
                right: '10%',
                bottom: '10%',
                containLabel: true
            },
                xAxis: {
                    type: 'category',
                    data:newUserCountYear.value.date
                    
                },
                yAxis: {
                    type:'value',
                    
                    min: 0,
                    max: 10,
                    position: 'left',
                },
                legend: {
                    data: [ t('dashboard.Registration volume')],
                    textStyle: {
                        color: '#73767a',
                    },
                },
                series: [
                    
                    {
                        name: t('dashboard.Registration volume'),
                        data: newUserCountYear.value.new_users_count,
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            color: '#F48595',
                            opacity: 0.5,
                        },
                    },
                ],
            }
                myChart.setOption(option)
    }    
    }).finally(()=>{
       userLoading.value =false
    })
    
}

function countfundData(){
    fundLoading.value =true
    createAxios({
        url: '/admin/dashboard/countfundData',
        method: 'get',
    }).then((res)=>{

         pieChart = echarts.init(document.getElementById('pie'));
        if(fundvalue.value=='0'){
            
            rechargeNum.value =res.data.rechargeCountDay[0].recharge
            withdrawlNum.value =res.data.withdrawCountDay[0].withdraw

            const   pieoption = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} (占比{d}%)'
                },
                title: {
                    text: '资金进出',
                    left: 'center',
                    top: 'center'
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    
                    // data: ['充值', '提现'],
                    // label: {
                    //     show: true,
                    //     formatter: '{b}: {c} ({d}%)'
                    // },
                },
                series: [
                    {
                    name:'资金进出',
                    type: 'pie',
                    label: {
                show: true,
                formatter: '{b}: {c} ({d}%)'
            },
                    data: [
                        {
                        value: rechargeNum.value,
                        name: '充值',
                        
                        },
                        {
                        value: withdrawlNum.value,
                        name: '提现'
                        },
                        
                    ],
                    radius: ['40%', '70%']
                    }
                ]
                };
                pieChart.setOption(pieoption)
    }
    else if(fundvalue.value=='1'){

        rechargeNum.value =res.data.rechargeCountMonth[0].recharge
        withdrawlNum.value =res.data.withdrawCountMonth[0].withdraw

        const   pieoption = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} (占比{d}%)'
                },
                title: {
                    text: '资金进出',
                    left: 'center',
                    top: 'center'
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    
                    // data: ['充值', '提现'],
                    // label: {
                    //     show: true,
                    //     formatter: '{b}: {c} ({d}%)'
                    // },
                },
                series: [
                    {
                    name:'资金进出',
                    type: 'pie',
                    label: {
                show: true,
                formatter: '{b}: {c} ({d}%)'
            },
                    data: [
                        {
                        value: rechargeNum.value,
                        name: '充值',
                        
                        },
                        {
                        value: withdrawlNum.value,
                        name: '提现'
                        },
                        
                    ],
                    radius: ['40%', '70%']
                    }
                ]
                };
                pieChart.setOption(pieoption)
           
    }       
    else if(fundvalue.value=='2'){
       
        rechargeNum.value =res.data.rechargeCountThrMonth[0].recharge
        withdrawlNum.value =res.data.withdrawCountThrMonth[0].withdraw
        const   pieoption = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} (占比{d}%)'
                },
                title: {
                    text: '资金进出',
                    left: 'center',
                    top: 'center'
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    
                    // data: ['充值', '提现'],
                    // label: {
                    //     show: true,
                    //     formatter: '{b}: {c} ({d}%)'
                    // },
                },
                series: [
                    {
                    name:'资金进出',
                    type: 'pie',
                    label: {
                show: true,
                formatter: '{b}: {c} ({d}%)'
            },
                    data: [
                        {
                        value: rechargeNum.value,
                        name: '充值',
                        
                        },
                        {
                        value: withdrawlNum.value,
                        name: '提现'
                        },
                        
                    ],
                    radius: ['40%', '70%']
                    }
                ]
                };
                pieChart.setOption(pieoption)
           
    }        
    else if(fundvalue.value=='3'){
      
        rechargeNum.value =res.data.rechargeCountYear[0].recharge
        withdrawlNum.value =res.data.withdrawCountYear[0].withdraw
        const   pieoption = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} (占比{d}%)'
                },
                title: {
                    text: '资金进出',
                    left: 'center',
                    top: 'center'
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    
                    // data: ['充值', '提现'],
                    // label: {
                    //     show: true,
                    //     formatter: '{b}: {c} ({d}%)'
                    // },
                },
                series: [
                    {
                    name:'资金进出',
                    type: 'pie',
                    label: {
                show: true,
                formatter: '{b}: {c} ({d}%)'
            },
                    data: [
                        {
                        value: rechargeNum.value,
                        name: '充值',
                        
                        },
                        {
                        value: withdrawlNum.value,
                        name: '提现'
                        },
                        
                    ],
                    radius: ['40%', '70%']
                    }
                ]
                };
                pieChart.setOption(pieoption)
            
    }    
    }).finally(()=>{
        fundLoading.value =false
    })
    
}



const state: {
    charts: any[]
    remark: string
    workingTimeFormat: string
    pauseWork: boolean
} = reactive({
    charts: [],
    remark: 'dashboard.Loading',
    workingTimeFormat: '',
    pauseWork: false,
})

/**
 * 带有数字向上变化特效的数据
 */
const countUp = reactive({
    userRegNumber: 0,
    fileNumber: 0,
    usersNumber: 0,
    addonsNumber: 0,
})

const countUpRefs = toRefs(countUp)
const userRegNumberOutput = useTransition(countUpRefs.userRegNumber, { duration: 1500 })
const fileNumberOutput = useTransition(countUpRefs.fileNumber, { duration: 1500 })
const usersNumberOutput = useTransition(countUpRefs.usersNumber, { duration: 1500 })
const addonsNumberOutput = useTransition(countUpRefs.addonsNumber, { duration: 1500 })
const statisticValueStyle: CSSProperties = {
    fontSize: '28px',
}

index().then((res) => {
    state.remark = res.data.remark
})

function countUser() {
        return createAxios({
            url:  ('/admin/user.User/count'),
            method: 'get',
        })
    }

function countRecharge(){
    return createAxios({
            url:  ('/admin/finance.Recharge/count'),
            method: 'get',
        })
}


function countWithdrawl(){
    return createAxios({
            url:  ('/admin/finance.Withdrawl/count'),
            method: 'get',
        })
}


const initCountUp = () => {
    // 虚拟数据
    // countUpRefs.userRegNumber.value = 5456
    // countUpRefs.fileNumber.value = 1234
    // countUpRefs.usersNumber.value = 9486
    // countUpRefs.addonsNumber.value = 875
    
    

}

const initUserGrowthChart = () => {
    
    const userGrowthChart = echarts.init(chartRefs.value[0] as HTMLElement)
    
    if(value.value=='0')
    {
            
    }
    const option = {
        
    }
    userGrowthChart.setOption(option)

    
    state.charts.push(userGrowthChart)
}


const echartsResize = () => {
    nextTick(() => {
        for (const key in state.charts) {
            state.charts[key].resize()
        }
    })
}

const onChangeWorkState = () => {
    const time = parseInt((new Date().getTime() / 1000).toString())
    const workingTime = Local.get(WORKING_TIME)
    if (state.pauseWork) {
        // 继续工作
        workingTime.pauseTime += time - workingTime.startPauseTime
        workingTime.startPauseTime = 0
        Local.set(WORKING_TIME, workingTime)
        state.pauseWork = false
        
    } else {
        // 暂停工作
        workingTime.startPauseTime = time
        Local.set(WORKING_TIME, workingTime)
        clearInterval(workTimer)
        state.pauseWork = true
    }
}


const formatSeconds = (seconds: number) => {
    var secondTime = 0 // 秒
    var minuteTime = 0 // 分
    var hourTime = 0 // 小时
    var dayTime = 0 // 天
    var result = ''

    if (seconds < 60) {
        secondTime = seconds
    } else {
        // 获取分钟，除以60取整数，得到整数分钟
        minuteTime = Math.floor(seconds / 60)
        // 获取秒数，秒数取佘，得到整数秒数
        secondTime = Math.floor(seconds % 60)
        // 如果分钟大于60，将分钟转换成小时
        if (minuteTime >= 60) {
            // 获取小时，获取分钟除以60，得到整数小时
            hourTime = Math.floor(minuteTime / 60)
            // 获取小时后取佘的分，获取分钟除以60取佘的分
            minuteTime = Math.floor(minuteTime % 60)
            if (hourTime >= 24) {
                // 获取天数， 获取小时除以24，得到整数天
                dayTime = Math.floor(hourTime / 24)
                // 获取小时后取余小时，获取分钟除以24取余的分；
                hourTime = Math.floor(hourTime % 24)
            }
        }
    }

    result =
        hourTime +
        t('dashboard.hour') +
        ((minuteTime >= 10 ? minuteTime : '0' + minuteTime) + t('dashboard.minute')) +
        ((secondTime >= 10 ? secondTime : '0' + secondTime) + t('dashboard.second'))
    if (dayTime > 0) {
        result = dayTime + t('dashboard.day') + result
    }
    return result
}

onActivated(() => {
    echartsResize()
})



onMounted(() => {
    
    
    
    // initUserGrowthChart()
    // initFileGrowthChart()
    // initUserSourceChart()
    // initUserSurnameChart()
    useEventListener(window, 'resize', echartsResize)
    countuserData()
    countfundData()
    
})

onBeforeMount(() => {
   

    for (const key in state.charts) {
        state.charts[key].dispose()
    }
})

onUnmounted(() => {
    clearInterval(workTimer)
})

watch(
    () => navTabs.state.tabFullScreen,
    () => {
        echartsResize()
    }
)
</script>

<style scoped lang="scss">
.welcome {
    background: #e1eaf9;
    border-radius: 6px;
    display: flex;
    align-items: center;
    padding: 15px 20px !important;
    box-shadow: 0 0 30px 0 rgba(82, 63, 105, 0.05);
    .welcome-img {
        height: 100px;
        margin-right: 10px;
        user-select: none;
    }
    .welcome-title {
        font-size: 1.5rem;
        line-height: 30px;
        color: var(--ba-color-primary-light);
    }
    .welcome-note {
        padding-top: 6px;
        font-size: 15px;
        color: var(--el-text-color-primary);
    }
}
.working {
    height: 130px;
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    height: 100%;
    position: relative;
    &:hover {
        .working-coffee {
            -webkit-transform: translateY(-4px) scale(1.02);
            -moz-transform: translateY(-4px) scale(1.02);
            -ms-transform: translateY(-4px) scale(1.02);
            -o-transform: translateY(-4px) scale(1.02);
            transform: translateY(-4px) scale(1.02);
            z-index: 999;
        }
    }
    .working-coffee {
        transition: all 0.3s ease;
        width: 80px;
    }
    .working-text {
        display: block;
        width: 100%;
        font-size: 15px;
        text-align: center;
        color: var(--el-text-color-primary);
    }
    .working-opt {
        position: absolute;
        top: -40px;
        right: 10px;
        background-color: rgba($color: #000000, $alpha: 0.3);
        padding: 10px 20px;
        border-radius: 20px;
        color: var(--ba-bg-color-overlay);
        transition: all 0.3s ease;
        cursor: pointer;
        opacity: 0;
        z-index: 999;
        &:active {
            background-color: rgba($color: #000000, $alpha: 0.6);
        }
    }
    &:hover {
        .working-opt {
            opacity: 1;
            top: 0;
        }
        .working-done {
            opacity: 1;
            top: 50px;
        }
    }
}
.small-panel-box {
    margin-top: 20px;
}
.small-panel {
    background-color: #ffffff;
    border-radius: var(--el-border-radius-base);
    padding: 25px;
    margin-bottom: 20px;
    .small-panel-title {
        color: #92969a;
        font-size: 15px;
    }
    .small-panel-content {
        display: flex;
        align-items: flex-end;
        margin-top: 20px;
        color: #2c3f5d;
        .content-left {
            display: flex;
            align-items: center;
            font-size: 24px;
            .icon {
                margin-right: 10px;
            }
        }
        .content-right {
            font-size: 18px;
            margin-left: auto;
        }
        .color-success {
            color: var(--el-color-success);
        }
        .color-warning {
            color: var(--el-color-warning);
        }
        .color-danger {
            color: var(--el-color-danger);
        }
        .color-info {
            color: var(--el-text-color-secondary);
        }
    }
}
.growth-chart {
    margin-bottom: 20px;
    margin-top: 20px;
}
.user-growth-chart,
.file-growth-chart {
    height: 260px;
}
.new-user-growth {
    height: 300px;
}

.user-source-chart,
.user-surname-chart {
    height: 400px;
}
.new-user-item {
    display: flex;
    align-items: center;
    padding: 20px;
    margin: 10px 15px;
    box-shadow: 0 0 30px 0 rgba(82, 63, 105, 0.05);
    background-color: var(--ba-bg-color-overlay);
    .new-user-avatar {
        height: 48px;
        width: 48px;
        border-radius: 50%;
    }
    .new-user-base {
        margin-left: 10px;
        color: #2c3f5d;
        .new-user-name {
            font-size: 15px;
        }
        .new-user-time {
            font-size: 13px;
        }
    }
    .new-user-arrow {
        margin-left: auto;
    }
}
.new-user-card :deep(.el-card__body) {
    padding: 0;
}

@media screen and (max-width: 425px) {
    .welcome-img {
        display: none;
    }
}
@media screen and (max-width: 1200px) {
    .lg-mb-20 {
        margin-bottom: 20px;
    }
}
html.dark {
    .welcome {
        background-color: var(--ba-bg-color-overlay);
    }
    .small-panel {
        background-color: var(--ba-bg-color-overlay);
        .small-panel-content {
            color: var(--el-text-color-regular);
        }
    }
    .new-user-item {
        .new-user-base {
            color: var(--el-text-color-regular);
        }
    }
}
</style>
