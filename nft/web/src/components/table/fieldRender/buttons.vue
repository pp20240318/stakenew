<template>
    <div v-memo="[field]">
        <template v-for="(btn, idx) in field.buttons" :key="idx">
            <template v-if="btn.display ? btn.display(row, field) : true">
                <!-- 常规按钮 -->
                <el-button
                    v-if="btn.render == 'basicButton'"
                    v-blur
                    @click="onButtonClick(btn)"
                    :class="btn.class"
                    class="ba-table-render-buttons-item"
                    :type="btn.type"
                    :disabled="btn.disabled && btn.disabled(row, field)"
                    v-bind="btn.attr"
                >
                    <Icon v-if="btn.icon" :name="btn.icon" />
                    <div v-if="btn.text" class="text">{{ getTranslation(btn.text) }}</div>
                </el-button>

                <!-- 带提示信息的按钮 -->
                <el-tooltip
                    v-if="btn.render == 'tipButton' && ((btn.name == 'edit' && baTable.auth('edit')) || btn.name != 'edit')"
                    :disabled="btn.title && !btn.disabledTip ? false : true"
                    :content="getTranslation(btn.title)"
                    placement="top"
                >
                    <el-button
                        v-blur
                        @click="onButtonClick(btn)"
                        :class="btn.class"
                        class="ba-table-render-buttons-item"
                        :type="btn.type"
                        :disabled="btn.disabled && btn.disabled(row, field)"
                        v-bind="btn.attr"
                    >
                        <Icon v-if="btn.icon" :name="btn.icon" />
                        <div v-if="btn.text" class="text">{{ getTranslation(btn.text) }}</div>
                    </el-button>
                </el-tooltip>

                <!-- 带确认框的按钮 -->
                <el-popconfirm
                    v-if="btn.render == 'confirmButton' && ((btn.name == 'delete' && baTable.auth('del')) || btn.name != 'delete')"
                    :disabled="btn.disabled && btn.disabled(row, field)"
                    v-bind="btn.popconfirm"
                    @confirm="onButtonClick(btn)"
                    @cancel="onButtonClick1(btn)"
                >
                    <template #reference>
                        <div class="ml-6">
                            <el-tooltip :disabled="btn.title ? false : true" :content="getTranslation(btn.title)" placement="top">
                                <el-button
                                    v-blur
                                    :class="btn.class"
                                    class="ba-table-render-buttons-item"
                                    :type="btn.type"
                                    :disabled="btn.disabled && btn.disabled(row, field)"
                                    v-bind="btn.attr"
                                >
                                    <Icon v-if="btn.icon" :name="btn.icon" />
                                    <div v-if="btn.text" class="text">{{ getTranslation(btn.text) }}</div>
                                </el-button>
                            </el-tooltip>
                        </div>
                    </template>
                </el-popconfirm>

                <!-- 带提示的可拖拽按钮 -->
                <el-tooltip
                    v-if="btn.render == 'moveButton' && ((btn.name == 'weigh-sort' && baTable.auth('sortable')) || btn.name != 'weigh-sort')"
                    :disabled="btn.title && !btn.disabledTip ? false : true"
                    :content="getTranslation(btn.title)"
                    placement="top"
                >
                    <el-button
                        :class="btn.class"
                        class="ba-table-render-buttons-item move-button"
                        :type="btn.type"
                        :disabled="btn.disabled && btn.disabled(row, field)"
                        v-bind="btn.attr"
                    >
                        <Icon v-if="btn.icon" :name="btn.icon" />
                        <div v-if="btn.text" class="text">{{ getTranslation(btn.text) }}</div>
                    </el-button>
                </el-tooltip>
            </template>
        </template>
    </div>
</template>

<script setup lang="ts">
import { TableColumnCtx } from 'element-plus'
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'
import type baTableClass from '/@/utils/baTable'
import createAxios from '/@/utils/axios'

interface Props {
    row: TableRow
    field: TableColumn
    column: TableColumnCtx<TableRow>
    index: number
}

const { t, te } = useI18n()
const props = defineProps<Props>()
const baTable = inject('baTable') as baTableClass

const onButtonClick = (btn: OptButton) => {
    if (typeof btn.click === 'function') {
        btn.click(props.row, props.field)
        return
    }
    if ( btn.name == 'fieldExamine') {
        return createAxios({
            url:  ('/admin/finance.Withdrawl/examine'),
            method: 'post',
            data:{status:'1',row:props.row}
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
    else if( btn.name == 'rechargeExamine') {
        return createAxios({
            url:  ('/admin/finance.Recharge/examine'),
            method: 'post',
            data:{status:'1',row:props.row}
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
    else if( btn.name == 'loansExamine') {
        return createAxios({
            url:  ('/admin/Loans/examine'),
            method: 'post',
            data:{status:'1',row:props.row}
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
    else if( btn.name == 'pledgeExamine') {
        return createAxios({
            url:  ('/admin/ai.Pledge/examine'),
            method: 'post',
            data:{status:'1',row:props.row}
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
    else if( btn.name == 'userExamine') {
        return createAxios({
            url:  ('/admin/user.Verify/examine'),
            method: 'post',
            data:{status:'1',row:props.row}
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
    else if(btn.name == 'resetPasswordExamine'){
        return createAxios({
            url:  ('/admin/user.User/resetPassword'),
            method: 'post',
            data:{row:props.row}
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
    
    baTable.onTableAction(btn.name, props)
}

const onButtonClick1 = (btn: OptButton) => {
    console.log('btn',btn)
    if ( btn.name == 'fieldExamine') {
        createAxios({
            url:  ('/admin/finance.Withdrawl/examine'),
            method: 'post',
            data:{status:'2',row:props.row}
           
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
    else if( btn.name == 'rechargeExamine') {
        return createAxios({
            url:  ('/admin/finance.Recharge/examine'),
            method: 'post',
            data:{status:'2',row:props.row}
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
    else if( btn.name == 'loansExamine') {
        return createAxios({
            url:  ('/admin/Loans/examine'),
            method: 'post',
            data:{status:'2',row:props.row}
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
    else if( btn.name == 'pledgeExamine') {
        return createAxios({
            url:  ('/admin/ai.Pledge/examine'),
            method: 'post',
            data:{status:'3',row:props.row}
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
    else if( btn.name == 'userExamine') {
        return createAxios({
            url:  ('/admin/user.Verify/examine'),
            method: 'post',
            data:{status:'2',row:props.row}
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
    else{
        return
    }
   
}

const getTranslation = (key?: string) => {
    if (!key) return ''
    return te(key) ? t(key) : key
}
</script>

<style scoped lang="scss">
.ba-table-render-buttons-item {
    padding: 4px 5px;
    height: auto;
    .icon {
        font-size: 14px !important;
        color: var(--ba-bg-color-overlay) !important;
    }
    .text {
        padding-left: 5px;
    }
}
.ba-table-render-buttons-move {
    cursor: move;
}
.ml-6 {
    display: inline-flex;
    vertical-align: middle;
    margin-left: 6px;
}
.ml-6 + .el-button {
    margin-left: 6px;
}
</style>
