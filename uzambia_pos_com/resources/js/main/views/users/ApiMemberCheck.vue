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
                <a-breadcrumb-item>User</a-breadcrumb-item>
                <a-breadcrumb-item>Member</a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <admin-page-table-content>
        <div class="filters">
            <a-form :model="filters" layout="inline">
                <a-form-item label="Start Date">
                    <input
                        type="date"
                        v-model="filters.startDate"
                        @change="validateDates"
                        class="custom-date-input"
                    />
                </a-form-item>
                <a-form-item label="End Date">
                    <input
                        type="date"
                        v-model="filters.endDate"
                        @change="validateDates"
                        class="custom-date-input"
                    />
                </a-form-item>
                <a-form-item label="Search">
                    <input
                        type="text"
                        v-model="filters.search"
                        placeholder="Search"
                        class="custom-input"
                    />
                </a-form-item>
                <a-form-item>
                    <a-button type="primary" @click="fetchData">Search</a-button>
                    <a-button type="default" @click="clearFilters" class="btn-refresh btn-warning">Refresh</a-button>
                </a-form-item>
            </a-form>
        </div>

        <a-table :data-source="tableData" :columns="columns" row-key="id" :pagination="false" :loading="loading">
            <template #action="{ record }">
                <a-button
                    :disabled="record.isMatching"
                    :class="{
            'btn-success': record.isMatching,
            'btn-warning': !record.isMatching
          }"
                    @click="handleAction(record)"
                >
                    {{ record.isMatching ? 'Exist' : 'Add' }}
                </a-button>
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
import { ref, onMounted, watch } from 'vue';
import { message } from 'ant-design-vue';
import axios from 'axios';
import AdminPageHeader from '../../../common/layouts/AdminPageHeader.vue';

export default {
    components: {
        AdminPageHeader
    },
    setup() {
        const filters = ref({
            startDate: '',
            endDate: '',
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
        const loading = ref(false);

        const fetchData = async () => {
            loading.value = true;
            try {
                const { startDate, endDate, search } = filters.value;

                console.log('Fetching data with params:', {
                    startDate,
                    endDate,
                    search,
                    page: pagination.value.current,
                    pageSize: pagination.value.pageSize
                });

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
                loading.value = false;
            }
        };

        const validateDates = () => {
            const { startDate, endDate } = filters.value;
            if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
                message.error('Start Date cannot be after End Date.');
                filters.value.endDate = '';
            }
        };

        const clearFilters = () => {
            filters.value.startDate = '';
            filters.value.endDate = '';
            filters.value.search = '';
            fetchData(); // Fetch data after clearing filters
        };

        const handlePageChange = (page) => {
            console.log(`Page changed to: ${page}`);
            pagination.value.current = page;
            fetchData();
        };

        const handleAction = (record) => {
            message.info(`Action for ${record.id}`);
        };

        onMounted(() => {
            fetchData();
        });

        // Watch filters for changes and fetch data
        watch(filters, (newFilters) => {
            pagination.value.current = 1; // Reset to first page on filter change
            fetchData();
        }, { deep: true });

        return {
            filters,
            tableData,
            columns,
            pagination,
            loading,
            fetchData,
            handlePageChange,
            handleAction,
            validateDates,
            clearFilters
        };
    }
};
</script>

<style scoped>
.filters {
    margin-bottom: 16px;
    padding-top: 20px; /* Adjusted padding for the top area */
}

.custom-date-input {
    margin-right: 8px;
    padding: 8px;
    border: 1px solid #d9d9d9;
    border-radius: 4px;
}

.custom-input {
    margin-right: 8px;
    padding: 8px;
    border: 1px solid #d9d9d9;
    border-radius: 4px;
}

.btn-success {
    background-color: #52c41a;
    color: #fff;
    border-color: #52c41a;
}

.btn-warning {
    background-color: #faad14;
    color: #fff;
    border-color: #faad14;
}

.btn-refresh {
    margin-left: 8px;
}
</style>
