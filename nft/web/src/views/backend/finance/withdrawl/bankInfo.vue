<!-- 示例核心代码(3/3) -->
<template>
    <el-dialog class="ba-operate-dialog" v-model="baTable.table.extend!.showInfo" width="50%">
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">详情</div>
        </template>
            <el-scrollbar class="ba-table-form-scrollbar" v-loading="baTable.table.extend!.infoLoading">
            <div
                class="ba-operate-form"
                :class="'ba-' + baTable.form.operate + '-form'"
                :style="config.layout.shrink ? '':'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
            >
                <el-form
                    v-if="baTable.table.extend!.infoData"
                    ref="formRef"
                    @submit.prevent=""
                    :model="baTable.form.items"
                    :label-width="baTable.form.labelWidth + 'px'"
                >
                    <!-- <FormItem :label="t('finance.withdrawl.user_id')" type="remoteSelect" v-model="baTable.form.items!.user_id" prop="user_id" :input-attr="{ pk: 'ba_user.id', field: 'username', remoteUrl: '/admin/user.User/index' }" :placeholder="t('Please select field', { field: t('finance.withdrawl.user_id') })" />
                    <FormItem :label="t('finance.withdrawl.target_currency')" type="select" v-model="baTable.form.items!.target_currency" prop="target_currency" :input-attr="{ content: { '0': t('finance.withdrawl.currency 0'), '1': t('finance.withdrawl.currency 1')} }" :placeholder="t('Please input field', { field: t('finance.withdrawl.target_currency') })" /> -->
                    <!-- <FormItem :label="t('finance.withdrawl.target_num')" type="number" prop="target_num" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.target_num" :placeholder="t('Please input field', { field: t('finance.withdrawl.target_num') })" /> -->
                    <!-- <el-form-item :label="t('finance.withdrawl.target_num')" prop="target_num">
                        <el-input-number v-model.number="baTable.form.items!.target_num" :precision="2" :step="0.01" />
                    </el-form-item> -->
                    <!-- <FormItem :label="t('finance.withdrawl.exchange_rate')" type="number" prop="exchange_rate" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.exchange_rate" :placeholder="t('Please input field', { field: t('finance.withdrawl.exchange_rate') })" /> -->
                    <!-- <el-form-item :label="t('finance.withdrawl.exchange_rate')" prop="price">
                        <el-input-number v-model.number="baTable.form.items!.exchange_rate" :precision="2" :step="0.01" />
                    </el-form-item> -->
                    <!-- <FormItem :label="t('finance.withdrawl.username')" type="string" v-model="baTable.form.items!.username" prop="username" :placeholder="t('Please input field', { field: t('finance.withdrawl.username') })" /> -->
                    <!-- <FormItem :label="t('finance.withdrawl.deposit_address')" type="string" v-model="baTable.form.items!.deposit_address" prop="deposit_address" :placeholder="t('Please input field', { field: t('finance.withdrawl.deposit_address') })" />
                    <FormItem :label="t('finance.withdrawl.payout_address')" type="string" v-model="baTable.form.items!.payout_address" prop="payout_address" :placeholder="t('Please input field', { field: t('finance.withdrawl.payout_address') })" /> -->
                    <!-- <FormItem :label="t('finance.withdrawl.status')" type="select" v-model="baTable.form.items!.status" prop="status" :input-attr="{ content: { '0': t('finance.withdrawl.status 0'), '1': t('finance.withdrawl.status 1'), '2': t('finance.withdrawl.status 2') } }" :placeholder="t('Please select field', { field: t('finance.withdrawl.status') })" /> -->
                    <FormItem :label="t('finance.withdrawl.name')" type="string" v-model="baTable.table.extend!.infoData.name" prop="name" :placeholder="t('Please input field', { field: t('finance.withdrawl.name') })" />
                    <FormItem :label="t('finance.withdrawl.bank_name')" type="string" v-model="baTable.table.extend!.infoData!.bank_name" prop="bank_name" :placeholder="t('Please input field', { field: t('finance.withdrawl.bank_name') })" />
                    <FormItem :label="t('finance.withdrawl.bank_account')" type="string" v-model="baTable.table.extend!.infoData.bank_account" prop="bank_account" :placeholder="t('Please input field', { field: t('finance.withdrawl.bank_account') })" />
                    <FormItem :label="t('finance.withdrawl.legal_tender')" type="string" v-model="baTable.table.extend!.infoData!.legal_tender" prop="legal_tender" :placeholder="t('Please input field', { field: t('finance.withdrawl.legal_tender') })" />
                    <FormItem :label="t('finance.withdrawl.branch')" type="string" v-model="baTable.table.extend!.infoData!.branch" prop="branch" :placeholder="t('Please input field', { field: t('finance.withdrawl.branch') })" />
                    <!-- <FormItem :label="t('finance.withdrawl.memo')" type="string" v-model="baTable.form.items!.memo" prop="memo" :placeholder="t('Please input field', { field: t('finance.withdrawl.memo') })" />
                    <FormItem :label="t('finance.withdrawl.salesman')" type="string" v-model="baTable.form.items!.salesman" prop="salesman" :placeholder="t('Please input field', { field: t('finance.withdrawl.salesman') })" /> -->
                    <!-- <FormItem :label="t('finance.withdrawl.image')" type="image" v-model="baTable.form.items!.image" prop="image" /> -->
                </el-form>
            </div>
        </el-scrollbar>
            
        
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.table.extend!.showInfo = false,baTable.form.operate = ''">{{ t('Cancel') }}</el-button>
                <el-button v-blur :loading="submitLoading" @click="edit()" type="primary">
                    {{  t('Save') }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { inject ,ref,onBeforeMount} from 'vue'
import type { FormInstance, FormItemRule } from 'element-plus'
import { timeFormat } from '/@/utils/common'
import type baTableClass from '/@/utils/baTable'
import { useI18n } from 'vue-i18n'
import FormItem from '/@/components/formItem/index.vue'
import { useConfig } from '/@/stores/config'
import createAxios from '/@/utils/axios'
import { buildValidatorData } from '/@/utils/validate'

const config = useConfig()


const formRef = ref<FormInstance>()

const baTable = inject('baTable') as baTableClass
const { t } = useI18n()
const submitLoading = ref(false)

onBeforeMount(() => {
   
    
   
})

function edit(){
    submitLoading.value = true
    
    createAxios({
            url:  ('/admin/finance.Withdrawl/editBank'),
            method: 'post',
            params:{},
            data:{'data':baTable.table.extend!.infoData,'row':baTable.table.extend!.infoData},
        },
        {
            showSuccessMessage: true,
            showCodeMessage:true,
        }
        ).then((res)=>{
            baTable.table.extend!.showInfo = false
            baTable.onTableHeaderAction('refresh', {})

            submitLoading.value  = false
        })
}
</script>

<style scoped lang="scss">
.info-box {
    margin-top: 60px;
    div {
        width: 100%;
        text-align: center;
    }
    .mt-40 {
        margin-top: 40px;
    }
}
</style>
