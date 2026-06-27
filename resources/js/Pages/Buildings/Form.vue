<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Textarea from '@/components/ui/Textarea.vue';
import Card from '@/components/ui/Card.vue';

const props = defineProps({ building: Object });
const isEditing = !!props.building;

const form = useForm({
  name: props.building?.name ?? '',
  address: props.building?.address ?? '',
  city: props.building?.city ?? '',
  country: props.building?.country ?? 'Kenya',
  phone: props.building?.phone ?? '',
  email: props.building?.email ?? '',
  description: props.building?.description ?? '',
  is_active: props.building?.is_active ?? true,
});

function submit() {
  isEditing ? form.put(route('buildings.update', props.building.id)) : form.post(route('buildings.store'));
}
</script>

<template>
  <AppLayout>
    <div class="max-w-2xl space-y-6">
      <h1 class="text-2xl font-bold text-foreground">{{ isEditing ? 'Edit Building' : 'Add Building' }}</h1>

      <Card class="p-6">
        <form @submit.prevent="submit" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2 space-y-1.5">
              <Label for="name">Building Name</Label>
              <Input id="name" v-model="form.name" type="text" />
              <p v-if="form.errors.name" class="text-destructive text-xs">{{ form.errors.name }}</p>
            </div>

            <div class="col-span-2 space-y-1.5">
              <Label for="address">Address</Label>
              <Input id="address" v-model="form.address" type="text" />
            </div>

            <div class="space-y-1.5">
              <Label for="city">City</Label>
              <Input id="city" v-model="form.city" type="text" />
            </div>

            <div class="space-y-1.5">
              <Label for="country">Country</Label>
              <Input id="country" v-model="form.country" type="text" />
            </div>

            <div class="space-y-1.5">
              <Label for="phone">Phone</Label>
              <Input id="phone" v-model="form.phone" type="tel" />
            </div>

            <div class="space-y-1.5">
              <Label for="email">Email</Label>
              <Input id="email" v-model="form.email" type="email" />
            </div>

            <div class="col-span-2 space-y-1.5">
              <Label for="description">Description</Label>
              <Textarea id="description" v-model="form.description" rows="3" />
            </div>

            <div class="flex items-center gap-2">
              <input v-model="form.is_active" type="checkbox" id="is_active"
                class="rounded border-input bg-background text-primary focus:ring-ring" />
              <Label for="is_active">Building Active</Label>
            </div>
          </div>

          <div class="flex gap-3 pt-2">
            <Button type="submit" :disabled="form.processing">
              {{ form.processing ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
            </Button>
            <Button as="a" :href="route('buildings.index')" variant="outline">Cancel</Button>
          </div>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>
