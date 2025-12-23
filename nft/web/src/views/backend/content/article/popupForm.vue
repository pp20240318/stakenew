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
                    <FormItem :label="t('content.article.type')" type="string" v-model="baTable.form.items!.type" prop="type" :placeholder="t('Please input field', { field: t('content.article.type') })" />
                    <FormItem :label="t('content.article.sort_id')" type="remoteSelect" v-model="baTable.form.items!.sort_id" prop="sort_id" :input-attr="{ pk: 'ba_content_sort.id', field: 'name', remoteUrl: '/admin/content.Sort/index' }" :placeholder="t('Please select field', { field: t('content.article.sort_id') })" />
                    <FormItem :label="t('content.article.language')" type="remoteSelect" v-model="baTable.form.items!.language" prop="language" :input-attr="{ pk: 'ba_basicset.id', field: 'name', remoteUrl: '/admin/Basicset/index' ,params:{type:0}}" :placeholder="t('Please select field', { field: t('content.article.language') })" />
                    <FormItem :label="t('content.article.title')" type="string" v-model="baTable.form.items!.title" prop="title" :placeholder="t('Please input field', { field: t('content.article.title') })" />
                    <FormItem :label="t('content.article.content_text')" type="textarea" v-model="baTable.form.items!.content_text" prop="content_text" @keyup.enter.stop="" @keyup.ctrl.enter="baTable.onSubmit(formRef)" :placeholder="t('Please input field', { field: t('content.article.content_text') })" />
                    <FormItem :label="t('content.article.user_ids')" type="remoteSelects" v-model="baTable.form.items!.user_ids" prop="user_ids" :input-attr="{ pk: 'ba_user.id', field: 'username', remoteUrl: '/admin/user.User/index' }" :placeholder="t('Please select field', { field: t('content.article.user_ids') })" />
                    <FormItem :label="t('content.article.weigh')" type="number" prop="weigh" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.weigh" :placeholder="t('Please input field', { field: t('content.article.weigh') })" />
                    <FormItem :label="t('content.article.status')" type="switch" v-model="baTable.form.items!.status" prop="status" :input-attr="{ content: { '0': t('content.article.status 0'), '1': t('content.article.status 1') } }" />
                    <!-- <FormItem :label="t('content.article.show')" type="switch" v-model="baTable.form.items!.show" prop="show" :input-attr="{ content: { '0': t('content.article.show 0'), '1': t('content.article.show 1') } }" /> -->
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
    type: [buildValidatorData({ name: 'required', title: t('content.article.type') })],
    sort_id: [buildValidatorData({ name: 'required', title: t('content.article.sort_id') })],
    language: [buildValidatorData({ name: 'required', title: t('content.article.language') })],
    title: [buildValidatorData({ name: 'required', title: t('content.article.title') })],
    create_time: [buildValidatorData({ name: 'date', title: t('content.article.create_time') })],
})
</script>

<style scoped lang="scss"></style>
