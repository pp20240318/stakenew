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
                    <FormItem :label="t('ai.product.computer_name')" type="string" v-model="baTable.form.items!.computer_name" prop="computer_name" :placeholder="t('Please input field', { field: t('ai.product.computer_name') })" />
                    <FormItem :label="t('ai.product.image')" type="image" v-model="baTable.form.items!.image" prop="image" />
                    <!-- <FormItem :label="t('ai.product.begin_price')" type="number" prop="begin_price" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.begin_price" :placeholder="t('Please input field', { field: t('ai.product.begin_price') })" />
                     -->
                     <el-form-item :label="t('ai.product.begin_price')" prop="begin_price">
                        <el-input-number v-model="baTable.form.items!.begin_price" :precision="2" :step="0.01" :min="0" />
                    </el-form-item>
                    <!-- <FormItem :label="t('ai.product.end_price')" type="number" prop="end_price" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.end_price" :placeholder="t('Please input field', { field: t('ai.product.end_price') })" /> -->
                    <el-form-item :label="t('ai.product.end_price')" prop="end_price">
                        <el-input-number v-model="baTable.form.items!.end_price" :precision="2" :step="0.01" :min="0" />
                    </el-form-item>
                    <FormItem :label="t('ai.product.valid_day')" type="string" v-model="baTable.form.items!.valid_day" prop="valid_day" :placeholder="t('Please input field', { field: t('ai.product.valid_day') })" />
                    <!-- <FormItem :label="t('ai.product.earning_rate')" type="number" prop="earning_rate" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.earning_rate" :placeholder="t('Please input field', { field: t('ai.product.earning_rate') })" /> -->
                    <el-form-item :label="t('ai.product.earning_rate')" prop="earning_rate">
                        <el-input-number v-model="baTable.form.items!.earning_rate" :precision="2" :step="0.01" :min="0"/>
                    </el-form-item>
                    <FormItem :label="t('ai.product.status')" type="switch" v-model="baTable.form.items!.status" prop="status" :input-attr="{ content: { '0': t('ai.product.status 0'), '1': t('ai.product.status 1') } }" />
                    <FormItem :label="t('ai.product.is_sale')" type="switch" v-model="baTable.form.items!.is_sale" prop="is_sale" :input-attr="{ content: { '0': t('ai.product.is_sale 0'), '1': t('ai.product.is_sale 1') } }" />
                    <!-- <FormItem :label="t('ai.product.total_sale')" type="number" prop="total_sale" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.total_sale" :placeholder="t('Please input field', { field: t('ai.product.total_sale') })" /> -->
                    <FormItem :label="t('ai.product.sort')" type="number" prop="sort" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.sort" :placeholder="t('Please input field', { field: t('ai.product.sort') })" />
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
    id: [buildValidatorData({ name: 'number', title: t('ai.product.id') })],
    computer_name: [buildValidatorData({ name: 'required', title: t('ai.product.computer_name') })],
    begin_price: [buildValidatorData({ name: 'number', title: t('ai.product.begin_price') })],
    end_price: [buildValidatorData({ name: 'number', title: t('ai.product.end_price') })],
    earning_rate: [buildValidatorData({ name: 'number', title: t('ai.product.earning_rate') })],
    valid_day: [buildValidatorData({ name: 'required', title: t('ai.product.valid_day') })],
    update_time: [buildValidatorData({ name: 'date', title: t('ai.product.update_time') })],

})
</script>

<style scoped lang="scss"></style>
