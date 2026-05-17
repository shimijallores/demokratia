<script setup lang="ts">
import { watch } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    open: boolean;
    precinct: {
        id: number;
        precinct_code: string;
        name: string;
        region: string | null;
        province: string | null;
        municipality: string | null;
        barangay: string | null;
        registered_voters: number | null;
        status: string;
    } | null;
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
    status: 'pending',
});

function submit() {
    if (!props.precinct) {
        return;
    }

    form.put(`/precincts/${props.precinct.id}`, {
        onSuccess: () => {
            emit('update:open', false);
        },
    });
}

watch(() => props.open, (isOpen) => {
    if (isOpen && props.precinct) {
        form.precinct_code = props.precinct.precinct_code;
        form.name = props.precinct.name;
        form.region = props.precinct.region || '';
        form.province = props.precinct.province || '';
        form.municipality = props.precinct.municipality || '';
        form.barangay = props.precinct.barangay || '';
        form.registered_voters = props.precinct.registered_voters?.toString() || '';
        form.status = props.precinct.status;
        form.clearErrors();
    }
});
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Edit Precinct</DialogTitle>
                <DialogDescription>Update precinct details.</DialogDescription>
            </DialogHeader>

            <form v-if="precinct" @submit.prevent="submit" class="space-y-4">
                <div class="grid gap-2">
                    <Label for="precinct_code">Precinct Code</Label>
                    <Input id="precinct_code" v-model="form.precinct_code" />
                    <p v-if="form.errors.precinct_code" class="text-sm text-red-500">{{ form.errors.precinct_code }}</p>
                </div>

                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" />
                    <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="region">Region</Label>
                        <Input id="region" v-model="form.region" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="province">Province</Label>
                        <Input id="province" v-model="form.province" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="municipality">Municipality</Label>
                        <Input id="municipality" v-model="form.municipality" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="barangay">Barangay</Label>
                        <Input id="barangay" v-model="form.barangay" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="registered_voters">Registered Voters</Label>
                        <Input id="registered_voters" v-model="form.registered_voters" type="number" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="status">Status</Label>
                        <Select v-model="form.status">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="transmitting">Transmitting</SelectItem>
                                <SelectItem value="partial">Partial</SelectItem>
                                <SelectItem value="complete">Complete</SelectItem>
                                <SelectItem value="error">Error</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="emit('update:open', false)">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">Update Precinct</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
