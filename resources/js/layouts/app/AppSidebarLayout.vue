<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Sun, Moon, ChevronsUpDown } from 'lucide-vue-next';
import { Toaster } from '@/components/ui/sonner';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const currentPath = computed(() => page.url);

const navItems = computed(() => [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Results', href: '/results' },
    { title: 'Precincts', href: '/precincts' },
    { title: 'Import', href: '/import' },
    { title: 'Transmission Log', href: '/logs' },
    { title: 'Export', href: '/export' },
]);

const isDark = ref(false);

onMounted(() => {
    isDark.value = document.documentElement.classList.contains('dark');
});

function toggleTheme() {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle('dark', isDark.value);
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
}
</script>

<template>
    <div class="min-h-screen bg-background">
        <div class="mx-auto w-full lg:w-[60%]">
            <nav class="sticky top-0 z-50 border-b bg-background/80 backdrop-blur-md">
                <div class="flex items-center justify-between px-6 py-3">
                    <Link href="/dashboard" class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 overflow-hidden rounded-full bg-primary/10 ring-1 ring-border">
                            <img src="/images/demokratia.png" alt="Demokratia" class="h-full w-full object-cover" />
                        </div>
                        <span class="text-lg font-bold tracking-tight">Demokratia Central</span>
                    </Link>
                    <div class="flex items-center gap-4">
                        <button
                            @click="toggleTheme"
                            class="flex h-8 w-8 items-center justify-center rounded-full text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                        >
                            <Sun v-if="isDark" class="h-4 w-4" />
                            <Moon v-else class="h-4 w-4" />
                        </button>

                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <button class="flex items-center gap-2 rounded-full border bg-muted/40 p-1 pr-3 transition-colors hover:bg-accent">
                                    <UserInfo :user="user" />
                                    <ChevronsUpDown class="h-3.5 w-3.5 text-muted-foreground" />
                                </button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent class="min-w-56 rounded-lg" align="end" :side-offset="4">
                                <UserMenuContent :user="user" />
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
            </nav>
            <div class="px-6 py-6">
                <nav class="mb-6 flex gap-1 rounded-xl bg-card p-1 shadow-sm ring-1 ring-border">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        :class="[
                            'flex flex-1 items-center justify-center rounded-lg px-3 py-2 text-sm font-medium transition-all text-center',
                            currentPath === item.href || (item.href !== '/dashboard' && currentPath.startsWith(item.href))
                                ? 'bg-primary text-primary-foreground shadow-sm'
                                : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                        ]"
                    >
                        {{ item.title }}
                    </Link>
                </nav>
                <slot />
            </div>
        </div>
        <Toaster />
    </div>
</template>
