<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.users_reports`)" class="p-0">
                <template #extra>
                    <ExprotTable
                        exportType="user_reports"
                        tableName="users-reports-table"
                        :title="$t('menu.users_reports')"
                    />
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
                    {{ $t(`menu.reports`) }}
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.users_reports`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <admin-page-filters>
        <a-row :gutter="[16, 16]">
            <a-col :xs="24" :sm="24" :md="12" :lg="10" :xl="10"> </a-col>
            <a-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14">
                <a-row :gutter="[16, 16]" justify="end">
                    <a-col :xs="24" :sm="24" :md="24" :lg="10" :xl="10">
                        <a-input-group compact>
                            <a-select
                                style="width: 35%"
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
                                style="width: 65%"
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
        <a-row>
            <a-col :span="24">
                <a-tabs v-model:activeKey="userType" @change="setUrlData">
                    <a-tab-pane
                        v-if="
                            permsArray.includes('customers_view') ||
                            permsArray.includes('admin')
                        "
                        key="customers"
                        :tab="$t('menu.customers')"
                    />
                    <a-tab-pane
                        v-if="
                            permsArray.includes('suppliers_view') ||
                            permsArray.includes('admin')
                        "
                        key="suppliers"
                        :tab="$t('menu.suppliers')"
                    />
                </a-tabs>
            </a-col>
        </a-row>

        <a-row :gutter="[16, 16]">
            <a-col :span="24">
                <div class="table-responsive">
                    <a-table
                        :columns="columns"
                        :row-key="(record) => record.xid"
                        :data-source="table.data"
                        :pagination="table.pagination"
                        :loading="table.loading"
                        @change="handleTableChange"
                        id="users-reports-table"
                        bordered
                        size="middle"
                    >
                        <template #bodyCell="{ column, text, record }">
                            <template v-if="column.dataIndex === 'name'">
                                <UserInfo :user="record" />
                            </template>
                            <template
                                v-if="
                                    column.dataIndex ===
                                    'user_details.purchase_order_count'
                                "
                            >
                                <a
                                    v-if="
                                        permsArray.includes('purchases_view') ||
                                        permsArray.includes('admin')
                                    "
                                    @click="openUserReportDrawer('', record)"
                                >
                                    {{ record.details.purchase_order_count }}
                                </a>
                                <span v-else>{{
                                    record.details.purchase_order_count
                                }}</span>
                            </template>
                            <template
                                v-if="
                                    column.dataIndex ===
                                    'user_details.purchase_return_count'
                                "
                            >
                                <a
                                    v-if="
                                        permsArray.includes(
                                            'purchase_returns_view'
                                        ) || permsArray.includes('admin')
                                    "
                                    @click="
                                        openUserReportDrawer('returns', record)
                                    "
                                >
                                    {{ record.details.purchase_return_count }}
                                </a>
                                <span v-else>{{
                                    record.details.purchase_return_count
                                }}</span>
                            </template>
                            <template
                                v-if="
                                    column.dataIndex ===
                                    'user_details.sales_order_count'
                                "
                            >
                                <a
                                    v-if="
                                        permsArray.includes('sales_view') ||
                                        permsArray.includes('admin')
                                    "
                                    @click="openUserReportDrawer('', record)"
                                >
                                    {{ record.details.sales_order_count }}
                                </a>
                                <span v-else>{{
                                    record.details.sales_order_count
                                }}</span>
                            </template>
                            <template
                                v-if="
                                    column.dataIndex ===
                                    'user_details.sales_return_count'
                                "
                            >
                                <a
                                    v-if="
                                        permsArray.includes(
                                            'sales_returns_view'
                                        ) || permsArray.includes('admin')
                                    "
                                    @click="
                                        openUserReportDrawer('returns', record)
                                    "
                                >
                                    {{ record.details.sales_return_count }}
                                </a>
                                <span v-else>
                                    {{ record.details.sales_return_count }}
                                </span>
                            </template>
                            <template
                                v-if="
                                    column.dataIndex ===
                                    'user_details.total_amount'
                                "
                            >
                                {{
                                    formatAmountCurrency(
                                        convertToPositive(
                                            record.details.total_amount
                                        )
                                    )
                                }}
                            </template>
                            <template
                                v-if="
                                    column.dataIndex ===
                                    'user_details.paid_amount'
                                "
                            >
                                {{
                                    formatAmountCurrency(
                                        convertToPositive(
                                            record.details.paid_amount
                                        )
                                    )
                                }}
                            </template>
                            <template
                                v-if="
                                    column.dataIndex ===
                                    'user_details.due_amount'
                                "
                            >
                                <UserBalance
                                    :amount="record.details.due_amount"
                                />
                            </template>
                        </template>
                        <template #summary>
                            <a-table-summary-row>
                                <a-table-summary-cell :col-span="4">
                                </a-table-summary-cell>
                                <a-table-summary-cell :col-span="1">
                                    <a-typography-text strong>
                                        {{ $t("common.total") }}
                                    </a-typography-text>
                                </a-table-summary-cell>
                                <a-table-summary-cell :col-span="1">
                                    <a-typography-text strong>
                                        {{
                                            formatAmountCurrency(
                                                totals.totalAmount
                                            )
                                        }}
                                    </a-typography-text>
                                </a-table-summary-cell>
                                <a-table-summary-cell :col-span="1">
                                    <a-typography-text strong>
                                        {{
                                            formatAmountCurrency(
                                                totals.paidAmount
                                            )
                                        }}
                                    </a-typography-text>
                                </a-table-summary-cell>
                                <a-table-summary-cell :col-span="1">
                                    <a-tooltip
                                        v-if="totals.dueAmount < 0"
                                        :title="$t('payments.you_will_pay')"
                                    >
                                        <ArrowUpOutlined
                                            :style="{ color: 'red' }"
                                        />
                                        {{
                                            formatAmountCurrency(
                                                totals.dueAmount
                                            )
                                        }}
                                    </a-tooltip>
                                    <a-tooltip
                                        v-else
                                        :title="$t('payments.you_will_receive')"
                                    >
                                        <span v-if="totals.dueAmount > 0">
                                            <ArrowDownOutlined
                                                :style="{ color: 'green' }"
                                            />
                                        </span>
                                        {{
                                            formatAmountCurrency(
                                                totals.dueAmount
                                            )
                                        }}
                                    </a-tooltip>
                                </a-table-summary-cell>
                            </a-table-summary-row>
                        </template>
                    </a-table>
                </div>
            </a-col>
        </a-row>

        <Details
            :visible="detailsVisible"
            @closed="detailsVisible = false"
            :data="selectedUser"
            :orderType="activeOrderType"
            :destroyOnClose="true"
        />
    </admin-page-table-content>
