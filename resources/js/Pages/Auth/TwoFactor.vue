<script setup>
import { useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';

const form = useForm({ code: '' });

function submit() {
  form.post(route('2fa.challenge'));
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-background px-4">
    <div class="w-full max-w-md">
      <div class="rounded-xl border border-border bg-card shadow-xl p-8">
        <div class="text-center mb-8">
          <h1 class="text-2xl font-bold text-foreground">Two-Factor Authentication</h1>
          <p class="text-muted-foreground text-sm mt-2">
            Enter the 6-digit code from your authenticator app.
          </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <div class="space-y-1.5">
            <Label for="code">Authentication Code</Label>
            <Input
              id="code"
              v-model="form.code"
              type="text"
              maxlength="6"
              autocomplete="one-time-code"
              inputmode="numeric"
              placeholder="000000"
              autofocus
              class="text-center text-2xl tracking-widest"
            />
            <p v-if="form.errors.code" class="text-destructive text-xs mt-1">{{ form.errors.code }}</p>
          </div>

          <Button type="submit" :disabled="form.processing" class="w-full">
            {{ form.processing ? 'Verifying...' : 'Verify' }}
          </Button>
        </form>
      </div>
    </div>
  </div>
</template>
