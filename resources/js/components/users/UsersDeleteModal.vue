<script setup lang="ts">
  import { router } from '@inertiajs/vue3'
  import { ref } from 'vue'

  interface AppUser {
    id: string
    uuid: string
    name: string
    email: string
    email_verified_at: string | null
    created_at: string
  }

  const props = withDefaults(
    defineProps<{
      count?: number
      selectedUsers?: AppUser[]
    }>(),
    {
      count: 0,
      selectedUsers: () => [],
    },
  )

  const emit = defineEmits<{
    deleted: []
  }>()

  const open = ref(false)
  const toast = useToast()
  const isDeleting = ref(false)

  function onSubmit() {
    if (!props.selectedUsers || props.selectedUsers.length === 0) {
      return
    }

    isDeleting.value = true
    open.value = false

    const uuids = props.selectedUsers.map((user: AppUser) => user.uuid)

    router.post('/users/bulk-delete', { uuids }, {
      preserveScroll: true,
      onSuccess: () => {
        toast.add({
          title: 'Users deleted',
          description: `${props.count} user(s) have been deleted successfully.`,
          color: 'success',
        })
        emit('deleted')
      },
      onError: (errors: Record<string, unknown>) => {
        const errorMessage = (errors?.message as string) || (errors?.uuids as string[])?.[0] || 'Failed to delete users.'
        toast.add({
          title: 'Error',
          description: errorMessage,
          color: 'error',
        })
      },
      onFinish: () => {
        isDeleting.value = false
      },
    })
  }
</script>

<template>
  <UModal
    v-model:open="open"
    :title="`Delete ${count} user${count > 1 ? 's' : ''}`"
    description="Are you sure? This action cannot be undone."
  >
    <slot />

    <template #body>
      <div class="flex justify-end gap-2">
        <UButton label="Cancel" color="neutral" variant="subtle" @click="open = false" />
        <UButton label="Delete" color="error" variant="solid" :loading="isDeleting" @click="onSubmit" />
      </div>
    </template>
  </UModal>
</template>

