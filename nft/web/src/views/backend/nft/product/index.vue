<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('nft.product.quick Search Fields') })"
        ></TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef"></Table>

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
    name: 'nft/product',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['weigh-sort', 'edit', 'delete'])

const formatter_rate = (row:any , column: any,cellValue:number) => {
  return cellValue*100+'%'
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

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/nft.Product/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('nft.product.id'), prop: 'id', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('nft.product.computer_name'), prop: 'computer_name', align: 'center',width:90, operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('nft.product.image'), prop: 'image', align: 'center', render: 'image', operator: false },
            { label: t('nft.product.price'), prop: 'price', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('nft.product.begin_time'), prop: 'begin_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('nft.product.end_time'), prop: 'end_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('nft.product.earning_rate'), prop: 'earning_rate', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('nft.product.status'), prop: 'status', align: 'center', render: 'switch', operator: 'eq', sortable: false, replaceValue: { '0': t('nft.product.status 0'), '1': t('nft.product.status 1') } },
            { label: t('nft.product.is_sale'), prop: 'is_sale', align: 'center',width:90, render: 'switch', operator: 'eq', sortable: false, replaceValue: { '0': t('nft.product.is_sale 0'), '1': t('nft.product.is_sale 1') } },
            { label: t('nft.product.view_count'), prop: 'view_count', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('nft.product.level__name'), prop: 'level.name', align: 'center',width:90, operatorPlaceholder: t('Fuzzy query'), render: 'tags', operator: 'LIKE' },
            { label: t('nft.product.sort'), prop: 'sort', align: 'center', operator: 'RANGE', sortable: 'custom' },
            { label: t('nft.product.description'), prop: 'description', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false,showOverflowTooltip:true },
            { label: t('nft.product.author'), prop: 'author', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('nft.product.nftaddress'), prop: 'nftaddress', align: 'center',width:90, operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false ,showOverflowTooltip:true},
            { label: t('nft.product.update_time'), prop: 'update_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('Operate'), align: 'center', width: 140, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined, 'status', 'is_sale'],
        defaultOrder: { prop: 'sort', order: 'desc' },
    },
    {
        defaultItems: { status: '1', is_sale: '1', description: '',sort:1, },
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
