<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('finance.withdrawl.quick Search Fields') })"
        ></TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef">
            <template #user >
                <!-- 在插槽内，您可以随意发挥，通常使用 el-table-column 组件 -->
                <el-table-column :label="t('finance.withdrawl.user__username')"  prop="reference">
                    <template #default="scope">
                        
                        <el-button size="small"  v-if="!scope.row.user">无</el-button>
                        <el-popover
                            placement="top-start"
                            :width="400"
                            trigger="hover"
                            v-if="scope.row.user"
                        >
                            <template #reference>
                            <el-button size="small">{{scope.row.user.username}}</el-button>
                            </template>
                            <el-descriptions   direction="vertical" :column="3">
                                <el-descriptions-item label="id" min-width="40">{{scope.row.user.id}}</el-descriptions-item>
                                <el-descriptions-item label="用户名">{{scope.row.user.username}}</el-descriptions-item>
                                <el-descriptions-item label="手机号">{{scope.row.user.mobile}}</el-descriptions-item>
                              
                                
                            </el-descriptions>
                            </el-popover>
                    </template>
                </el-table-column>
            </template>


            <template #test>
                <!-- 在插槽内，您可以随意发挥，通常使用 el-table-column 组件 -->
                <!-- 还可以继续使用 el-table-column 组件本身的插槽 -->
                <el-table-column prop="name" :label="t('finance.withdrawl.name')" width="62">
                    <template #default="scope">
                        {{ scope.row['name'] }} 
                        <el-button type="primary" :icon="Edit" size="small" v-if="!scope.row['name'] " plain @click="showInfo(scope.row,scope.field)">  </el-button>
                        
                    </template>
                </el-table-column>
                <el-table-column prop="bank_name" :label="t('finance.withdrawl.bank_name')" width="85">
                    <template #default="scope">
                        {{ scope.row['bank_name'] }} 
                        <el-button type="primary" :icon="Edit" size="small" v-if="!scope.row['bank_name'] " plain @click="showInfo(scope.row,scope.field)">  </el-button>
                    </template>
                </el-table-column>
                <el-table-column prop="bank_account" :label="t('finance.withdrawl.bank_account')" width="85">
                    <template #default="scope">
                        {{ scope.row['bank_account'] }} 
                        <el-button type="primary" :icon="Edit" size="small" v-if="!scope.row['bank_account']" plain @click="showInfo(scope.row,scope.field)">  </el-button>
                    </template>
                </el-table-column>
                <el-table-column prop="legal_tender" :label="t('finance.withdrawl.legal_tender')" width="62">
                    <template #default="scope">
                        {{ scope.row['legal_tender'] }} 
                        <el-button type="primary" :icon="Edit" size="small" v-if="!scope.row['legal_tender']" plain @click="showInfo(scope.row,scope.field)">  </el-button>
                    </template>
                </el-table-column>
                <el-table-column prop="branch" :label="t('finance.withdrawl.branch')" width="85">
                    <template #default="scope">
                        {{ scope.row['branch'] }} 
                        <el-button type="primary" :icon="Edit" size="small" v-if="!scope.row['branch']" plain @click="showInfo(scope.row,scope.field)">  </el-button>
                    </template>
                </el-table-column>
                <el-table-column prop="bank_code" :label="t('finance.withdrawl.bank_code')" width="85">
                    <template #default="scope">
                        {{ scope.row['bank_code'] }} 
                        <el-button type="primary" :icon="Edit" size="small" v-if="!scope.row['bank_code']" plain @click="showInfo(scope.row,scope.field)">  </el-button>
                    </template>
                </el-table-column>
            </template>


            <template #admin >
                <!-- 在插槽内，您可以随意发挥，通常使用 el-table-column 组件 -->
                <el-table-column :label="t('finance.withdrawl.salesman')"  prop="reference">
                    <template #default="scope">
                        
                        <el-button size="small"  v-if="!scope.row.admin">无</el-button>
                        <el-popover
                            placement="top-start"
                            :width="400"
                            trigger="hover"
                            v-if="scope.row.admin"
                        >
                            <template #reference>
                            <el-button size="small">{{scope.row.admin.username}}</el-button>
                            </template>
                            <el-descriptions   direction="vertical" :column="3">
                                <el-descriptions-item label="id" min-width="40">{{scope.row.admin.id}}</el-descriptions-item>
                                <el-descriptions-item :label="t('username')">{{scope.row.admin.username}}</el-descriptions-item>
                                <el-descriptions-item :label="t('mobile')">{{scope.row.admin.mobile}}</el-descriptions-item>
                              
                                
                            </el-descriptions>
                            </el-popover>
                    </template>
                </el-table-column>
            </template>

        </Table>

        <!-- 表单 -->
        <PopupForm />

        <bankInfo/>
    </div>
</template>

