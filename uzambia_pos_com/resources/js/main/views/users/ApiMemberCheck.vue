<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header title="Member Check" class="p-0" />
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        Dashboard
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    User
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    Member
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <admin-page-table-content>
        <div class="filters">
            <a-form :model="filters" layout="inline">
                <a-form-item label="Date Range">
                    <a-date-picker v-model="filters.dateRange" :format="dateFormat" />
                </a-form-item>
                <a-form-item label="Search">
                    <a-input v-model="filters.search" placeholder="Search" />
                </a-form-item>
                <a-form-item>
                    <a-button type="primary" @click="fetchData">Search</a-button>
                </a-form-item>
            </a-form>
        </div>

        <a-table :data-source="tableData" :columns="columns" row-key="id" :pagination="pagination" :loading="loading">
            <template #action="{ record }">
                <a-button @click="handleAction(record)" type="link">Action</a-button>
            </template>
        </a-table>

        <a-pagination
            :current="pagination.current"
            :total="pagination.total"
            :page-size="pagination.pageSize"
            @change="handlePageChange"
            style="margin-top: 16px;"
        />
    </admin-page-table-content>
</template>

<script>
import { ref, onMounted } from 'vue';
import { message } from 'ant-design-vue';
import axios from 'axios';
import AdminPageHeader from '../../../common/layouts/AdminPageHeader.vue';

export default {
    components: {
        AdminPageHeader
    },
    setup() {
        const filters = ref({
            dateRange: null,
            search: ''
        });
        const tableData = ref([]);
        const columns = ref([
            { title: 'ID', dataIndex: 'id', key: 'id' },
            { title: 'Province', dataIndex: 'province', key: 'province' },
            { title: 'District', dataIndex: 'district', key: 'district' },
            { title: 'Ministry', dataIndex: 'ministry', key: 'ministry' },
            { title: 'Employee No', dataIndex: 'employeeNo', key: 'employeeNo' },
            { title: 'Man No', dataIndex: 'manNo', key: 'manNo' },
            { title: 'NRC No', dataIndex: 'nrcNo', key: 'nrcNo' },
            { title: 'Names', dataIndex: 'names', key: 'names' },
            { title: 'Reference No', dataIndex: 'referenceNo', key: 'referenceNo' },
            { title: 'Period Name', dataIndex: 'periodName', key: 'periodName' },
            { title: 'Sub Total', dataIndex: 'subTotal', key: 'subTotal' },
            { title: 'Total', dataIndex: 'total', key: 'total' },
            { title: 'Emp No', dataIndex: 'empNo', key: 'empNo' },
            { title: 'Rec No', dataIndex: 'recNo', key: 'recNo' },
            { title: 'Import At', dataIndex: 'importAt', key: 'importAt' },
            { title: 'Import By', dataIndex: 'importBy', key: 'importBy' },
            { title: 'Action', key: 'action', slots: { customRender: 'action' } }
        ]);
        const pagination = ref({
            current: 1,
            pageSize: 10,
            total: 0
        });
        const dateFormat = 'YYYY-MM-DD';
        const loading = ref(false); // Loader state

        const fetchData = async () => {
            loading.value = true; // Show loader
            try {
                const { dateRange, search } = filters.value;
                const startDate = dateRange ? dateRange[0].format(dateFormat) : '';
                const endDate = dateRange ? dateRange[1].format(dateFormat) : '';

                const response = await axios.get('http://127.0.0.1:8001/api/all-data-get', {
                    params: {
                        startDate,
                        endDate,
                        search,
                        page: pagination.value.current,
                        pageSize: pagination.value.pageSize
                    }
                });

                console.log('API response:', response.data);

                tableData.value = response.data.total_data || [];
                pagination.value.total = response.data.total_count || 0;

            } catch (error) {
                console.error('Fetch data error:', error);
                message.error('Failed to fetch data.');
            } finally {
                loading.value = false; // Hide loader
            }
        };

        const handlePageChange = (page) => {
            pagination.value.current = page;
            fetchData();
        };

        const handleAction = (record) => {
            message.info(`Action for ${record.id}`);
        };

        onMounted(() => {
            fetchData();
        });

        return {
            filters,
            tableData,
            columns,
            pagination,
            loading, // Include loading state in return
            fetchData,
            handlePageChange,
            handleAction,
            dateFormat
        };
    }
};
</script>

<style scoped>
.filters {
    margin-bottom: 16px;
}
</style>
