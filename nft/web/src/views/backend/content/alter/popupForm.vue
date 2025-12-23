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
                    <FormItem :label="t('content.alter.language')" type="remoteSelect" v-model="baTable.form.items!.language" prop="language" :input-attr="{ pk: 'ba_basicset.id', field: 'name', remoteUrl: '/admin/Basicset/index' ,params:{type:0}}" :placeholder="t('Please select field', { field: t('content.alter.language') })" />
                    <FormItem :label="t('content.alter.title')" type="string" v-model="baTable.form.items!.title" prop="title" :placeholder="t('Please input field', { field: t('content.alter.title') })" />
                    <FormItem :label="t('content.alter.content_text')" type="textarea" v-model="baTable.form.items!.content_text" prop="content_text" @keyup.enter.stop="" @keyup.ctrl.enter="baTable.onSubmit(formRef)" :placeholder="t('Please input field', { field: t('content.article.content_text') })" />
                    <FormItem :label="t('content.alter.start_time')" type="datetime" v-model="baTable.form.items!.start_time" prop="start_time" :placeholder="t('Please select field', { field: t('content.alter.start_time') })" />
                    <FormItem :label="t('content.alter.end_time')" type="datetime" v-model="baTable.form.items!.end_time" prop="end_time" :placeholder="t('Please select field', { field: t('content.alter.end_time') })" />
                    <FormItem :label="t('content.alter.user_ids')" type="remoteSelects" v-model="baTable.form.items!.user_ids" prop="user_ids" :input-attr="{ pk: 'ba_user.id', field: 'username', remoteUrl: '/admin/user.User/index' }" :placeholder="t('Please select field', { field: t('content.alter.user_ids') })" />
                    <FormItem :label="t('content.alter.show')" type="switch" v-model="baTable.form.items!.show" prop="show" :input-attr="{ content: { '0': t('content.alter.show 0'), '1': t('content.alter.show 1') } }" />
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
    language: [buildValidatorData({ name: 'required', title: t('content.alter.language') })],
    title: [buildValidatorData({ name: 'required', title: t('content.alter.title') })],
    user_ids: [buildValidatorData({ name: 'required', title: t('content.alter.user_ids') })],
    start_time: [buildValidatorData({ name: 'date', title: t('content.alter.start_time') })],
    end_time: [buildValidatorData({ name: 'date', title: t('content.alter.end_time') })],
    create_time: [buildValidatorData({ name: 'date', title: t('content.alter.create_time') })],
})
</script>

<style scoped lang="scss"></style>
