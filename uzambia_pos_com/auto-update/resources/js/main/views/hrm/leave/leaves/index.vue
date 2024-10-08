<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.leaves`)" class="p-0" />
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.hrm`) }}
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.leaves`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>
    <a-row>
        <a-col :xs="24" :sm="24" :md="24" :lg="4" :xl="4" class="bg-setting-sidebar">
            <LeaveSidebar />
        </a-col>
        <a-col :xs="24" :sm="24" :md="24" :lg="20" :xl="20">
            <admin-page-filters>
                <a-row :gutter="[16, 16]">
                    <a-col :xs="24" :sm="24" :md="12" :lg="10" :xl="10">
                        <a-space>
                            <a-button type="primary" @click="addItem">
                                <PlusOutlined />
                                {{ $t("leave.add") }}
                            </a-button>
                            <a-button
                                v-if="table.selectedRowKeys.length > 0"
                                type="primary"
                                @click="showSelectedDeleteConfirm"
                                danger
                            >
                                <template #icon><DeleteOutlined /></template>
                                {{ $t("common.delete") }}
                            </a-button>
                        </a-space>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14">
                        <a-row :gutter="[16, 16]" justify="end">
                            <a-col :xs="24" :sm="24" :md="12" :lg="8" :xl="8">
                                <a-select
                                    v-model:value="extraFilters.leave_type_id"
                                    @change="setUrlData"
                                    show-search
                                    style="width: 100%"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('leave.leave_type'),
                                        ])
                                    "
                                    :allowClear="true"
                                >
                                    <a-select-option
                                        v-for="leaveType in leaveTypes"
                                        :key="leaveType.xid"
                                        :value="leaveType.xid"
                                        :title="leaveType.name"
                                    >
                                        {{ leaveType.name }}
                                    </a-select-option>
                                </a-select>
                            </a-col>
                            <a-col
                                :xs="24"
                                :sm="24"
                                :md="12"
                                :lg="8"
                                :xl="8"
                                v-if="
                                    permsArray.includes('admin') ||
                                    permsArray.includes('leaves_assign_to_all')
                                "
                            >
                                <a-select
                                    v-model:value="extraFilters.user_id"
                                    @change="setUrlData"
                                    show-search
                                    style="width: 100%"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('leave.user'),
                                        ])
                                    "
                                    :allowClear="true"
                                >
                                    <a-select-option
                                        v-for="allUser in allUsers"
                                        :key="allUser.xid"
                                        :value="allUser.xid"
                                        :title="allUser.name"
                                    >
                                        {{ allUser.name }}
                                    </a-select-option>
                                </a-select>
                            </a-col>
                        </a-row>
                    </a-col>
                </a-row>
            </admin-page-filters>

            <admin-page-table-content>
                <AddEdit
                    :addEditType="addEditType"
                    :visible="addEditVisible"
                    :url="addEditUrl"
                    @addEditSuccess="addEditSuccess"
                    @closed="onCloseAddEdit"
                    :formData="formData"
                    :data="viewData"
                    :pageTitle="pageTitle"
                    :successMessage="successMessage"
                />
                <a-row>
                    <a-col :span="24">
                        <a-tabs
                            v-model:activeKey="extraFilters.status"
                            @change="setUrlData"
                        >
                            <a-tab-pane key="all" :tab="`${$t('common.all')}`" />
                            <a-tab-pane key="pending" :tab="`${$t('common.pending')}`" />
                            <a-tab-pane
                                key="approved"
                                :tab="`${$t('common.approved')}`"
                            />
                            <a-tab-pane
                                key="rejected"
                                :tab="`${$t('common.rejected')}`"
                            />
                        </a-tabs>
                    </a-col>
                </a-row>

                <a-row>
                    <a-col :span="24">
                        <div class="table-responsive">
                            <a-table
                                :row-selection="{
                                    selectedRowKeys: table.selectedRowKeys,
                                    onChange: onRowSelectChange,
                                    getCheckboxProps: (record) => ({
                                        disabled:
                                            permsArray.includes('admin') ||
                                            permsArray.includes('leaves_delete_all') ||
                                            record.status == 'pending'
                                                ? false
                                                : true,
                                        name: record.xid,
                                    }),
                                }"
                                :columns="columns"
                                :row-key="(record) => record.xid"
                                :data-source="table.data"
                                :pagination="table.pagination"
                                :loading="table.loading"
                                @change="handleTableChange"
                                bordered
                                size="middle"
                            >
                                <template #bodyCell="{ column, record }">
                                    <template v-if="column.dataIndex == 'user_id'">
                                        {{ record.user.name }}
                                    </template>
                                    <template v-if="column.dataIndex == 'start_date'">
                                        {{ formatDate(record.start_date) }}
                                    </template>
                                    <template v-if="column.dataIndex == 'end_date'">
                                        {{ formatDate(record.end_date) }}
                                    </template>
                                    <template v-if="column.dataIndex == 'is_half_day'">
                                        {{
                                            record.is_half_day
                                                ? $t("common.yes")
                                                : $t("common.no")
                                        }}
                                    </template>

                                    <template v-if="column.dataIndex == 'leave_type_id'">
                                        {{ record.leave_type.name }}
                                    </template>
                                    <template v-if="column.dataIndex === 'status'">
                                        <div v-if="record.status == 'pending'">
                                            <a-tag color="yellow">
                                                {{ $t(`common.${"pending"}`) }}
                                            </a-tag>
                                        </div>
                                        <div v-if="record.status == 'approved'">
                                            <a-tag color="green">
                                                {{ $t(`common.${"approved"}`) }}
                                            </a-tag>
                                        </div>
                                        <div v-if="record.status == 'rejected'">
                                            <a-tag color="red">
                                                {{ $t(`common.${"rejected"}`) }}
                                            </a-tag>
                                        </div>
                                    </template>

                                    <template v-if="column.dataIndex === 'action'">
                                        <a-space>
                                            <a-button
                                                type="primary"
                                                @click="leaveApproved(record.xid)"
                                                v-if="
                                                    record.status == 'pending' &&
                                                    (permsArray.includes('admin') ||
                                                        permsArray.includes(
                                                            'leaves_approve_reject'
                                                        ))
                                                "
                                            >
                                                <template #icon
                                                    ><CheckOutlined
                                                /></template>
                                            </a-button>
                                            <a-button
                                                type="primary"
                                                @click="leaveRejected(record.xid)"
                                                danger
                                                v-if="
                                                    record.status == 'pending' &&
                                                    (permsArray.includes('admin') ||
                                                        permsArray.includes(
                                                            'leaves_approve_reject'
                                                        ))
                                                "
                                            >
                                                <template #icon
                                                    ><CloseOutlined
                                                /></template>
                                            </a-button>
                                            <a-button
                                                v-if="
                                                    record.status == 'pending' &&
                                                    (record.x_user_id == user.xid ||
                                                        permsArray.includes('admin') ||
                                                        permsArray.includes(
                                                            'leaves_edit_all'
                                                        ))
                                                "
                                                type="primary"
                                                @click="editItem(record)"
                                            >
                                                <template #icon
                                                    ><EditOutlined
                                                /></template>
                                            </a-button>
                                            <a-button
                                                v-if="
                                                    (record.status == 'pending' &&
                                                        record.x_user_id == user.xid) ||
                                                    permsArray.includes('admin') ||
                                                    permsArray.includes(
                                                        'leaves_delete_all'
                                                    )
                                                "
                                                type="primary"
                                                @click="showDeleteConfirm(record.xid)"
                                            >
                                                <template #icon
                                                    ><DeleteOutlined
                                                /></template>
                                            </a-button>
                                        </a-space>
                                    </template>
                                </template>
                            </a-table>
                        </div>
                    </a-col>
                </a-row>
            </admin-page-table-content>
        </a-col>
    </a-row>
</template>
<script>
import { onMounted, ref, createVNode } from "vue";
import {
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
    ExclamationCircleOutlined,
    CloseOutlined,
    CheckOutlined,
} from "@ant-design/icons-vue";
import { Modal, notification } from "ant-design-vue";
import fields from "./fields";
import crud from "../../../../../common/composable/crud";
import common from "../../../../../common/composable/common";
import AddEdit from "./AddEdit.vue";
import AdminPageHeader from "../../../../../common/layouts/AdminPageHeader.vue";
import { useI18n } from "vue-i18n";
import LeaveSidebar from "../LeaveSidebar.vue";

export default {
    components: {
        PlusOutlined,
        EditOutlined,
        DeleteOutlined,
        AddEdit,
        AdminPageHeader,
        LeaveSidebar,
        CloseOutlined,
        CheckOutlined,
    },
    setup() {
        const {
            addEditUrl,
            initData,
            columns,
            filterableColumns,
            hashableColumns,
        } = fields();
        const crudVariables = crud();
        const { t } = useI18n();
        const { permsArray, appSetting, formatDate, user } = common();
        const extraFilters = ref({
            status: "all",
            user_id: undefined,
            leave_type_id: undefined,
        });
        const activeTabKey = ref("all");
        const allUsers = ref([]);
        const staffMembersUrl = "users?limit=10000";
        const leaveTypes = ref([]);
        const leaveTypesUrl = "leave-types?limit=10000";

        onMounted(() => {
            setUrlData();
            const leaveTypesPromise = axiosAdmin.get(leaveTypesUrl);
            const staffMemberPromise = axiosAdmin.get(staffMembersUrl);
            Promise.all([staffMemberPromise, leaveTypesPromise]).then(
                ([staffMemberResponse, leaveTypesResponse]) => {
                    allUsers.value = staffMemberResponse.data;
                    leaveTypes.value = leaveTypesResponse.data;
                }
            );

            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "leave";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };
            crudVariables.hashableColumns.value = [...hashableColumns];
        });

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url:
                    "leaves?fields=id,xid,user_id,x_user_id,user{id,xid,name},leave_type_id,x_leave_type_id,leaveType{id,xid,name},start_date,end_date,is_half_day,reason,status",
                extraFilters,
            };
            crudVariables.table.filterableColumns = filterableColumns;

            crudVariables.fetch({
                page: 1,
            });
        };
        const leaveApproved = (xid) => {
            Modal.confirm({
                title: t("common.approved") + "?",
                icon: createVNode(ExclamationCircleOutlined),
                content: t("common.approved_message"),
                centered: true,
                okText: t("common.yes"),
                okType: "danger",
                cancelText: t("common.no"),

                onOk() {
                    return axiosAdmin
                        .post(`leaves/update-status/${xid}`, { status: "approved" })
                        .then((successResponse) => {
                            setUrlData();
                            // Update Visible Subscription Modules
                            notification.success({
                                message: t("common.success"),
                                description: t(`common.status_changed`),
                                placement: "bottomRight",
                            });
                        })
                        .catch(() => {});
                },
                onCancel() {},
            });
        };

        const leaveRejected = (xid) => {
            Modal.confirm({
                title: t("common.rejected") + "?",
                icon: createVNode(ExclamationCircleOutlined),
                content: t("common.rejected_message"),
                centered: true,
                okText: t("common.yes"),
                okType: "danger",
                cancelText: t("common.no"),

                onOk() {
                    return axiosAdmin
                        .post(`leaves/update-status/${xid}`, { status: "rejected" })
                        .then((successResponse) => {
                            setUrlData();
                            // Update Visible Subscription Modules
                            notification.success({
                                message: t("common.success"),
                                description: t(`common.status_changed`),
                                placement: "bottomRight",
                            });
                        })
                        .catch(() => {});
                },
                onCancel() {},
            });
        };

        return {
            columns,
            ...crudVariables,
            filterableColumns,
            permsArray,
            appSetting,
            formatDate,
            setUrlData,
            leaveApproved,
            leaveRejected,
            activeTabKey,
            allUsers,
            staffMembersUrl,
            extraFilters,
            leaveTypes,
            user,
        };
    },
};
</script>
