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
                    <FormItem :label="t('user.user.reference')" type="remoteSelect" v-model="baTable.form.items!.reference" prop="reference" :input-attr="{ pk: 'ba_user.id', field: 'username', remoteUrl: '/admin/user.User/index' ,tooltipParams: {用户名: 'username',手机号: 'mobile',},}" :placeholder="t('Please select field', { field: t('user.user.reference') })" />
                    <!-- <FormItem :label="t('user.user.group_id')" type="remoteSelect" v-model="baTable.form.items!.group_id" prop="group_id" :input-attr="{ pk: 'ba_user_group.id', field: 'name', remoteUrl: '/admin/user.Group/index' }" :placeholder="t('Please select field', { field: t('user.user.group_id') })" /> -->
                    <FormItem :label="t('user.user.username')" type="string" v-model="baTable.form.items!.username" prop="username" :placeholder="t('Please input field', { field: t('user.user.username') })" />
                    <FormItem :label="t('user.user.nickname')" type="string" v-model="baTable.form.items!.nickname" prop="nickname" :placeholder="t('Please input field', { field: t('user.user.nickname') })" />
                    <FormItem :label="t('user.user.email')" type="string" v-model="baTable.form.items!.email" prop="email" :placeholder="t('Please input field', { field: t('user.user.email') })" />
                    <FormItem :label="t('user.user.mobile')" type="string" v-model="baTable.form.items!.mobile" prop="mobile" :placeholder="t('Please input field', { field: t('user.user.mobile') })" />
                    <FormItem :label="t('user.user.avatar')" type="image" v-model="baTable.form.items!.avatar" prop="avatar" />
                    <!-- <FormItem :label="t('user.user.birthday')" type="date" v-model="baTable.form.items!.birthday" prop="birthday" :placeholder="t('Please select field', { field: t('user.user.birthday') })" /> -->
                    <!-- <FormItem :label="t('user.user.credit')" type="number" prop="score" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.credit" :placeholder="t('Please input field', { field: t('user.user.credit') })" /> -->
                    <FormItem :label="t('user.user.invite_code')" type="string" v-model="baTable.form.items!.invite_code" prop="invite_code" :placeholder="t('Please input field', { field: t('user.user.invite_code') })" />
                    <!-- <FormItem :label="t('user.user.true_name')" type="switch" v-model="baTable.form.items!.true_name" prop="true_name" :input-attr="{ content: { '0': t('user.user.true_name 0'), '1': t('user.user.true_name 1') } }" /> -->
                    <!--<FormItem :label="t('user.user.is_auction')" type="switch" v-model="baTable.form.items!.is_auction" prop="is_auction" :input-attr="{ content: { '0': t('user.user.is_auction 0'), '1': t('user.user.is_auction 1') } }" />-->
                    <!-- <FormItem :label="t('user.user.salesman')" type="switch" v-model="baTable.form.items!.salesman" prop="salesman" :input-attr="{ content: { '0': t('user.user.salesman 0'), '1': t('user.user.salesman 1') } }" /> -->
                    <FormItem
                        type="remoteSelect"
                        prop="agent"
                        :label="t('user.user.agent')"
                        v-model="baTable.form.items!.agent"
                        :placeholder="t('Click select')"
                        :input-attr="{
                            pk: 'id',
                            field: 'username',
                            remoteUrl: '/admin/user.User/index',
                            tooltipParams: {用户名: 'username',手机号: 'mobile',}
                        }"
                    />
                    <el-form-item :label="t('user.user.money')" prop="money">
                        <el-input-number v-model.number="baTable.form.items!.money" :precision="4" :step="0.0001" :min="0"/>
                    </el-form-item>
                    <el-form-item :label="t('user.user.virtualmoney')" prop="virtualmoney">
                        <el-input-number v-model.number="baTable.form.items!.virtualmoney" :precision="4" :step="0.0001" :min="0"/>
                    </el-form-item>

                    <!-- <el-form-item :label="t('user.user.loanmoney')" prop="lonanmoney">
                        <el-input-number v-model.number="baTable.form.items!.lonanmoney" :precision="4" :step="0.0001" :min="0"/>
                    </el-form-item>-->
                    <!-- <FormItem :label="t('user.user.score')" type="number" prop="score" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.score" :placeholder="t('Please input field', { field: t('user.user.score') })" /> -->
                    <!-- <FormItem :label="t('user.user.gender')" type="switch" v-model="baTable.form.items!.gender" prop="gender" :input-attr="{ content: { '0': t('user.user.gender 0'), '1': t('user.user.gender 1'), '2': t('user.user.gender 2') } }" /> -->
                    <!-- <FormItem :label="t('user.user.last_login_time')" type="number" prop="last_login_time" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.last_login_time" :placeholder="t('Please input field', { field: t('user.user.last_login_time') })" />
                    <FormItem :label="t('user.user.last_login_ip')" type="string" v-model="baTable.form.items!.last_login_ip" prop="last_login_ip" :placeholder="t('Please input field', { field: t('user.user.last_login_ip') })" />
                    <FormItem :label="t('user.user.login_failure')" type="number" prop="login_failure" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.login_failure" :placeholder="t('Please input field', { field: t('user.user.login_failure') })" />
                    <FormItem :label="t('user.user.join_ip')" type="string" v-model="baTable.form.items!.join_ip" prop="join_ip" :placeholder="t('Please input field', { field: t('user.user.join_ip') })" />
                    <FormItem :label="t('user.user.join_time')" type="number" prop="join_time" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.join_time" :placeholder="t('Please input field', { field: t('user.user.join_time') })" /> -->
                    <!-- <FormItem :label="t('user.user.motto')" type="string" v-model="baTable.form.items!.motto" prop="motto" :placeholder="t('Please input field', { field: t('user.user.motto') })" /> -->
                    <FormItem :label="t('user.user.password')" type="password" v-model="baTable.form.items!.password" prop="password" :placeholder="t('Please input field', { field: t('user.user.password') })" />
                    <FormItem :label="t('user.user.password2')" type="password" v-model="baTable.form.items!.password2" prop="password2" :placeholder="t('Please input field', { field: t('user.user.password2') })" />
                    <!-- <FormItem :label="t('user.user.salt')" type="string" v-model="baTable.form.items!.salt" prop="salt" :placeholder="t('Please input field', { field: t('user.user.salt') })" /> -->
                    <FormItem :label="t('user.user.status')" type="switch" v-model="baTable.form.items!.status" prop="status" :input-attr="{ content: { '0': t('user.user.status 0'), '1': t('user.user.status 1') } }" />
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
import { regularPassword, buildValidatorData } from '/@/utils/validate'

