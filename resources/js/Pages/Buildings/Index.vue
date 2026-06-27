<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Pagination from '@/Components/Pagination.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { Plus, Search, Pencil, Trash2, Building2 } from 'lucide-vue-next';

const props = defineProps({ buildings: Object, filters: Object });
const search = ref(props.filters.search ?? '');

function applyFilters() {
  router.get(route('buildings.index'), { search: search.value }, { preserveState: true });
}

function deleteBuilding(id) {
  if (confirm('Delete this building?')) router.delete(route('buildings.destroy', id));
}

const columns = [
  { key: 'name', label: 'Name' },
  { key: 'address', label: 'Address' },
  { key: 'city', label: 'City' },
  { key: 'units_count', label: 'Units' },
  { key: 'users_count', label: 'Users' },
  { key: 'is_active', label: 'Status' },
  { key: 'actions', label: '' },
];
</script>

<template>
  <AppLayout>
    <div class="space-y-5">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-foreground">Buildings</h1>
        <Button as="a" :href="route('buildings.create')">
          <Plus class="w-4 h-4 mr-1.5" /> Add Building
        </Button>
      </div>

      <div class="relative max-w-sm">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
        <Input
          v-model="search"
          @input="applyFilters"
          type="text"
          placeholder="Search buildings..."
          class="pl-9"
        />
      </div>

      <DataTable :columns="columns" :rows="buildings.data">
        <template #name="{ row }">
          <div class="flex items-center gap-2">
            <Building2 class="w-4 h-4 text-primary" />
            <span class="font-medium text-foreground">{{ row.name }}</span>
          </div>
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
            <Button as="a" :href="route('buildings.edit', row.id)" variant="ghost" size="icon">
              <Pencil class="w-4 h-4" />
            </Button>
            <Button @click="deleteBuilding(row.id)" variant="ghost" size="icon" class="text-muted-foreground hover:text-destructive">
              <Trash2 class="w-4 h-4" />
            </Button>
          </div>
        </template>
      </DataTable>

      <Pagination :links="buildings.links" />
    </div>
  </AppLayout>
</template>
