<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.departments`)" class="p-0" />
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
                    {{ $t(`menu.departments`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <a-row>
        <a-col :xs="24" :sm="24" :md="24" :lg="4" :xl="4" class="bg-setting-sidebar">
            <StaffSidebar />
        </a-col>
        <a-col :xs="24" :sm="24" :md="24" :lg="20" :xl="20">
            <admin-page-filters>
                <a-row :gutter="[16, 16]">
                    <a-col :xs="24" :sm="24" :md="12" :lg="10" :xl="10">
                        <a-space>
                            <template
                                v-if="
                                    permsArray.includes('departments_create') ||
                                    permsArray.includes('admin')
                                "
                            >
                                <a-button type="primary" @click="addItem">
                                    <PlusOutlined />
                                    {{ $t("department.add") }}
                                </a-button>
                            </template>
                            <a-button
                                v-if="
                                    table.selectedRowKeys.length > 0 &&
                                    (permsArray.includes('departments_delete') ||
                                        permsArray.includes('admin'))
                                "
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
                            <a-col :xs="24" :sm="24" :md="12" :lg="12" :xl="8">
                                <a-input-group compact>
                                    <a-select
                                        style="width: 25%"
                                        v-model:value="table.searchColumn"
                                        :placeholder="
                                            $t('common.select_default_text', [''])
                                        "
                                    >
                                        <a-select-option
                                            v-for="filterableColumn in filterableColumns"
                                            :key="filterableColumn.key"
                                        >
                                            {{ filterableColumn.value }}
                                        </a-select-option>
                                    </a-select>
                                    <a-input-search
                                        style="width: 75%"
                                        v-model:value="table.searchString"
                                        show-search
                                        @change="onTableSearch"
                                        @search="onTableSearch"
                                        :loading="table.filterLoading"
                                    />
                                </a-input-group>
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
                        <div class="table-responsive">
                            <a-table
                                :row-selection="{
                                    selectedRowKeys: table.selectedRowKeys,
                                    onChange: onRowSelectChange,
                                    getCheckboxProps: (record) => ({
                                        disabled: false,
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
                                    <template v-if="column.dataIndex === 'action'">
                                        <a-button
                                            v-if="
                                                permsArray.includes('departments_edit') ||
                                                permsArray.includes('admin')
                                            "
                                            type="primary"
                                            @click="editItem(record)"
                                            style="margin-left: 4px"
                                        >
                                            <template #icon><EditOutlined /></template>
                                        </a-button>
                                        <a-button
                                            v-if="
                                                permsArray.includes(
                                                    'departments_delete'
                                                ) || permsArray.includes('admin')
                                            "
                                            type="primary"
                                            @click="showDeleteConfirm(record.xid)"
                                            style="margin-left: 4px"
                                        >
                                            <template #icon><DeleteOutlined /></template>
                                        </a-button>
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
import { onMounted } from "vue";
import { PlusOutlined, EditOutlined, DeleteOutlined } from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import fields from "./fields";
import crud from "../../../../../common/composable/crud";
import common from "../../../../../common/composable/common";
import AddEdit from "./AddEdit.vue";
import AdminPageHeader from "../../../../../common/layouts/AdminPageHeader.vue";
import StaffSidebar from "../StaffSidebar.vue";

export default {
    components: {
        PlusOutlined,
        EditOutlined,
        DeleteOutlined,
        AddEdit,
        AdminPageHeader,
        StaffSidebar,
    },
    setup() {
        const { t } = useI18n();
        const {
            addEditUrl,
            initData,
            columns,
            filterableColumns,
            hashableColumns,
        } = fields();
        const crudVariables = crud();
        const { permsArray } = common();

        onMounted(() => {
            if (
                permsArray.value.includes("departments_edit") ||
                permsArray.value.includes("departments_delete") ||
                permsArray.value.includes("admin")
            ) {
                columns.value = [
                    ...columns.value,
                    {
                        title: t("common.action"),
                        dataIndex: "action",
                    },
                ];
            }

            crudVariables.tableUrl.value = {
                url: "departments?fields=id,xid,name",
            };
            crudVariables.table.filterableColumns = filterableColumns;

            crudVariables.fetch({
                page: 1,
            });

            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "department";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };
            crudVariables.hashableColumns.value = [...hashableColumns];
        });

        return {
            columns,
            ...crudVariables,
            filterableColumns,
            permsArray,
        };
    },
};
</script>
