<script setup>
  import { computed } from 'vue';
  import { onBeforeMount } from 'vue';
  import { reactive } from 'vue';
  import { ref } from 'vue';

  defineProps({
    canLogin: {
      type: Boolean,
    },
  });
</script>

<template>
  <div>Hello</div>
</template>

<style></style>
