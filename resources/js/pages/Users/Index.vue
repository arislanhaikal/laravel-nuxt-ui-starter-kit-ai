<script setup lang="ts">
  import Layout from '@/layouts/Default.vue'
  import type { TableColumn } from '@nuxt/ui'
  import type { Row } from '@tanstack/table-core'
  import { upperFirst } from 'scule'
  import { h, ref, resolveComponent, useTemplateRef, computed, watch } from 'vue'
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

  type UsersPayload = {
    data: AppUser[]
    meta?: {
      current_page: number
      last_page: number
      per_page: number
      total: number
    }
    current_page?: number
    last_page?: number
    per_page?: number
    total?: number
  }

  const props = defineProps<{
    users?: UsersPayload
    filters?: {
      search?: string
    }
  }>()

  const usersData = computed(() => props.users?.data ?? [])
  const usersMeta = computed(() => {
    const meta = props.users?.meta
    if (meta) {
      return meta
    }

    return {
      current_page: props.users?.current_page ?? 1,
      last_page: props.users?.last_page ?? 1,
      per_page: props.users?.per_page ?? 5,
      total: props.users?.total ?? 0,
    }
  })

  const currentPage = ref(usersMeta.value.current_page)

  watch(
    () => usersMeta.value.current_page,
    (value) => {
      currentPage.value = value
    },
  )

  const UAvatar = resolveComponent('UAvatar')
  const UButton = resolveComponent('UButton')
  const UBadge = resolveComponent('UBadge')
  const UDropdownMenu = resolveComponent('UDropdownMenu')
  const UCheckbox = resolveComponent('UCheckbox')

  const table = useTemplateRef('table')

  const columnVisibility = ref()
  const rowSelection = ref({})
  const search = ref(props.filters?.search ?? '')
  const isLoading = ref(false)

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
  const deletingUser = ref<AppUser | null>(null)
  const isDeleteModalOpen = ref(false)

  function editUser(user: AppUser) {
    editingUser.value = user
    isAddModalOpen.value = true
  }

  function deleteUser(user: AppUser) {
    deletingUser.value = user
    isDeleteModalOpen.value = true
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

  function handleUserSaved() {
    isAddModalOpen.value = false
    editingUser.value = null
    router.reload({ only: ['users'] })
  }

  function fetchUsers(page: number) {
    isLoading.value = true
    router.get('/users', { page, search: search.value || undefined }, {
      preserveScroll: true,
      preserveState: true,
      only: ['users', 'filters'],
      onFinish: () => {
        isLoading.value = false
      },
    })
  }

  function handlePageChange(page: number) {
    if (page === usersMeta.value.current_page) {
      return
    }

    fetchUsers(page)
  }

  let searchTimeout: ReturnType<typeof setTimeout> | null = null
  watch(search, (value) => {
    if (searchTimeout) {
      clearTimeout(searchTimeout)
    }

    searchTimeout = setTimeout(() => {
      if (value !== (props.filters?.search ?? '')) {
        fetchUsers(1)
      }
    }, 300)
  })
</script>

<template>
  <UserDeleteModal
    v-model:open="isDeleteModalOpen"
    :user="deletingUser"
    @deleted="handleUserSaved"
  />
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
          v-model="search"
          class="max-w-sm"
          icon="i-lucide-search"
          placeholder="Filter emails..."
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
        v-model:column-visibility="columnVisibility"
        v-model:row-selection="rowSelection"
        class="shrink-0"
        :data="usersData"
        :columns="columns"
        :loading="isLoading"
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
          {{ usersMeta.total }} row(s) selected.
        </div>

        <div class="flex items-center gap-1.5">
          <UPagination
            v-model:page="currentPage"
            :items-per-page="usersMeta.per_page"
            :total="usersMeta.total"
            @update:page="handlePageChange"
          />
        </div>
      </div>
    </template>
  </UDashboardPanel>
</template>

