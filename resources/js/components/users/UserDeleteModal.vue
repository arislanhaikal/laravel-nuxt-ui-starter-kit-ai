<script setup lang="ts">
  import { router } from '@inertiajs/vue3'
  import { computed, ref } from 'vue'

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
      open?: boolean
      user?: AppUser | null
    }>(),
    {
      open: false,
      user: null,
    },
  )

  const emit = defineEmits<{
    'update:open': [value: boolean]
    deleted: []
  }>()

  const isOpen = computed({
    get: () => props.open,
    set: (value) => emit('update:open', value),
  })

  const toast = useToast()
  const isDeleting = ref(false)

  function confirmDelete() {
    if (!props.user || isDeleting.value) {
      return
    }

    isDeleting.value = true
    const user = props.user
    isOpen.value = false

    router.delete(`/users/${user.uuid}`, {
      preserveScroll: true,
      onSuccess: () => {
        toast.add({
          title: 'User deleted',
          description: `${user.name} has been deleted successfully.`,
          color: 'success',
        })
        emit('deleted')
      },
      onError: () => {
        toast.add({
          title: 'Error',
          description: 'Failed to delete user.',
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
    v-model:open="isOpen"
    :title="`Delete ${props.user?.name ?? 'user'}`"
    description="Are you sure? This action cannot be undone."
  >
    <template #body>
      <div class="space-y-4">
        <div class="flex justify-end gap-2">
          <UButton label="Cancel" color="neutral" variant="subtle" @click="isOpen = false" />
          <UButton label="Delete" color="error" variant="solid" :loading="isDeleting" @click="confirmDelete" />
        </div>
      </div>
    </template>
  </UModal>
</template>
