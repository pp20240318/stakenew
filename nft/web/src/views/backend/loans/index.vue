<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('loans.quick Search Fields') })"
        ></TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef">

            <template #user >
                <!-- 在插槽内，您可以随意发挥，通常使用 el-table-column 组件 -->
                <el-table-column :label="t('loans.borrower_name')"  prop="reference">
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
    name: 'loans',
})
const formatter_rate = (row:any , column: any,cellValue:number) => {
  return cellValue+'%'
}
const formatter_day = (row:any , column: any,cellValue:number) => {
return cellValue+'天'
}
const formatter_date = (row:any , column: any,cellValue:number) => {
    if(cellValue){
        const date = cellValue.toString()
        const year = date.substring(0, 4); // 获取年份
        
        const month = date.substring(4, 6); // 获取月份
        const day = date.substring(6, 8); // 获取日期
        // return date
        return `${year}-${month}-${day}`; // 格式化为 "YYYY-MM-DD" 的形式
    }
    return 
}
const { t } = useI18n()
const tableRef = ref()
const optButtons0: OptButton[] = defaultOptButtons(['edit', 'delete'])
const optButtons1: OptButton[] = [{
                render: 'confirmButton',
                name: 'loansExamine',
                title: 'loans.examine',
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
                    confirmButtonText: t('loans.examinePass'),
                    cancelButtonText: t('loans.examineFail'),
                    confirmButtonType: 'primary',
                    cancelButtonType: 'danger',
                    title: t('loans.Are you sure to examine pass the selected record?'),
                },
                
            },]
const optButtons: OptButton[] = optButtons0.concat(optButtons1)
/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/Loans/'),
    {
        pk: 'loan_id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('loans.loan_id'), prop: 'loan_id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            // { label: t('loans.borrower_name'), prop: 'borrower_name', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            // { label: t('loans.user_id'), prop: 'user_id', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE' },
            // { label: t('loans.user__username'), prop: 'user.username', align: 'center', operatorPlaceholder: t('Fuzzy query'), render: 'tags', operator: 'LIKE' },
            {render: 'slot', slotName: 'user',operator:false},
            { label: t('loans.loan_amount'), prop: 'loan_amount', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('loans.interest_rate'), prop: 'interest_rate', align: 'center', operator: 'RANGE', sortable: false ,formatter:formatter_rate},
            { label: t('loans.loan_term'), prop: 'loan_term', align: 'center', operator: 'RANGE', sortable: false ,formatter:formatter_day},
            { label: t('loans.start_date'), prop: 'start_date',render: 'datetime', align: 'center', width: 160 ,operator: 'RANGE', sortable: 'custom',timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('loans.end_date'), prop: 'end_date', render: 'datetime',align: 'center', width: 160 , operator: 'RANGE', sortable: 'custom' ,timeFormat: 'yyyy-mm-dd hh:MM:ss'},
            { label: t('loans.status'), prop: 'status', align: 'center', render: 'tag', operator: 'eq', sortable: false, replaceValue: { '0': t('loans.status 0'), '1': t('loans.status 1'), '2': t('loans.status 2') } },
            { label: t('loans.repayment_status'), prop: 'repayment_status', align: 'center', render: 'tag', operator: 'eq', sortable: false, replaceValue: { '0': t('loans.repayment_status 0'), '1': t('loans.repayment_status 1') } },
            { label: t('loans.create_time'), prop: 'create_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('loans.update_time'), prop: 'update_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('Operate'), align: 'center', width: 150, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
        defaultOrder: { prop: 'loan_id', order: 'desc' },
    },
    {
        defaultItems: { status: '0', repayment_status: '0' },
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
