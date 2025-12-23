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
                    <FormItem :label="t('nft.product.computer_name')" type="string" v-model="baTable.form.items!.computer_name" prop="computer_name" :placeholder="t('Please input field', { field: t('nft.product.computer_name') })" />
                    <FormItem :label="t('nft.product.image')" type="image" v-model="baTable.form.items!.image" prop="image" />
                    <!-- <FormItem :label="t('nft.product.price')" type="number" prop="price" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.price" :placeholder="t('Please input field', { field: t('nft.product.price') })" /> -->
                    <el-form-item :label="t('nft.product.price')" prop="price">
                        <el-input-number v-model.number="baTable.form.items!.price" :precision="2" :step="0.01" />
                    </el-form-item>
                    <FormItem :label="t('nft.product.begin_time')" type="datetime" v-model="baTable.form.items!.begin_time" prop="begin_time" :placeholder="t('Please select field', { field: t('nft.product.begin_time') })" />
                    <FormItem :label="t('nft.product.end_time')" type="datetime" v-model="baTable.form.items!.end_time" prop="end_time" :placeholder="t('Please select field', { field: t('nft.product.end_time') })" />
                    <!-- <FormItem :label="t('nft.product.earning_rate')" type="number" prop="earning_rate" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.earning_rate" :placeholder="t('Please input field', { field: t('nft.product.earning_rate') })" /> -->
                    <el-form-item :label="t('nft.product.earning_rate')" prop="earning_rate">
                        <el-input-number v-model.number="baTable.form.items!.earning_rate" :precision="4" :step="0.0001" />
                    </el-form-item>
                    <FormItem :label="t('nft.product.status')" type="switch" v-model="baTable.form.items!.status" prop="status" :input-attr="{ content: { '0': t('nft.product.status 0'), '1': t('nft.product.status 1') } }" />
                    <FormItem :label="t('nft.product.is_sale')" type="switch" v-model="baTable.form.items!.is_sale" prop="is_sale" :input-attr="{ content: { '0': t('nft.product.is_sale 0'), '1': t('nft.product.is_sale 1') } }" />
                    <!-- <FormItem :label="t('nft.product.view_count')" type="number" prop="view_count" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.view_count" :placeholder="t('Please input field', { field: t('nft.product.view_count') })" /> -->
                    <FormItem :label="t('nft.product.level_id')" type="remoteSelect" v-model="baTable.form.items!.level_id" prop="level_id" :input-attr="{ pk: 'ba_nft_level.id', field: 'name', remoteUrl: '/admin/nft.Level/index' }" :placeholder="t('Please select field', { field: t('nft.product.level_id') })" />
                    <FormItem :label="t('nft.product.sort')" type="number" prop="sort" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.sort" :placeholder="t('Please input field', { field: t('nft.product.sort') })" />
                    <FormItem :label="t('nft.product.description')" type="textarea" v-model="baTable.form.items!.description" prop="description" @keyup.enter.stop="" @keyup.ctrl.enter="baTable.onSubmit(formRef)" :placeholder="t('Please input field', { field: t('nft.product.description') })" />
                    <FormItem :label="t('nft.product.author')" type="string" v-model="baTable.form.items!.author" prop="author" :placeholder="t('Please input field', { field: t('nft.product.author') })" />
                    <FormItem :label="t('nft.product.nftaddress')" type="string" v-model="baTable.form.items!.nftaddress" prop="nftaddress" :placeholder="t('Please input field', { field: t('nft.product.nftaddress') })" />
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
    id: [buildValidatorData({ name: 'number', title: t('nft.product.id') })],
    computer_name: [buildValidatorData({ name: 'required', title: t('nft.product.computer_name') })],
    price: [buildValidatorData({ name: 'number', title: t('nft.product.price') })],
    begin_time: [buildValidatorData({ name: 'date', title: t('nft.product.begin_time') })],
    end_time: [buildValidatorData({ name: 'date', title: t('nft.product.end_time') })],
    earning_rate: [buildValidatorData({ name: 'number', title: t('nft.product.earning_rate') })],
    level_id: [buildValidatorData({ name: 'required', title: t('nft.product.level_id') })],
    view_count: [buildValidatorData({ name: 'number', title: t('nft.product.view_count') })],
    description: [buildValidatorData({ name: 'editorRequired', title: t('nft.product.description') })],
    update_time: [buildValidatorData({ name: 'date', title: t('nft.product.update_time') })],
    author: [buildValidatorData({ name: 'required', title: t('nft.product.author') })],
   
})
</script>

<style scoped lang="scss"></style>
