<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Pagination from '@/Components/Pagination.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Select from '@/components/ui/Select.vue';
import { Plus, Search, Pencil, Trash2 } from 'lucide-vue-next';

const props = defineProps({ units: Object, buildings: Array, filters: Object });
const search = ref(props.filters.search ?? '');
const buildingId = ref(props.filters.building_id ?? '');

function applyFilters() {
  router.get(route('units.index'), { search: search.value, building_id: buildingId.value }, { preserveState: true });
}

function deleteUnit(id) {
  if (confirm('Delete this unit?')) router.delete(route('units.destroy', id));
}

const columns = [
  { key: 'name', label: 'Unit' },
  { key: 'building', label: 'Building' },
  { key: 'floor', label: 'Floor' },
  { key: 'type', label: 'Type' },
  { key: 'tenants', label: 'Tenants' },
  { key: 'is_active', label: 'Status' },
  { key: 'actions', label: '' },
];
</script>

<template>
  <AppLayout>
    <div class="space-y-5">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-foreground">Units</h1>
        <Button as="a" :href="route('units.create')">
          <Plus class="w-4 h-4 mr-1.5" /> Add Unit
        </Button>
      </div>

      <div class="flex flex-wrap gap-3">
        <div class="relative">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
          <Input v-model="search" @input="applyFilters" type="text" placeholder="Search units..." class="pl-9" />
        </div>
        <Select v-model="buildingId" @change="applyFilters">
          <option value="">All Buildings</option>
          <option v-for="b in buildings" :key="b.id" :value="b.id">{{ b.name }}</option>
        </Select>
      </div>

      <DataTable :columns="columns" :rows="units.data">
        <template #name="{ row }">
          <span class="font-medium text-foreground">{{ row.name }}</span>
          <span class="text-muted-foreground text-xs ml-1">#{{ row.unit_number }}</span>
        </template>
        <template #building="{ row }">
          <span class="text-muted-foreground">{{ row.building?.name ?? '-' }}</span>
        </template>
        <template #type="{ row }">
          <span class="capitalize text-xs bg-muted text-muted-foreground px-2 py-0.5 rounded-full border border-border">
            {{ row.type }}
          </span>
        </template>
        <template #tenants="{ row }">
          <span class="text-foreground text-sm">{{ row.tenants?.length ?? 0 }}</span>
        </template>
        <template #is_active="{ row }">
          <span :class="['text-xs px-2 py-0.5 rounded-full border font-medium',
            row.is_active
              ? 'bg-green-500/10 text-green-400 border-green-500/30'
              : 'bg-red-500/10 text-red-400 border-red-500/30']">
            {{ row.is_active ? 'Active' : 'Inactive' }}
          </span>
        </template>
        <template #actions="{ row }">
          <div class="flex gap-1 justify-end">
            <Button as="a" :href="route('units.edit', row.id)" variant="ghost" size="icon">
              <Pencil class="w-4 h-4" />
            </Button>
            <Button @click="deleteUnit(row.id)" variant="ghost" size="icon" class="text-muted-foreground hover:text-destructive">
              <Trash2 class="w-4 h-4" />
            </Button>
          </div>
        </template>
      </DataTable>

      <Pagination :links="units.links" />
    </div>
  </AppLayout>
</template>
