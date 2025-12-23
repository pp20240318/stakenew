<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />
        
        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="
                t('Quick search placeholder', { fields: t('user.moneyLog.User name') + '/' + t('user.moneyLog.User nickname') })
            "
        >
            <el-button v-if="!isEmpty(state.userInfo)" v-blur class="table-header-operate">
                <span class="table-header-operate-text">{{
                    state.userInfo.username + '(ID:' + state.userInfo.id + ') ' + t('user.moneyLog.balance') + ':' + state.userInfo.money
                }}</span>
            </el-button>
        </TableHeader>

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" >
            <template #business >
                <!-- 在插槽内，您可以随意发挥，通常使用 el-table-column 组件 -->
                <el-table-column :label="t('user.moneyLog.business_id')"  prop="business_id">
                    <template #default="scope" >
                        <div v-if="scope.row.business_typeid==1 || scope.row.business_typeid==3  ||scope.row.business_typeid==6 ">
                            <el-button size="small"  v-if="!scope.row.business_id ">无</el-button>
                            <el-popover
                                placement="top-start"
                                :width="400"
                                trigger="click"
                                v-if="scope.row.business_id"
                            >
                                <template #reference>
                                <el-button size="small" @click="searchProduct(scope.row.business_id)">{{scope.row.business_id}}</el-button>~
                                </template>
                                <el-descriptions   direction="vertical" :column="3" v-loading="businessLoding">
                                    <el-descriptions-item label="id" min-width="40">{{product.id}}</el-descriptions-item>
                                    <el-descriptions-item :label="t('computer_name')">{{product.computer_name}}</el-descriptions-item>
                                    <el-descriptions-item :label="t('image')"> <el-image style="width: 100px; height: 100px" :src="fullUrl(product.image)" /></el-descriptions-item>
                                
                                
                                </el-descriptions>
                            </el-popover>
                        </div>

                        <div v-if="scope.row.business_typeid==2 || scope.row.business_typeid==4  ||scope.row.business_typeid==7 ">
                            <el-button size="small"  v-if="!scope.row.business_id ">无</el-button>
                            <el-popover
                                placement="top-start"
                                :width="400"
                                trigger="click"
                                v-if="scope.row.business_id"
                            >
                                <template #reference>
                                <el-button size="small" @click="searchAIProduct(scope.row.business_id)">{{scope.row.business_id}}</el-button>~
                                </template>
                                <el-descriptions   direction="vertical" :column="3" v-loading="businessLoding">
                                    <el-descriptions-item label="id" min-width="40">{{product.id}}</el-descriptions-item>
                                    <el-descriptions-item :label="t('computer_name')">{{product.computer_name}}</el-descriptions-item>
                                    <el-descriptions-item :label="t('image')"> <el-image style="width: 100px; height: 100px" :src="fullUrl(product.image)" /></el-descriptions-item>
                                
                                
                                </el-descriptions>
                            </el-popover>
                        </div>
                    </template>
                </el-table-column>
            </template>
        </Table>

        <!-- 表单 -->
        <PopupForm />
    </div>
</template>

<script setup lang="ts">
import { isEmpty, parseInt } from 'lodash-es'
import { ref, provide, reactive, watch } from 'vue'
import baTableClass from '/@/utils/baTable'
import { url } from '/@/api/backend/user/moneyLog'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { baTableApi } from '/@/api/common'
import { useRoute } from 'vue-router'
import { add } from '/@/api/backend/user/moneyLog'
import { useI18n } from 'vue-i18n'
import createAxios from '/@/utils/axios'
import { fullUrl } from '/@/utils/common'
defineOptions({
    name: 'user/moneyLog',
})

const { t } = useI18n()
const tableRef = ref()
const route = useRoute()
const defalutUser = (route.query.user_id ?? '') as string
const state: {
    userInfo: anyObj
} = reactive({
    userInfo: {},
})

const product = 
ref({
    id:'',
    computer_name:'',
    image:'',
})

const businessLoding= ref(false);
const baTable = new baTableClass(
    new baTableApi(url),
    {
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('Id'), prop: 'id', align: 'center', operator: '=', operatorPlaceholder: t('Id'), width: 70 , sortable: 'custom' },
           
           // { label: t('user.moneyLog.User ID'), prop: 'user_id', align: 'center', width: 70 },
            //{ label: t('user.moneyLog.currency'), prop: 'currency', align: 'center', width:120,  operator: 'LIKE'},
           // { label: t('user.moneyLog.money_type'), prop: 'money_type', align: 'center', width: 100, operator: 'LIKE', operatorPlaceholder: t('Fuzzy query')  },
            { label: t('user.moneyLog.business_type'), prop: 'business_type', align: 'center', width: 100, operator: 'LIKE', operatorPlaceholder: t('Fuzzy query')  },
            { label: t('user.moneyLog.User name'), prop: 'user.username', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query') }, 
            { label: t('user.moneyLog.Change balance'), prop: 'money', align: 'center', operator: 'RANGE', sortable: 'custom' },
            { label: t('user.moneyLog.Before change'), prop: 'before', align: 'center', operator: 'RANGE', sortable: 'custom' },
            { label: t('user.moneyLog.After change'), prop: 'after', align: 'center', operator: 'RANGE', sortable: 'custom' },
            //{ label: t('user.moneyLog.profit_rate'), prop: 'profit_rate', align: 'center', width: 100, operator: 'RANGE', sortable: 'custom' },
            // { label: t('user.moneyLog.business_id'), prop: 'business_id', align: 'center', width: 70 },
            //{render: 'slot', slotName: 'business',operator:false},
            // { label: t('user.moneyLog.parent_id'), prop: 'parent_id', align: 'center', width: 70 },
            {
                label: t('user.moneyLog.remarks'),
                prop: 'memo',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
                showOverflowTooltip: true,
            },
            { label: t('Create time'), prop: 'create_time', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
        ],
        dblClickNotEditColumn: ['all'],
    },
    {
        defaultItems: {
            user_id: defalutUser,
            memo: '',
        },
    },
    {},
    {
        onSubmit: () => {
            getUserInfo(baTable.comSearch.form.user_id)
        },
    }
)

baTable.mount()
baTable.getIndex()

provide('baTable', baTable)

const getUserInfo = (userId: string) => {
    if (userId && parseInt(userId) > 0) {
        add(userId).then((res) => {
            state.userInfo = res.data.user
        })
    } else {
        state.userInfo = {}
    }
}

getUserInfo(baTable.comSearch.form.user_id)

watch(
    () => baTable.comSearch.form.user_id,
    (newVal) => {
        baTable.form.defaultItems!.user_id = newVal
        getUserInfo(newVal)
    }
)

function searchProduct(business_id:number){
    businessLoding.value = true
    createAxios({
    url:  ('/admin/nft.Product/index'),
    method: 'get',
    params:{product_id:business_id,purpose:1}
    },
    ).then((res:any)=>{
        // baTable.onTableHeaderAction('refresh', {})
        console.log(res);
        product.value = res.data.list[0];
        
    }).finally(()=>{
        businessLoding.value = false
    })

}

function searchAIProduct(business_id:number){
    businessLoding.value = true
   createAxios({
   url:  ('/admin/ai.Product/index'),
   method: 'get',
   params:{product_id:business_id,purpose:1}
   },
   ).then((res:any)=>{
       // baTable.onTableHeaderAction('refresh', {})
       console.log(res);
       product.value = res.data.list[0];

   }).finally(()=>{
        businessLoding.value = false
    })

}
</script>

<style scoped lang="scss"></style>
