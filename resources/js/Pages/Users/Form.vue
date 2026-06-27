<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Select from '@/components/ui/Select.vue';
import Card from '@/components/ui/Card.vue';

const props = defineProps({
  user: Object,
  roles: Array,
  buildings: Array,
});

const isEditing = !!props.user;
const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

const form = useForm({
  name: props.user?.name ?? '',
  email: props.user?.email ?? '',
  phone: props.user?.phone ?? '',
  password: '',
  role: props.user?.roles?.[0]?.name ?? '',
  building_id: props.user?.building_id ?? '',
  is_active: props.user?.is_active ?? true,
  hours: props.user?.working_hours ?? days.map((_, i) => ({
    day_of_week: i, start_time: '08:00', end_time: '17:00', is_active: i >= 1 && i <= 5,
  })),
});

function submit() {
  isEditing ? form.put(route('users.update', props.user.id)) : form.post(route('users.store'));
}
</script>

<template>
  <AppLayout>
    <div class="max-w-2xl space-y-6">
      <h1 class="text-2xl font-bold text-foreground">{{ isEditing ? 'Edit User' : 'Add User' }}</h1>

      <form @submit.prevent="submit" class="space-y-6">
        <Card class="p-6 space-y-4">
          <h2 class="font-semibold text-foreground">User Details</h2>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <Label for="name">Full Name</Label>
              <Input id="name" v-model="form.name" type="text" />
              <p v-if="form.errors.name" class="text-destructive text-xs">{{ form.errors.name }}</p>
            </div>

            <div class="space-y-1.5">
              <Label for="email">Email</Label>
              <Input id="email" v-model="form.email" type="email" />
              <p v-if="form.errors.email" class="text-destructive text-xs">{{ form.errors.email }}</p>
            </div>

            <div class="space-y-1.5">
              <Label for="phone">Phone</Label>
              <Input id="phone" v-model="form.phone" type="tel" />
            </div>

            <div class="space-y-1.5">
              <Label for="password">
                Password {{ isEditing ? '(leave blank to keep)' : '' }}
              </Label>
              <Input id="password" v-model="form.password" type="password" />
              <p v-if="form.errors.password" class="text-destructive text-xs">{{ form.errors.password }}</p>
            </div>

            <div class="space-y-1.5">
              <Label for="role">Role</Label>
              <Select id="role" v-model="form.role">
                <option value="">Select role...</option>
                <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name.replace('_', ' ') }}</option>
              </Select>
              <p v-if="form.errors.role" class="text-destructive text-xs">{{ form.errors.role }}</p>
            </div>

            <div class="space-y-1.5">
              <Label for="building">Building</Label>
              <Select id="building" v-model="form.building_id">
                <option value="">Select building...</option>
                <option v-for="b in buildings" :key="b.id" :value="b.id">{{ b.name }}</option>
              </Select>
            </div>

            <div class="flex items-center gap-2 col-span-2">
              <input v-model="form.is_active" type="checkbox" id="is_active"
                class="rounded border-input bg-background text-primary focus:ring-ring" />
              <Label for="is_active">Account Active</Label>
            </div>
          </div>
        </Card>

        <!-- Working hours for security officers -->
        <Card v-if="form.role === 'security_officer'" class="p-6">
          <h2 class="font-semibold text-foreground mb-4">Working Hours</h2>
          <div class="space-y-3">
            <div v-for="(hour, i) in form.hours" :key="i" class="flex items-center gap-3">
              <input v-model="hour.is_active" type="checkbox"
                class="rounded border-input bg-background text-primary focus:ring-ring" />
              <span class="text-sm text-foreground w-24">{{ days[i] }}</span>
              <Input
                v-model="hour.start_time"
                type="time"
                :disabled="!hour.is_active"
                class="w-32 disabled:opacity-40"
              />
              <span class="text-muted-foreground text-sm">to</span>
              <Input
                v-model="hour.end_time"
                type="time"
                :disabled="!hour.is_active"
                class="w-32 disabled:opacity-40"
              />
            </div>
          </div>
        </Card>

        <div class="flex gap-3">
          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Saving...' : (isEditing ? 'Update User' : 'Create User') }}
          </Button>
          <Button as="a" :href="route('users.index')" variant="outline">Cancel</Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
