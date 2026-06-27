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

const props = defineProps({
  users: Object,
  roles: Array,
  buildings: Array,
  filters: Object,
});

const search = ref(props.filters.search ?? '');
const selectedRole = ref(props.filters.role ?? '');
const selectedBuilding = ref(props.filters.building_id ?? '');

function applyFilters() {
  router.get(route('users.index'), {
    search: search.value,
    role: selectedRole.value,
    building_id: selectedBuilding.value,
  }, { preserveState: true });
}

function deleteUser(id) {
  if (confirm('Delete this user?')) router.delete(route('users.destroy', id));
}

const columns = [
  { key: 'name', label: 'Name' },
  { key: 'email', label: 'Email' },
  { key: 'role', label: 'Role' },
  { key: 'building', label: 'Building' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: '' },
];
</script>

<template>
  <AppLayout>
    <div class="space-y-5">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-foreground">Users</h1>
        <Button as="a" :href="route('users.create')">
          <Plus class="w-4 h-4 mr-1.5" /> Add User
        </Button>
      </div>

      <div class="flex flex-wrap gap-3">
        <div class="relative">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
          <Input
            v-model="search"
            @input="applyFilters"
            type="text"
            placeholder="Search..."
            class="pl-9"
          />
        </div>
        <Select v-model="selectedRole" @change="applyFilters">
          <option value="">All Roles</option>
          <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name.replace('_', ' ') }}</option>
        </Select>
        <Select v-model="selectedBuilding" @change="applyFilters">
          <option value="">All Buildings</option>
          <option v-for="b in buildings" :key="b.id" :value="b.id">{{ b.name }}</option>
        </Select>
      </div>

      <DataTable :columns="columns" :rows="users.data">
        <template #name="{ row }">
          <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-primary/20 flex items-center justify-center text-primary text-xs font-bold">
              {{ row.name[0].toUpperCase() }}
            </div>
            <span class="text-foreground">{{ row.name }}</span>
          </div>
        </template>
        <template #role="{ row }">
          <span class="capitalize text-xs bg-primary/10 text-primary px-2 py-0.5 rounded-full border border-primary/20">
            {{ row.roles?.[0]?.name?.replace('_', ' ') ?? '-' }}
          </span>
        </template>
        <template #building="{ row }">
          <span class="text-muted-foreground">{{ row.building?.name ?? '-' }}</span>
        </template>
        <template #status="{ row }">
          <span :class="['text-xs px-2 py-0.5 rounded-full border font-medium',
            row.is_active
              ? 'bg-green-500/10 text-green-400 border-green-500/30'
              : 'bg-red-500/10 text-red-400 border-red-500/30']">
            {{ row.is_active ? 'Active' : 'Inactive' }}
          </span>
        </template>
        <template #actions="{ row }">
          <div class="flex gap-1 justify-end">
            <Button as="a" :href="route('users.edit', row.id)" variant="ghost" size="icon">
              <Pencil class="w-4 h-4" />
            </Button>
            <Button @click="deleteUser(row.id)" variant="ghost" size="icon" class="text-muted-foreground hover:text-destructive">
              <Trash2 class="w-4 h-4" />
            </Button>
          </div>
        </template>
      </DataTable>

      <Pagination :links="users.links" />
    </div>
  </AppLayout>
</template>
