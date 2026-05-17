<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/InputError.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Import',
                href: '/import',
            },
        ],
    },
});

const props = defineProps<{
    precincts: Array<{ id: number; precinct_code: string; name: string }>;
}>();

const page = usePage();
const flashMessage = computed(() => page.props.flash?.success as string | undefined);
const importErrors = computed(() => (page.props.flash?.import_errors as string[] | undefined) ?? []);

const flashForm = useForm({
    precinct_code: '',
    file: null as File | null,
});

const csvForm = useForm({
    file: null as File | null,
});

function handleFlashFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        flashForm.file = target.files[0];
    }
}

function handleCsvFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        csvForm.file = target.files[0];
    }
}

function submitFlash() {
    if (!flashForm.file) {
        return;
    }

    const formData = new FormData();
    formData.append('precinct_code', flashForm.precinct_code);
    formData.append('file', flashForm.file);

    flashForm.post('/import', {
        data: formData,
        forceFormData: true,
        preserveScroll: true,
    });
}

function submitCsv() {
    if (!csvForm.file) {
        return;
    }

    const formData = new FormData();
    formData.append('file', csvForm.file);

    csvForm.post('/import/csv', {
        data: formData,
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            csvForm.reset();
        },
    });
}
</script>

<template>
    <Head title="Import" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div v-if="flashMessage" class="rounded-lg bg-emerald-50 p-4 text-sm text-emerald-800 ring-1 ring-emerald-200">
            {{ flashMessage }}
        </div>

        <div v-if="importErrors.length > 0" class="rounded-lg bg-red-50 p-4 text-sm text-red-800 ring-1 ring-red-200">
            <p class="font-medium mb-2">Import Errors:</p>
            <ul class="list-disc pl-5 space-y-1">
                <li v-for="(error, index) in importErrors" :key="index">{{ error }}</li>
            </ul>
        </div>

        <Card class="rounded-2xl ring-1 ring-border max-w-lg">
            <CardHeader>
                <CardTitle>Import CSV</CardTitle>
                <CardDescription>Upload a CSV file to import votes or candidates. Ballot CSVs need columns: Ballot ID, Position, Candidate, Party, Ballot Number. Candidate CSVs need: ballot_number, name, position, party.</CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submitCsv" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="csv-file">CSV File</Label>
                        <Input id="csv-file" type="file" accept=".csv,.txt" @change="handleCsvFileChange" />
                        <InputError :message="csvForm.errors.file" />
                    </div>

                    <Button type="submit" :disabled="csvForm.processing || !csvForm.file">
                        Import CSV
                    </Button>
                </form>
            </CardContent>
        </Card>

        <Card class="rounded-2xl ring-1 ring-border max-w-lg">
            <CardHeader>
                <CardTitle>Import Flash Drive</CardTitle>
                <CardDescription>Upload a .acm file from a flash drive to import vote data.</CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submitFlash" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="precinct_code">Precinct</Label>
                        <Select v-model="flashForm.precinct_code">
                            <SelectTrigger>
                                <SelectValue placeholder="Select precinct" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="precinct in precincts" :key="precinct.id" :value="precinct.precinct_code">
                                    {{ precinct.precinct_code }} - {{ precinct.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="flashForm.errors.precinct_code" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="file">.acm File</Label>
                        <Input id="file" type="file" accept=".acm,.bin" @change="handleFlashFileChange" />
                        <InputError :message="flashForm.errors.file" />
                    </div>

                    <Button type="submit" :disabled="flashForm.processing || !flashForm.file">
                        Import File
                    </Button>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
