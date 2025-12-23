<template>
    <!-- 对话框表单 -->
    <!-- 建议使用 Prettier 格式化代码 -->
    <!-- el-form 内可以混用 el-form-item、FormItem、ba-input 等输入组件 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        @close="baTable.toggleForm"
        width="50%"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t(baTable.form.operate) : '' }}
            </div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <div
                class="ba-operate-form"
                :class="'ba-' + baTable.form.operate + '-form'"
                :style="config.layout.shrink ? '':'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
            >
                <el-form
                    v-if="!baTable.form.loading"
                    ref="formRef"
                    @submit.prevent=""
                    @keyup.enter="baTable.onSubmit(formRef)"
                    :model="baTable.form.items"
                    :label-position="config.layout.shrink ? 'top' : 'right'"
                    :label-width="baTable.form.labelWidth + 'px'"
                    :rules="rules"
                >
                    <FormItem :label="t('finance.withdrawl.user_id')" type="remoteSelect" v-model="baTable.form.items!.user_id" prop="user_id" :input-attr="{ pk: 'ba_user.id', field: 'username', remoteUrl: '/admin/user.User/index' ,tooltipParams: {id: 'id',用户名: 'username',电话: 'mobile'},}" :placeholder="t('Please select field', { field: t('finance.withdrawl.user_id') })" />
                    <!-- <FormItem :label="t('finance.withdrawl.target_currency')" type="remoteSelect" v-model="baTable.form.items!.target_currency" prop="target_currency" :input-attr="{ pk: 'ba_basicset.id', field: 'name', remoteUrl: '/admin/Basicset/index' ,params:{type:1}}" :placeholder="t('Please select field', { field: t('finance.withdrawl.target_currency') })" /> -->
                    <!-- <FormItem :label="t('finance.withdrawl.target_currency')" type="select" v-model="baTable.form.items!.target_currency" prop="target_currency" :input-attr="{ content: { '0': t('finance.withdrawl.currency 0'), '1': t('finance.withdrawl.currency 1')} }" :placeholder="t('Please input field', { field: t('finance.withdrawl.target_currency') })" /> -->
                    <!-- <FormItem :label="t('finance.withdrawl.target_num')" type="number" prop="target_num" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.target_num" :placeholder="t('Please input field', { field: t('finance.withdrawl.target_num') })" /> -->
                    <el-form-item :label="t('finance.withdrawl.target_num')" prop="target_num">
                        <el-input-number v-model.number="baTable.form.items!.target_num" :precision="2" :step="0.01" :min="0"/>
                    </el-form-item>
                    <!-- <FormItem :label="t('finance.withdrawl.exchange_rate')" type="number" prop="exchange_rate" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.exchange_rate" :placeholder="t('Please input field', { field: t('finance.withdrawl.exchange_rate') })" /> -->
                    <!-- <el-form-item :label="t('finance.withdrawl.exchange_rate')" prop="exchange_rate">
                        <el-input-number v-model.number="baTable.form.items!.exchange_rate" :precision="2" :step="0.01" :min="0"/>
                    </el-form-item> -->
                    <!-- <FormItem :label="t('finance.withdrawl.username')" type="string" v-model="baTable.form.items!.username" prop="username" :placeholder="t('Please input field', { field: t('finance.withdrawl.username') })" /> -->
                    <FormItem :label="t('finance.withdrawl.deposit_address')" type="string" v-model="baTable.form.items!.deposit_address" prop="deposit_address" :placeholder="t('Please input field', { field: t('finance.withdrawl.deposit_address') })" />
                    <FormItem :label="t('finance.withdrawl.payout_address')" type="string" v-model="baTable.form.items!.payout_address" prop="payout_address" :placeholder="t('Please input field', { field: t('finance.withdrawl.payout_address') })" />
                    <!-- <FormItem :label="t('finance.withdrawl.status')" type="select" v-model="baTable.form.items!.status" prop="status" :input-attr="{ content: { '0': t('finance.withdrawl.status 0'), '1': t('finance.withdrawl.status 1'), '2': t('finance.withdrawl.status 2') } }" :placeholder="t('Please select field', { field: t('finance.withdrawl.status') })" /> -->
                    <!-- <FormItem :label="t('finance.withdrawl.name')" type="string" v-model="baTable.form.items!.name" prop="name" :placeholder="t('Please input field', { field: t('finance.withdrawl.name') })" />
                    <FormItem :label="t('finance.withdrawl.bank_name')" type="string" v-model="baTable.form.items!.bank_name" prop="bank_name" :placeholder="t('Please input field', { field: t('finance.withdrawl.bank_name') })" />
                    <FormItem :label="t('finance.withdrawl.bank_account')" type="string" v-model="baTable.form.items!.bank_account" prop="bank_account" :placeholder="t('Please input field', { field: t('finance.withdrawl.bank_account') })" />
                    <FormItem :label="t('finance.withdrawl.legal_tender')" type="string" v-model="baTable.form.items!.legal_tender" prop="legal_tender" :placeholder="t('Please input field', { field: t('finance.withdrawl.legal_tender') })" />
                    <FormItem :label="t('finance.withdrawl.branch')" type="string" v-model="baTable.form.items!.branch" prop="branch" :placeholder="t('Please input field', { field: t('finance.withdrawl.branch') })" /> -->
                    <FormItem :label="t('finance.withdrawl.memo')" type="string" v-model="baTable.form.items!.memo" prop="memo" :placeholder="t('Please input field', { field: t('finance.withdrawl.memo') })" />
                    <FormItem :label="t('finance.withdrawl.salesman')" type="string" v-model="baTable.form.items!.salesman" prop="salesman" :placeholder="t('Please input field', { field: t('finance.withdrawl.salesman') })" />
                    <!-- <FormItem :label="t('finance.withdrawl.image')" type="image" v-model="baTable.form.items!.image" prop="image" /> -->
                </el-form>
            </div>
        </el-scrollbar>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.toggleForm()">{{ t('Cancel') }}</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="baTable.onSubmit(formRef)" type="primary">
                    {{ baTable.form.operateIds && baTable.form.operateIds.length > 1 ? t('Save and edit next item') : t('Save') }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import type { FormInstance, FormItemRule } from 'element-plus'
import { inject, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import FormItem from '/@/components/formItem/index.vue'
import { useConfig } from '/@/stores/config'
import type baTableClass from '/@/utils/baTable'
import { buildValidatorData } from '/@/utils/validate'

const config = useConfig()
const formRef = ref<FormInstance>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    user_id: [buildValidatorData({ name: 'required', title: t('finance.withdrawl.user_id') })],
    target_currency: [buildValidatorData({ name: 'required', title: t('finance.withdrawl.target_currency') })],
    
    target_num: [buildValidatorData({ name: 'number', title: t('finance.withdrawl.target_num') })],
    exchange_rate: [buildValidatorData({ name: 'number', title: t('finance.withdrawl.exchange_rate') })],
    create_time: [buildValidatorData({ name: 'date', title: t('finance.withdrawl.create_time') })],
    update_time: [buildValidatorData({ name: 'date', title: t('finance.withdrawl.update_time') })],
})
</script>

<style scoped lang="scss"></style>
