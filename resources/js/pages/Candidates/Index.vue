<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Plus, Search, Edit2, Trash2, Download } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Politicians',
                href: '/candidates',
            },
        ],
    },
});

interface Candidate {
    id: number;
    ballot_number: string;
    name: string;
    position: string;
    party: string | null;
    photo_url: string | null;
    created_at: string;
}

interface PaginatedData {
    data: Candidate[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    next_page_url: string | null;
    prev_page_url: string | null;
}

const props = defineProps<{
    candidates: PaginatedData;
    filters: { search?: string; position?: string };
}>();

const localFilters = ref({
    search: props.filters.search ?? '',
    position: props.filters.position ?? 'all',
});

// Debounce-free filtering
function applyFilters() {
    router.get('/candidates', {
        search: localFilters.value.search || undefined,
        position: localFilters.value.position !== 'all' ? localFilters.value.position : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function handlePageChange(url: string | null) {
    if (url) {
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    }
}

// Format Position helper
function formatPosition(pos: string): string {
    const titles: Record<string, string> = {
        president: 'President',
        vice_president: 'Vice President',
        senator: 'Senator',
        party_list: 'Party List',
    };
    return titles[pos] || pos;
}

// Modals State
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const activeCandidate = ref<Candidate | null>(null);

// Forms
const createForm = useForm({
    ballot_number: '',
    name: '',
    position: 'president',
    party: '',
    photo_url: '',
});

const editForm = useForm({
    ballot_number: '',
    name: '',
    position: 'president',
    party: '',
    photo_url: '',
});

function openCreateModal() {
    createForm.reset();
    createForm.clearErrors();
    showCreateModal.value = true;
}

function submitCreate() {
    createForm.post('/candidates', {
        onSuccess: () => {
            showCreateModal.value = false;
            toast.success('Politician created successfully.');
        },
    });
}

function openEditModal(candidate: Candidate) {
    activeCandidate.value = candidate;
    editForm.ballot_number = candidate.ballot_number;
    editForm.name = candidate.name;
    editForm.position = candidate.position;
    editForm.party = candidate.party ?? '';
    editForm.photo_url = candidate.photo_url ?? '';
    editForm.clearErrors();
    showEditModal.value = true;
}

function submitEdit() {
    if (!activeCandidate.value) return;
    editForm.put(`/candidates/${activeCandidate.value.id}`, {
        onSuccess: () => {
            showEditModal.value = false;
            toast.success('Politician updated successfully.');
        },
    });
}

function openDeleteModal(candidate: Candidate) {
    activeCandidate.value = candidate;
    showDeleteModal.value = true;
}

function submitDelete() {
    if (!activeCandidate.value) return;
    router.delete(`/candidates/${activeCandidate.value.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            toast.success('Politician deleted successfully.');
        },
    });
}

function triggerExport() {
    window.location.href = '/candidates/export';
}
</script>

<template>
    <Head title="Politicians" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <!-- Action Row -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 flex-wrap items-center gap-3">
                <div class="relative w-full max-w-sm">
                    <Search class="absolute top-2.5 left-3 h-4 w-4 text-muted-foreground" />
                    <Input
                        v-model="localFilters.search"
                        placeholder="Search politicians..."
                        class="pl-9 rounded-xl"
                        @keyup.enter="applyFilters"
                    />
                </div>

                <Select v-model="localFilters.position" @update:model-value="applyFilters">
                    <SelectTrigger class="w-[180px] rounded-xl">
                        <SelectValue placeholder="Position" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Positions</SelectItem>
                        <SelectItem value="president">President</SelectItem>
                        <SelectItem value="vice_president">Vice President</SelectItem>
                        <SelectItem value="senator">Senator</SelectItem>
                        <SelectItem value="party_list">Party List</SelectItem>
                    </SelectContent>
                </Select>

                <Button variant="outline" @click="applyFilters" class="rounded-xl">
                    Apply Filters
                </Button>
            </div>

            <div class="flex items-center gap-3">
                <Button variant="outline" @click="triggerExport" class="rounded-xl flex items-center gap-2">
                    <Download class="h-4 w-4" />
                    Export CSV
                </Button>
                <Button @click="openCreateModal" class="rounded-xl flex items-center gap-2">
                    <Plus class="h-4 w-4" />
                    Add Politician
                </Button>
            </div>
        </div>

        <!-- Table Card -->
        <Card class="rounded-2xl ring-1 ring-border">
            <CardHeader class="pb-2">
                <CardTitle>Politician Directory</CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <Table>
                    <TableHeader>
                        <TableRow class="h-12">
                            <TableHead class="px-6 w-[120px]">Ballot #</TableHead>
                            <TableHead class="px-6">Name</TableHead>
                            <TableHead class="px-6 w-[180px]">Position</TableHead>
                            <TableHead class="px-6 w-[180px]">Party</TableHead>
                            <TableHead class="px-6 w-[120px] text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="candidate in candidates.data" :key="candidate.id" class="hover:bg-muted/40 transition-colors">
                            <TableCell class="px-6 py-4 font-mono font-medium text-sm">
                                {{ candidate.ballot_number }}
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-full bg-muted flex items-center justify-center overflow-hidden border">
                                        <img
                                            v-if="candidate.photo_url"
                                            :src="candidate.photo_url"
                                            :alt="candidate.name"
                                            class="h-full w-full object-cover"
                                        />
                                        <span v-else class="text-xs font-semibold text-muted-foreground">
                                            {{ candidate.name.charAt(0) }}
                                        </span>
                                    </div>
                                    <span class="font-medium">{{ candidate.name }}</span>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <Badge variant="secondary">
                                    {{ formatPosition(candidate.position) }}
                                </Badge>
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <span v-if="candidate.party" class="text-sm font-medium">
                                    {{ candidate.party }}
                                </span>
                                <span v-else class="text-sm text-muted-foreground italic">
                                    Independent
                                </span>
                            </TableCell>
                            <TableCell class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button variant="ghost" size="icon" @click="openEditModal(candidate)" class="h-8 w-8 rounded-lg hover:bg-accent">
                                        <Edit2 class="h-4 w-4 text-muted-foreground" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="openDeleteModal(candidate)" class="h-8 w-8 rounded-lg hover:bg-destructive/10 hover:text-destructive">
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="candidates.data.length === 0">
                            <TableCell colspan="5" class="py-8 text-center text-muted-foreground text-sm">
                                No politicians registered yet.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <!-- Pagination -->
                <div class="flex items-center justify-between border-t px-6 py-4">
                    <p class="text-sm text-muted-foreground">
                        Showing {{ candidates.data.length }} of {{ candidates.total }} politicians
                    </p>
                    <div class="flex gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="!candidates.prev_page_url"
                            @click="handlePageChange(candidates.prev_page_url)"
                            class="rounded-lg"
                        >
                            Previous
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="!candidates.next_page_url"
                            @click="handlePageChange(candidates.next_page_url)"
                            class="rounded-lg"
                        >
                            Next
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Create Modal -->
        <Dialog :open="showCreateModal" @update:open="showCreateModal = $event">
            <DialogContent class="sm:max-w-[425px] rounded-2xl">
                <DialogHeader>
                    <DialogTitle>Add Politician</DialogTitle>
                    <DialogDescription>
                        Register a new candidate in the central repository.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitCreate" class="space-y-4 py-2">
                    <div class="grid gap-2">
                        <Label for="create-ballot-number">Ballot Number</Label>
                        <Input id="create-ballot-number" v-model="createForm.ballot_number" placeholder="e.g. 1" class="rounded-xl" />
                        <span v-if="createForm.errors.ballot_number" class="text-xs text-destructive">{{ createForm.errors.ballot_number }}</span>
                    </div>

                    <div class="grid gap-2">
                        <Label for="create-name">Name</Label>
                        <Input id="create-name" v-model="createForm.name" placeholder="Full Name" class="rounded-xl" />
                        <span v-if="createForm.errors.name" class="text-xs text-destructive">{{ createForm.errors.name }}</span>
                    </div>

                    <div class="grid gap-2">
                        <Label for="create-position">Position</Label>
                        <Select v-model="createForm.position">
                            <SelectTrigger class="rounded-xl">
                                <SelectValue placeholder="Select Position" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="president">President</SelectItem>
                                <SelectItem value="vice_president">Vice President</SelectItem>
                                <SelectItem value="senator">Senator</SelectItem>
                                <SelectItem value="party_list">Party List</SelectItem>
                            </SelectContent>
                        </Select>
                        <span v-if="createForm.errors.position" class="text-xs text-destructive">{{ createForm.errors.position }}</span>
                    </div>

                    <div class="grid gap-2">
                        <Label for="create-party">Party</Label>
                        <Input id="create-party" v-model="createForm.party" placeholder="Independent / Party Name" class="rounded-xl" />
                        <span v-if="createForm.errors.party" class="text-xs text-destructive">{{ createForm.errors.party }}</span>
                    </div>

                    <div class="grid gap-2">
                        <Label for="create-photo">Photo URL</Label>
                        <Input id="create-photo" v-model="createForm.photo_url" placeholder="https://..." class="rounded-xl" />
                        <span v-if="createForm.errors.photo_url" class="text-xs text-destructive">{{ createForm.errors.photo_url }}</span>
                    </div>

                    <DialogFooter class="pt-4 border-t gap-2 sm:gap-0">
                        <Button type="button" variant="outline" @click="showCreateModal = false" class="rounded-xl">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="createForm.processing" class="rounded-xl">
                            Save Politician
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Edit Modal -->
        <Dialog :open="showEditModal" @update:open="showEditModal = $event">
            <DialogContent class="sm:max-w-[425px] rounded-2xl">
                <DialogHeader>
                    <DialogTitle>Edit Politician</DialogTitle>
                    <DialogDescription>
                        Update the candidate's registered details.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitEdit" class="space-y-4 py-2">
                    <div class="grid gap-2">
                        <Label for="edit-ballot-number">Ballot Number</Label>
                        <Input id="edit-ballot-number" v-model="editForm.ballot_number" class="rounded-xl" />
                        <span v-if="editForm.errors.ballot_number" class="text-xs text-destructive">{{ editForm.errors.ballot_number }}</span>
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit-name">Name</Label>
                        <Input id="edit-name" v-model="editForm.name" class="rounded-xl" />
                        <span v-if="editForm.errors.name" class="text-xs text-destructive">{{ editForm.errors.name }}</span>
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit-position">Position</Label>
                        <Select v-model="editForm.position">
                            <SelectTrigger class="rounded-xl">
                                <SelectValue placeholder="Select Position" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="president">President</SelectItem>
                                <SelectItem value="vice_president">Vice President</SelectItem>
                                <SelectItem value="senator">Senator</SelectItem>
                                <SelectItem value="party_list">Party List</SelectItem>
                            </SelectContent>
                        </Select>
                        <span v-if="editForm.errors.position" class="text-xs text-destructive">{{ editForm.errors.position }}</span>
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit-party">Party</Label>
                        <Input id="edit-party" v-model="editForm.party" class="rounded-xl" />
                        <span v-if="editForm.errors.party" class="text-xs text-destructive">{{ editForm.errors.party }}</span>
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit-photo">Photo URL</Label>
                        <Input id="edit-photo" v-model="editForm.photo_url" class="rounded-xl" />
                        <span v-if="editForm.errors.photo_url" class="text-xs text-destructive">{{ editForm.errors.photo_url }}</span>
                    </div>

                    <DialogFooter class="pt-4 border-t gap-2 sm:gap-0">
                        <Button type="button" variant="outline" @click="showEditModal = false" class="rounded-xl">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="editForm.processing" class="rounded-xl">
                            Save Changes
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Modal -->
        <Dialog :open="showDeleteModal" @update:open="showDeleteModal = $event">
            <DialogContent class="sm:max-w-[425px] rounded-2xl">
                <DialogHeader>
                    <DialogTitle>Delete Politician</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this politician? This action is permanent and cannot be undone.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="activeCandidate" class="py-4 font-medium text-center">
                    {{ activeCandidate.name }} ({{ formatPosition(activeCandidate.position) }})
                </div>

                <DialogFooter class="pt-4 border-t gap-2 sm:gap-0">
                    <Button type="button" variant="outline" @click="showDeleteModal = false" class="rounded-xl">
                        Cancel
                    </Button>
                    <Button type="button" variant="destructive" @click="submitDelete" class="rounded-xl">
                        Delete Politician
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
