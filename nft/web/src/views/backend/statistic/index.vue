<template>
    <div class="default-main ba-table-box">
        
        <div  class="demonstration">

                <el-col :xs="24" :sm="14">
                    <div class="com-search-col" >
                        <div class="com-search-col-label w16">{{ t('Select TimeRange') }}</div>
                        <div class="com-search-col-input-range w83">
                        <el-date-picker
                        v-model="searchTime"
                        type="datetimerange"
                        :start-placeholder="t('Start Date')"
                        :end-placeholder="t('End Date')"
                        :default-time="defaultTime1"
                       />
                        </div>

                        <div class="com-search-col-label w16">{{ t('statistic.admin_id') }}</div>
                        <div class="com-search-col-input-range w83">
                            <BaInput
                                type="remoteSelect"
                                v-model="searchAgent"
                                :attr="{ pk: 'ba_admin.id', field: 'username', remoteUrl: '/admin/auth.Admin/index' }"
                                
                            />
                        </div>
                        <div class="com-search-col pl-20">
                        <el-button v-blur @click="initTable()" type="primary">{{ $t('Search') }}</el-button>
                        <el-button @click="resetTable()">{{ $t('Reset') }}</el-button>
                    </div>
                    </div>
                    
                </el-col>
                <el-col :xs="24" :sm="6">
                    
                </el-col>
                
                
                
        </div>
    
        <el-table  v-loading="loading" :data="tableStatistics" style="width: 100%">
            
    <!-- <el-table-column prop="salesman" label="业务员"></el-table-column> -->
    <el-table-column :label="t('statistic.admin_id')"  prop="admin_id">
                    <template #default="scope">
                        <el-popover
                            placement="top-start"
                            :width="400"
                            trigger="hover"
                        >
                            <template #reference>
                            <el-button size="small">{{scope.row.recharge_total ? scope.row.recharge_total.admin.username : scope.row.withdrawal_total.admin.username}}</el-button>
                            </template>
                            <el-descriptions   direction="vertical" :column="3">
                                <div v-if="scope.row.recharge_total">
                                <el-descriptions-item label="id" min-width="40">{{scope.row.recharge_total.admin.id}}</el-descriptions-item>
                                <el-descriptions-item :label="t('statistic.admin__username')">{{scope.row.recharge_total.admin.username}}</el-descriptions-item>
                                <el-descriptions-item :label="t('statistic.mobile')">{{scope.row.recharge_total.admin.mobile}}</el-descriptions-item>
                                </div>
                                <div v-else="scope.row.withdrawal_total">
                                <el-descriptions-item label="id" min-width="40">{{scope.row.withdrawal_total.admin.id}}</el-descriptions-item>
                                <el-descriptions-item :label="t('statistic.admin__username')">{{scope.row.withdrawal_total.admin.username}}</el-descriptions-item>
                                <el-descriptions-item :label="t('statistic.mobile')">{{scope.row.withdrawal_total.admin.mobile}}</el-descriptions-item>
                                </div>
                                
                                
                            </el-descriptions>
                        </el-popover>
                    </template>
      </el-table-column>
      <el-table-column  label="会员数">
        <template #default="scope">
            <div v-if="scope.row.recharge_total">
                {{scope.row.recharge_total.admin.user_num}}             
            </div>
            <div v-else="scope.row.withdrawal_total">
                {{scope.row.withdrawal_total.admin.user_num}}             
            </div>
        </template>

      </el-table-column>
      <el-table-column prop="recharge_total.recharge_total" label="充值总额" :formatter="rechargeFormatter"  ></el-table-column>
      <el-table-column prop="withdrawal_total.withdrawal_total" label="提现总额" :formatter="withdrawalFormatter"></el-table-column>

      
    </el-table>

    <div  class="table-pagination">
            <el-pagination
                :currentPage="currentPage"
                :page-size="limit"
                :page-sizes="pageSizes"
                background
                :layout="config.layout.shrink ? 'prev, next, jumper' : 'sizes,total, ->, prev, pager, next, jumper'"
                :total="total"
                @size-change="onTableSizeChange"
                @current-change="onTableCurrentChange"
            ></el-pagination>
    </div>
  </div>
  
</template>

<script setup lang="ts">
import { onMounted, provide, ref ,computed} from 'vue'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import { baTableApi } from '/@/api/common'
import { defaultOptButtons } from '/@/components/table'
import TableHeader from '/@/components/table/header/index.vue'
import Table from '/@/components/table/index.vue'
import baTableClass from '/@/utils/baTable'
import createAxios from '/@/utils/axios'
import { useConfig } from '/@/stores/config'
import BaInput from '/@/components/baInput/index.vue'
import { result } from 'lodash-es'
defineOptions({
    name: 'statistic',
})

