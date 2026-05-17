<script setup lang="ts">
import { ref, watch } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { usePage } from '@inertiajs/vue3';

const props = defineProps<{
    open: boolean;
    precinct: {
        id: number;
        precinct_code: string;
        name: string;
    } | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const page = usePage();
const showKeys = ref(false);
const generatedKeys = ref<{ precinct_code: string; api_key: string; aes_key: string } | null>(null);

watch(() => page.props.flash?.precinct_keys, (keys) => {
    if (keys) {
        generatedKeys.value = keys;
        showKeys.value = true;
    }
}, { deep: true });

function copyToClipboard(text: string) {
    navigator.clipboard.writeText(text);
}
</script>

<template>
    <Dialog :open="showKeys" @update:open="showKeys = $event">
        <DialogContent class="max-w-lg">
            <DialogHeader>
                <DialogTitle>Precinct Keys Regenerated</DialogTitle>
                <DialogDescription>Save these credentials securely. They are needed to configure the client app.</DialogDescription>
            </DialogHeader>

            <div v-if="generatedKeys" class="space-y-4">
                <div class="rounded-lg bg-muted p-4 space-y-3">
                    <div>
                        <Label class="text-xs text-muted-foreground">Precinct Code</Label>
                        <div class="flex items-center gap-2 mt-1">
                            <code class="text-sm font-mono bg-background px-2 py-1 rounded flex-1">{{ generatedKeys.precinct_code }}</code>
                            <Button variant="ghost" size="sm" @click="copyToClipboard(generatedKeys.precinct_code)">Copy</Button>
                        </div>
                    </div>
                    <div>
                        <Label class="text-xs text-muted-foreground">API Key</Label>
                        <div class="flex items-center gap-2 mt-1">
                            <code class="text-xs font-mono bg-background px-2 py-1 rounded flex-1 break-all">{{ generatedKeys.api_key }}</code>
                            <Button variant="ghost" size="sm" @click="copyToClipboard(generatedKeys.api_key)">Copy</Button>
                        </div>
                    </div>
                    <div>
                        <Label class="text-xs text-muted-foreground">AES Key</Label>
                        <div class="flex items-center gap-2 mt-1">
                            <code class="text-xs font-mono bg-background px-2 py-1 rounded flex-1 break-all">{{ generatedKeys.aes_key }}</code>
                            <Button variant="ghost" size="sm" @click="copyToClipboard(generatedKeys.aes_key)">Copy</Button>
                        </div>
                    </div>
                </div>

                <p class="text-sm text-destructive">⚠️ These keys will not be shown again. Save them now.</p>
            </div>

            <DialogFooter>
                <Button @click="showKeys = false">Done</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
