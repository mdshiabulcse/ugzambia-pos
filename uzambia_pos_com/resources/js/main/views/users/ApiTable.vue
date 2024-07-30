<template>

        <!-- Existing Content -->

        <!-- New Table -->
        <a-table :data-source="totalData" :columns="totalTableColumns" row-key="id">
            <!-- You can customize table columns and other properties here -->
        </a-table>

</template>

<script>
import { defineComponent, ref, onMounted, watch } from "vue";
import { PlusOutlined, LoadingOutlined, SaveOutlined } from "@ant-design/icons-vue";
import { useStore } from "vuex";
import { forEach } from "lodash-es";
import apiAdmin from "../../../common/composable/apiAdmin";
import Upload from "../../../common/core/ui/file/Upload.vue";
import WarehouseAddButton from "../settings/warehouses/AddButton.vue";
import RoleAddButton from "../settings/roles/AddButton.vue";
import common from "../../../common/composable/common";
import TooltipWarehouse from "./TooltipWarehouse.vue";

export default defineComponent({
    props: [
        "addEditData",
        "data",
        "visible",
        "url",
        "addEditType",
        "pageTitle",
        "successMessage",
    ],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
        Upload,
        RoleAddButton,
        WarehouseAddButton,
        TooltipWarehouse,
    },
    setup(props, { emit }) {
        const { permsArray, user, appSetting, selectedWarehouse } = common();
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const roles = ref([]);
        const warehouses = ref([]);
        const formData = ref({});
        const totalData = ref([]); // New table data
        const totalTableColumns = [ // New table columns
            {
                title: 'ID',
                dataIndex: 'id',
                key: 'id',
            },
            {
                title: 'Province',
                dataIndex: 'province',
                key: 'province',
            },
            {
                title: 'District',
                dataIndex: 'district',
                key: 'district',
            },
            {
                title: 'Ministry',
                dataIndex: 'ministry',
                key: 'ministry',
            },
            {
                title: 'Employee No',
                dataIndex: 'employeeNo',
                key: 'employeeNo',
            },
            {
                title: 'Man No',
                dataIndex: 'manNo',
                key: 'manNo',
            },
            {
                title: 'NRC No',
                dataIndex: 'nrcNo',
                key: 'nrcNo',
            },
            {
                title: 'Names',
                dataIndex: 'names',
                key: 'names',
            },
            {
                title: 'Reference No',
                dataIndex: 'referenceNo',
                key: 'referenceNo',
            },
            {
                title: 'Period Name',
                dataIndex: 'periodName',
                key: 'periodName',
            },
            {
                title: 'Sub Total',
                dataIndex: 'subTotal',
                key: 'subTotal',
            },
            {
                title: 'Total',
                dataIndex: 'total',
                key: 'total',
            },
            {
                title: 'Emp No',
                dataIndex: 'empNo',
                key: 'empNo',
            },
            {
                title: 'Rec No',
                dataIndex: 'recNo',
                key: 'recNo',
            },
            {
                title: 'Import At',
                dataIndex: 'importAt',
                key: 'importAt',
                slots: { customRender: 'importAt' }, // Custom render for formatting
            },
            {
                title: 'Import By',
                dataIndex: 'importBy',
                key: 'importBy',
            },
        ];

        const roleUrl = "roles?limit=10000";
        const warehouseUrl = "warehouses?limit=10000";
        const totalDataUrl = "path-to-fetch-total-data"; // URL to fetch total data
        const store = useStore();
        const warehouseVisible = ref(true);

        onMounted(() => {
            const rolesPromise = axiosAdmin.get(roleUrl);
            const warehousesPromise = axiosAdmin.get(warehouseUrl);
            const totalDataPromise = axiosAdmin.get(totalDataUrl); // Fetch total data

            Promise.all([rolesPromise, warehousesPromise, totalDataPromise]).then(
                ([rolesResponse, warehousesResponse, totalDataResponse]) => {
                    roles.value = rolesResponse.data;
                    warehouses.value = warehousesResponse.data;
                    totalData.value = totalDataResponse.data; // Set new table data
                }
            );

            formData.value = { ...props.addEditData };
        });

        const onSubmit = () => {
            addEditRequestAdmin({
                url: props.url,
                data: formData.value,
                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", res.xid);

                    if (user.value.xid == res.xid) {
                        store.dispatch("auth/updateUser");
                    }
                },
            });
        };

        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        const roleAdded = () => {
            axiosAdmin.get(roleUrl).then((response) => {
                roles.value = response.data;
            });
        };

        const warehouseAdded = () => {
            axiosAdmin.get(warehouseUrl).then((response) => {
                warehouses.value = response.data;
            });
        };

        const warehousesChanged = (warehouseValues, options) => {
            formData.value = {
                ...formData.value,
                warehouse_id: warehouseValues.length > 0 ? warehouseValues[0] : undefined,
            };
        };

        const roleChanged = (value, option) => {
            if (option && option.title && option.title == "admin") {
                warehouseVisible.value = false;
            } else {
                warehouseVisible.value = true;
            }
        };

        watch(
            () => props.visible,
            (newVal, oldVal) => {
                if (newVal) {
                    warehouseVisible.value = true;
                    if (props.addEditType == "edit") {
                        warehouseVisible.value = true;

                        if (
                            props.data.user_type == "staff_members" &&
                            props.data.role &&
                            props.data.role.name == "admin"
                        ) {
                            warehouseVisible.value = false;
                        }

                        if (
                            props.data.user_type == "customers" &&
                            props.data.is_walkin_customer
                        ) {
                            warehouseVisible.value = false;
                        }
                    }

                    if (props.addEditType == "add") {
                        if (props.addEditData.user_type == "staff_members") {
                            formData.value = {
                                ...props.addEditData,
                                warehouse_id: selectedWarehouse.value.xid,
                                warehouses: [selectedWarehouse.value.xid],
                            };
                        } else {
                            formData.value = {
                                ...props.addEditData,
                                warehouse_id: selectedWarehouse.value.xid,
                                warehouses: [],
                            };
                        }
                    } else {
                        var addEditUserWarehouseId =
                            props.data.warehouse && props.data.warehouse.xid
                                ? props.data.warehouse.xid
                                : undefined;

                        var addEditUserWarehouses = [];
                        if (props.addEditData.user_type == "staff_members") {
                            var userWarehouses = props.data.user_warehouses;
                            forEach(userWarehouses, (userWarehouseValue) => {
                                addEditUserWarehouses.push(
                                    userWarehouseValue.x_warehouse_id
                                );
                            });
                        }

                        formData.value = {
                            ...props.addEditData,
                            role_id:
                                props.data.role && props.data.role.xid
                                    ? props.data.role.xid
                                    : undefined,
                            warehouse_id: addEditUserWarehouseId,
                            warehouses: addEditUserWarehouses,
                            opening_balance:
                                props.data.details && props.data.details.opening_balance
                                    ? props.data.details.opening_balance
                                    : undefined,
                            opening_balance_type:
                                props.data.details &&
                                props.data.details.opening_balance_type
                                    ? props.data.details.opening_balance_type
                                    : undefined,
                            credit_period:
                                props.data.details && props.data.details.credit_period
                                    ? props.data.details.credit_period
                                    : undefined,
                            credit_limit:
                                props.data.details && props.data.details.credit_limit
                                    ? props.data.details.credit_limit
                                    : undefined,
                            _method: "PUT",
                        };
                    }
                }
            }
        );

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            roles,
            warehouses,
            formData,

            roleAdded,
            warehouseAdded,
            permsArray,
            appSetting,
            selectedWarehouse,
            warehousesChanged,

            drawerWidth: window.innerWidth <= 991 ? "90%" : "45%",

            warehouseVisible,
            roleChanged,

            // New table properties
            totalData,
            totalTableColumns,
        };
    },
});

</script>
