<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('trade.orders.quick Search Fields') })"
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
    name: 'trade/orders',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/trade.Orders/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            /* { label: t('trade.orders.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            { label: t('trade.orders.user_id'), prop: 'user_id', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE' },*/
            { label: t('trade.orders.user__username'), prop: 'user.username', align: 'center', operatorPlaceholder: t('Fuzzy query'), render: 'tags', operator: 'LIKE' },
            { label: t('trade.orders.coin__name'), prop: 'coin.name', align: 'center', operatorPlaceholder: t('Fuzzy query'), render: 'tags', operator: 'LIKE' },
           /*  { label: t('trade.orders.coin_id'), prop: 'coin_id', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE' }, 
            { label: t('trade.orders.trading_symbol'), prop: 'trading_symbol', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('trade.orders.coin_symbol'), prop: 'coin_symbol', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },*/
            { label: t('trade.orders.trade_type'), prop: 'trade_type', align: 'center', render: 'tag', operator: 'eq', sortable: false, replaceValue: { 'up': t('trade.orders.trade_type  up'), 'down': t('trade.orders.trade_type  down') } },
            { label: t('trade.orders.amount'), prop: 'amount', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('trade.orders.profit_rate'), prop: 'profit_rate', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('trade.orders.trade_time'), prop: 'trade_time', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
           /*  { label: t('trade.orders.total_seconds'), prop: 'total_seconds', align: 'center', operator: 'RANGE', sortable: false },*/
            { label: t('trade.orders.remaining_seconds'), prop: 'remaining_seconds', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('trade.orders.entry_price'), prop: 'entry_price', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('trade.orders.exit_price'), prop: 'exit_price', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('trade.orders.profit_loss'), prop: 'profit_loss', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('trade.orders.result'), prop: 'result', align: 'center', render: 'tag', operator: 'eq', sortable: false, replaceValue: { win: '赢', lose: '输', draw: '平' } },
            { label: t('trade.orders.money_type'), prop: 'money_type', align: 'center', render: 'tag', operator: 'eq', sortable: false, replaceValue: { '1': t('trade.orders.money_type  1'), '2': t('trade.orders.money_type  2') } },
            { label: t('trade.orders.status'), prop: 'status', align: 'center', operator: 'RANGE', render: 'tag', sortable: false, replaceValue: { 0: t('trade.orders.status0'), 1: t('trade.orders.status1'), 2: t('trade.orders.status2'), 3: t('trade.orders.status3') } },
            { label: t('trade.orders.created_at'), prop: 'created_at', align: 'center', operator: 'eq', sortable: 'custom', width: 160 },
            /* { label: t('trade.orders.updated_at'), prop: 'updated_at', align: 'center', operator: 'eq', sortable: 'custom', width: 160 },
            { label: t('trade.orders.expired_at'), prop: 'expired_at', align: 'center', operator: 'eq', sortable: 'custom', width: 160 },*/
            { label: t('trade.orders.settled_at'), prop: 'settled_at', align: 'center', operator: 'eq', sortable: 'custom', width: 160 },
            { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: { created_at: 'CURRENT_TIMESTAMP', updated_at: 'CURRENT_TIMESTAMP' },
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
