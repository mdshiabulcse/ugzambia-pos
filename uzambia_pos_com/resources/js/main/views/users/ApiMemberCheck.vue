<template>
    <div>
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
                    <a-form-item label="Search">
                        <input
                            type="text"
                            v-model="filters.search"
                            placeholder="Search"
                            class="custom-input"
                        />
                    </a-form-item>
                    <a-form-item label="Current Period">
                        <select
                            v-model="filters.currentPeriod"
                                 placeholder="Select Current Period"
                            class="custom-input"
                        >
                            <option   v-for="period in importPeriods"
                                      :key="period"
                                      :value="period">
                                {{ period }}
                            </option>
                        </select>
                    </a-form-item>
                    <!-- End Period Filter -->
                    <a-form-item label="Previous Period">
                        <select
                            v-model="filters.previousPeriod"
                            placeholder="Select Previous Period"
                            class="custom-input"
                        >
                            <option   v-for="period in importPeriods"
                                      :key="period"
                                      :value="period">
                                {{ period }}
                            </option>
                        </select>
                    </a-form-item>
                    <a-form-item v-if="filters.currentPeriod && filters.previousPeriod" >
                        <input class="custom-input" type="checkbox" id="checkbox" v-model="filters.showNonMatchingOnly" />
                        <label for="checkbox">Check Missing Member</label>
                    </a-form-item>
                    <a-form-item>
                        <a-button type="primary" @click="fetchData">Search</a-button>
                        <a-button type="default" @click="clearFilters" class="btn-refresh btn-warning">Refresh</a-button>
                    </a-form-item>
                </a-form>
            </div>

            <!-- Loader -->
            <div v-if="loading" class="loader-container">
                <div class="loader"></div>
            </div>

            <div class="table-container" v-else>
                <table class="custom-table">
                    <thead>
                    <tr>
                        <th>Province</th>
                        <th>District</th>
                        <th>Ministry</th>
                        <th>Employee No</th>
                        <th>Man No</th>
                        <th>NRC No</th>
                        <th>Names</th>
                        <th>Period Name</th>
                        <th>Import At</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="record in tableData" :key="record.id" :style="getRowStyle(record)">
                        <td>{{ record.province }}</td>
                        <td>{{ record.district }}</td>
                        <td>{{ record.ministry }}</td>
                        <td>{{ record.employeeNo }}</td>
                        <td>{{ record.manNo }}</td>
                        <td>{{ record.nrcNo }}</td>
                        <td>{{ record.names }}</td>
                        <td>{{ record.periodName }}</td>
                        <td>{{ record.importAt }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="pagination-container">
                <div class="pagination-controls">
                    <a-select v-model="pagination.pageSize" @change="handlePageSizeChange" style="width: 120px;">
                        <a-select-option value="10">10 items per page</a-select-option>
                        <a-select-option value="25">25 items per page</a-select-option>
                        <a-select-option value="50">50 items per page</a-select-option>
                        <a-select-option value="100">100 items per page</a-select-option>
                        <a-select-option value="500">500 items per page</a-select-option>
                        <a-select-option value="1000">1000 items per page</a-select-option>
                    </a-select>

                    <div class="custom-pagination">
                        <a-pagination
                            :current="pagination.current"
                            :total="pagination.total"
                            :page-size="pagination.pageSize"
                            @change="handlePageChange"
                            show-size-changer
                            show-quick-jumper
                            :show-total="showTotal"
                            style="flex: 1;"
                        />
                    </div>
                </div>
            </div>
        </admin-page-table-content>
    </div>
</template>


<script>
import { ref, onMounted, watch } from 'vue';
import { message } from 'ant-design-vue';
import axios from 'axios';
import AdminPageHeader from '../../../common/layouts/AdminPageHeader.vue';
import API_ROUTES from '../../../main/config/apiRoute.js';


export default {
    components: {
        AdminPageHeader
    },
    setup() {
        const filters = ref({
            search: '',
            currentPeriod: null,
            previousPeriod: null,
            showNonMatchingOnly: false,
        });
        const tableData = ref([]);
        const importPeriods = ref([]);
        const pagination = ref({
            current: 1,
            pageSize: 10,
            total: 0
        });
        const loading = ref(false);

        const fetchData = async () => {

            const { search, currentPeriod, previousPeriod , showNonMatchingOnly} = filters.value;
            if (currentPeriod !== null && previousPeriod !== null){
               if (!currentPeriod || (previousPeriod && parseInt(currentPeriod) <= parseInt(previousPeriod))) {
                   message.error('Invalid period selection. Current Period must be after Previous Period.');
                   return; // Exit early if validation fails
               }
           }
            console.log('fetchData called');  // Added log here to ensure function is executed
            loading.value = true;
            try {
                const response = await axios.get(API_ROUTES.BASE_URL+'/all-data-get', {
                    params: {
                        search,
                        currentPeriod,
                        previousPeriod,
                        showNonMatchingOnly,
                        page: pagination.value.current,
                        pageSize: pagination.value.pageSize
                    }
                });

                tableData.value = response.data.total_data || [];
                pagination.value.total = response.data.total_count || 0;

            } catch (error) {
                console.error('Fetch data error:', error);
                message.error('Failed to fetch data.');
            } finally {
                loading.value = false;
            }
        };


        const fetchImportPeriods = async () => {
            try {
                const response = await axios.get(API_ROUTES.BASE_URL+'/data-import-period');
                // Only keep valid 6-digit periods
                const periods = response.data.import_period.filter(period => /^[0-9]{6}$/.test(period));
                importPeriods.value = periods;
            } catch (error) {
                console.error('Failed to fetch import periods:', error);
                message.error('Failed to fetch import periods.');
            }
        };

        const validateDates = () => {
            const { currentPeriod, previousPeriod } = filters.value;

            // If previousPeriod is greater than or equal to currentPeriod
            if (currentPeriod && previousPeriod && parseInt(previousPeriod) >= parseInt(currentPeriod)) {
                message.error('Previous Period cannot be greater than or equal to Current Period.');
                filters.value.previousPeriod = ''; // Clear previous period
            }
        };

        // Watch currentPeriod to enable previousPeriod selection
        watch(() => filters.value.currentPeriod, validateDates);

        // Watch previousPeriod to validate against currentPeriod
        watch(() => filters.value.previousPeriod, validateDates);

        const clearFilters = () => {
            filters.value.search = '';
            filters.value.currentPeriod = null;
            filters.value.previousPeriod = null;
            filters.value.showNonMatchingOnly = false;
            fetchData();
        };

        const handlePageChange = (page) => {
            if (page > 0 && page <= Math.ceil(pagination.value.total / pagination.value.pageSize)) {
                pagination.value.current = page;
                fetchData();
            }
        };

        const handlePageSizeChange = (size) => {
            pagination.value.pageSize = size;
            pagination.value.current = 1; // Reset to first page
            fetchData();
        };

        const handleAction = (record) => {
            message.info(`Action for ${record.id}`);
        };

        const getRowStyle = (record) => {
            return record.isMatching
                ? { backgroundColor: '#ffffff' } // Light green
                : { backgroundColor: '#ec7316' }; // Light red
        };

        const getButtonStyle = (isMatching) => {
            return isMatching
                ? { backgroundColor: '#52c41a', color: '#fff', borderColor: '#52c41a' }
                : { backgroundColor: '#faad14', color: '#fff', borderColor: '#faad14' };
        };

        const showTotal = (total, range) => `Total ${total} items`;

        onMounted(() => {
            fetchData();
            fetchImportPeriods();
        });

        watch(filters, (newFilters) => {
            pagination.value.current = 1;
            fetchData();
        }, { deep: true });

        return {
            filters,
            tableData,
            pagination,
            loading,
            fetchData,
            handlePageChange,
            handlePageSizeChange,
            handleAction,
            validateDates,
            clearFilters,
            getRowStyle,
            getButtonStyle,
            showTotal,
            importPeriods
        };
    }
};
</script>

<style scoped>
.filters {
    margin-bottom: 16px;
    padding-top: 20px; /* Adjusted padding for the top area */
}

.custom-date-input,
.custom-input {
    margin-right: 8px;
    padding: 8px;
    border: 1px solid #d9d9d9;
    border-radius: 4px;
}

.btn-refresh {
    margin-left: 8px;
}

.table-container {
    margin-bottom: 40px; /* Add padding to the bottom of the table */
}

.custom-table {
    width: 100%;
    border-collapse: collapse;
}

.custom-table th,
.custom-table td {
    padding: 12px;
    border: 1px solid #ddd;
}

.custom-table thead {
    background-color: #f5f5f5;
}

.custom-table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.custom-table tr:hover {
    background-color: #f1f1f1;
}

.custom-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.custom-pagination .ant-pagination {
    margin-top: 16px;
}

.pagination-controls {
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.pagination-controls a-select {
    margin-right: 8px;
}

.loader-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100px;
    margin: 20px 0;
}

.loader {
    border: 8px solid #f3f3f3;
    border-top: 8px solid #3498db;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

