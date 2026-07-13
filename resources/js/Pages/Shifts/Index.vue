<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Select from '@/components/ui/Select.vue';
import Textarea from '@/components/ui/Textarea.vue';
import Dialog from '@/components/ui/Dialog.vue';
import { formatDateTime } from '@/lib/utils.js';
import { Plus, X, Trash2, Pencil } from 'lucide-vue-next';

const props = defineProps({ shifts: Object, buildings: Array, officers: Array, filters: Object });

const showModal = ref(false);
const editing = ref(null);
const buildingFilter = ref(props.filters.building_id ?? '');
const statusFilter = ref(props.filters.status ?? '');

const form = reactive({
  building_id: '', user_id: '', relieved_by: '',
  starts_at: '', ends_at: '', notes: '', errors: {},
});

function applyFilters() {
  router.get(route('shifts.index'), { building_id: buildingFilter.value, status: statusFilter.value }, { preserveState: true });
}

function openCreate() {
  editing.value = null;
  Object.assign(form, { building_id: '', user_id: '', relieved_by: '', starts_at: '', ends_at: '', notes: '', errors: {} });
  showModal.value = true;
}

function openEdit(shift) {
  editing.value = shift;
  Object.assign(form, {
    building_id: shift.building_id, user_id: shift.user_id,
    relieved_by: shift.relieved_by ?? '', starts_at: shift.starts_at?.slice(0, 16) ?? '',
    ends_at: shift.ends_at?.slice(0, 16) ?? '', notes: shift.notes ?? '', errors: {},
  });
  showModal.value = true;
}

function save() {
  const data = { ...form };
  delete data.errors;
  const opts = { onError: e => { form.errors = e; }, onSuccess: () => { showModal.value = false; } };
  editing.value
    ? router.put(route('shifts.update', editing.value.id), data, opts)
    : router.post(route('shifts.store'), data, opts);
}

const columns = [
  { key: 'officer', label: 'Officer' },
  { key: 'building', label: 'Building' },
  { key: 'starts_at', label: 'Starts' },
  { key: 'ends_at', label: 'Ends' },
  { key: 'relief', label: 'Relieved By' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: '' },
];
</script>

<template>
  <AppLayout>
    <div class="space-y-5">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-foreground">Shifts</h1>
        <Button @click="openCreate">
          <Plus class="w-4 h-4 mr-1.5" /> Schedule Shift
        </Button>
      </div>

      <div class="flex gap-3">
        <Select v-model="buildingFilter" @change="applyFilters">
          <option value="">All Buildings</option>
          <option v-for="b in buildings" :key="b.id" :value="b.id">{{ b.name }}</option>
        </Select>
        <Select v-model="statusFilter" @change="applyFilters">
          <option value="">All Statuses</option>
          <option value="scheduled">Scheduled</option>
          <option value="active">Active</option>
          <option value="completed">Completed</option>
          <option value="missed">Missed</option>
        </Select>
      </div>

      <DataTable :columns="columns" :rows="shifts.data">
        <template #officer="{ row }">
          <span class="text-foreground">{{ row.officer?.name ?? '-' }}</span>
        </template>
        <template #building="{ row }">
          <span class="text-muted-foreground">{{ row.building?.name ?? '-' }}</span>
        </template>
        <template #starts_at="{ row }">
          <span class="text-muted-foreground text-sm">{{ formatDateTime(row.starts_at) }}</span>
        </template>
        <template #ends_at="{ row }">
          <span class="text-muted-foreground text-sm">{{ formatDateTime(row.ends_at) }}</span>
        </template>
        <template #relief="{ row }">
          <span class="text-muted-foreground">{{ row.relief?.name ?? '-' }}</span>
        </template>
        <template #status="{ row }"><StatusBadge :status="row.status" /></template>
        <template #actions="{ row }">
          <div class="flex gap-1 justify-end">
            <Button @click="openEdit(row)" variant="ghost" size="icon">
              <Pencil class="w-4 h-4" />
            </Button>
            <Button @click="router.delete(route('shifts.destroy', row.id))" variant="ghost" size="icon" class="text-muted-foreground hover:text-destructive">
              <Trash2 class="w-4 h-4" />
            </Button>
          </div>
        </template>
      </DataTable>

      <Pagination :links="shifts.links" />

      <!-- Shift Modal -->
      <Dialog :open="showModal" @close="showModal = false" max-width="max-w-lg">
        <div class="p-6">
          <div class="flex items-center justify-between mb-5">
            <h2 class="font-semibold text-foreground text-lg">{{ editing ? 'Edit Shift' : 'Schedule Shift' }}</h2>
            <Button variant="ghost" size="icon" @click="showModal = false">
              <X class="w-4 h-4" />
            </Button>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1.5">
              <Label>Building</Label>
              <Select v-model="form.building_id">
                <option value="">Select...</option>
                <option v-for="b in buildings" :key="b.id" :value="b.id">{{ b.name }}</option>
              </Select>
            </div>
            <div class="space-y-1.5">
              <Label>Officer</Label>
              <Select v-model="form.user_id">
                <option value="">Select...</option>
                <option v-for="o in officers" :key="o.id" :value="o.id">{{ o.name }}</option>
              </Select>
            </div>
            <div class="space-y-1.5">
              <Label>Starts At</Label>
              <Input v-model="form.starts_at" type="datetime-local" />
            </div>
            <div class="space-y-1.5">
              <Label>Ends At</Label>
              <Input v-model="form.ends_at" type="datetime-local" />
            </div>
            <div class="col-span-2 space-y-1.5">
              <Label>Relieved By</Label>
              <Select v-model="form.relieved_by">
                <option value="">None</option>
                <option v-for="o in officers" :key="o.id" :value="o.id">{{ o.name }}</option>
              </Select>
            </div>
            <div class="col-span-2 space-y-1.5">
              <Label>Notes</Label>
              <Textarea v-model="form.notes" rows="2" />
            </div>
          </div>

          <div class="flex gap-3 mt-5">
            <Button @click="save">Save</Button>
            <Button variant="outline" @click="showModal = false">Cancel</Button>
          </div>
        </div>
      </Dialog>
    </div>
  </AppLayout>
</template>
