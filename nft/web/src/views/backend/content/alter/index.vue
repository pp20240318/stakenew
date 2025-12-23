<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('content.alter.quick Search Fields') })"
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
    name: 'content/alter',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/content.Alter/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            // { label: t('content.alter.language'), prop: 'language', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE' },]
            { label: t('content.alter.id'), prop: 'id', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE' ,width: 50},
            { label: t('content.alter.language'), prop: 'languageTable.name', align: 'center', operatorPlaceholder: t('Fuzzy query'), render: 'tags', operator: 'LIKE' },
            { label: t('content.alter.title'), prop: 'title', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('content.alter.content_text'), prop: 'content_text', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false,showOverflowTooltip:true },
            { label: t('content.alter.start_time'), prop: 'start_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('content.alter.end_time'), prop: 'end_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('content.alter.user__username'), prop: 'user.username', align: 'center', render: 'tags', operator: false },
            { label: t('content.alter.user__username'), prop: 'user_ids', align: 'center', operator: 'FIND_IN_SET', show: false, comSearchRender: 'remoteSelect', remote: { pk: 'ba_user.id', field: 'username', remoteUrl: '/admin/user.User/index', multiple: true } },
            { label: t('content.alter.show'), prop: 'show', align: 'center', render: 'switch', operator: 'eq', sortable: false, replaceValue: { '0': t('content.alter.show 0'), '1': t('content.alter.show 1') } },
            { label: t('content.alter.create_time'), prop: 'create_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined, 'show'],
    },
    {
        defaultItems: { show: '1' },
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
