<script setup lang="ts">
  import Layout from '@/layouts/Default.vue'
  import type { TableColumn } from '@nuxt/ui'
  import { getPaginationRowModel, type Row } from '@tanstack/table-core'
  import { upperFirst } from 'scule'
  import { h, ref, resolveComponent, useTemplateRef, watch } from 'vue'
  import { router } from '@inertiajs/vue3'

  defineOptions({ layout: Layout })

  interface AppUser {
    id: string
    uuid: string
    name: string
    email: string
    email_verified_at: string | null
    created_at: string
  }

  const props = defineProps<{
    users: AppUser[]
  }>()

  const UAvatar = resolveComponent('UAvatar')
  const UButton = resolveComponent('UButton')
  const UBadge = resolveComponent('UBadge')
  const UDropdownMenu = resolveComponent('UDropdownMenu')
  const UCheckbox = resolveComponent('UCheckbox')

  const toast = useToast()
  const table = useTemplateRef('table')

  const columnFilters = ref([
    {
      id: 'email',
      value: '',
    },
  ])
  const columnVisibility = ref()
  const rowSelection = ref({})

  function getRowItems(row: Row<AppUser>) {
    return [
      {
        type: 'label',
        label: 'Actions',
      },
      {
        label: 'Edit user',
        icon: 'i-lucide-edit',
        onSelect() {
          editUser(row.original)
        },
      },
      {
        type: 'separator',
      },
      {
        label: 'Delete user',
        icon: 'i-lucide-trash',
        color: 'error',
        onSelect() {
          deleteUser(row.original)
        },
      },
    ]
  }

  const editingUser = ref<AppUser | null>(null)
  const isAddModalOpen = ref(false)

  function editUser(user: AppUser) {
    editingUser.value = user
    isAddModalOpen.value = true
  }

  function deleteUser(user: AppUser) {
    if (confirm(`Are you sure you want to delete ${user.name}? This action cannot be undone.`)) {
      router.delete(`/users/${user.uuid}`, {
        preserveScroll: true,
        onSuccess: () => {
          toast.add({
            title: 'User deleted',
            description: `${user.name} has been deleted successfully.`,
            color: 'success',
          })
        },
        onError: () => {
          toast.add({
            title: 'Error',
            description: 'Failed to delete user.',
            color: 'error',
          })
        },
      })
    }
  }

  const columns: TableColumn<AppUser>[] = [
    {
      id: 'select',
      header: ({ table }) =>
        h(UCheckbox, {
          modelValue: table.getIsSomePageRowsSelected() ? 'indeterminate' : table.getIsAllPageRowsSelected(),
          'onUpdate:modelValue': (value: boolean | 'indeterminate') => table.toggleAllPageRowsSelected(!!value),
          ariaLabel: 'Select all',
        }),
      cell: ({ row }) =>
        h(UCheckbox, {
          modelValue: row.getIsSelected(),
          'onUpdate:modelValue': (value: boolean | 'indeterminate') => row.toggleSelected(!!value),
          ariaLabel: 'Select row',
        }),
    },
    {
      accessorKey: 'id',
      header: 'ID',
    },
    {
      accessorKey: 'name',
      header: 'Name',
      cell: ({ row }) => {
        const initials = row.original.name
          .split(' ')
          .map((n) => n[0])
          .join('')
          .toUpperCase()
          .slice(0, 2)

        return h('div', { class: 'flex items-center gap-3' }, [
          h(UAvatar, {
            alt: row.original.name,
            size: 'lg',
          }, () => initials),
          h('div', undefined, [
            h('p', { class: 'font-medium text-highlighted' }, row.original.name),
            h('p', { class: 'text-sm text-muted' }, row.original.email),
          ]),
        ])
      },
    },
    {
      accessorKey: 'email',
      header: ({ column }) => {
        const isSorted = column.getIsSorted()

        return h(UButton, {
          color: 'neutral',
          variant: 'ghost',
          label: 'Email',
          icon: isSorted ? (isSorted === 'asc' ? 'i-lucide-arrow-up-narrow-wide' : 'i-lucide-arrow-down-wide-narrow') : 'i-lucide-arrow-up-down',
          class: '-mx-2.5',
          onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
        })
      },
    },
    {
      accessorKey: 'email_verified_at',
      header: 'Verified',
      cell: ({ row }) => {
        const isVerified = row.original.email_verified_at !== null
        return h(UBadge, {
          class: 'capitalize',
          variant: 'subtle',
          color: isVerified ? 'success' : 'warning',
        }, () => isVerified ? 'Verified' : 'Unverified')
      },
    },
    {
      accessorKey: 'created_at',
      header: 'Created At',
      cell: ({ row }) => {
        const date = new Date(row.original.created_at)
        return date.toLocaleDateString()
      },
    },
    {
      id: 'actions',
      cell: ({ row }) => {
        return h(
          'div',
          { class: 'text-right' },
          h(
            UDropdownMenu,
            {
              content: {
                align: 'end',
              },
              items: getRowItems(row),
            },
            () =>
              h(UButton, {
                icon: 'i-lucide-ellipsis-vertical',
                color: 'neutral',
                variant: 'ghost',
                class: 'ml-auto',
              }),
          ),
        )
      },
    },
  ]

  const pagination = ref({
    pageIndex: 0,
    pageSize: 10,
  })

  function handleUserSaved() {
    isAddModalOpen.value = false
    editingUser.value = null
    router.reload({ only: ['users'] })
  }
