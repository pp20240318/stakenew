<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('user.user.quick Search Fields') })"
        ></TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef">
            <template #reference >
                <!-- 在插槽内，您可以随意发挥，通常使用 el-table-column 组件 -->
                <el-table-column :label="t('user.user.reference')"  prop="reference">
                    <template #default="scope">
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
                                <el-descriptions-item :label="t('username')">{{scope.row.referenceTable.username}}</el-descriptions-item>
                                <el-descriptions-item :label="t('mobile')">{{scope.row.referenceTable.mobile}}</el-descriptions-item>
                                <!-- <el-descriptions-item label="用户名">{{scope.row.referenceTable.id}}</el-descriptions-item> --> 
                                
                            </el-descriptions>
                        </el-popover>
                    </template>
                </el-table-column>
            </template>

            <template #agent >
                <!-- 在插槽内，您可以随意发挥，通常使用 el-table-column 组件 -->
                <el-table-column :label="t('user.user.agent')"  prop="agent">
                    <template #default="scope">
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
                                <el-descriptions-item label="用户名">{{scope.row.admin.username}}</el-descriptions-item>
                                <el-descriptions-item label="手机号">{{scope.row.admin.mobile}}</el-descriptions-item> 
                                
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
    name: 'user/user',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])



/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/user.User/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('user.user.id'), prop: 'id', align: 'center', width: 70, operator: 'eq', sortable: 'custom' }, 
             {render: 'slot', slotName: 'reference',operator:false},
            { label: t('user.user.username'), prop: 'username', align: 'center', operatorPlaceholder: t('Fuzzy query'),  operator: 'like', sortable: false }, 
            { label: t('user.user.email'), prop: 'email', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            //{ label: t('user.user.mobile'), prop: 'mobile', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            //{ label: t('user.user.credit'), prop: 'score', align: 'center', operator: false, sortable: false },
            { label: t('user.user.invite_code'), prop: 'invite_code', align: 'center', operator: false, sortable: false },
            { label: t('user.user.true_name'), prop: 'true_name', align: 'center', render: 'tag', operator: false, sortable: false, replaceValue: { '0': t('user.user.true_name 0'), '1': t('user.user.true_name 1') } },
            //{ label: t('user.user.is_auction'), prop: 'is_auction', align: 'center', render: 'switch', operator: false, sortable: false, replaceValue: { '0': t('user.user.is_auction 0'), '1': t('user.user.is_auction 1') } },
            
            { label: t('user.user.salesman'), prop: 'salesman', align: 'center', render: 'switch', operator: 'eq', sortable: false, replaceValue: { '0': t('user.user.salesman 0'), '1': t('user.user.salesman 1') } },
            //{render: 'slot', slotName: 'agent', operator:false},

            { label: t('user.user.salesman'), prop: 'agent', comSearchRender: 'remoteSelect', remote: {
            // 主键，下拉 value
            pk: 'id',
            // 字段，下拉 label
            field: 'username',
            // 远程接口URL 
            // 比如想要获取 user(会员) 表的数据，后台`会员管理`控制器URL为`/index.php/admin/user.user/index`
            // 因为已经通过 CRUD 生成过`会员管理`功能，所以该URL地址可以从`/@/api/controllerUrls`导入使用，如下面的 userUser
            // 该URL地址通常等于对应后台管理功能的`查看`操作请求的URL
            remoteUrl: '/index.php/admin/user.user/agentindex',
            // 额外的请求参数
            params: {},
        },show:false},
            { label: t('user.user.money'), prop: 'money', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('user.user.virtualmoney'), prop: 'virtualmoney', align: 'center', operator: 'RANGE', sortable: false },
            //{ label: t('user.user.recharge_num'), prop: 'recharge_num', align: 'center', sortable: false },
            //{ label: t('user.user.withdrawl_num'), prop: 'withdrawl_num', align: 'center', sortable: false }, 
            //{ label: t('user.user.team_num'), prop: 'team_num', align: 'center', operator: 'RANGE', sortable: false },
            //{ label: t('user.user.reference_num'), prop: 'reference_num', align: 'center', sortable: false }, 
            //{ label: t('user.user.subordinate_today_recharge'), prop: 'subordinate_today_recharge', align: 'center', sortable: false },
            //{ label: t('user.user.subordinate_today_withdrawal'), prop: 'subordinate_today_withdrawal', align: 'center', sortable: false }, 
           // { label: t('user.user.subordinate_total_recharge'), prop: 'subordinate_total_recharge', align: 'center', sortable: false },
           // { label: t('user.user.subordinate_total_withdrawal'), prop: 'subordinate_total_withdrawal', align: 'center', sortable: false }, 
            // { label: t('user.user.withdrawl_num'), prop: 'withdrawl_num', align: 'center', sortable: false },
            //{ label: t('user.user.loanmoney'), prop: 'lonanmoney', align: 'center', operator: 'RANGE', sortable: false },
            // { label: t('user.user.last_login_ip'), prop: 'last_login_ip', align: 'center', operator: false, sortable: false },
            // { label: t('user.user.join_time'), prop: 'create_time', align: 'center', operator: 'RANGE', sortable: false, width: 160,timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('user.user.status'), prop: 'status', align: 'center', render: 'switch', operator: 'eq', sortable: false, replaceValue: { '0': t('user.user.status 0'), '1': t('user.user.status 1') } },
            { label: t('Operate'), align: 'center', width: 150, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined, 'true_name', 'is_auction', 'salesman', 'gender', 'status'],
    },
    {
        defaultItems: { is_auction: '1', salesman: '1', gender: '1', status: '1',money:'0' ,credit:'0',lonanmoney:'0'},
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
