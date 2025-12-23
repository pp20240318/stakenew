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
                    <FormItem :label="t('finance.recharge.user_id')" type="remoteSelect" v-model="baTable.form.items!.user_id" prop="user_id" :input-attr="{ pk: 'ba_user.id', field: 'username', remoteUrl: '/admin/user.User/index', tooltipParams: {id: 'id',用户名: 'username',电话: 'mobile'},}" :placeholder="t('Please select field', { field: t('finance.recharge.user_id') })" />
                    <!--  <FormItem  style="display:none" :label="t('finance.recharge.source_currency')" type="remoteSelect" v-model="baTable.form.items!.source_currency" prop="source_currency" :input-attr="{ pk: 'ba_basicset.id', field: 'name', remoteUrl: '/admin/Basicset/index' ,params:{type:1}}" :placeholder="t('Please select field', { field: t('finance.recharge.source_currency') })" /> -->
                    <el-form-item style="display:none" :label="t('finance.recharge.source_num')" prop="source_num">
                        <el-input-number v-model.number="baTable.form.items!.source_num" :precision="2" :step="0.01" :min="0"/>
                    </el-form-item>
                    <!--  <FormItem :label="t('finance.recharge.target_currency')" type="remoteSelect" v-model="baTable.form.items!.target_currency" prop="target_currency" 
                    :input-attr="{ pk: 'ba_basicset.id', field: 'name', remoteUrl: '/admin/Basicset/index' ,params:{type:1}}" :placeholder="t('Please select field', { field: t('finance.recharge.target_currency') })" /> -->
                    <!-- <FormItem :label="t('finance.recharge.target_num')" type="number" prop="target_num" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.target_num" :placeholder="t('Please input field', { field: t('finance.recharge.target_num') })" /> -->
                    <el-form-item :label="t('finance.recharge.target_num')" prop="target_num">
                        <el-input-number v-model.number="baTable.form.items!.target_num" :precision="2" :step="0.01" :min="0"/>
                    </el-form-item>
                    <!-- <FormItem :label="t('finance.recharge.exchange_rate')" type="number" prop="exchange_rate" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.exchange_rate" :placeholder="t('Please input field', { field: t('finance.recharge.exchange_rate') })" /> -->
                    <el-form-item :label="t('finance.recharge.exchange_rate')" prop="exchange_rate">
                        <el-input-number v-model.number="baTable.form.items!.exchange_rate" :precision="2" :step="0.01" :min="0"/>
                    </el-form-item>
                    <FormItem :label="t('finance.recharge.account')" type="string" v-model="baTable.form.items!.account" prop="account" :placeholder="t('Please input field', { field: t('finance.recharge.account') })" />
                    <FormItem :label="t('finance.recharge.username')" type="string" v-model="baTable.form.items!.username" prop="username" :placeholder="t('Please input field', { field: t('finance.recharge.username') })" />
                    <FormItem :label="t('finance.recharge.payout_address')" type="string" v-model="baTable.form.items!.payout_address" prop="payout_address" :placeholder="t('Please input field', { field: t('finance.recharge.payout_address') })" />
                    <!-- <FormItem :label="t('finance.recharge.status')" type="select" v-model="baTable.form.items!.status" prop="status" :input-attr="{ content: { '0': t('finance.recharge.status 0'), '1': t('finance.recharge.status 1'), '2': t('finance.recharge.status 2') } }" :placeholder="t('Please select field', { field: t('finance.recharge.status') })" /> -->
                    <FormItem :label="t('finance.recharge.memo')" type="string" v-model="baTable.form.items!.memo" prop="memo" :placeholder="t('Please input field', { field: t('finance.recharge.memo') })" />
                    <FormItem :label="t('finance.recharge.image')" type="image" v-model="baTable.form.items!.image" prop="image" />
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
    user_id: [buildValidatorData({ name: 'required', title: t('finance.recharge.user_id') })], 
    target_currency: [buildValidatorData({ name: 'required', title: t('finance.recharge.target_currency') })], 
    target_num: [buildValidatorData({ name: 'number', title: t('finance.recharge.target_num') })],
    into_account: [buildValidatorData({ name: 'number', title: t('finance.recharge.into_account') })], 
    create_time: [buildValidatorData({ name: 'date', title: t('finance.recharge.create_time') })],
})
</script>

<style scoped lang="scss"></style>
