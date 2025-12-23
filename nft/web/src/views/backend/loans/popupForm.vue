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
                    <!-- <FormItem :label="t('loans.loan_id')" type="string" v-model="baTable.form.items!.loan_id" prop="loan_id" :placeholder="t('Please input field', { field: t('loans.loan_id') })" /> -->
                    <!-- <FormItem :label="t('loans.borrower_name')" type="string" v-model="baTable.form.items!.borrower_name" prop="borrower_name" :placeholder="t('Please input field', { field: t('loans.borrower_name') })" /> -->
                    <FormItem :label="t('loans.user_id')" type="remoteSelect" v-model="baTable.form.items!.user_id" prop="user_id" :input-attr="{ pk: 'ba_user.id', field: 'username', remoteUrl: '/admin/user.User/index' }" :placeholder="t('Please select field', { field: t('loans.user_id') })" />
                    <FormItem :label="t('loans.loan_amount')" type="number" prop="loan_amount" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.loan_amount" :placeholder="t('Please input field', { field: t('loans.loan_amount') })" />
                    <FormItem :label="t('loans.interest_rate')" type="number" prop="interest_rate" :input-attr="{ step: 0.01,append:'%'}" v-model.number="baTable.form.items!.interest_rate"  :placeholder="t('Please input field', { field: t('loans.form interest_rate') }) " />
                    
                    <FormItem :label="t('loans.loan_term')" type="number" prop="loan_term" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.loan_term" :placeholder="t('Please input field', { field: t('loans.form loan_term') })" />
                    <FormItem :label="t('loans.start_date')" type="datetime" v-model="baTable.form.items!.start_date" prop="start_date" :placeholder="t('Please select field', { field: t('loans.start_date') })" />
                    <FormItem :label="t('loans.end_date')" type="datetime" v-model="baTable.form.items!.end_date" prop="end_date" :placeholder="t('Please select field', { field: t('loans.end_date') })" />
                    <!-- <FormItem :label="t('loans.status')" type="radio" v-model="baTable.form.items!.status" prop="status" :input-attr="{ content: { '0': t('loans.status 0'), '1': t('loans.status 1'), '2': t('loans.status 2') } }" :placeholder="t('Please select field', { field: t('loans.status') })" />
                    <FormItem :label="t('loans.repayment_status')" type="radio" v-model="baTable.form.items!.repayment_status" prop="repayment_status" :input-attr="{ content: { '0': t('loans.repayment_status 0'), '1': t('loans.repayment_status 1') } }" :placeholder="t('Please select field', { field: t('loans.repayment_status') })" /> -->
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
    user_id: [buildValidatorData({ name: 'required', title: t('loans.loan_amount') })],
    loan_amount: [buildValidatorData({ name: 'required', title: t('loans.loan_amount') })],
    interest_rate: [buildValidatorData({ name: 'required', title: t('loans.interest_rate') })],
    loan_term: [buildValidatorData({ name: 'number', title: t('loans.loan_term') })],
    start_date: [buildValidatorData({ name: 'date', title: t('loans.start_date') })],
    end_date: [buildValidatorData({ name: 'date', title: t('loans.end_date') })],
    create_time: [buildValidatorData({ name: 'date', title: t('loans.create_time') })],
    update_time: [buildValidatorData({ name: 'date', title: t('loans.update_time') })],
})
</script>

<style scoped lang="scss"></style>
