<script setup lang="ts">
  import Layout from '@/layouts/Default.vue'
  import type { Period, Range } from '@/types'
  import type { DropdownMenuItem } from '@nuxt/ui'
  import { sub } from 'date-fns'
  import { ref, shallowRef } from 'vue'

  defineOptions({ layout: Layout })

  const { isNotificationsSlideoverOpen } = useDashboard()

  const items = [
    [
      {
        label: 'New mail',
        icon: 'i-lucide-send',
        to: '/inbox',
      },
      {
        label: 'New customer',
        icon: 'i-lucide-user-plus',
        to: '/customers',
      },
    ],
  ] satisfies DropdownMenuItem[][]

  const range = shallowRef<Range>({
    start: sub(new Date(), { days: 14 }),
    end: new Date(),
  })
  const period = ref<Period>('daily')
</script>

<template>
  <UDashboardPanel id="home">
    <template #header>
      <UDashboardNavbar title="Home" :ui="{ right: 'gap-3' }">
        <template #leading>
          <UDashboardSidebarCollapse as="button" :disabled="false" />
        </template>

        <template #right>
          <UTooltip text="Notifications" :shortcuts="['N']">
            <UButton color="neutral" variant="ghost" square @click="isNotificationsSlideoverOpen = true">
              <UChip color="error" inset>
                <UIcon name="i-lucide-bell" class="size-5 shrink-0" />
              </UChip>
            </UButton>
          </UTooltip>

          <UDropdownMenu :items="items">
            <UButton icon="i-lucide-plus" size="md" class="rounded-full" />
          </UDropdownMenu>
        </template>
      </UDashboardNavbar>

      <UDashboardToolbar>
        <template #left>
          <!-- NOTE: The `-ms-1` class is used to align with the `DashboardSidebarCollapse` button here. -->
          <HomeDateRangePicker v-model="range" class="-ms-1" />

          <HomePeriodSelect v-model="period" :range="range" />
        </template>
      </UDashboardToolbar>
    </template>

    <template #body>
      <div class="flex flex-col gap-6">
        <UCard>
          <template #header>
            <div>
              <p class="text-sm font-bold text-muted">Overview</p>
              <p class="text-xs text-muted">Key performance indicators for the selected period.</p>
            </div>
          </template>
          <HomeStats :period="period" :range="range" />
        </UCard>

        <UCard>
          <template #header>
            <div>
              <p class="text-sm font-bold text-muted">Trends</p>
              <p class="text-xs text-muted">Traffic and revenue movement over time.</p>
            </div>
          </template>
          <HomeChart :period="period" :range="range" />
        </UCard>

        <UCard>
          <template #header>
            <div>
              <p class="text-sm font-bold text-muted">Recent Sales</p>
              <p class="text-xs text-muted">Latest transactions with customer details.</p>
            </div>
          </template>
          <HomeSales :period="period" :range="range" />
        </UCard>
      </div>
    </template>
  </UDashboardPanel>
</template>
