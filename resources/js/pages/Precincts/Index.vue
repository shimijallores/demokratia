<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import PrecinctCreateModal from '@/components/precincts/create/CreateModal.vue';
import PrecinctEditModal from '@/components/precincts/edit/EditModal.vue';
import PrecinctDeleteModal from '@/components/precincts/destroy/DeleteModal.vue';
import PrecinctKeysModal from '@/components/precincts/keys/KeysModal.vue';
import { ref, computed } from 'vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Precincts',
                href: '/precincts',
            },
        ],
    },
});

interface Precinct {
    id: number;
    precinct_code: string;
    name: string;
    region: string | null;
    province: string | null;
    municipality: string | null;
    barangay: string | null;
    registered_voters: number | null;
    status: string;
    created_at: string;
    updated_at: string;
}

interface PaginatedData {
    data: Precinct[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    next_page_url: string | null;
    prev_page_url: string | null;
}

const props = defineProps<{
    precincts: PaginatedData;
    filters: { search?: string; status?: string; region?: string };
}>();

const showCreateModal = ref(false);
const editingPrecinct = ref<Precinct | null>(null);
const deletingPrecinct = ref<Precinct | null>(null);
const regeneratingPrecinct = ref<Precinct | null>(null);

const statusBadgeClass = computed(() => (status: string) => {
    const classes: Record<string, string> = {
        pending: 'bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        transmitting: 'bg-yellow-50 text-yellow-700 dark:bg-yellow-950 dark:text-yellow-300',
        partial: 'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
        complete: 'bg-green-50 text-green-700 dark:bg-green-950 dark:text-green-300',
        error: 'bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300',
    };
    return classes[status] || classes.pending;
});

function filterPrecincts() {
    router.get('/precincts', props.filters, {
        preserveState: true,
        preserveScroll: true,
    });
}

function handlePageChange(url: string | null) {
    if (url) {
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    }
}

function regenerateKeys(precinct: Precinct) {
    router.post(`/precincts/${precinct.id}/regenerate-keys`, {}, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Precincts" />

    <div class="mx-auto w-full lg:w-[60%]">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card class="rounded-2xl ring-1 ring-border">
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle>Precincts</CardTitle>
                    <Button @click="showCreateModal = true">Add Precinct</Button>
                </CardHeader>
                <CardContent>
                    <div class="mb-4 flex gap-4">
                        <Input
                            v-model="filters.search"
                            placeholder="Search precincts..."
                            class="max-w-sm"
                            @input="filterPrecincts"
                        />
                        <Select v-model="filters.status" @update:model-value="filterPrecincts">
                            <SelectTrigger class="w-[180px]">
                                <SelectValue placeholder="All statuses" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All statuses</SelectItem>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="transmitting">Transmitting</SelectItem>
                                <SelectItem value="partial">Partial</SelectItem>
                                <SelectItem value="complete">Complete</SelectItem>
                                <SelectItem value="error">Error</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <Table>
                        <TableHeader>
                            <TableRow class="h-12">
                                <TableHead class="px-4">Code</TableHead>
                                <TableHead class="px-4">Name</TableHead>
                                <TableHead class="px-4">Region</TableHead>
                                <TableHead class="px-4">Registered Voters</TableHead>
                                <TableHead class="px-4">Status</TableHead>
                                <TableHead class="px-4 text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="precinct in precincts.data" :key="precinct.id">
                                <TableCell class="px-4 py-4 font-medium">{{ precinct.precinct_code }}</TableCell>
                                <TableCell class="px-4 py-4">{{ precinct.name }}</TableCell>
                                <TableCell class="px-4 py-4">{{ precinct.region || '-' }}</TableCell>
                                <TableCell class="px-4 py-4">{{ precinct.registered_voters?.toLocaleString() || '-' }}</TableCell>
                                <TableCell class="px-4 py-4">
                                    <Badge :class="statusBadgeClass(precinct.status)">
                                        {{ precinct.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="px-4 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="outline" size="sm" @click="regenerateKeys(precinct)">
                                            Keys
                                        </Button>
                                        <Button variant="outline" size="sm" @click="editingPrecinct = precinct">
                                            Edit
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="deletingPrecinct = precinct">
                                            Delete
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="precincts.data.length === 0">
                                <TableCell colspan="6" class="text-center">No precincts found.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <div class="mt-4 flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">
                            Showing {{ precincts.total }} precinct(s)
                        </p>
                        <div class="flex gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="!precincts.prev_page_url"
                                @click="handlePageChange(precincts.prev_page_url)"
                            >
                                Previous
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="!precincts.next_page_url"
                                @click="handlePageChange(precincts.next_page_url)"
                            >
                                Next
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>

    <PrecinctCreateModal v-model:open="showCreateModal" />
    <PrecinctEditModal :open="!!editingPrecinct" :precinct="editingPrecinct" @update:open="(val: boolean) => { if (!val) editingPrecinct = null }" />
    <PrecinctDeleteModal :open="!!deletingPrecinct" :precinct="deletingPrecinct" @update:open="(val: boolean) => { if (!val) deletingPrecinct = null }" />
    <PrecinctKeysModal :open="!!regeneratingPrecinct" :precinct="regeneratingPrecinct" @update:open="(val: boolean) => { if (!val) regeneratingPrecinct = null }" />
</template>
