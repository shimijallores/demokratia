<script setup lang="ts">
import { ref, watch } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm({
    precinct_code: '',
    name: '',
    region: '',
    province: '',
    municipality: '',
    barangay: '',
    registered_voters: '',
});

const showKeys = ref(false);
const generatedKeys = ref<{ precinct_code: string; api_key: string; aes_key: string } | null>(null);

const page = usePage();

watch(() => page.props.flash?.precinct_keys, (keys) => {
    if (keys) {
        generatedKeys.value = keys;
        showKeys.value = true;
    }
}, { deep: true });

function submit() {
    form.post('/precincts', {
        onSuccess: () => {
            form.reset();
            emit('update:open', false);
        },
    });
}

watch(() => props.open, (isOpen) => {
    if (!isOpen) {
        form.reset();
        form.clearErrors();
    }
});

function copyToClipboard(text: string) {
    navigator.clipboard.writeText(text);
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Add Precinct</DialogTitle>
                <DialogDescription>Create a new precinct for vote transmission.</DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid gap-2">
                    <Label for="precinct_code">Precinct Code</Label>
                    <Input id="precinct_code" v-model="form.precinct_code" placeholder="e.g., NCR-QC-001" />
                    <p v-if="form.errors.precinct_code" class="text-sm text-red-500">{{ form.errors.precinct_code }}</p>
                </div>

                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" placeholder="Precinct name" />
                    <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="region">Region</Label>
                        <Input id="region" v-model="form.region" placeholder="e.g., NCR" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="province">Province</Label>
                        <Input id="province" v-model="form.province" placeholder="Province" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="municipality">Municipality</Label>
                        <Input id="municipality" v-model="form.municipality" placeholder="Municipality" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="barangay">Barangay</Label>
                        <Input id="barangay" v-model="form.barangay" placeholder="Barangay" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="registered_voters">Registered Voters</Label>
                    <Input id="registered_voters" v-model="form.registered_voters" type="number" placeholder="Number of voters" />
                    <p v-if="form.errors.registered_voters" class="text-sm text-red-500">{{ form.errors.registered_voters }}</p>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="emit('update:open', false)">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">Create Precinct</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <Dialog :open="showKeys" @update:open="showKeys = $event">
        <DialogContent class="max-w-lg">
            <DialogHeader>
                <DialogTitle>Precinct Created</DialogTitle>
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
