<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { toUrl } from '@/lib/utils';
import type { NavItem } from '@/types';

type Props = {
    items: NavItem[];
    class?: string;
};

const props = defineProps<Props>();

/** Determina si el enlace apunta a un sitio externo. */
function isExternal(href: unknown): boolean {
    const url = toUrl(href as any);

    return /^https?:\/\//i.test(url);
}
</script>

<template>
    <SidebarGroup
        :class="`group-data-[collapsible=icon]:p-0 ${props.class || ''}`"
    >
        <SidebarGroupContent>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in items" :key="item.title">
                    <SidebarMenuButton
                        class="text-muted-foreground hover:text-foreground"
                        as-child
                    >
                        <a
                            :href="toUrl(item.href)"
                            :target="
                                isExternal(item.href) ? '_blank' : undefined
                            "
                            :rel="
                                isExternal(item.href)
                                    ? 'noopener noreferrer'
                                    : undefined
                            "
                        >
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </a>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>
