<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Textarea from '@/components/ui/Textarea.vue';
import Card from '@/components/ui/Card.vue';
import Dialog from '@/components/ui/Dialog.vue';
import { Plus, Pencil, Trash2, X } from 'lucide-vue-next';

const props = defineProps({ types: Object });

const showModal = ref(false);
const editing = ref(null);

const form = reactive({
  name: '', description: '', color: '#3B82F6',
  requires_escort: false, is_active: true,
  errors: {},
});

function openCreate() {
  editing.value = null;
  Object.assign(form, { name: '', description: '', color: '#3B82F6', requires_escort: false, is_active: true, errors: {} });
  showModal.value = true;
}

function openEdit(type) {
  editing.value = type;
  Object.assign(form, { ...type, errors: {} });
  showModal.value = true;
}

function save() {
  const data = { name: form.name, description: form.description, color: form.color, requires_escort: form.requires_escort, is_active: form.is_active };
  const opts = {
    onError: (errors) => { form.errors = errors; },
    onSuccess: () => { showModal.value = false; },
  };
  editing.value
    ? router.put(route('visitor-types.update', editing.value.id), data, opts)
    : router.post(route('visitor-types.store'), data, opts);
}

function deleteType(id) {
  if (confirm('Delete this visitor type?')) router.delete(route('visitor-types.destroy', id));
}
</script>

<template>
  <AppLayout>
    <div class="space-y-5">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-foreground">Visitor Types</h1>
        <Button @click="openCreate">
          <Plus class="w-4 h-4 mr-1.5" /> Add Type
        </Button>
      </div>

      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <Card v-for="type in types.data" :key="type.id" class="p-4">
          <div class="flex items-start justify-between">
            <div class="flex items-center gap-2">
              <span class="w-3 h-3 rounded-full shrink-0" :style="{ background: type.color }"></span>
              <span class="font-medium text-foreground">{{ type.name }}</span>
            </div>
            <div class="flex gap-1">
              <Button @click="openEdit(type)" variant="ghost" size="icon" class="h-7 w-7">
                <Pencil class="w-3.5 h-3.5" />
              </Button>
              <Button @click="deleteType(type.id)" variant="ghost" size="icon" class="h-7 w-7 text-muted-foreground hover:text-destructive">
                <Trash2 class="w-3.5 h-3.5" />
              </Button>
            </div>
          </div>

          <p v-if="type.description" class="text-xs text-muted-foreground mt-2">{{ type.description }}</p>

          <div class="flex flex-wrap gap-1.5 mt-3">
            <span v-if="type.requires_escort" class="text-xs bg-orange-500/10 text-orange-400 border border-orange-500/30 px-2 py-0.5 rounded-full">
              Escort Required
            </span>
            <span :class="['text-xs px-2 py-0.5 rounded-full border',
              type.is_active
                ? 'bg-green-500/10 text-green-400 border-green-500/30'
                : 'bg-muted text-muted-foreground border-border']">
              {{ type.is_active ? 'Active' : 'Inactive' }}
            </span>
            <span class="text-xs text-muted-foreground ml-auto">{{ type.visits_count }} visits</span>
          </div>
        </Card>
      </div>

      <!-- Visitor Type Modal -->
      <Dialog :open="showModal" @close="showModal = false" max-width="max-w-md">
        <div class="p-6">
          <div class="flex items-center justify-between mb-5">
            <h2 class="font-semibold text-foreground text-lg">{{ editing ? 'Edit Type' : 'Add Visitor Type' }}</h2>
            <Button variant="ghost" size="icon" @click="showModal = false">
              <X class="w-4 h-4" />
            </Button>
          </div>

          <div class="space-y-3">
            <div class="space-y-1.5">
              <Label>Name</Label>
              <Input v-model="form.name" type="text" />
              <p v-if="form.errors.name" class="text-destructive text-xs">{{ form.errors.name }}</p>
            </div>

            <div class="space-y-1.5">
              <Label>Description</Label>
              <Textarea v-model="form.description" rows="2" />
            </div>

            <div class="space-y-1.5">
              <Label>Badge Color</Label>
              <div class="flex items-center gap-3">
                <input v-model="form.color" type="color"
                  class="w-10 h-10 rounded-md border border-input bg-background cursor-pointer p-0.5" />
                <Input v-model="form.color" type="text" class="flex-1 font-mono" />
              </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-foreground cursor-pointer">
              <input v-model="form.requires_escort" type="checkbox"
                class="rounded border-input bg-background text-primary focus:ring-ring" />
              Requires Escort
            </label>

            <label class="flex items-center gap-2 text-sm text-foreground cursor-pointer">
              <input v-model="form.is_active" type="checkbox"
                class="rounded border-input bg-background text-primary focus:ring-ring" />
              Active
            </label>
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
