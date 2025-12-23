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
                    <!-- <FormItem :label="t('content.sort.pid')" type="remoteSelect" v-model="baTable.form.items!.pid" prop="pid" :input-attr="{ pk: 'ba_content_sort.id', field: 'name', remoteUrl: '/admin/content.Sort/index' }" :placeholder="t('Please select field', { field: t('content.sort.pid') })" /> -->
                    <FormItem
                        type="remoteSelect"
                        prop="pid"
                        :label="t('content.sort.pid')"
                        v-model="baTable.form.items!.pid"
                        :placeholder="t('Click select')"
                        :input-attr="{
                            params: { isTree: true },
                            field: 'name',
                            remoteUrl: baTable.api.actionUrl.get('index'),
                        }"
                    />
                    <FormItem :label="t('content.sort.name')" type="string" v-model="baTable.form.items!.name" prop="name" :placeholder="t('Please input field', { field: t('content.sort.name') })" />
                    <FormItem :label="t('content.sort.weigh')" type="number" prop="weigh" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.weigh" :placeholder="t('Please input field', { field: t('content.sort.weigh') })" />
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
    name: [buildValidatorData({ name: 'required', title: t('content.sort.name') })],
    weigh: [buildValidatorData({ name: 'date', title: t('content.sort.weigh') })],
    create_time: [buildValidatorData({ name: 'date', title: t('content.sort.create_time') })],
    pid: [
        {
            validator: (rule: any, val: string, callback: Function) => {
                if (!val) {
                    return callback()
                }
                if (parseInt(val) == parseInt(baTable.form.items!.id)) {
                    return callback(new Error('上级不能是自身'))
                }
                return callback()
            },
            trigger: 'blur',
        },
    ],
})
</script>

<style scoped lang="scss"></style>
