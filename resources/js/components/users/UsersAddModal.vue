<script setup lang="ts">
  import { Form } from '@inertiajs/vue3'
  import { computed } from 'vue'
  import { store, update } from '@/actions/App/Http/Controllers/UserController'

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
    saved: []
  }>()

  const isOpen = computed({
    get: () => props.open,
    set: (value) => emit('update:open', value),
  })

  const isEditing = computed(() => props.user !== null)

  const toast = useToast()

  function handleSuccess() {
    toast.add({
      title: 'Success',
      description: isEditing.value ? 'User updated successfully.' : 'User created successfully.',
      color: 'success',
    })
    isOpen.value = false
    emit('saved')
  }

  function handleError() {
    toast.add({
      title: 'Error',
      description: isEditing.value ? 'Failed to update user.' : 'Failed to create user.',
      color: 'error',
    })
  }
</script>

<template>
  <UModal
    v-model:open="isOpen"
    :title="isEditing ? 'Edit user' : 'New user'"
    :description="isEditing ? 'Update user information' : 'Add a new user to the database'"
  >
    <UButton :label="isEditing ? 'Edit user' : 'New user'" icon="i-lucide-plus" />

    <template #body>
      <Form
        v-if="isEditing && user"
        v-bind="update.form(user.uuid)"
        :options="{
          preserveScroll: true,
          onSuccess: handleSuccess,
          onError: handleError,
        }"
        #default="{ errors, processing }"
        class="space-y-4"
      >
        <UFormField label="Name" name="name" :error="errors.name">
          <UInput name="name" class="w-full" placeholder="John Doe" :default-value="user.name" required />
        </UFormField>

        <UFormField label="Email" name="email" :error="errors.email">
          <UInput
            name="email"
            type="email"
            class="w-full"
            placeholder="john.doe@example.com"
            :default-value="user.email"
            required
          />
        </UFormField>

        <UFormField label="New Password (leave blank to keep current)" name="password" :error="errors.password">
          <UInput name="password" type="password" class="w-full" placeholder="Leave blank to keep current password" />
        </UFormField>

        <UFormField label="Confirm Password" name="password_confirmation" :error="errors.password_confirmation">
          <UInput name="password_confirmation" type="password" class="w-full" placeholder="Confirm password" />
        </UFormField>

        <div class="flex justify-end gap-2">
          <UButton label="Cancel" color="neutral" variant="subtle" @click="isOpen = false" />
          <UButton label="Update" color="primary" variant="solid" type="submit" :disabled="processing" />
        </div>
      </Form>

      <Form
        v-else
        v-bind="store.form()"
        :options="{
          preserveScroll: true,
          onSuccess: handleSuccess,
          onError: handleError,
        }"
        #default="{ errors, processing }"
        class="space-y-4"
      >
        <UFormField label="Name" name="name" :error="errors.name">
          <UInput name="name" class="w-full" placeholder="John Doe" required />
        </UFormField>

        <UFormField label="Email" name="email" :error="errors.email">
          <UInput name="email" type="email" class="w-full" placeholder="john.doe@example.com" required />
        </UFormField>

        <UFormField label="Password" name="password" :error="errors.password">
          <UInput name="password" type="password" class="w-full" placeholder="Enter password" required />
        </UFormField>

        <UFormField label="Confirm Password" name="password_confirmation" :error="errors.password_confirmation">
          <UInput name="password_confirmation" type="password" class="w-full" placeholder="Confirm password" required />
        </UFormField>

        <div class="flex justify-end gap-2">
          <UButton label="Cancel" color="neutral" variant="subtle" @click="isOpen = false" />
          <UButton label="Create" color="primary" variant="solid" type="submit" :disabled="processing" />
        </div>
      </Form>
    </template>
  </UModal>
</template>
