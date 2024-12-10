<script setup>
  import ConfirmationModal from '@/components/ConfirmationModal.vue';
  import DangerButton from '@/components/DangerButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
</script>

<template>
  <ConfirmationModal :show="!!$notify?.confirmation?.message" @close="$notify.handleConfirm(false)">
    <template #title> {{ $notify.confirmation.options?.title || 'Are you sure?' }} </template>

    <template #content> {{ $notify.confirmation.message }} </template>

    <template #footer>
      <SecondaryButton @click="$notify.handleConfirm(false)"> Cancel </SecondaryButton>

      <DangerButton class="ms-3" @click="$notify.handleConfirm(true)"> Confirm </DangerButton>
    </template>
  </ConfirmationModal>
</template>
