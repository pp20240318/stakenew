<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('ai.product.quick Search Fields') })"
        ></TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef">
            <template #Header>
                <el-table-column :label="t('ai.product.purchase_attribute')" align="center" >
                    <el-table-column :label="t('ai.product.begin_price')" align="center" prop="begin_price" width="125" />
                    <el-table-column :label="t('ai.product.end_price')" align="center" prop="end_price" width="125"/>
                    <el-table-column :label="t('ai.product.valid_day')" align="center" prop="valid_day" width="85"/>
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
    name: 'ai/product',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['weigh-sort', 'edit', 'delete'])

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/ai.Product/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('ai.product.id'), prop: 'id', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('ai.product.computer_name'), width:"85",prop: 'computer_name', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('ai.product.image'), prop: 'image', align: 'center', render: 'image', operator: false },
            {
                render: 'slot',
                slotName: 'Header',
                operator: false,
            },
            { label: t('ai.product.begin_price'), prop: 'begin_price', align: 'center', operator: 'RANGE', sortable: false ,show:false},
            { label: t('ai.product.end_price'), prop: 'end_price', align: 'center', operator: 'RANGE', sortable: false,show:false },
            { label: t('ai.product.valid_day'), prop: 'valid_day', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false ,show:false},
            
            { label: t('ai.product.earning_rate'), prop: 'earning_rate', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('ai.product.status'), prop: 'status', align: 'center', render: 'switch', operator: 'eq', sortable: false, replaceValue: { '0': t('ai.product.status 0'), '1': t('ai.product.status 1') } },
            { label: t('ai.product.is_sale'), width:85,prop: 'is_sale', align: 'center', render: 'switch', operator: 'eq', sortable: false, replaceValue: { '0': t('ai.product.is_sale 0'), '1': t('ai.product.is_sale 1') } },
            { label: t('ai.product.total_sale'), prop: 'total_sale', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('ai.product.sort'), prop: 'sort', align: 'center', operator: 'RANGE', sortable: 'custom' },
            { label: t('ai.product.update_time'), width:110,prop: 'update_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('Operate'), align: 'center', width: 140, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined, 'status', 'is_sale'],
        defaultOrder: { prop: 'sort', order: 'desc' },
    },
    {
        defaultItems: { status: '1', is_sale: '1' ,sort:1},
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
