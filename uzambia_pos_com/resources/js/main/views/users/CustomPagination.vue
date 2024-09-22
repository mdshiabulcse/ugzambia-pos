<!-- CustomPagination.vue -->
<template>
    <div class="pagination-container">
        <div class="pagination-controls">
            <select v-model="localPageSize" @change="onPageSizeChange" class="page-size-selector">
                <option :value="size" v-for="size in pageSizeOptions" :key="size">
                    {{ size }} items per page
                </option>
            </select>

            <div class="pagination-controls-inner">
                <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1">
                    Previous
                </button>
                <span class="page-info">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
                <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages">
                    Next
                </button>
                <select v-model="localPage" @change="onPageChange" class="page-selector">
                    <option :value="page" v-for="page in pageNumbers" :key="page">
                        {{ page }}
                    </option>
                </select>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, watch, computed } from 'vue';

export default {
    props: {
        currentPage: {
            type: Number,
            required: true
        },
        pageSize: {
            type: Number,
            required: true
        },
        totalItems: {
            type: Number,
            required: true
        },
        pageSizeOptions: {
            type: Array,
            default: () => [10, 25, 50, 100]
        }
    },
    setup(props, { emit }) {
        const localPage = ref(props.currentPage);
        const localPageSize = ref(props.pageSize);

        const totalPages = computed(() => Math.ceil(props.totalItems / localPageSize.value));
        const pageNumbers = computed(() => {
            return Array.from({ length: totalPages.value }, (_, i) => i + 1);
        });

        const changePage = (page) => {
            if (page > 0 && page <= totalPages.value) {
                localPage.value = page;
                emit('update:currentPage', page);
            }
        };

        const onPageSizeChange = () => {
            emit('update:pageSize', localPageSize.value);
            emit('update:currentPage', 1);
        };

        const onPageChange = () => {
            changePage(Number(localPage.value));
        };

        watch(() => props.currentPage, (newPage) => {
            localPage.value = newPage;
        });

        return {
            localPage,
            localPageSize,
            totalPages,
            pageNumbers,
            changePage,
            onPageSizeChange,
            onPageChange
        };
    }
};
</script>

<style scoped>
.pagination-container {
    margin-top: 20px;
    padding-bottom: 20px; /* Add padding at the bottom */
}

.pagination-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.page-size-selector,
.page-selector {
    margin-right: 8px;
}

.page-info {
    margin: 0 8px;
}
</style>
