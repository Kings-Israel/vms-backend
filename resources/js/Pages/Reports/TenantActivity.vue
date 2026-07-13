<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Pagination from '@/Components/Pagination.vue';
import Input from '@/components/ui/Input.vue';
import Select from '@/components/ui/Select.vue';

const props = defineProps({ tenants: Object, buildings: Array, filters: Object });
const buildingId = ref(props.filters.building_id ?? '');
const search = ref(props.filters.search ?? '');

function applyFilters() {
  router.get(route('reports.tenant-activity'), { building_id: buildingId.value, search: search.value }, { preserveState: true });
}

const columns = [
  { key: 'name', label: 'Tenant' },
  { key: 'email', label: 'Email' },
  { key: 'total_visits', label: 'Total Visits' },
  { key: 'active_visits', label: 'Currently Hosting' },
];
</script>

<template>
  <AppLayout>
    <div class="space-y-5">
      <h1 class="text-2xl font-bold text-foreground">Tenant Activity Report</h1>

      <div class="flex flex-wrap gap-3">
        <Input
          v-model="search"
          @input="applyFilters"
          type="text"
          placeholder="Search tenant..."
          class="max-w-xs"
        />
        <Select v-model="buildingId" @change="applyFilters">
          <option value="">All Buildings</option>
          <option v-for="b in buildings" :key="b.id" :value="b.id">{{ b.name }}</option>
        </Select>
      </div>

      <DataTable :columns="columns" :rows="tenants.data">
        <template #name="{ row }">
          <span class="font-medium text-foreground">{{ row.name }}</span>
        </template>
        <template #email="{ row }">
          <span class="text-muted-foreground">{{ row.email }}</span>
        </template>
        <template #total_visits="{ row }">
          <span class="text-foreground">{{ row.total_visits }}</span>
        </template>
        <template #active_visits="{ row }">
          <span :class="['font-semibold', row.active_visits > 0 ? 'text-green-400' : 'text-muted-foreground']">
            {{ row.active_visits }}
          </span>
        </template>
      </DataTable>

      <Pagination :links="tenants.links" />
    </div>
  </AppLayout>
</template>