</script>

<template>
  <UDashboardPanel id="users">
    <template #header>
      <UDashboardNavbar title="Users">
        <template #leading>
          <UDashboardSidebarCollapse as="button" :disabled="false" />
        </template>

        <template #right>
          <UsersAddModal v-model:open="isAddModalOpen" :user="editingUser" @saved="handleUserSaved" />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="flex flex-wrap items-center justify-between gap-1.5">
        <UInput
          :model-value="table?.tableApi?.getColumn('email')?.getFilterValue() as string"
          class="max-w-sm"
          icon="i-lucide-search"
          placeholder="Filter emails..."
          @update:model-value="table?.tableApi?.getColumn('email')?.setFilterValue($event)"
        />

        <div class="flex flex-wrap items-center gap-1.5">
          <UsersDeleteModal
            :count="table?.tableApi?.getFilteredSelectedRowModel().rows.length"
            :selected-users="
              table?.tableApi
                ?.getFilteredSelectedRowModel()
                .rows.map((row) => row.original as AppUser) ?? []
            "
            @deleted="handleUserSaved"
          >
            <UButton
              v-if="table?.tableApi?.getFilteredSelectedRowModel().rows.length"
              label="Delete"
              color="error"
              variant="subtle"
              icon="i-lucide-trash"
            >
              <template #trailing>
                <UKbd>
                  {{ table?.tableApi?.getFilteredSelectedRowModel().rows.length }}
                </UKbd>
              </template>
            </UButton>
          </UsersDeleteModal>

          <UDropdownMenu
            :items="
              table?.tableApi
                ?.getAllColumns()
                .filter((column: any) => column.getCanHide())
                .map((column: any) => ({
                  label: upperFirst(column.id),
                  type: 'checkbox' as const,
                  checked: column.getIsVisible(),
                  onUpdateChecked(checked: boolean) {
                    table?.tableApi?.getColumn(column.id)?.toggleVisibility(!!checked)
                  },
                  onSelect(e?: Event) {
                    e?.preventDefault()
                  },
                }))
            "
            :content="{ align: 'end' }"
          >
            <UButton label="Display" color="neutral" variant="outline" trailing-icon="i-lucide-settings-2" />
          </UDropdownMenu>
        </div>
      </div>

      <UTable
        ref="table"
        v-model:column-filters="columnFilters"
        v-model:column-visibility="columnVisibility"
        v-model:row-selection="rowSelection"
        v-model:pagination="pagination"
        :pagination-options="{
          getPaginationRowModel: getPaginationRowModel(),
        }"
        class="shrink-0"
        :data="users ?? []"
        :columns="columns"
        :ui="{
          base: 'table-fixed border-separate border-spacing-0',
          thead: '[&>tr]:bg-elevated/50 [&>tr]:after:content-none',
          tbody: '[&>tr]:last:[&>td]:border-b-0',
          th: 'py-2 first:rounded-l-lg last:rounded-r-lg border-y border-default first:border-l last:border-r',
          td: 'border-b border-default',
        }"
      />

      <div class="mt-auto flex items-center justify-between gap-3 border-t border-default pt-4">
        <div class="text-sm text-muted">
          {{ table?.tableApi?.getFilteredSelectedRowModel().rows.length || 0 }} of
          {{ table?.tableApi?.getFilteredRowModel().rows.length || 0 }} row(s) selected.
        </div>

        <div class="flex items-center gap-1.5">
          <UPagination
            :default-page="(table?.tableApi?.getState().pagination.pageIndex || 0) + 1"
            :items-per-page="table?.tableApi?.getState().pagination.pageSize"
            :total="table?.tableApi?.getFilteredRowModel().rows.length"
            @update:page="(p: number) => table?.tableApi?.setPageIndex(p - 1)"
          />
        </div>
      </div>
    </template>
  </UDashboardPanel>
</template>

