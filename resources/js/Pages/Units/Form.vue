<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Select from '@/components/ui/Select.vue';
import Textarea from '@/components/ui/Textarea.vue';
import Card from '@/components/ui/Card.vue';

const props = defineProps({ unit: Object, buildings: Array, tenants: Array });
const isEditing = !!props.unit;

const form = useForm({
  building_id: props.unit?.building_id ?? '',
  name: props.unit?.name ?? '',
  floor: props.unit?.floor ?? '',
  unit_number: props.unit?.unit_number ?? '',
  type: props.unit?.type ?? 'office',
  description: props.unit?.description ?? '',
  is_active: props.unit?.is_active ?? true,
  tenant_ids: props.unit?.tenants?.map(t => t.id) ?? [],
});

function submit() {
  isEditing ? form.put(route('units.update', props.unit.id)) : form.post(route('units.store'));
}
</script>

<template>
  <AppLayout>
    <div class="max-w-2xl space-y-6">
      <h1 class="text-2xl font-bold text-foreground">{{ isEditing ? 'Edit Unit' : 'Add Unit' }}</h1>

      <Card class="p-6">
        <form @submit.prevent="submit" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <Label for="building">Building</Label>
              <Select id="building" v-model="form.building_id">
                <option value="">Select building...</option>
                <option v-for="b in buildings" :key="b.id" :value="b.id">{{ b.name }}</option>
              </Select>
              <p v-if="form.errors.building_id" class="text-destructive text-xs">{{ form.errors.building_id }}</p>
            </div>

            <div class="space-y-1.5">
              <Label for="name">Unit Name</Label>
              <Input id="name" v-model="form.name" type="text" />
            </div>

            <div class="space-y-1.5">
              <Label for="floor">Floor</Label>
              <Input id="floor" v-model="form.floor" type="text" />
            </div>

            <div class="space-y-1.5">
              <Label for="unit_number">Unit Number</Label>
              <Input id="unit_number" v-model="form.unit_number" type="text" />
            </div>

            <div class="space-y-1.5">
              <Label for="type">Type</Label>
              <Select id="type" v-model="form.type">
                <option value="office">Office</option>
                <option value="residential">Residential</option>
                <option value="commercial">Commercial</option>
                <option value="other">Other</option>
              </Select>
            </div>

            <div class="space-y-1.5">
              <Label for="tenants">Assign Tenants</Label>
              <Select id="tenants" v-model="form.tenant_ids" multiple class="h-24">
                <option v-for="t in tenants" :key="t.id" :value="t.id">{{ t.name }}</option>
              </Select>
            </div>

            <div class="col-span-2 space-y-1.5">
              <Label for="description">Description</Label>
              <Textarea id="description" v-model="form.description" rows="2" />
            </div>

            <div class="flex items-center gap-2">
              <input v-model="form.is_active" type="checkbox" id="is_active"
                class="rounded border-input bg-background text-primary focus:ring-ring" />
              <Label for="is_active">Active</Label>
            </div>
          </div>

          <div class="flex gap-3 pt-2">
            <Button type="submit" :disabled="form.processing">
              {{ form.processing ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
            </Button>
            <Button as="a" :href="route('units.index')" variant="outline">Cancel</Button>
          </div>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>
