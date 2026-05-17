<script setup lang="ts">
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';

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

function confirmDelete() {
    if (!props.precinct) {
        return;
    }

    router.delete(`/precincts/${props.precinct.id}`, {
        onSuccess: () => {
            emit('update:open', false);
        },
    });
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete Precinct</DialogTitle>
                <DialogDescription v-if="precinct">
                    Are you sure you want to delete "{{ precinct.name }}" ({{ precinct.precinct_code }})? This action cannot be undone.
                </DialogDescription>
            </DialogHeader>

            <DialogFooter>
                <Button variant="outline" @click="emit('update:open', false)">Cancel</Button>
                <Button variant="destructive" @click="confirmDelete">Delete Precinct</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
