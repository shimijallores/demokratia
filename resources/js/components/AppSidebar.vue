<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { FileDown, LayoutGrid, ListChecks, MapPin, Upload, Vote } from 'lucide-vue-next';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Results',
        href: '/results',
        icon: Vote,
    },
    {
        title: 'Precincts',
        href: '/precincts',
        icon: MapPin,
    },
    {
        title: 'Import',
        href: '/import',
        icon: Upload,
    },
    {
        title: 'Transmission Log',
        href: '/logs',
        icon: ListChecks,
    },
    {
        title: 'Export',
        href: '/export',
        icon: FileDown,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <div class="flex flex-col items-center gap-1 px-2 py-2">
            <Link :href="dashboard()">
                <img src="/images/demokratia.png" alt="Demokratia" class="h-16 w-16 rounded-full object-cover" />
            </Link>
            <span class="text-sm font-semibold">Demokratia</span>
        </div>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
