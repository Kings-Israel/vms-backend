<script setup>
import { ref, computed, watchEffect } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
  LayoutDashboard, Users, Building2, DoorOpen, Shield,
  Clock, BarChart3, FileText, Settings, LogOut, Bell,
  ChevronDown, Menu, X, Home,
} from 'lucide-vue-next';

const page = usePage();
const user = computed(() => page.props.auth.user);
const roles = computed(() => user.value?.roles ?? []);

const sidebarOpen = ref(false);
const openGroup = ref(null);

const isAdmin = computed(() => roles.value.some(r => ['super_admin', 'building_manager'].includes(r)));
const isTenant = computed(() => roles.value.includes('tenant'));

const navigation = computed(() => {
  const items = [
    { name: 'Dashboard', href: route('dashboard'), icon: LayoutDashboard },
  ];

  if (isAdmin.value) {
    items.push(
      {
        name: 'Configuration', icon: Settings, children: [
          { name: 'Users', href: route('users.index'), icon: Users },
          { name: 'Buildings', href: route('buildings.index'), icon: Building2 },
          { name: 'Units', href: route('units.index'), icon: Home },
          { name: 'Visitor Types', href: route('visitor-types.index'), icon: DoorOpen },
          { name: 'Shifts', href: route('shifts.index'), icon: Clock },
        ],
      },
      {
        name: 'Reports', icon: BarChart3, children: [
          { name: 'Activity Log', href: route('reports.activity-log'), icon: FileText },
          { name: 'Visitor Activity', href: route('reports.visitor-activity'), icon: Users },
          { name: 'Tenant Activity', href: route('reports.tenant-activity'), icon: Building2 },
        ],
      },
      { name: 'Visits', href: route('visits.index'), icon: Shield },
    );
  }

  if (isTenant.value) {
    items.push(
      { name: 'My Portal', href: route('tenant.dashboard'), icon: Home },
      { name: 'Visit History', href: route('tenant.history'), icon: FileText },
    );
  }

  return items;
});

watchEffect(() => {
  const currentPath = page.url;
  for (const item of navigation.value) {
    if (item.children?.some(child => currentPath === child.href || currentPath.startsWith(child.href + '/'))) {
      openGroup.value = item.name;
      return;
    }
  }
});

function logout() {
  router.post(route('logout'));
}
</script>

<template>
  <div class="min-h-screen flex bg-background">
    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 w-64 bg-card border-r border-border transform transition-transform duration-200',
        'lg:translate-x-0 lg:static lg:inset-auto',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <!-- Logo -->
      <div class="flex items-center h-16 px-6 border-b border-border">
        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary mr-2.5">
          <Shield class="w-4 h-4 text-primary-foreground" />
        </div>
        <span class="text-lg font-bold text-foreground tracking-tight">VMS</span>
      </div>

      <!-- Nav -->
      <nav class="p-3 space-y-0.5 overflow-y-auto h-[calc(100vh-4rem)]">
        <template v-for="item in navigation" :key="item.name">
          <!-- Group -->
          <div v-if="item.children">
            <button
              @click="openGroup = openGroup === item.name ? null : item.name"
              class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-muted-foreground rounded-md hover:bg-accent hover:text-accent-foreground transition-colors"
            >
              <span class="flex items-center gap-2.5">
                <component :is="item.icon" class="w-4 h-4 shrink-0" />
                {{ item.name }}
              </span>
              <ChevronDown :class="['w-4 h-4 transition-transform duration-200', openGroup === item.name ? 'rotate-180' : '']" />
            </button>
            <div v-show="openGroup === item.name" class="ml-3 mt-0.5 pl-3 border-l border-border space-y-0.5">
              <Link
                v-for="child in item.children" :key="child.name"
                :href="child.href"
                class="flex items-center gap-2.5 px-3 py-2 text-sm text-muted-foreground rounded-md hover:bg-accent hover:text-accent-foreground transition-colors"
              >
                <component :is="child.icon" class="w-3.5 h-3.5 shrink-0" />
                {{ child.name }}
              </Link>
            </div>
          </div>
          <!-- Single -->
          <Link
            v-else
            :href="item.href"
            class="flex items-center gap-2.5 px-3 py-2 text-sm font-medium text-muted-foreground rounded-md hover:bg-accent hover:text-accent-foreground transition-colors"
          >
            <component :is="item.icon" class="w-4 h-4 shrink-0" />
            {{ item.name }}
          </Link>
        </template>
      </nav>
    </aside>

    <!-- Mobile overlay -->
    <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-black/60 lg:hidden" @click="sidebarOpen = false" />

    <!-- Main content -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- Topbar -->
      <header class="h-16 bg-card border-b border-border flex items-center justify-between px-4 lg:px-6 shrink-0">
        <button
          @click="sidebarOpen = !sidebarOpen"
          class="lg:hidden p-2 rounded-md hover:bg-accent text-muted-foreground hover:text-foreground transition-colors"
        >
          <Menu v-if="!sidebarOpen" class="w-5 h-5" />
          <X v-else class="w-5 h-5" />
        </button>

        <div class="flex items-center gap-2 ml-auto">
          <Link
            :href="route('2fa.setup')"
            class="p-2 rounded-md hover:bg-accent text-muted-foreground hover:text-foreground transition-colors"
          >
            <Bell class="w-5 h-5" />
          </Link>

          <div class="flex items-center gap-2 px-2">
            <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-primary-foreground text-sm font-semibold">
              {{ user?.name?.[0]?.toUpperCase() }}
            </div>
            <span class="text-sm font-medium text-foreground hidden sm:block">{{ user?.name }}</span>
          </div>

          <button
            @click="logout"
            class="p-2 rounded-md hover:bg-destructive/10 text-muted-foreground hover:text-destructive transition-colors"
          >
            <LogOut class="w-5 h-5" />
          </button>
        </div>
      </header>

      <!-- Flash messages -->
      <div v-if="$page.props.flash?.success" class="mx-6 mt-4 p-3 bg-green-500/10 border border-green-500/20 text-green-400 rounded-md text-sm">
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash?.error" class="mx-6 mt-4 p-3 bg-destructive/10 border border-destructive/20 text-red-400 rounded-md text-sm">
        {{ $page.props.flash.error }}
      </div>

      <!-- Page slot -->
      <main class="flex-1 p-6 overflow-auto">
        <slot />
      </main>
    </div>
  </div>
</template>
