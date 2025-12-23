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
                    <FormItem :label="t('trade.orders.user_id')" type="remoteSelect" v-model="baTable.form.items!.user_id" prop="user_id" :input-attr="{ pk: 'ba_user.id', field: 'username', remoteUrl: '/admin/user.User/index' }" :placeholder="t('Please select field', { field: t('trade.orders.user_id') })" />
                    <FormItem :label="t('trade.orders.coin_id')" type="remoteSelect" v-model="baTable.form.items!.coin_id" prop="coin_id" :input-attr="{ pk: 'ba_soptcoin.id', field: 'name', remoteUrl: '/admin/Soptcoin/index' }" :placeholder="t('Please select field', { field: t('trade.orders.coin_id') })" />
                    <FormItem :label="t('trade.orders.trading_symbol')" type="string" v-model="baTable.form.items!.trading_symbol" prop="trading_symbol" :placeholder="t('Please input field', { field: t('trade.orders.trading_symbol') })" />
                    <FormItem :label="t('trade.orders.coin_symbol')" type="string" v-model="baTable.form.items!.coin_symbol" prop="coin_symbol" :placeholder="t('Please input field', { field: t('trade.orders.coin_symbol') })" />
                    <FormItem :label="t('trade.orders.trade_type')" type="radio" v-model="baTable.form.items!.trade_type" prop="trade_type" :input-attr="{ content: {  'up': t('trade.orders.trade_type  up'), 'down': t('trade.orders.trade_type  down') } }" :placeholder="t('Please select field', { field: t('trade.orders.trade_type') })" />
                    <FormItem :label="t('trade.orders.amount')" type="number" prop="amount" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.amount" :placeholder="t('Please input field', { field: t('trade.orders.amount') })" />
                    <FormItem :label="t('trade.orders.profit_rate')" type="number" prop="profit_rate" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.profit_rate" :placeholder="t('Please input field', { field: t('trade.orders.profit_rate') })" />
                    <FormItem :label="t('trade.orders.trade_time')" type="string" v-model="baTable.form.items!.trade_time" prop="trade_time" :placeholder="t('Please input field', { field: t('trade.orders.trade_time') })" />
                    <FormItem :label="t('trade.orders.total_seconds')" type="number" prop="total_seconds" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.total_seconds" :placeholder="t('Please input field', { field: t('trade.orders.total_seconds') })" />
                    <FormItem :label="t('trade.orders.remaining_seconds')" type="number" prop="remaining_seconds" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.remaining_seconds" :placeholder="t('Please input field', { field: t('trade.orders.remaining_seconds') })" />
                    <FormItem :label="t('trade.orders.entry_price')" type="number" prop="entry_price" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.entry_price" :placeholder="t('Please input field', { field: t('trade.orders.entry_price') })" />
                    <FormItem :label="t('trade.orders.exit_price')" type="number" prop="exit_price" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.exit_price" :placeholder="t('Please input field', { field: t('trade.orders.exit_price') })" />
                    <FormItem :label="t('trade.orders.profit_loss')" type="number" prop="profit_loss" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.profit_loss" :placeholder="t('Please input field', { field: t('trade.orders.profit_loss') })" />
                    <FormItem :label="t('trade.orders.result')" type="radio" v-model="baTable.form.items!.result" prop="result" :input-attr="{ content: { win: '赢', lose: '输', draw: '平' } }" :placeholder="t('Please select field', { field: t('trade.orders.result') })" />
                    <FormItem :label="t('trade.orders.money_type')" type="radio" v-model="baTable.form.items!.money_type" prop="money_type" :input-attr="{ content: {  '1': t('trade.orders.money_type  1'), '2': t('trade.orders.money_type  2') } }" :placeholder="t('Please select field', { field: t('trade.orders.money_type') })" />
                    <FormItem :label="t('trade.orders.status')" type="number" v-model="baTable.form.items!.status" prop="status" :input-attr="{ content: { '0': t('trade.orders.status  0'), '1': t('trade.orders.status  1'), '2': t('trade.orders.status  2'), '3': t('trade.orders.status  3') } }" :placeholder="t('Please input field', { field: t('trade.orders.status') })" />
                    <FormItem :label="t('trade.orders.created_at')" type="datetime" v-model="baTable.form.items!.created_at" prop="created_at" :placeholder="t('Please select field', { field: t('trade.orders.created_at') })" />
                    <FormItem :label="t('trade.orders.updated_at')" type="datetime" v-model="baTable.form.items!.updated_at" prop="updated_at" :placeholder="t('Please select field', { field: t('trade.orders.updated_at') })" />
                    <FormItem :label="t('trade.orders.expired_at')" type="datetime" v-model="baTable.form.items!.expired_at" prop="expired_at" :placeholder="t('Please select field', { field: t('trade.orders.expired_at') })" />
                    <FormItem :label="t('trade.orders.settled_at')" type="datetime" v-model="baTable.form.items!.settled_at" prop="settled_at" :placeholder="t('Please select field', { field: t('trade.orders.settled_at') })" />
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
    amount: [buildValidatorData({ name: 'number', title: t('trade.orders.amount') })],
    profit_rate: [buildValidatorData({ name: 'number', title: t('trade.orders.profit_rate') })],
    total_seconds: [buildValidatorData({ name: 'number', title: t('trade.orders.total_seconds') })],
    remaining_seconds: [buildValidatorData({ name: 'number', title: t('trade.orders.remaining_seconds') })],
    entry_price: [buildValidatorData({ name: 'number', title: t('trade.orders.entry_price') })],
    exit_price: [buildValidatorData({ name: 'number', title: t('trade.orders.exit_price') })],
    profit_loss: [buildValidatorData({ name: 'number', title: t('trade.orders.profit_loss') })],
    status: [buildValidatorData({ name: 'number', title: t('trade.orders.status') })],
    created_at: [buildValidatorData({ name: 'date', title: t('trade.orders.created_at') })],
    updated_at: [buildValidatorData({ name: 'date', title: t('trade.orders.updated_at') })],
    expired_at: [buildValidatorData({ name: 'date', title: t('trade.orders.expired_at') })],
    settled_at: [buildValidatorData({ name: 'date', title: t('trade.orders.settled_at') })],
})
</script>

<style scoped lang="scss"></style>
