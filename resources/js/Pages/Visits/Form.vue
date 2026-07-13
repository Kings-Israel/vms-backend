<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Select from '@/components/ui/Select.vue';
import Textarea from '@/components/ui/Textarea.vue';
import Card from '@/components/ui/Card.vue';

const props = defineProps({ buildings: Array, visitorTypes: Array });

const form = useForm({
  building_id: '',
  unit_id: '',
  visitor_type_id: '',
  purpose: '',
  notes: '',
  expected_arrival: '',
  expected_departure: '',
  visitor: { first_name: '', last_name: '', national_id: '', phone: '', email: '', company: '' },
  vehicle: { plate_number: '', make: '', model: '', color: '' },
});

const availableUnits = computed(() => {
  return props.buildings.find(b => b.id == form.building_id)?.units ?? [];
});

function onBuildingChange() { form.unit_id = ''; }
function submit() { form.post(route('visits.store')); }
</script>

<template>
  <AppLayout>
    <div class="max-w-3xl space-y-6">
      <h1 class="text-2xl font-bold text-foreground">Register Visit</h1>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Visit Details -->
        <Card class="p-6 space-y-4">
          <h2 class="font-semibold text-foreground">Visit Details</h2>
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <Label for="building">Building <span class="text-destructive">*</span></Label>
              <Select id="building" v-model="form.building_id" @change="onBuildingChange">
                <option value="">Select building...</option>
                <option v-for="b in buildings" :key="b.id" :value="b.id">{{ b.name }}</option>
              </Select>
              <p v-if="form.errors.building_id" class="text-destructive text-xs">{{ form.errors.building_id }}</p>
            </div>

            <div class="space-y-1.5">
              <Label for="unit">Unit</Label>
              <Select id="unit" v-model="form.unit_id" :disabled="!form.building_id">
                <option value="">Select unit...</option>
                <option v-for="u in availableUnits" :key="u.id" :value="u.id">{{ u.name }}</option>
              </Select>
              <p v-if="form.errors.unit_id" class="text-destructive text-xs">{{ form.errors.unit_id }}</p>
            </div>

            <div class="space-y-1.5">
              <Label for="visitor_type">Visitor Type</Label>
              <Select id="visitor_type" v-model="form.visitor_type_id">
                <option value="">Select type...</option>
                <option v-for="t in visitorTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
              </Select>
            </div>

            <div class="space-y-1.5">
              <Label for="purpose">Purpose</Label>
              <Input id="purpose" v-model="form.purpose" type="text" placeholder="e.g. Meeting, Delivery" />
            </div>

            <div class="space-y-1.5">
              <Label for="expected_arrival">Expected Arrival</Label>
              <Input id="expected_arrival" v-model="form.expected_arrival" type="datetime-local" />
              <p v-if="form.errors.expected_arrival" class="text-destructive text-xs">{{ form.errors.expected_arrival }}</p>
            </div>

            <div class="space-y-1.5">
              <Label for="expected_departure">Expected Departure</Label>
              <Input id="expected_departure" v-model="form.expected_departure" type="datetime-local" />
              <p v-if="form.errors.expected_departure" class="text-destructive text-xs">{{ form.errors.expected_departure }}</p>
            </div>

            <div class="col-span-2 space-y-1.5">
              <Label for="notes">Notes</Label>
              <Textarea id="notes" v-model="form.notes" rows="2" placeholder="Any additional notes..." />
            </div>
          </div>
        </Card>

        <!-- Visitor Information -->
        <Card class="p-6 space-y-4">
          <h2 class="font-semibold text-foreground">Visitor Information</h2>
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <Label>First Name <span class="text-destructive">*</span></Label>
              <Input v-model="form.visitor.first_name" type="text" />
              <p v-if="form.errors['visitor.first_name']" class="text-destructive text-xs">{{ form.errors['visitor.first_name'] }}</p>
            </div>
            <div class="space-y-1.5">
              <Label>Last Name <span class="text-destructive">*</span></Label>
              <Input v-model="form.visitor.last_name" type="text" />
              <p v-if="form.errors['visitor.last_name']" class="text-destructive text-xs">{{ form.errors['visitor.last_name'] }}</p>
            </div>
            <div class="space-y-1.5">
              <Label>National ID</Label>
              <Input v-model="form.visitor.national_id" type="text" placeholder="ID / Passport number" />
            </div>
            <div class="space-y-1.5">
              <Label>Phone</Label>
              <Input v-model="form.visitor.phone" type="tel" />
            </div>
            <div class="space-y-1.5">
              <Label>Email</Label>
              <Input v-model="form.visitor.email" type="email" />
              <p v-if="form.errors['visitor.email']" class="text-destructive text-xs">{{ form.errors['visitor.email'] }}</p>
            </div>
            <div class="space-y-1.5">
              <Label>Company</Label>
              <Input v-model="form.visitor.company" type="text" />
            </div>
          </div>
        </Card>

        <!-- Vehicle Information -->
        <Card class="p-6 space-y-4">
          <h2 class="font-semibold text-foreground">
            Vehicle <span class="text-xs font-normal text-muted-foreground">(optional)</span>
          </h2>
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <Label>Plate Number</Label>
              <Input v-model="form.vehicle.plate_number" type="text" placeholder="e.g. ABC 1234" />
            </div>
            <div class="space-y-1.5">
              <Label>Color</Label>
              <Input v-model="form.vehicle.color" type="text" placeholder="e.g. Silver" />
            </div>
            <div class="space-y-1.5">
              <Label>Make</Label>
              <Input v-model="form.vehicle.make" type="text" placeholder="e.g. Toyota" />
            </div>
            <div class="space-y-1.5">
              <Label>Model</Label>
              <Input v-model="form.vehicle.model" type="text" placeholder="e.g. Corolla" />
            </div>
          </div>
        </Card>

        <div class="flex gap-3">
          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Registering...' : 'Register Visit' }}
          </Button>
          <Button as="a" :href="route('visits.index')" variant="outline">Cancel</Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
