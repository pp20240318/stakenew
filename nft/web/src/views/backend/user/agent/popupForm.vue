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
                    <FormItem :label="t('user.agent.type')" type="string" v-model="baTable.form.items!.type" prop="type" :placeholder="t('Please input field', { field: t('user.agent.type') })" />
                    <FormItem :label="t('user.agent.rid')" type="number" prop="rid" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.rid" :placeholder="t('Please input field', { field: t('user.agent.rid') })" />
                    <FormItem :label="t('user.agent.email')" type="string" v-model="baTable.form.items!.email" prop="email" :placeholder="t('Please input field', { field: t('user.agent.email') })" />
                    <FormItem :label="t('user.agent.nickname')" type="string" v-model="baTable.form.items!.nickname" prop="nickname" :placeholder="t('Please input field', { field: t('user.agent.nickname') })" />
                    <FormItem :label="t('user.agent.credit')" type="number" prop="credit" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.credit" :placeholder="t('Please input field', { field: t('user.agent.credit') })" />
                    <FormItem :label="t('user.agent.phone')" type="string" v-model="baTable.form.items!.phone" prop="phone" :placeholder="t('Please input field', { field: t('user.agent.phone') })" />
                    <FormItem :label="t('user.agent.invite_code')" type="string" v-model="baTable.form.items!.invite_code" prop="invite_code" :placeholder="t('Please input field', { field: t('user.agent.invite_code') })" />
                    <FormItem :label="t('user.agent.true_name')" type="radio" v-model="baTable.form.items!.true_name" prop="true_name" :input-attr="{ content: { '0': t('user.agent.true_name 0'), '1': t('user.agent.true_name 1') } }" :placeholder="t('Please select field', { field: t('user.agent.true_name') })" />
                    <!-- <FormItem :label="t('user.agent.money')" type="number" prop="money" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.money" :placeholder="t('Please input field', { field: t('user.agent.money') })" /> -->
                    <el-form-item :label="t('user.agent.money')" prop="money">
                        <el-input-number v-model.number="baTable.form.items!.money" :precision="2" :step="0.01" />
                    </el-form-item>
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
    rid: [buildValidatorData({ name: 'number', title: t('user.agent.rid') })],
    email: [buildValidatorData({ name: 'email', title: t('user.agent.email') })],
    credit: [buildValidatorData({ name: 'number', title: t('user.agent.credit') })],
    phone: [buildValidatorData({ name: 'mobile', title: t('user.agent.phone') })],
    money: [buildValidatorData({ name: 'number', title: t('user.agent.money') })],
    create_time: [buildValidatorData({ name: 'date', title: t('user.agent.create_time') })],
})
</script>

<style scoped lang="scss"></style>
