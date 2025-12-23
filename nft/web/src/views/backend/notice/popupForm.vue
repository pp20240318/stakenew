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
                    <FormItem :label="t('notice.title')" type="string" v-model="baTable.form.items!.title" prop="title" :placeholder="t('Please input field', { field: t('notice.title') })" />
                    <FormItem :label="t('notice.content')" type="editor" v-model="baTable.form.items!.content" prop="content" @keyup.enter.stop="" @keyup.ctrl.enter="baTable.onSubmit(formRef)" :placeholder="t('Please input field', { field: t('notice.content') })" />
                    <!-- <FormItem :label="t('notice.cover_image')" type="image" v-model="baTable.form.items!.cover_image" prop="cover_image" /> -->
                    <FormItem :label="t('notice.status')" type="radio" v-model="baTable.form.items!.status" prop="status" :input-attr="{ content: { '0': t('notice.status  0'), '1': t('notice.status  1') } }" :placeholder="t('Please select field', { field: t('notice.status') })" />
                    <FormItem :label="t('notice.start_time')" type="datetime" v-model="baTable.form.items!.start_time" prop="start_time" :placeholder="t('Please select field', { field: t('notice.start_time') })" />
                    <FormItem :label="t('notice.end_time')" type="datetime" v-model="baTable.form.items!.end_time" prop="end_time" :placeholder="t('Please select field', { field: t('notice.end_time') })" />
                    <FormItem :label="t('notice.sort')" type="number" prop="sort" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.sort" :placeholder="t('Please input field', { field: t('notice.sort') })" />
                    <FormItem :label="t('notice.view_count')" type="number" prop="view_count" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.view_count" :placeholder="t('Please input field', { field: t('notice.view_count') })" />
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
    content: [buildValidatorData({ name: 'editorRequired', title: t('notice.content') })],
    start_time: [buildValidatorData({ name: 'date', title: t('notice.start_time') })],
    end_time: [buildValidatorData({ name: 'date', title: t('notice.end_time') })],
    sort: [buildValidatorData({ name: 'number', title: t('notice.sort') })],
    view_count: [buildValidatorData({ name: 'number', title: t('notice.view_count') })],
    create_time: [buildValidatorData({ name: 'date', title: t('notice.create_time') })],
    update_time: [buildValidatorData({ name: 'date', title: t('notice.update_time') })],
})
</script>

<style scoped lang="scss"></style>
