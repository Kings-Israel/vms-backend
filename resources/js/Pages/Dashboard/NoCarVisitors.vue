<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import PeriodSelect from '@/Components/PeriodSelect.vue';
import Input from '@/components/ui/Input.vue';
import Select from '@/components/ui/Select.vue';
import { formatDateTime } from '@/lib/utils.js';
import { ArrowLeft, Search } from 'lucide-vue-next';

const props = defineProps({ visits: Object, filters: Object });

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const date = ref(props.filters.date ?? '');
const period = ref(props.filters.period ?? 'week');

function applyFilters() {
  router.get(route('dashboard.visitors.without-vehicle'), {
    search: search.value, status: status.value, date: date.value, period: period.value,
  }, { preserveState: true });
}

const columns = [
  { key: 'visitor', label: 'Visitor' },
  { key: 'unit', label: 'Unit' },
  { key: 'status', label: 'Status' },
  { key: 'checked_in_at', label: 'Check-in' },
  { key: 'checked_out_at', label: 'Check-out' },
];
</script>

<template>
  <AppLayout>
    <div class="space-y-5">
      <div class="flex items-center justify-between">
        <div>
          <Link :href="route('dashboard')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors">
            <ArrowLeft class="w-4 h-4" /> Back to dashboard
          </Link>
          <h1 class="text-2xl font-bold text-foreground mt-1">Visitors Without Vehicle</h1>
        </div>
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
        <PeriodSelect v-model="period" @update:modelValue="applyFilters" class="h-10" />
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
        <template #status="{ row }"><StatusBadge :status="row.status" /></template>
        <template #checked_in_at="{ row }">
          <span class="text-muted-foreground text-sm">{{ formatDateTime(row.checked_in_at) }}</span>
        </template>
        <template #checked_out_at="{ row }">
          <span class="text-muted-foreground text-sm">{{ formatDateTime(row.checked_out_at) }}</span>
        </template>
      </DataTable>

      <Pagination :links="visits.links" />
    </div>
  </AppLayout>
</template>
