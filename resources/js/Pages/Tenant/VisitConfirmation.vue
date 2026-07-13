<script setup>
import { Link } from '@inertiajs/vue3';
import QrcodeVue from 'qrcode.vue';
import AppLayout from '@/Components/AppLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Card from '@/components/ui/Card.vue';
import Button from '@/components/ui/Button.vue';
import { formatDateTime } from '@/lib/utils.js';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({ visit: Object });
</script>

<template>
  <AppLayout>
    <div class="max-w-lg mx-auto space-y-5">
      <Link :href="route('tenant.dashboard')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors">
        <ArrowLeft class="w-4 h-4" /> Back to portal
      </Link>

      <Card class="p-6 space-y-5 text-center">
        <div>
          <h1 class="text-xl font-bold text-foreground">Visitor Pre-registered</h1>
          <p class="text-sm text-muted-foreground mt-1">
            {{ visit.visitor.first_name }} {{ visit.visitor.last_name }} &middot; {{ visit.unit?.name ?? 'Walk-in' }}
          </p>
          <p class="text-xs text-muted-foreground mt-0.5">{{ formatDateTime(visit.expected_arrival) }}</p>
          <p v-if="visit.vehicle" class="text-xs text-primary mt-0.5">{{ visit.vehicle.plate_number }}</p>
        </div>

        <div v-if="visit.qr_token" class="flex flex-col items-center gap-3">
          <div class="p-4 bg-white rounded-lg inline-block">
            <QrcodeVue :value="visit.qr_token" :size="220" level="H" />
          </div>
          <p class="text-sm text-muted-foreground">
            Have your visitor show this code at the gate for a quick check-in.
          </p>
        </div>
        <div v-else class="py-4">
          <StatusBadge :status="visit.status" />
          <p class="text-sm text-muted-foreground mt-2">
            This visit is no longer pending arrival, so the code is no longer active.
          </p>
        </div>

        <Button as="a" :href="route('tenant.dashboard')" variant="outline">Back to Portal</Button>
      </Card>
    </div>
  </AppLayout>
</template>
