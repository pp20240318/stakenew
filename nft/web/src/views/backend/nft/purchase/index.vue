<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add',  'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('nft.purchase.quick Search Fields') })"
        ></TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef">
            <template #test>
                <!-- 在插槽内，您可以随意发挥，通常使用 el-table-column 组件 -->
                <el-table-column :label="t('nft.purchase.ai_attributes')"  align="center">
                <el-table-column prop="product.computer_name" :label="t('nft.purchase.product__computer_name')" width="180" />
                <el-table-column prop="product.price" :label="t('nft.purchase.product__price')" width="180" />
                <el-table-column prop="valid_day" :label="t('nft.purchase.valid_day')" width="180" />
                </el-table-column>  
            </template>

            <template #reference >
                <!-- 在插槽内，您可以随意发挥，通常使用 el-table-column 组件 -->
                <el-table-column :label="t('nft.purchase.reference')"  prop="reference">
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
                <el-table-column :label="t('nft.purchase.user__username')"  prop="reference">
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
    name: 'nft/purchase',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons([ 'delete'])

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/nft.Purchase/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('nft.purchase.id'), prop: 'id', align: 'center', operator: 'RANGE', sortable: false },
            {render: 'slot', slotName: 'reference',operator:false},
            {render: 'slot', slotName: 'user',operator:false},
            // { label: t('nft.purchase.reference'), prop: 'reference.username', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: false },
            // { label: t('nft.purchase.referencetable__username'), prop: 'referenceTable.username', align: 'center', operatorPlaceholder: t('Fuzzy query'), render: 'tags', operator: 'LIKE' },
            // { label: t('nft.purchase.user_id'), prop: 'user_id', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE' },
            // { label: t('nft.purchase.user__username'), prop: 'user.username', align: 'center', operatorPlaceholder: t('Fuzzy query'), render: 'tags', operator: 'LIKE' },
            { label: t('nft.purchase.status'), prop: 'status', align: 'center', render: 'tag', operator: 'eq', sortable: false, replaceValue: { 0: t('nft.purchase.status opt0'), 1: t('nft.purchase.status opt1') } },
            { label: t('nft.purchase.source'), prop: 'source', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: false, sortable: false },
            { label: t('nft.purchase.create_time'), prop: 'create_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('nft.purchase.expire_time'), prop: 'expire_time', align: 'center',width: 160, operator: 'RANGE', sortable: false },
            
            { label: t('nft.purchase.product__computer_name'), render: 'slot', slotName: 'test', operator: false },
            { label: t('nft.purchase.product__computer_name'), prop: 'product.product_id', comSearchRender: 'remoteSelect', remote: {
            // 主键，下拉 value
            pk: 'id',
            // 字段，下拉 label
            field: 'computer_name',
            // 远程接口URL
            // 比如想要获取 user(会员) 表的数据，后台`会员管理`控制器URL为`/index.php/admin/user.user/index`
            // 因为已经通过 CRUD 生成过`会员管理`功能，所以该URL地址可以从`/@/api/controllerUrls`导入使用，如下面的 userUser
            // 该URL地址通常等于对应后台管理功能的`查看`操作请求的URL
            remoteUrl: '/index.php/admin/nft.product/index',
            // 额外的请求参数
            params: {},
        },show:false},
            // { label: t('nft.purchase.product__price'), prop: 'product.price', align: 'center', operatorPlaceholder: t('Fuzzy query'), render: 'tags', operator: 'LIKE' },
            // { label: t('nft.purchase.valid_day'), prop: 'valid_day', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('nft.purchase.bonus'), prop: 'bonus', align: 'center', operator: false, sortable: false },
            { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: { status: 'opt0' ,bonus:0,valid_day:1,},
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
