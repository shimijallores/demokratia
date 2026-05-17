<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Progress } from '@/components/ui/progress';
import { Badge } from '@/components/ui/badge';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { ref } from 'vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Results',
                href: '/results',
            },
        ],
    },
});

const props = defineProps<{
    tally: Record<string, Array<{
        candidate_id: number;
        name: string;
        party: string;
        position: string;
        ballot_number: string;
        photo_url: string | null;
        vote_count: number;
        percentage: number;
    }>>;
    precincts: Array<{
        id: number;
        precinct_code: string;
        name: string;
        region: string | null;
        status: string;
        registered_voters: number | null;
    }>;
}>();

const selectedPrecinct = ref<any>(null);

const positions = ['president', 'vice_president', 'senator', 'party_list'];
const positionLabels: Record<string, string> = {
    president: 'President',
    vice_president: 'Vice President',
    senator: 'Senator',
    party_list: 'Party-list',
};

const precinctStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        complete: 'bg-green-500',
        transmitting: 'bg-yellow-500',
        partial: 'bg-blue-500',
        pending: 'bg-gray-400',
        error: 'bg-red-500',
    };
    return colors[status] || 'bg-gray-400';
};
</script>

<template>
    <Head title="Results" />

    <div class="mx-auto w-full lg:w-[60%]">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Tabs default-value="president">
                <div class="flex items-center justify-between">
                    <TabsList>
                        <TabsTrigger v-for="position in positions" :key="position" :value="position">
                            {{ positionLabels[position] }}
                        </TabsTrigger>
                    </TabsList>
                </div>

                <TabsContent v-for="position in positions" :key="position" :value="position" class="mt-4">
                    <Card class="rounded-2xl ring-1 ring-border">
                        <CardHeader>
                            <CardTitle>{{ positionLabels[position] }} Results</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow class="h-12">
                                        <TableHead class="w-[60px] px-4">Rank</TableHead>
                                        <TableHead class="px-4">Candidate</TableHead>
                                        <TableHead class="px-4">Party</TableHead>
                                        <TableHead class="px-4 text-right">Votes</TableHead>
                                        <TableHead class="w-[200px] px-4">% of Votes</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="(candidate, index) in tally[position]" :key="candidate.candidate_id">
                                        <TableCell class="px-4 py-4 font-bold">{{ index + 1 }}</TableCell>
                                        <TableCell class="px-4 py-4 font-medium">{{ candidate.name }}</TableCell>
                                        <TableCell class="px-4 py-4">{{ candidate.party }}</TableCell>
                                        <TableCell class="px-4 py-4 text-right">{{ candidate.vote_count.toLocaleString() }}</TableCell>
                                        <TableCell class="px-4 py-4">
                                            <div class="flex items-center gap-2">
                                                <Progress :model-value="candidate.percentage" class="h-2" />
                                                <span class="text-sm">{{ candidate.percentage }}%</span>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="!tally[position] || tally[position].length === 0">
                                        <TableCell colspan="5" class="text-center">No votes counted yet.</TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>

            <Card class="rounded-2xl ring-1 ring-border">
                <CardHeader>
                    <CardTitle>Precinct Status</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-4 gap-2 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10">
                        <button
                            v-for="precinct in precincts"
                            :key="precinct.id"
                            class="flex flex-col items-center gap-1 rounded-lg border p-2 text-center hover:bg-muted"
                            @click="selectedPrecinct = precinct"
                        >
                            <div :class="['h-3 w-3 rounded-full', precinctStatusColor(precinct.status)]" />
                            <span class="text-xs">{{ precinct.precinct_code }}</span>
                        </button>
                    </div>

                    <div class="mt-4 flex gap-4 text-sm">
                        <div class="flex items-center gap-1">
                            <div class="h-3 w-3 rounded-full bg-green-500" />
                            <span>Complete</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="h-3 w-3 rounded-full bg-yellow-500" />
                            <span>Transmitting</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="h-3 w-3 rounded-full bg-blue-500" />
                            <span>Partial</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="h-3 w-3 rounded-full bg-gray-400" />
                            <span>Pending</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="h-3 w-3 rounded-full bg-red-500" />
                            <span>Error</span>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>

    <Sheet v-model:open="selectedPrecinct">
        <SheetContent v-if="selectedPrecinct">
            <SheetHeader>
                <SheetTitle>{{ selectedPrecinct.name }}</SheetTitle>
                <SheetDescription>{{ selectedPrecinct.precinct_code }}</SheetDescription>
            </SheetHeader>
            <div class="mt-4 space-y-4">
                <div>
                    <h4 class="text-sm font-medium">Location</h4>
                    <p class="text-sm text-muted-foreground">
                        {{ [selectedPrecinct.barangay, selectedPrecinct.municipality, selectedPrecinct.province, selectedPrecinct.region].filter(Boolean).join(', ') }}
                    </p>
                </div>
                <div>
                    <h4 class="text-sm font-medium">Status</h4>
                    <Badge>{{ selectedPrecinct.status }}</Badge>
                </div>
                <div>
                    <h4 class="text-sm font-medium">Registered Voters</h4>
                    <p class="text-sm">{{ selectedPrecinct.registered_voters?.toLocaleString() || 'N/A' }}</p>
                </div>
            </div>
        </SheetContent>
    </Sheet>
</template>
