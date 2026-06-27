<script setup>
import AppLayout from '@/Components/AppLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Card from '@/components/ui/Card.vue';
import { formatDateTime } from '@/lib/utils.js';
import { Users, Building2, UserCheck, Clock, CalendarCheck, Shield } from 'lucide-vue-next';

const props = defineProps({
  stats: Object,
  todayVisits: Array,
  recentActivity: Array,
});

const statCards = [
  { label: 'Visitors Today',    key: 'total_visitors_today', icon: Users,        color: 'text-blue-400 bg-blue-500/10' },
  { label: 'Currently Inside',  key: 'currently_inside',     icon: UserCheck,    color: 'text-green-400 bg-green-500/10' },
  { label: 'Expected Today',    key: 'expected_today',       icon: CalendarCheck, color: 'text-yellow-400 bg-yellow-500/10' },
  { label: 'Active Shifts',     key: 'active_shifts',        icon: Clock,        color: 'text-purple-400 bg-purple-500/10' },
  { label: 'Total Tenants',     key: 'total_tenants',        icon: Building2,    color: 'text-indigo-400 bg-indigo-500/10' },
  { label: 'Security Officers', key: 'total_officers',       icon: Shield,       color: 'text-red-400 bg-red-500/10' },
];
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold text-foreground">Dashboard</h1>

      <!-- Stat cards -->
      <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
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

      <div class="grid md:grid-cols-2 gap-6">
        <!-- Today's visitors -->
        <Card>
          <div class="p-4 border-b border-border">
            <h2 class="font-semibold text-foreground">Today's Visitors</h2>
          </div>
          <div class="divide-y divide-border">
            <div v-if="!todayVisits.length" class="p-6 text-center text-muted-foreground text-sm">
              No visitors today.
            </div>
            <div
              v-for="visit in todayVisits"
              :key="visit.id"
              class="flex items-center justify-between p-4"
            >
              <div>
                <p class="text-sm font-medium text-foreground">
                  {{ visit.visitor.first_name }} {{ visit.visitor.last_name }}
                </p>
                <p class="text-xs text-muted-foreground">{{ visit.unit?.name ?? 'Walk-in' }}</p>
              </div>
              <div class="text-right">
                <StatusBadge :status="visit.status" />
                <p class="text-xs text-muted-foreground mt-1">
                  {{ visit.expected_arrival ? formatDateTime(visit.expected_arrival) : '-' }}
                </p>
              </div>
            </div>
          </div>
        </Card>

        <!-- Recent activity -->
        <Card>
          <div class="p-4 border-b border-border">
            <h2 class="font-semibold text-foreground">Recent Activity</h2>
          </div>
          <div class="divide-y divide-border">
            <div v-if="!recentActivity.length" class="p-6 text-center text-muted-foreground text-sm">
              No activity yet.
            </div>
            <div
              v-for="visit in recentActivity"
              :key="visit.id"
              class="flex items-center justify-between p-4"
            >
              <div>
                <p class="text-sm font-medium text-foreground">
                  {{ visit.visitor.first_name }} {{ visit.visitor.last_name }}
                </p>
                <p class="text-xs text-muted-foreground">By {{ visit.checked_in_by?.name ?? '-' }}</p>
              </div>
              <div class="text-right">
                <StatusBadge :status="visit.status" />
                <p class="text-xs text-muted-foreground mt-1">{{ formatDateTime(visit.updated_at) }}</p>
              </div>
            </div>
          </div>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