<script setup lang="ts">
import { onMounted, provide, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import { baTableApi } from '/@/api/common'
import { defaultOptButtons } from '/@/components/table'
import TableHeader from '/@/components/table/header/index.vue'
import Table from '/@/components/table/index.vue'
import baTableClass from '/@/utils/baTable'
import bankInfo from './bankInfo.vue'
import {
  Check,
  Delete,
  Edit,
  Message,
  Search,
  Star,
} from '@element-plus/icons-vue'

defineOptions({
    name: 'finance/withdrawl',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons0: OptButton[] = defaultOptButtons(['edit', 'delete'])
const optButtons1: OptButton[] = [{
                render: 'confirmButton',
                name: 'fieldExamine',
                title: 'finance.withdrawl.examine',
                text: '审核',
                type: 'primary',
                icon: 'el-icon-Edit',
                class: 'table-row-edit',
                disabledTip: false,
                attr:{},
                display(row, field) {
                    // status 值为 'opt0' 表示待审核
                    return row['status'] == 'opt0';
                },
                popconfirm: {
                    confirmButtonText: t('finance.withdrawl.examinePass'),
                    cancelButtonText: t('finance.withdrawl.examineFail'),
                    confirmButtonType: 'primary',
                    cancelButtonType: 'danger',
                    title: t('finance.withdrawl.Are you sure to examine pass the selected record?'),
                },
                
            },]
const optButtons: OptButton[] = optButtons0.concat(optButtons1)
/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/finance.Withdrawl/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            // { label: t('finance.withdrawl.id'), prop: 'id', align: 'center', width: 60, operator: 'RANGE', sortable: 'custom' },
            // { label: t('finance.withdrawl.user__username'), prop: 'user.username', align: 'center', operatorPlaceholder: t('Fuzzy query'), render: 'tags', operator: 'LIKE' },
            {render: 'slot', slotName: 'user',operator:false},
            { label: t('finance.withdrawl.target_currency'), prop: 'target_currency',  render: 'tag', width: 70,align: 'center',sortable: false },
            { label: t('finance.withdrawl.target_num'), prop: 'target_num', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('finance.withdrawl.exchange_rate'), prop: 'exchange_rate', align: 'center', width: 100,operator: 'RANGE', sortable: false },
            { label: t('finance.withdrawl.usdtold_num'), prop: 'usdtold_num', align: 'center', width:100,operator: 'RANGE', sortable: false },
            { label: t('finance.withdrawl.usdt_num'), prop: 'usdt_num', align: 'center', width: 100,operator: 'RANGE', sortable: false },

            // { label: t('finance.withdrawl.username'), prop: 'username', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('finance.withdrawl.deposit_address'), prop: 'deposit_address', align: 'center',width: 90, operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false ,showOverflowTooltip:true},
            { label: t('finance.withdrawl.payout_address'), prop: 'payout_address', align: 'center', width: 90,operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('finance.withdrawl.status'), prop: 'status', align: 'center', render: 'tag',width: 60, operator: 'eq', sortable: false, replaceValue: { 'opt0': t('finance.withdrawl.status 0'), 'opt1': t('finance.withdrawl.status 1'), 'opt2': t('finance.withdrawl.status 2') } },
             
            { label: t('finance.withdrawl.memo'), prop: 'memo', align: 'center', width: 60,operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false }, 
            {render: 'slot', slotName: 'admin',operator:false},
            { label: t('finance.withdrawl.create_time'), prop: 'create_time', width: 85,align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom',  timeFormat: 'yyyy-mm-dd hh:MM:ss',showOverflowTooltip:true},
            { label: t('finance.withdrawl.update_time'), prop: 'update_time', width: 85,align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom',  timeFormat: 'yyyy-mm-dd hh:MM:ss' ,showOverflowTooltip:true},
            { label: t('Operate'), align: 'center', width: 150, render: 'buttons', buttons: optButtons, operator: false ,fixed:'right'},
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: { status: '0' },
    }
)

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
    console.log(baTable.table)
    console.log(optButtons)
})


function showInfo(row:any,field:any){
   
        console.info('%c-------详情按钮被点击了--------', 'color:blue')
        console.log('接受到行数据和列数据', row, field)
        console.log('%c赋值：baTable.table.extend!.showInfo = true', 'color:red')

        // 在 extend 上自定义一个变量标记详情弹窗显示状态，详情组件内以此判断显示即可！
        baTable.table.extend!.showInfo = true

        // 您也可以使用 baTable.form.operate，默认情况它有三个值`Add、Edit、空字符串`，前两个值将显示添加和编辑弹窗

        // 您也可以再来个 loading 态，然后请求详情数据等
        baTable.table.extend!.infoLoading = true
        setTimeout(() => {
            baTable.table.extend!.infoData = row
            baTable.table.extend!.infoLoading = false
            
        }, 1000)
    
}
</script>

<style scoped lang="scss"></style>
