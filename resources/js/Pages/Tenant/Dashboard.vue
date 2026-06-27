<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Select from '@/components/ui/Select.vue';
import Card from '@/components/ui/Card.vue';
import Dialog from '@/components/ui/Dialog.vue';
import { formatDateTime } from '@/lib/utils.js';
import { Plus, X, Users, CalendarClock, UserCheck } from 'lucide-vue-next';

const props = defineProps({ units: Array, expectedVisits: Array, stats: Object, visitorTypes: Array });

const showModal = ref(false);

const form = useForm({
  unit_id: props.units?.[0]?.id ?? '',
  visitor_type_id: '',
  purpose: '',
  expected_arrival: '',
  expected_departure: '',
  notes: '',
  'visitor.first_name': '',
  'visitor.last_name': '',
  'visitor.national_id': '',
  'visitor.phone': '',
  'visitor.email': '',
  'visitor.company': '',
  'vehicle.plate_number': '',
  'vehicle.make': '',
  'vehicle.model': '',
  'vehicle.color': '',
});

function submit() {
  form.post(route('tenant.register-visitor'), {
    onSuccess: () => { showModal.value = false; form.reset(); },
  });
}

const statCards = [
  { key: 'total_visits',     label: 'Total Visits',      icon: Users,        color: 'text-blue-400 bg-blue-500/10' },
  { key: 'today_visits',     label: 'Today',             icon: CalendarClock, color: 'text-yellow-400 bg-yellow-500/10' },
  { key: 'currently_inside', label: 'Currently Inside',  icon: UserCheck,    color: 'text-green-400 bg-green-500/10' },
];
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-foreground">Tenant Portal</h1>
        <Button @click="showModal = true">
          <Plus class="w-4 h-4 mr-1.5" /> Pre-register Visitor
        </Button>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-3 gap-4">
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

      <!-- Expected & Current Visitors -->
      <Card>
        <div class="p-4 border-b border-border font-semibold text-foreground">
          Expected &amp; Current Visitors
        </div>
        <div class="divide-y divide-border">
          <div v-if="!expectedVisits.length" class="p-6 text-center text-muted-foreground text-sm">
            No visitors expected today.
          </div>
          <div v-for="visit in expectedVisits" :key="visit.id" class="flex items-center justify-between p-4">
            <div>
              <p class="font-medium text-sm text-foreground">
                {{ visit.visitor.first_name }} {{ visit.visitor.last_name }}
              </p>
              <p class="text-xs text-muted-foreground">{{ visit.visitor.phone ?? visit.visitor.company ?? '' }}</p>
              <p v-if="visit.vehicle" class="text-xs text-primary">{{ visit.vehicle.plate_number }}</p>
            </div>
            <div class="text-right">
              <StatusBadge :status="visit.status" />
              <p class="text-xs text-muted-foreground mt-1">{{ formatDateTime(visit.expected_arrival) }}</p>
              <button
                v-if="visit.status === 'expected'"
                @click="router.delete(route('tenant.cancel-visit', visit.id))"
                class="text-xs text-destructive hover:underline mt-1"
              >Cancel</button>
            </div>
          </div>
        </div>
      </Card>

      <!-- Pre-register Modal -->
      <Dialog :open="showModal" @close="showModal = false" max-width="max-w-xl">
        <div class="p-6">
          <div class="flex items-center justify-between mb-5">
            <h2 class="font-semibold text-foreground text-lg">Pre-register Visitor</h2>
            <Button variant="ghost" size="icon" @click="showModal = false">
              <X class="w-4 h-4" />
            </Button>
          </div>

          <form @submit.prevent="submit" class="space-y-4">
            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1.5">
                <Label>First Name <span class="text-destructive">*</span></Label>
                <Input v-model="form['visitor.first_name']" type="text" />
              </div>
              <div class="space-y-1.5">
                <Label>Last Name <span class="text-destructive">*</span></Label>
                <Input v-model="form['visitor.last_name']" type="text" />
              </div>
              <div class="space-y-1.5">
                <Label>National ID</Label>
                <Input v-model="form['visitor.national_id']" type="text" />
              </div>
              <div class="space-y-1.5">
                <Label>Phone</Label>
                <Input v-model="form['visitor.phone']" type="tel" />
              </div>
              <div class="space-y-1.5">
                <Label>Company</Label>
                <Input v-model="form['visitor.company']" type="text" />
              </div>
              <div class="space-y-1.5">
                <Label>Unit</Label>
                <Select v-model="form.unit_id">
                  <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                </Select>
              </div>
              <div class="space-y-1.5">
                <Label>Expected Arrival <span class="text-destructive">*</span></Label>
                <Input v-model="form.expected_arrival" type="datetime-local" />
              </div>
              <div class="space-y-1.5">
                <Label>Expected Departure</Label>
                <Input v-model="form.expected_departure" type="datetime-local" />
              </div>
              <div class="col-span-2 space-y-1.5">
                <Label>Purpose</Label>
                <Input v-model="form.purpose" type="text" />
              </div>
            </div>

            <p class="text-sm font-medium text-muted-foreground">Vehicle (Optional)</p>
            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1.5">
                <Label>Plate Number</Label>
                <Input v-model="form['vehicle.plate_number']" type="text" />
              </div>
              <div class="space-y-1.5">
                <Label>Make</Label>
                <Input v-model="form['vehicle.make']" type="text" />
              </div>
              <div class="space-y-1.5">
                <Label>Model</Label>
                <Input v-model="form['vehicle.model']" type="text" />
              </div>
              <div class="space-y-1.5">
                <Label>Color</Label>
                <Input v-model="form['vehicle.color']" type="text" />
              </div>
            </div>

            <div class="flex gap-3 pt-1">
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Registering...' : 'Register Visitor' }}
              </Button>
              <Button type="button" variant="outline" @click="showModal = false">Cancel</Button>
            </div>
          </form>
        </div>
      </Dialog>
    </div>
  </AppLayout>
</template>
