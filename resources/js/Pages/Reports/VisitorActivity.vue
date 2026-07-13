<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';
import Select from '@/components/ui/Select.vue';
import { formatDateTime } from '@/lib/utils.js';
import { Users, UserCheck, LogOut, CalendarClock } from 'lucide-vue-next';

const props = defineProps({ visits: Object, stats: Object, buildings: Array, filters: Object });

const buildingId = ref(props.filters.building_id ?? '');
const status = ref(props.filters.status ?? '');
const from = ref(props.filters.from ?? '');
const to = ref(props.filters.to ?? '');

function applyFilters() {
  router.get(route('reports.visitor-activity'), {
    building_id: buildingId.value, status: status.value, from: from.value, to: to.value,
  }, { preserveState: true });
}

const statCards = [
  { key: 'total_today',        label: 'Total Today',          icon: Users,        color: 'text-blue-400 bg-blue-500/10' },
  { key: 'checked_in',         label: 'Currently Inside',     icon: UserCheck,    color: 'text-green-400 bg-green-500/10' },
  { key: 'checked_out_today',  label: 'Checked Out Today',    icon: LogOut,       color: 'text-zinc-400 bg-zinc-500/10' },
  { key: 'expected_today',     label: 'Expected Today',       icon: CalendarClock, color: 'text-yellow-400 bg-yellow-500/10' },
];

const columns = [
  { key: 'visitor', label: 'Visitor' },
  { key: 'type', label: 'Type' },
  { key: 'unit', label: 'Unit' },
  { key: 'vehicle', label: 'Vehicle' },
  { key: 'status', label: 'Status' },
  { key: 'checked_in_at', label: 'Check-in' },
  { key: 'checked_out_at', label: 'Check-out' },
];
</script>

<template>
  <AppLayout>
    <div class="space-y-5">
      <h1 class="text-2xl font-bold text-foreground">Visitor Activity Report</h1>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <Card v-for="card in statCards" :key="card.key" class="p-4">
          <div class="flex items-center gap-3">
            <div :class="['p-2 rounded-lg shrink-0', card.color]">
              <component :is="card.icon" class="w-5 h-5" />
            </div>
            <div>
              <p class="text-2xl font-bold text-foreground">{{ stats[card.key] }}</p>
              <p class="text-xs text-muted-foreground">{{ card.label }}</p>
            </div>
          </div>
        </Card>
      </div>

      <div class="flex flex-wrap gap-3">
        <Select v-model="buildingId" @change="applyFilters">
          <option value="">All Buildings</option>
          <option v-for="b in buildings" :key="b.id" :value="b.id">{{ b.name }}</option>
        </Select>
        <Select v-model="status" @change="applyFilters">
          <option value="">All Statuses</option>
          <option value="checked_in">Checked In</option>
          <option value="checked_out">Checked Out</option>
          <option value="expected">Expected</option>
          <option value="denied">Denied</option>
        </Select>
        <Input v-model="from" @change="applyFilters" type="date" class="w-auto" />
        <Input v-model="to" @change="applyFilters" type="date" class="w-auto" />
      </div>

      <DataTable :columns="columns" :rows="visits.data">
        <template #visitor="{ row }">
          <p class="font-medium text-sm text-foreground">{{ row.visitor?.first_name }} {{ row.visitor?.last_name }}</p>
          <p class="text-xs text-muted-foreground">{{ row.visitor?.national_id ?? '-' }}</p>
        </template>
        <template #type="{ row }">
          <span v-if="row.visitor_type" class="text-xs px-2 py-0.5 rounded-full text-white font-medium" :style="{ background: row.visitor_type.color }">
            {{ row.visitor_type.name }}
          </span>
          <span v-else class="text-muted-foreground text-xs">-</span>
        </template>
        <template #unit="{ row }">
          <span class="text-muted-foreground">{{ row.unit?.name ?? '-' }}</span>
        </template>
        <template #vehicle="{ row }">
          <span class="text-muted-foreground">{{ row.vehicle?.plate_number ?? '-' }}</span>
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
