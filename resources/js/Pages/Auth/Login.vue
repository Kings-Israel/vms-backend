<script setup>
import { useForm } from '@inertiajs/vue3';
import { Shield } from 'lucide-vue-next';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

function submit() {
  form.post(route('login'));
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-background px-4">
    <div class="w-full max-w-md">
      <div class="rounded-xl border border-border bg-card shadow-xl p-8">
        <div class="text-center mb-8">
          <div class="inline-flex items-center justify-center w-14 h-14 bg-primary rounded-xl mb-4">
            <Shield class="w-7 h-7 text-primary-foreground" />
          </div>
          <h1 class="text-2xl font-bold text-foreground">Visitor Management</h1>
          <p class="text-muted-foreground text-sm mt-1">Sign in to your account</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <div class="space-y-1.5">
            <Label for="email">Email</Label>
            <Input
              id="email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              placeholder="admin@example.com"
            />
            <p v-if="form.errors.email" class="text-destructive text-xs mt-1">{{ form.errors.email }}</p>
          </div>

          <div class="space-y-1.5">
            <Label for="password">Password</Label>
            <Input
              id="password"
              v-model="form.password"
              type="password"
              autocomplete="current-password"
            />
            <p v-if="form.errors.password" class="text-destructive text-xs mt-1">{{ form.errors.password }}</p>
          </div>

          <label class="flex items-center gap-2 text-sm text-muted-foreground cursor-pointer">
            <input
              v-model="form.remember"
              type="checkbox"
              class="rounded border-input bg-background text-primary focus:ring-ring"
            />
            Remember me
          </label>

          <Button type="submit" :disabled="form.processing" class="w-full">
            {{ form.processing ? 'Signing in...' : 'Sign In' }}
          </Button>
        </form>
      </div>
    </div>
  </div>
</template>
