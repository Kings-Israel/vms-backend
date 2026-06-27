<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Select from '@/components/ui/Select.vue';
import { formatDateTime } from '@/lib/utils.js';
import { Plus, Search } from 'lucide-vue-next';

const props = defineProps({ visits: Object, buildings: Array, filters: Object });

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const buildingId = ref(props.filters.building_id ?? '');
const date = ref(props.filters.date ?? '');

function applyFilters() {
  router.get(route('visits.index'), {
    search: search.value, status: status.value, building_id: buildingId.value, date: date.value,
  }, { preserveState: true });
}

const columns = [
  { key: 'visitor', label: 'Visitor' },
  { key: 'unit', label: 'Unit' },
  { key: 'type', label: 'Type' },
  { key: 'status', label: 'Status' },
  { key: 'checked_in_at', label: 'Check-in' },
  { key: 'checked_out_at', label: 'Check-out' },
  { key: 'actions', label: '' },
];
</script>

<template>
  <AppLayout>
    <div class="space-y-5">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-foreground">Visits</h1>
        <Button as="a" :href="route('visits.create')">
          <Plus class="w-4 h-4 mr-1.5" /> Register Visit
        </Button>
      </div>

      <div class="flex flex-wrap gap-3">
        <div class="relative">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
          <Input v-model="search" @input="applyFilters" type="text" placeholder="Search visitor..." class="pl-9" />
        </div>
        <Select v-model="status" @change="applyFilters">
          <option value="">All Statuses</option>
          <option value="expected">Expected</option>
          <option value="checked_in">Checked In</option>
          <option value="checked_out">Checked Out</option>
          <option value="cancelled">Cancelled</option>
        </Select>
        <Input v-model="date" @change="applyFilters" type="date" class="w-auto" />
        <Select v-model="buildingId" @change="applyFilters">
          <option value="">All Buildings</option>
          <option v-for="b in buildings" :key="b.id" :value="b.id">{{ b.name }}</option>
        </Select>
      </div>

      <DataTable :columns="columns" :rows="visits.data">
        <template #visitor="{ row }">
          <div>
            <p class="font-medium text-sm text-foreground">{{ row.visitor?.first_name }} {{ row.visitor?.last_name }}</p>
            <p class="text-xs text-muted-foreground">{{ row.visitor?.national_id ?? 'No ID' }}</p>
          </div>
        </template>
        <template #unit="{ row }">
          <span class="text-muted-foreground">{{ row.unit?.name ?? 'Walk-in' }}</span>
        </template>
        <template #type="{ row }">
          <span v-if="row.visitor_type" class="text-xs px-2 py-0.5 rounded-full text-white font-medium" :style="{ background: row.visitor_type.color }">
            {{ row.visitor_type.name }}
          </span>
          <span v-else class="text-muted-foreground text-xs">-</span>
        </template>
        <template #status="{ row }"><StatusBadge :status="row.status" /></template>
        <template #checked_in_at="{ row }">
          <span class="text-muted-foreground text-sm">{{ formatDateTime(row.checked_in_at) }}</span>
        </template>
        <template #checked_out_at="{ row }">
          <span class="text-muted-foreground text-sm">{{ formatDateTime(row.checked_out_at) }}</span>
        </template>
        <template #actions="{ row }">
          <Button
            v-if="row.status === 'expected'"
            @click="router.delete(route('visits.destroy', row.id))"
            variant="destructive"
            size="sm"
          >
            Cancel
          </Button>
        </template>
      </DataTable>

      <Pagination :links="visits.links" />
    </div>
  </AppLayout>
</template>
