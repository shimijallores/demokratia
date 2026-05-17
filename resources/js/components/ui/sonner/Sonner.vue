<script setup lang="ts">
import { Toaster as SonnerPrimitive } from 'vue-sonner';
import 'vue-sonner/style.css';

const theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
</script>

<template>
    <SonnerPrimitive
        :theme="theme"
        class="toaster group"
        position="bottom-right"
        :style="{
            '--normal-bg': 'var(--popover)',
            '--normal-text': 'var(--popover-foreground)',
            '--normal-border': 'var(--border)',
        }"
    />
</template>
