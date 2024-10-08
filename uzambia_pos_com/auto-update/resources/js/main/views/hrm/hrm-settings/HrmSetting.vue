<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.hrm_settings`)" class="p-0">
                <template #extra>
                    <a-button type="primary" @click="onSubmit">
                        <template #icon> <SaveOutlined /> </template>
                        {{ $t("common.update") }}
                    </a-button>
                </template>
            </a-page-header>
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t("menu.settings") }}
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t("menu.hrm_settings") }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>
    <admin-page-table-content>
        <a-card class="page-content-container mt-20 mb-20">
            <a-form layout="vertical">
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="24" :lg="24">
                        <a-form-item
                            :label="$t('hrm_settings.leave_start_month')"
                            name="leave_start_month"
                            :help="
                                rules.leave_start_month
                                    ? rules.leave_start_month.message
                                    : null
                            "
                            :validateStatus="rules.leave_start_month ? 'error' : null"
                            class="required"
                        >
                            <a-select
                                v-model:value="formData.leave_start_month"
                                :placeholder="
                                    $t('common.select_default_text', [
                                        $t('hrm_settings.leave_start_month'),
                                    ])
                                "
                                style="width: 100%"
                                optionFilterProp="title"
                                show-search
                            >
                                <a-select-option
                                    v-for="month in monthArrays"
                                    :key="month.name"
                                    :value="month.value"
                                >
                                    {{ month.name }}
                                </a-select-option>
                            </a-select>
                        </a-form-item>
                    </a-col>
                </a-row>
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('hrm_settings.clock_in_time')"
                            name="clock_in_time"
                            :help="
                                rules.clock_in_time ? rules.clock_in_time.message : null
                            "
                            :validateStatus="rules.clock_in_time ? 'error' : null"
                            class="required"
                        >
                            <a-time-picker
                                v-model:value="formData.clock_in_time"
                                valueFormat="hh:mm:ss A"
                                style="width: 100%"
                                use12-hours
                                :allowClear="false"
                            />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('hrm_settings.clock_out_time')"
                            name="clock_out_time"
                            :help="
                                rules.clock_out_time ? rules.clock_out_time.message : null
                            "
                            :validateStatus="rules.clock_out_time ? 'error' : null"
                            class="required"
                        >
                            <a-time-picker
                                v-model:value="formData.clock_out_time"
                                valueFormat="h:mm:ss A"
                                style="width: 100%"
                                use12-hours
                                :allowClear="false"
                            />
                        </a-form-item>
                    </a-col>
                </a-row>
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('hrm_settings.early_clock_in_time')"
                            name="early_clock_in_time"
                            :help="
                                rules.early_clock_in_time
                                    ? rules.early_clock_in_time.message
                                    : null
                            "
                            :validateStatus="rules.early_clock_in_time ? 'error' : null"
                        >
                            <a-input-number
                                v-model:value="formData.early_clock_in_time"
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('hrm_settings.early_clock_in_time'),
                                    ])
                                "
                                style="width: 100%"
                            >
                                <template #addonAfter>
                                    {{ $t("common.minutes") }}
                                </template>
                            </a-input-number>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('hrm_settings.allow_clock_out_till')"
                            name="allow_clock_out_till"
                            :help="
                                rules.allow_clock_out_till
                                    ? rules.allow_clock_out_till.message
                                    : null
                            "
                            :validateStatus="rules.allow_clock_out_till ? 'error' : null"
                        >
                            <a-input-number
                                v-model:value="formData.allow_clock_out_till"
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('hrm_settings.allow_clock_out_till'),
                                    ])
                                "
                                style="width: 100%"
                            >
                                <template #addonAfter>
                                    {{ $t("common.minutes") }}
                                </template>
                            </a-input-number>
                        </a-form-item>
                    </a-col>
                </a-row>
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('hrm_settings.late_mark_after')"
                            name="late_mark_after"
                            :help="
                                rules.late_mark_after
                                    ? rules.late_mark_after.message
                                    : null
                            "
                            :validateStatus="rules.late_mark_after ? 'error' : null"
                        >
                            <a-input-number
                                v-model:value="formData.late_mark_after"
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('hrm_settings.late_mark_after'),
                                    ])
                                "
                                style="width: 100%"
                            >
                                <template #addonAfter>
                                    {{ $t("common.minutes") }}
                                </template>
                            </a-input-number>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('hrm_settings.self_clocking')"
                            name="self_clocking"
                            :help="
                                rules.self_clocking ? rules.self_clocking.message : null
                            "
                            :validateStatus="rules.self_clocking ? 'error' : null"
                        >
                            <a-radio-group
                                v-model:value="formData.self_clocking"
                                button-style="solid"
                                size="small"
                            >
                                <a-radio-button :value="1">
                                    {{ $t("common.yes") }}
                                </a-radio-button>
                                <a-radio-button :value="0">
                                    {{ $t("common.no") }}
                                </a-radio-button>
                            </a-radio-group>
                        </a-form-item>
                    </a-col>
                </a-row>
                <a-row
                    :gutter="16"
                    v-for="(formField, index) in formFields"
                    :key="formField.id"
                    style="display: flex; align-items: center"
                >
                    <a-col :xs="24" :sm="24" :md="23" :lg="23">
                        <a-form-item
                            :label="$t('hrm_settings.allowed_ip_address')"
                            name="allowed_ip_address"
                        >
                            <a-input
                                v-model:value="formField.allowed_ip_address"
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('hrm_settings.allowed_ip_address'),
                                    ])
                                "
                            />
                        </a-form-item>
                    </a-col>
                    <a-col :span="1" style="margin-top: 6px">
                        <MinusSquareOutlined @click="removeFormField(formField)" />
                    </a-col>
                </a-row>
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item>
                        <a-button type="dashed" block @click="addFormField">
                            <PlusOutlined />

                            {{ $t("hrm_settings.allowed_ip_address") }}
                        </a-button>
                    </a-form-item>
                </a-col>
            </a-form>
            <a-col>
                <a-button
                    type="primary"
                    @click="onSubmit"
                    style="margin-right: 8px"
                    :loading="loading"
                >
                    <template #icon> <SaveOutlined /> </template>
                    {{ $t("common.update") }}
                </a-button>
            </a-col>
        </a-card>
    </admin-page-table-content>
</template>
<script>
import { defineComponent, ref, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import apiAdmin from "../../../../common/composable/apiAdmin";
import hrmManagement from "../../../../common/composable/hrmManagement";
import common from "../../../../common/composable/common";
import dayjs from "dayjs";
import { forEach } from "lodash-es";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";

import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
    SettingOutlined,
    MinusSquareOutlined,
} from "@ant-design/icons-vue";
import { useStore } from "vuex";

export default defineComponent({
    emits: ["success"],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
        SettingOutlined,
        MinusSquareOutlined,
        AdminPageHeader,
    },
    setup({ emit }) {
        const { addEditRequestAdmin, rules } = apiAdmin();
        const { permsArray, appSetting } = common();
        const store = useStore();
        const { t } = useI18n();
        const { monthArrays } = hrmManagement();
        const removedIpAddress = ref([]);
        const formFields = ref([]);
        const formData = ref({
            leave_start_month: appSetting.value.leave_start_month,
            clock_in_time: dayjs(appSetting.value.clock_in_time, "HH:mm:ss").format(
                "hh:mm:ss A"
            ),
            clock_out_time: dayjs(appSetting.value.clock_out_time, "HH:mm:ss").format(
                "hh:mm:ss A"
            ),
            late_mark_after: appSetting.value.late_mark_after,
            self_clocking: appSetting.value.self_clocking,
            early_clock_in_time: appSetting.value.early_clock_in_time,
            allow_clock_out_till: appSetting.value.allow_clock_out_till,
            allowed_ip_address: appSetting.value.allowed_ip_address
                ? appSetting.value.allowed_ip_address
                : [],
        });
        const loading = ref(false);

        onMounted(() => {
            formFields.value = appSetting.value.allowed_ip_address
                ? appSetting.value.allowed_ip_address
                : [];
        });

        const visible = ref(false);

        const onSubmit = () => {
            loading.value = true;
            let clockInTime = dayjs(formData.value.clock_in_time, "hh:mm:ss A").format(
                "HH:mm:ss"
            );
            let clockOutTime = dayjs(formData.value.clock_out_time, "hh:mm:ss A").format(
                "HH:mm:ss"
            );

            let newFormData = {
                ...formData.value,
                clock_in_time: clockInTime,
                clock_out_time: clockOutTime,
                allowed_ip_address: ipAddressFilter(),
            };

            addEditRequestAdmin({
                url: "hrm/update-settings",
                data: newFormData,
                successMessage: t("hrm_settings.updated"),
                success: (res) => {
                    visible.value = false;
                    loading.value = false;
                    store.dispatch("auth/updateApp");
                    store.dispatch("hrm/updateSelfClocking");
                    emit("success");
                },
            });
        };

        const addFormField = () => {
            formFields.value.push({
                allowed_ip_address: "",
            });
        };

        const removeFormField = (item) => {
            let index = formFields.value.indexOf(item);
            if (index !== -1) {
                formFields.value.splice(index, 1);
            }

            if (item.id != "") {
                removedIpAddress.value.push(item.id);
            }
        };

        const ipAddressFilter = () => {
            var newFormField = [];

            forEach(formFields.value, (formField) => {
                if (formField.allowed_ip_address != "") {
                    newFormField.push(formField);
                }
            });

            return newFormField;
        };

        const openDrawer = () => {
            visible.value = true;
        };

        const onClose = () => {
            visible.value = false;
        };

        return {
            formData,
            rules,
            permsArray,
            appSetting,
            monthArrays,
            visible,
            formFields,
            addFormField,
            removeFormField,
            onClose,
            openDrawer,
            onSubmit,
            loading,

            drawerWidth: window.innerWidth <= 991 ? "90%" : "35%",
        };
    },
});
</script>
