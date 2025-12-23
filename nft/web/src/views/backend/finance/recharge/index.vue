<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('finance.recharge.quick Search Fields') })"
        ></TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef">
            <template #user >
                <!-- 在插槽内，您可以随意发挥，通常使用 el-table-column 组件 -->
                <el-table-column :label="t('finance.recharge.user_id')"  prop="reference">
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

            <template #sourceHeader>
                <el-table-column :label="t('finance.recharge.source')" align="center" >
                    <el-table-column :label="t('finance.recharge.source_currency')" align="center" prop="source_currency"  />
                    <el-table-column :label="t('finance.recharge.source_num')" align="center" prop="source_num" width="90"/>
                </el-table-column>
                <el-table-column :label="t('finance.recharge.target')" align="center">
                    <el-table-column :label="t('finance.recharge.target_currency')" align="center" prop="target_currency" />
                    <el-table-column :label="t('finance.recharge.target_num')" align="center" prop="target_num" width="90"/>
                    <el-table-column :label="t('finance.recharge.into_account')" align="center" prop="into_account" width="90"/>
                </el-table-column>

            </template>


            <template #admin >
                <!-- 在插槽内，您可以随意发挥，通常使用 el-table-column 组件 -->
                <el-table-column :label="t('finance.recharge.salesman')"  prop="reference">
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

defineOptions({
    name: 'finance/recharge',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons0: OptButton[] = defaultOptButtons(['edit', 'delete'])
const optButtons1: OptButton[] = [{
                render: 'confirmButton',
                name: 'rechargeExamine',
                title: 'finance.recharge.examine',
                text: '审核',
                type: 'primary',
                icon: 'el-icon-Edit',
                class: 'table-row-edit',
                disabledTip: false,
                attr:{},
                display(row, field) {
                    
                    if(row['status']==0){
                        return true
                    }
                    else
                        return false
                },
                popconfirm: {
                    confirmButtonText: t('finance.recharge.examinePass'),
                    cancelButtonText: t('finance.recharge.examineFail'),
                    confirmButtonType: 'primary',
                    cancelButtonType: 'danger',
                    title: t('finance.recharge.Are you sure to examine pass the selected record?'),
                },
                
            },]
const optButtons: OptButton[] = optButtons0.concat(optButtons1)

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/finance.Recharge/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('finance.recharge.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            { label: t('finance.recharge.order_id'), prop: 'order_id', align: 'center',width: 80, operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            // { label: t('finance.recharge.user_id'), prop: 'user_id', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE' },
            // { label: t('finance.recharge.user__id'), prop: 'user.id', align: 'center', operatorPlaceholder: t('Fuzzy query'), render: 'tags', operator: 'LIKE' },
            {render: 'slot', slotName: 'user',operator:false},
           
            {
                render: 'slot',
                slotName: 'sourceHeader',
                operator: false,
            },
            // { label: t('finance.recharge.source_currency'), prop: 'sourceCurrencyTable.name', align: 'center', operatorPlaceholder: t('Fuzzy query'), render: 'tags', operator: 'LIKE' },
            // { label: t('finance.recharge.source_num'), prop: 'source_num', align: 'center', operator: 'RANGE', sortable: false },
            // { label: t('finance.recharge.target_currency'), prop: 'targetCurrencyTable.name', align: 'center', operatorPlaceholder: t('Fuzzy query'), render: 'tags', operator: 'LIKE' },
            // { label: t('finance.recharge.target_num'), prop: 'target_num', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('finance.recharge.exchange_rate'), prop: 'exchange_rate', align: 'center', sortable: false },
            
            { label: t('finance.recharge.username'), prop: 'username',width: 90, align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false ,showOverflowTooltip:true},
            { label: t('finance.recharge.payout_address'),width: 90, prop: 'payout_address', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('finance.recharge.status'), width: 60,prop: 'status', align: 'center', render: 'tag', operator: 'eq', sortable: false, replaceValue: { '0': t('finance.recharge.status 0'), '1': t('finance.recharge.status 1'), '2': t('finance.recharge.status 2') } },
            { label: t('finance.recharge.memo'), width: 60,prop: 'memo', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            // { label: t('finance.recharge.salesman'), width: 70,prop: 'salesman', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            {render: 'slot', slotName: 'admin',operator:false},
            { label: t('finance.recharge.image'), width: 70,prop: 'image', align: 'center', render: 'image', operator: false },
            { label: t('finance.recharge.create_time'),width: 90, prop: 'create_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', timeFormat: 'yyyy-mm-dd hh:MM:ss' ,showOverflowTooltip:true},
            { label: t('Operate'), align: 'center', width: 150, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: { status: '0',exchange_rate:'1' },
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
})
</script>

<style scoped lang="scss"></style>