const { t } = useI18n()
const tableRef = ref()
const statistics = ref([])
const tableStatistics = ref([])
const config = useConfig()
const total = ref(0)
const searchTime = ref('')
const searchAgent = ref('')
const loading = ref(false)
const defaultTime1: [Date, Date] = [
  new Date(2000, 1, 1, 0, 0, 0),
  new Date(2000, 2, 1, 23, 59, 59),
] // '12:00:00', '08:00:00'

const rechargeFormatter = (row: any, column:any ) => {
    let result = row.recharge_total?row.recharge_total.recharge_total:0
    // 定义 formatter 函数来去除小数末尾的零
        // 将数字转换为字符串
        let str = result.toString();
        // 如果字符串中包含小数点
        if (str.includes('.')) {
          // 从右向左移除末尾的零
          while (str[str.length - 1] === '0') {
            str = str.slice(0, -1);
          }
          // 如果小数点后没有数字了，移除小数点
          if (str[str.length - 1] === '.') {
            str = str.slice(0, -1);
          }
        }
        return str;
}

const withdrawalFormatter = (row: any, column:any ) => {
    let result = row.withdrawal_total?row.withdrawal_total.withdrawal_total:0
    // 定义 formatter 函数来去除小数末尾的零
        // 将数字转换为字符串
        let str = result.toString();
        // 如果字符串中包含小数点
        if (str.includes('.')) {
          // 从右向左移除末尾的零
          while (str[str.length - 1] === '0') {
            str = str.slice(0, -1);
          }
          // 如果小数点后没有数字了，移除小数点
          if (str[str.length - 1] === '.') {
            str = str.slice(0, -1);
          }
        }
        return str;
}
const currentPage =ref(1);
const limit =ref(10);

const pageSizes = computed(() => {
    let defaultSizes = [10, 20, 50, 100]
    if (limit) {
        if (!defaultSizes.includes(limit.value)) {
            defaultSizes.push(limit.value)
        }
    }
    return defaultSizes
})

const onTableSizeChange = (val: number) => {
    limit.value =  val
    tableStatistics.value  = statistics.value.slice(0+(currentPage.value-1)*limit.value,0+(currentPage.value-1)*limit.value+limit.value)

}

const onTableCurrentChange = (val: number) => {
    currentPage.value =  val
    tableStatistics.value  = statistics.value.slice(0+(currentPage.value-1)*limit.value,0+(currentPage.value-1)*limit.value+limit.value)
}

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
 function countWithdrawl(){
   
}


function initTable(){
    loading.value = true
    createAxios({
        url: '/admin/statistic/index',
        method: 'get',
        params: {searchTime:searchTime.value,searchAgent:searchAgent.value}
    }).then(response => {
          statistics.value = response.data.list;
          tableStatistics.value = response.data.list;
          tableStatistics.value =tableStatistics.value.slice(0,10)
          total.value      = response.data.total;
        }).finally(()=>{
            loading.value = false
        })
}

function resetTable(){
    loading.value = true
    searchTime.value = ''
    searchAgent.value = ''
    createAxios({
        url: '/admin/statistic/index',
        method: 'get',
        params: {searchTime:searchTime.value,searchAgent:searchAgent.value}
    }).then(response => {
          statistics.value = response.data.list;
          tableStatistics.value = response.data.list;
          tableStatistics.value =tableStatistics.value.slice(0,10)
          total.value      = response.data.total;
        }).finally(()=>{
            loading.value = false
        })
}

onMounted(() => {
    initTable()
})
</script>

<style scoped lang="scss">
.table-pagination {
    box-sizing: border-box;
    width: 100%;
    max-width: 100%;
    background-color: var(--ba-bg-color-overlay);
    padding: 13px 15px;
}

.demonstration {
  display: block;
  color: var(--el-text-color-secondary);
  font-size: 14px;
  margin-bottom: 20px;
}

.com-search-col-label {
        width: 33.33%;
        padding: 0 15px;
        text-align: right;
        overflow: hidden;
        white-space: nowrap;
}
.com-search-col-input-range {
        display: flex;
        align-items: center;
        padding: 0 15px;
        width: 66.66%;
        .range-separator {
            padding: 0 5px;
        }
    }

.pl-20 {
    padding-left: 20px;
}
.w16 {
    width: 50.5% !important;
}
.w83 {
    width: 83.5% !important;
}
.com-search-col {
        display: flex;
        align-items: center;
        padding-top: 8px;
        color: var(--el-text-color-regular);
        font-size: 13px;
    }
</style>
