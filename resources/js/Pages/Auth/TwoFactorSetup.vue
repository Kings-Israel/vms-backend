<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Card from '@/components/ui/Card.vue';
import CardHeader from '@/components/ui/CardHeader.vue';
import CardTitle from '@/components/ui/CardTitle.vue';
import CardContent from '@/components/ui/CardContent.vue';

const props = defineProps({ enabled: Boolean });

const qrCode = ref(null);
const secret = ref(null);
const showSetup = ref(false);

const confirmForm = useForm({ code: '' });
const disableForm = useForm({ password: '' });

async function startSetup() {
  const res = await fetch(route('2fa.enable'), {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '' },
  });
  const data = await res.json();
  qrCode.value = data.qr_code;
  secret.value = data.secret;
  showSetup.value = true;
}

function confirm() {
  confirmForm.post(route('2fa.confirm'), { onSuccess: () => { showSetup.value = false; } });
}

function disable() {
  disableForm.delete(route('2fa.disable'));
}
</script>

<template>
  <AppLayout>
    <div class="max-w-lg space-y-4">
      <h1 class="text-2xl font-bold text-foreground">Two-Factor Authentication</h1>

      <Card>
        <CardHeader>
          <CardTitle>Authenticator App</CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
          <div v-if="!props.enabled">
            <p class="text-muted-foreground text-sm mb-4">
              Protect your account with an authenticator app (Google Authenticator, Authy, etc.).
            </p>

            <Button v-if="!showSetup" @click="startSetup">
              Enable 2FA
            </Button>

            <div v-if="showSetup" class="space-y-4">
              <p class="text-sm text-muted-foreground">Scan this QR code with your authenticator app:</p>
              <div class="flex justify-center p-4 bg-white rounded-lg" v-html="qrCode" />
              <p class="text-xs text-muted-foreground">
                Or enter this code manually:
                <code class="font-mono bg-muted px-1.5 py-0.5 rounded text-foreground">{{ secret }}</code>
              </p>

              <div class="space-y-1.5">
                <Label for="code">Enter verification code</Label>
                <Input
                  id="code"
                  v-model="confirmForm.code"
                  type="text"
                  maxlength="6"
                  placeholder="000000"
                />
                <p v-if="confirmForm.errors.code" class="text-destructive text-xs">{{ confirmForm.errors.code }}</p>
              </div>

              <Button @click="confirm" variant="default">
                Confirm &amp; Enable
              </Button>
            </div>
          </div>

          <div v-else class="space-y-4">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
              <span class="text-sm font-medium text-green-400">Two-factor authentication is enabled.</span>
            </div>

            <div class="space-y-3">
              <div class="space-y-1.5">
                <Label for="password">Enter your password to disable</Label>
                <Input
                  id="password"
                  v-model="disableForm.password"
                  type="password"
                />
                <p v-if="disableForm.errors.password" class="text-destructive text-xs">{{ disableForm.errors.password }}</p>
              </div>
              <Button @click="disable" variant="destructive">
                Disable 2FA
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
