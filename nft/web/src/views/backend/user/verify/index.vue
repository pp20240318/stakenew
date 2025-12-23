<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('user.verify.quick Search Fields') })"
        ></TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef">
            <template #user >
                <el-table-column :label="t('user.verify.user_id')"  prop="user">
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
                                <!-- <el-descriptions-item label="用户名">{{scope.row.referenceTable.id}}</el-descriptions-item> -->
                                <!-- <el-descriptions-item label="车牌">{{scope.row.car.carnum}}</el-descriptions-item>
                                <el-descriptions-item label="车架号">{{scope.row.car.vin}}</el-descriptions-item> -->
                                
                            </el-descriptions>
                        </el-popover>
                    </template>
                </el-table-column>
            </template>

            <template #verify >
                <!-- 在插槽内，您可以随意发挥，通常使用 el-table-column 组件 -->
                <el-table-column :label="t('user.verify.status2')" width="140">
                    <template #default="scope">
                        <el-tag type="primary" v-if="scope.row.status2 == 3" style="margin-right:10px">{{t('user.verify.status opt0')}}</el-tag>
                        <el-tag type="success" v-if="scope.row.status2==1">{{t('user.verify.status opt1')}}</el-tag>
                        <el-tag type="danger" v-if="scope.row.status2==2">{{t('user.verify.status opt2')}}</el-tag>
                        <el-popconfirm
                            :confirm-button-text="t('user.verify.examinePass')"
                            :cancel-button-text="t('user.verify.examineFail')"
                            icon-color="#626AEF"
                            :title="t('user.verify.Are you sure to examine pass the selected record?')"
                            @confirm="confirmEvent(scope)"
                            @cancel="cancelEvent(scope)"
                            content="111"
                        >
                            <template #reference>
                                <div style="display: inline-flex;vertical-align: middle;">
                                <el-tooltip  :content="t('审核')" placement="top">
                                    <el-button size="small" v-if="scope.row.status2 == 3" plain  circle> <Icon name="el-icon-Edit" color="#000000" size="20" /></el-button>
                                 </el-tooltip>
                                </div>
                            </template>
                        </el-popconfirm>
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
import createAxios from '/@/utils/axios'
defineOptions({
    name: 'user/verify',
})

const { t } = useI18n()
const tableRef = ref()
const showButton=ref(false)
const showhighButton=ref(false)
const shouldShowButton=ref(true)
const optButtons0: OptButton[] = defaultOptButtons(['edit', 'delete'])
const optButtons1: OptButton[] = [{
                render: 'confirmButton',
                name: 'userExamine',
                title: 'user.verify.examine',
                text: '审核',
                type: 'primary',
                icon: 'el-icon-Edit',
                class: 'table-row-edit',
                disabledTip: false,
                attr:{},
                display(row, field) {
                    
                    if(row['status']==3){
                        return true
                    }
                    else
                        return false
                },
                popconfirm: {
                    confirmButtonText: t('user.verify.examinePass'),
                    cancelButtonText: t('user.verify.examineFail'),
                    confirmButtonType: 'primary',
                    cancelButtonType: 'danger',
                    title: t('user.verify.Are you sure to examine pass the selected record?'),
                },
                
            },]
const optButtons: OptButton[] = optButtons0.concat(optButtons1)
/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/user.Verify/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('user.verify.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            
            //{render: 'slot', slotName: 'user',operator:false},
           
            { label: t('user.verify.user__username'), prop: 'user.username', align: 'center', operatorPlaceholder: t('Fuzzy query'), render: 'tags', operator: 'LIKE' },
            { label: t('user.verify.name'), prop: 'name', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('user.verify.certificate'), prop: 'certificate', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('user.verify.img_front'), prop: 'img_front', align: 'center', render: 'image', operator: false }, 
            {render: 'slot', slotName: 'verify',operator:false},
            // { label: t('user.verify.status'), prop: 'status', align: 'center', render: 'tag', operator: 'eq', sortable: false, replaceValue: {'3': t('user.verify.status opt0'),'1': t('user.verify.status opt1'),'2': t('user.verify.status opt2') } },
            { label: t('user.verify.memo'), prop: 'memo', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('user.verify.create_time'), prop: 'create_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('user.verify.update_time'), prop: 'update_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('Operate'), align: 'center', width: 150, render: 'buttons', buttons: optButtons0, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: { status: '3' },
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

function confirmEvent(scope:any){
     createAxios({
    url:  ('/admin/user.Verify/examine2'),
    method: 'post',
    data:{status:'1',row:scope.row}
    },
    {
        showSuccessMessage: true,
        showCodeMessage:true,
    }
    ).then((res:any)=>{
        baTable.onTableHeaderAction('refresh', {})
        console.log(res);
    })

}
function cancelEvent(scope:any){
     createAxios({
            url:  ('/admin/user.Verify/examine2'),
            method: 'post',
            data:{status:'2',row:scope.row}
        },
        {
            showSuccessMessage: true,
            showCodeMessage:true,
        }
        ).then((res)=>{
            baTable.onTableHeaderAction('refresh', {})
            console.log(res);
        })
}


function confirmEvent1(scope:any){
     createAxios({
    url:  ('/admin/user.Verify/examine'),
    method: 'post',
    data:{status:'1',row:scope.row}
    },
    {
        showSuccessMessage: true,
        showCodeMessage:true,
    }
    ).then((res:any)=>{
        baTable.onTableHeaderAction('refresh', {})
        console.log(res);
    })

}
function cancelEvent1(scope:any){
     createAxios({
            url:  ('/admin/user.Verify/examine'),
            method: 'post',
            data:{status:'2',row:scope.row}
        },
        {
            showSuccessMessage: true,
            showCodeMessage:true,
        }
        ).then((res)=>{
            baTable.onTableHeaderAction('refresh', {})
            console.log(res);
        })
}


function handleMouseEnter() {
      if (shouldShowButton.value) {
        showButton.value = true;
      }
    }
function handleMouseLeave() {
    if (shouldShowButton.value) {
        showButton.value = false;
    }
}



function handleMouseEnter1() {
      if (shouldShowButton.value) {
        showhighButton.value = true;
      }
    }
function handleMouseLeave1() {
    if (shouldShowButton.value) {
        showhighButton.value = false;
    }
}

</script>

<style scoped lang="scss"></style>
