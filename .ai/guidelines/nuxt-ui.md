# Nuxt UI v4 Guideline

## Core Principle

> **Always prefer Nuxt UI components over custom UI.**

If a component exists in Nuxt UI:

- It **must** be used
- Re-implementation is **not allowed**
- Styling duplication is **not acceptable**
- Customize theme or component configuration in `vite.config.ts`

This ensures:

- Consistent UX across all Boost modules
- Predictable behavior for auth, forms, and feedback
- Faster onboarding for new developers

## UI/UX Design Rules

### Visual Direction

- Minimal
- Neutral
- Functional
- Product-first (not marketing-heavy)

Avoid:

- Decorative UI
- Over-animated components
- Custom gradients or shadows

---

### Spacing & Layout

- Use Nuxt UI spacing defaults
- Use `UContainer`, `UCard`, `UDivider`
- Avoid manual padding unless necessary

Correct:

```vue
<UCard>
  Content
</UCard>
```

Avoid:

```html
<div class="rounded-xl p-6 shadow"></div>
```

---

## Authentication UI

### Login Form Example

```vue
<template>
    <Form
      v-bind="AuthenticatedSessionController.store.form()"
      :reset-on-success="['password']"
      v-slot="{ errors, processing }"
      class="flex flex-col gap-6"
    >
      <div class="grid gap-6">
        <UFormField name="email" :error="errors.email" label="Email address">
          <UInput type="email" class="w-full" autocomplete="email" placeholder="email@example.com" autofocus required />
        </UFormField>

        <UFormField name="password" :error="errors.password" label="Password">
          <UInput type="password" class="w-full" autocomplete="current-password" placeholder="Password" required />
          <template #hint>
            <TextLink v-if="canResetPassword" :href="request()" class="text-sm text-primary" :tabindex="5"> Forgot password? </TextLink>
          </template>
        </UFormField>

        <UFormField name="remember" :error="errors.password">
          <UCheckbox label="Remember me" />
        </UFormField>

        <UButton :loading="processing" type="submit" block class="mt-4">Log in</UButton>
      </div>
</template>
```

---

## Forms & Validation

### Server-Side Validation Feedback

Laravel Boost validation errors must be mapped to Nuxt UI:

```vue
<UFormGroup label="Email" :error="errors?.email?.[0]">
  <UInput v-model="form.email" />
</UFormGroup>
```

Never display raw error JSON.

---

## Data Presentation

### Table from Boost Resource

```vue
<script setup>
  const { data } = await useFetch('/api/users')
</script>

<template>
  <UCard>
    <UTable
      :rows="data.data"
      :columns="[
        { key: 'name', label: 'Name' },
        { key: 'email', label: 'Email' },
      ]"
    />
  </UCard>
</template>
```

---

## Modal & Action Confirmation

All destructive actions **must** require confirmation.

```vue
<UModal v-model="open">
  <UCard>
    <template #header>
      Confirm Deletion
    </template>

    This action cannot be undone.

    <template #footer>
      <div class="flex justify-end gap-2">
        <UButton variant="ghost" @click="open = false">
          Cancel
        </UButton>
        <UButton color="red">
          Delete
        </UButton>
      </div>
    </template>
  </UCard>
</UModal>
```

---

## Notification & Feedback

### API Success

```vue
<UAlert color="green" variant="soft" title="Success" description="Data saved successfully." />
```

### API Error

```vue
<UAlert color="red" variant="soft" title="Error" description="Something went wrong." />
```

---

## Tailwind Usage Policy

Allowed:

- Layout (`flex`, `grid`, `gap`)
- Positioning (`max-w`, `mx-auto`)

Disallowed:

- Custom button styles
- Custom inputs
- Visual duplication of Nuxt UI components

---

## Custom Components Rule (Strict)

Custom components are allowed **only if**:

1. Nuxt UI has no equivalent
2. Component wraps Nuxt UI internally
3. Approved by technical lead

Example (Allowed):

```vue
<!-- StatusBadge.vue -->
<UBadge :color="status === 'active' ? 'green' : 'gray'">
  {{ status }}
</UBadge>
```

---

## Page Structure Convention

```vue
<UContainer>
  <UCard>
    <template #header>
      Page Title
    </template>

    Page Content
  </UCard>
</UContainer>
```

---

## The Real Use Case

You can check /users for real use case for create, update, delete data using Nuxt UI