</template>

<script>
import { onMounted, onBeforeMount, ref, watch, computed } from "vue";
import { useRouter } from "vue-router";
import crud from "../../../../common/composable/crud";
import common from "../../../../common/composable/common";
import fields from "./fields";
import UserInfo from "../../../../common/components/user/UserInfo.vue";
import Details from "./Details.vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import UserBalance from "../../users/UserBalance.vue";
import ExprotTable from "../../../components/report-exports/ExportTable.vue";
import { ArrowDownOutlined, ArrowUpOutlined } from "@ant-design/icons-vue";

export default {
    components: {
        UserInfo,
        Details,
        AdminPageHeader,
        UserBalance,
        ExprotTable,
        ArrowUpOutlined,
        ArrowDownOutlined
    },
    setup() {
        const { userType, columns, filterableColumns } = fields();
        const crudVariables = crud();
        const {
            formatAmountCurrency,
            getOrderTypeFromstring,
            statusColors,
            permsArray,
            convertToPositive,
            selectedWarehouse,
            willSubscriptionModuleVisible,
        } = common();
        const detailsVisible = ref(false);
        const selectedUser = ref({});
        const activeOrderType = ref("sales");
        const router = useRouter();

        onBeforeMount(() => {
            if (
                !(
                    permsArray.value.includes("customers_view") ||
                    permsArray.value.includes("suppliers_view") ||
                    permsArray.value.includes("admin")
                ) ||
                !willSubscriptionModuleVisible("reports")
            ) {
                router.push("admin.dashboard.index");
            }

            if (
                permsArray.value.includes("customers_view") ||
                permsArray.value.includes("admin")
            ) {
                userType.value = "customers";
            } else if (
                permsArray.value.includes("suppliers_view") ||
                permsArray.value.includes("admin")
            ) {
                userType.value = "suppliers";
            }
        });

        onMounted(() => {
            setUrlData();
        });

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url: `${userType.value}?fields=id,xid,user_type,name,email,profile_image,profile_image_url,phone,details{purchase_order_count,purchase_return_count,sales_order_count,sales_return_count,total_amount,paid_amount,due_amount}`,
                filters: "",
            };
            crudVariables.table.filterableColumns = filterableColumns;
            crudVariables.exportDetails.value = {
                allowExport: true,
                exportType: "user_reports",
            };

            crudVariables.fetch({
                page: 1,
            });
        };

        const openUserReportDrawer = (orderType, selectedUserData) => {
            selectedUser.value = selectedUserData;
            activeOrderType.value = orderType;
            detailsVisible.value = true;
        };

        watch(selectedWarehouse, (newVal, oldVal) => {
            setUrlData();
        });

        const totals = computed(() => {
            let totalAmount = 0;
            let dueAmount = 0;
            let paidAmount = 0;
            crudVariables.table.data.forEach((tableRowData) => {
                totalAmount += tableRowData.details.total_amount;
                dueAmount += tableRowData.details.due_amount;
                paidAmount += tableRowData.details.paid_amount;
            });
            return {
                totalAmount,
                dueAmount,
                paidAmount,
            };
        });

        return {
            columns,
            ...crudVariables,
            filterableColumns,
            statusColors,
            userType,
            formatAmountCurrency,
            convertToPositive,
            getOrderTypeFromstring,
            setUrlData,
            permsArray,

            detailsVisible,
            openUserReportDrawer,
            selectedUser,
            activeOrderType,
            totals,
        };
    },
};
</script>
