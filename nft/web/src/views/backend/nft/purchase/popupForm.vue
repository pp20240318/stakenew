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
                    <FormItem :label="t('nft.purchase.reference')" type="remoteSelect" v-model="baTable.form.items!.reference" prop="reference" :input-attr="{ pk: 'ba_user.id', field: 'username', remoteUrl: '/admin/user.User/index' ,tooltipParams: {id: 'id',用户名: 'username',电话: 'mobile',} }" :placeholder="t('Please select field', { field: t('nft.purchase.reference') })" />
                    <FormItem :label="t('nft.purchase.user_id')"  type="remoteSelect" v-model="baTable.form.items!.user_id" prop="user_id" :input-attr="{ pk: 'ba_user.id', field: 'username', remoteUrl: '/admin/user.User/index' ,tooltipParams: {id: 'id',用户名: 'username',电话: 'mobile',} }" :placeholder="t('Please select field', { field: t('nft.purchase.user_id') })"  />
                    <FormItem :label="t('nft.purchase.status')" type="radio" v-model="baTable.form.items!.status" prop="status" :input-attr="{ content: { opt0: t('nft.purchase.status opt0'), opt1: t('nft.purchase.status opt1') } }" :placeholder="t('Please select field', { field: t('nft.purchase.status') })" />
                    <FormItem :label="t('nft.purchase.source')" type="string" v-model="baTable.form.items!.source" prop="source" :placeholder="t('Please input field', { field: t('nft.purchase.source') })" />
                    <FormItem :label="t('nft.purchase.product_id')" type="remoteSelect" v-model="baTable.form.items!.product_id" prop="product_id" :input-attr="{ pk: 'ba_nft_product.id', field: 'computer_name', remoteUrl: '/admin/nft.Product/index'  ,tooltipParams: {id: 'id',产品名: 'computer_name',描述: 'description',}}" :placeholder="t('Please select field', { field: t('nft.purchase.product_id') })" />
                    <FormItem :label="t('nft.purchase.valid_day')" type="number" prop="valid_day" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.valid_day" :placeholder="t('Please input field', { field: t('nft.purchase.valid_day') })" />
                    <FormItem :label="t('nft.purchase.bonus')" type="number" prop="bonus" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.bonus" :placeholder="t('Please input field', { field: t('nft.purchase.bonus') })" />
                    
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
import { useAdminInfo } from '/@/stores/adminInfo'

const adminInfo = useAdminInfo()
const config = useConfig()
const formRef = ref<FormInstance>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    id: [buildValidatorData({ name: 'number', title: t('nft.purchase.id') })],
    reference: [buildValidatorData({ name: 'required', title: t('nft.purchase.reference') })],
    user_id: [buildValidatorData({ name: 'required', title: t('nft.purchase.user_id') })],
    product_id: [buildValidatorData({ name: 'required', title: t('nft.purchase.product_id') })],
    create_time: [buildValidatorData({ name: 'date', title: t('nft.purchase.create_time') })],
    // expire_time: [buildValidatorData({ name: 'number', title: t('nft.purchase.expire_time') })],
    valid_day: [buildValidatorData({ name: 'number', title: t('nft.purchase.valid_day') })],
    bonus: [buildValidatorData({ name: 'number', title: t('nft.purchase.bonus') })],
})
</script>

<style scoped lang="scss"></style>
