<script setup lang="ts">
  import { useAttrs } from 'vue'
  import NuxtModal from '@nuxt/ui-modal'

  defineOptions({ inheritAttrs: false })

  const props = withDefaults(defineProps<{
    dismissible?: boolean
  }>(), {
    dismissible: false,
  })

  const attrs = useAttrs()
</script>

<template>
  <NuxtModal v-bind="attrs" :dismissible="props.dismissible">
    <template v-for="(_, name) in $slots" #[name]="slotProps">
      <slot :name="name" v-bind="slotProps" />
    </template>
  </NuxtModal>
</template>
