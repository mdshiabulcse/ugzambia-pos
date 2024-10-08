<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header
                :title="
                    holidayType == 'holiday' ? $t(`menu.holidays`) : $t(`menu.weekends`)
                "
                class="p-0"
            />
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
                    {{
                        holidayType == "holiday"
                            ? $t(`menu.holidays`)
                            : $t(`menu.weekends`)
                    }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <a-row>
        <a-col :xs="24" :sm="24" :md="24" :lg="4" :xl="4" class="bg-setting-sidebar">
            <HolidaySidebar />
        </a-col>
        <a-col :xs="24" :sm="24" :md="24" :lg="20" :xl="20">
            <admin-page-filters>
                <a-row :gutter="[16, 16]">
                    <a-col :xs="24" :sm="24" :md="12" :lg="10" :xl="10">
                        <a-space>
                            <template
                                v-if="
                                    permsArray.includes('holidays_create') ||
                                    permsArray.includes('admin')
                                "
                            >
                                <a-button
                                    v-if="holidayType == 'holiday'"
                                    type="primary"
                                    @click="addItem"
                                >
                                    <PlusOutlined />
                                    {{ $t("holiday.add") }}
                                </a-button>
                                <MarkHoliday v-else @onSuccess="setUrlData" />
                            </template>
                            <a-button
                                v-if="
                                    table.selectedRowKeys.length > 0 &&
                                    (permsArray.includes('holidays_delete') ||
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
                                    <a-input-search
                                        style="width: 100%"
                                        v-model:value="table.searchString"
                                        show-search
                                        @change="onTableSearch"
                                        @search="onTableSearch"
                                        :loading="table.filterLoading"
                                        :placeholder="
                                            $t('common.placeholder_search_text', [
                                                $t('common.name'),
                                            ])
                                        "
                                    />
                                </a-input-group>
                            </a-col>
                            <a-col :xs="24" :sm="24" :md="8" :lg="8" :xl="6">
                                <a-date-picker
                                    v-model:value="extraFilters.year"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('holiday.year'),
                                        ])
                                    "
                                    picker="year"
                                    @change="setUrlData"
                                    style="width: 100%"
                                    :allowClear="false"
                                />
                            </a-col>
                            <a-col :xs="24" :sm="24" :md="8" :lg="8" :xl="6">
                                <a-select
                                    v-model:value="extraFilters.month"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('holiday.month'),
                                        ])
                                    "
                                    :allowClear="true"
                                    style="width: 100%"
                                    optionFilterProp="title"
                                    show-search
                                    @change="setUrlData"
                                >
                                    <a-select-option
                                        v-for="month in monthArrays"
                                        :key="month.name"
                                        :value="month.value"
                                    >
                                        {{ month.name }}
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
                                    <template v-if="column.dataIndex === 'date'">
                                        {{ formatDate(record.date) }}
                                    </template>
                                    <template v-if="column.dataIndex === 'action'">
                                        <a-button
                                            v-if="
                                                permsArray.includes('holidays_edit') ||
                                                permsArray.includes('admin')
                                            "
                                            type="primary"
                                            @click="editItem(record)"
                                            style="margin-left: 4px"
                                        >
                                            <template #icon>
                                                <EditOutlined />
                                            </template>
                                        </a-button>
                                        <a-button
                                            v-if="
                                                permsArray.includes('holidays_delete') ||
                                                permsArray.includes('admin')
                                            "
                                            type="primary"
                                            @click="showDeleteConfirm(record.xid)"
                                            style="margin-left: 4px"
                                        >
                                            <template #icon>
                                                <DeleteOutlined />
                                            </template>
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
import { onMounted, ref, watch } from "vue";
import { PlusOutlined, EditOutlined, DeleteOutlined } from "@ant-design/icons-vue";
import { useRoute } from "vue-router";
import fields from "./fields";
import crud from "../../../../../common/composable/crud";
import common from "../../../../../common/composable/common";
import AddEdit from "./AddEdit.vue";
import MarkHoliday from "./MarkHoliday.vue";
import { useI18n } from "vue-i18n";
import AdminPageHeader from "../../../../../common/layouts/AdminPageHeader.vue";
import HolidaySidebar from "../HolidaySidebar.vue";
import hrmManagement from "../../../../../common/composable/hrmManagement";

export default {
    components: {
        PlusOutlined,
        EditOutlined,
        DeleteOutlined,
        AddEdit,
        AdminPageHeader,
        MarkHoliday,
        HolidaySidebar,
        hrmManagement,
    },
    setup() {
        const route = useRoute();
        const { t } = useI18n();
        const { addEditUrl, initData, columns, filterableColumns } = fields();
        const crudVariables = crud();
        const { permsArray, appSetting, formatDate, dayjs } = common();
        const holidayType = ref(route.meta.holidayType);
        const { monthArrays } = hrmManagement();
        const filterMonthYear = ref(dayjs());
        const extraFilters = ref({
            year: filterMonthYear,
            month: undefined,
        });

        onMounted(() => {
            setUrlData();
            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "holiday";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };
        });

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url: `holidays?fields=id,xid,name,year,date,month,is_weekend`,
                extraFilters: {
                    ...extraFilters.value,
                    holiday_type: holidayType.value,
                    year: filterMonthYear.value.format("YYYY"),
                },
            };

            crudVariables.table.filterableColumns = filterableColumns;
            crudVariables.table.sorter = { field: "date", order: "desc" };

            crudVariables.fetch({
                page: 1,
            });
        };

        watch(
            () => route.meta.holidayType,
            (newVal, oldVal) => {
                if (
                    route.meta.holidayType &&
                    (route.meta.holidayType == "holiday" ||
                        route.meta.holidayType == "weekend")
                ) {
                    holidayType.value = route.meta.holidayType;

                    setUrlData();
                }
            }
        );

        return {
            columns,
            ...crudVariables,
            filterableColumns,
            permsArray,
            appSetting,
            formatDate,
            setUrlData,
            monthArrays,
            filterMonthYear,
            extraFilters,
            holidayType,
        };
    },
};
</script>
