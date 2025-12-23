<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add',  'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('ai.pledge.quick Search Fields') })"
        ></TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef">
            <template #reference >
                <!-- 在插槽内，您可以随意发挥，通常使用 el-table-column 组件 -->
                <el-table-column :label="t('ai.pledge.reference')"  prop="reference">
                    <template #default="scope">
                        
                            <el-button size="small"  v-if="!scope.row.referenceTable">无</el-button>
                        <el-popover
                            placement="top-start"
                            :width="400"
                            trigger="hover"
                            v-if="scope.row.referenceTable"
                        >
                            <template #reference>
                            <el-button size="small">{{scope.row.referenceTable.username}}</el-button>
                            </template>
                            <el-descriptions   direction="vertical" :column="3">
                                <el-descriptions-item label="id" min-width="40">{{scope.row.referenceTable.id}}</el-descriptions-item>
                                <el-descriptions-item :label="t('username')">{{scope.row.user.username}}</el-descriptions-item>
                                <el-descriptions-item :label="t('mobile')">{{scope.row.user.mobile}}</el-descriptions-item>
                                <!-- <el-descriptions-item label="用户名">{{scope.row.referenceTable.id}}</el-descriptions-item> -->
                                <!-- <el-descriptions-item label="车牌">{{scope.row.car.carnum}}</el-descriptions-item>
                                <el-descriptions-item label="车架号">{{scope.row.car.vin}}</el-descriptions-item> -->
                                
                            </el-descriptions>
                        </el-popover>
                    </template>
                </el-table-column>
            </template>

            <template #user >
                <!-- 在插槽内，您可以随意发挥，通常使用 el-table-column 组件 -->
                <el-table-column :label="t('ai.pledge.referencetable__username')"  prop="reference">
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
                                <el-descriptions-item :label="t('username')">{{scope.row.user.username}}</el-descriptions-item>
                                <el-descriptions-item :label="t('mobile')">{{scope.row.user.mobile}}</el-descriptions-item>
                              
                                
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
    name: 'ai/pledge',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons0: OptButton[] = defaultOptButtons([ 'delete'])
const optButtons1: OptButton[] = [{
                render: 'confirmButton',
                name: 'pledgeExamine',
                title: 'ai.pledge.examine',
                text: '审核',
                type: 'primary',
                icon: 'el-icon-Edit',
                class: 'table-row-edit',
                disabledTip: false,
                attr:{},
                display(row, field) {
                    
                    if(row['status']==2){
                        return true
                    }
                    else
                        return false
                },
                popconfirm: {
                    confirmButtonText: t('ai.pledge.examinePass'),
                    cancelButtonText: t('ai.pledge.examineFail'),
                    confirmButtonType: 'primary',
                    cancelButtonType: 'danger',
                    title: t('ai.pledge.Are you sure to examine pass the selected record?'),
                },
                
            },]
const optButtons: OptButton[] = optButtons0.concat(optButtons1)

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/ai.Pledge/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('ai.pledge.id'), prop: 'id', align: 'center', operator: 'RANGE', sortable: false },
            {render: 'slot', slotName: 'reference',operator:false},
            {render: 'slot', slotName: 'user',operator:false},
            // { label: t('ai.pledge.reference'), prop: 'reference', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE' },
            // { label: t('ai.pledge.referencetable__username'), prop: 'referenceTable.username', align: 'center', operatorPlaceholder: t('Fuzzy query'), render: 'tags', operator: 'LIKE' },
            { label: t('ai.pledge.status'), prop: 'status', align: 'center', render: 'tag', operator: 'eq', sortable: false, replaceValue: { '0': t('ai.pledge.status 0'), '1': t('ai.pledge.status 1'), '2': t('ai.pledge.status 2'), '3': t('ai.pledge.status 3') } },
            { label: t('ai.pledge.source'), prop: 'source', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('ai.pledge.create_time'), prop: 'create_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('ai.pledge.confirm_time'), prop: 'confirm_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('ai.pledge.expire_time'), prop: 'expire_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('ai.pledge.productnametable__computer_name'), prop: 'productNameTable.computer_name', align: 'center', operatorPlaceholder: t('Fuzzy query'), render: 'tags', operator: 'LIKE' },
            { label: t('ai.pledge.price'), prop: 'price', align: 'center', operator: 'RANGE', sortable: false },
            // { label: t('ai.pledge.valid_day'), prop: 'valid_day', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('Operate'), align: 'center', width: 150, render: 'buttons', buttons: optButtons, operator: false },
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
})
</script>

<style scoped lang="scss"></style>