const config = useConfig()
const formRef = ref<FormInstance>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    // reference: [buildValidatorData({ name: 'required', title: t('user.user.reference') })],
    
    password: [
        {
            validator: (rule: any, val: string, callback: Function) => {
                if (baTable.form.operate == 'Add') {
                    if (!val) {
                        return callback(new Error(t('Please input field', { field: t('user.user.Password') })))
                    }
                } else {
                    if (!val) {
                        return callback()
                    }
                }
                if (!regularPassword(val)) {
                    return callback(new Error(t('validate.Please enter the correct password')))
                }
                return callback()
            },
            trigger: 'blur',
        },
    ],
    username: [buildValidatorData({ name: 'required', title: t('user.user.username') })],
    birthday: [buildValidatorData({ name: 'date', title: t('user.user.birthday') })],
    mobile: [buildValidatorData({ name: 'mobile', title: t('user.user.mobile') })],
    credit: [buildValidatorData({ name: 'number', title: t('user.user.credit') })],
    money: [buildValidatorData({ name: 'number', title: t('user.user.money') })],
    loanmoney: [buildValidatorData({ name: 'number', title: t('user.user.loanmoney') })],
    score: [buildValidatorData({ name: 'number', title: t('user.user.score') })],
    last_login_time: [buildValidatorData({ name: 'number', title: t('user.user.last_login_time') })],
    login_failure: [buildValidatorData({ name: 'number', title: t('user.user.login_failure') })],
    join_time: [buildValidatorData({ name: 'number', title: t('user.user.join_time') })],
    update_time: [buildValidatorData({ name: 'date', title: t('user.user.update_time') })],
    create_time: [buildValidatorData({ name: 'date', title: t('user.user.create_time') })],
})
</script>

<style scoped lang="scss"></style>
