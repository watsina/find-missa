<script setup>
  import { useForm } from '@/plugins';
  import FormSection from '@/components/FormSection.vue';
  import InputError from '@/components/InputError.vue';
  import InputLabel from '@/components/InputLabel.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';

  const form = useForm({
    name: '',
  });

  const createTeam = () => {
    form.post('/api/v1/teams', {
      errorBag: 'createTeam',
      preserveScroll: true,
    });
  };
</script>

<template>
  <FormSection @submitted="createTeam">
    <template #title> Team Details </template>

    <template #description> Create a new team to collaborate with others on projects. </template>

    <template #form>
      <div class="col-span-6">
        <InputLabel value="Team Owner" />

        <div class="flex items-center mt-2">
          <img
            :alt="$page.props.auth.user.name"
            class="object-cover w-12 h-12 rounded-full"
            :src="$page.props.auth.user.profile_photo_url"
          />

          <div class="ms-4 leading-tight">
            <div class="text-gray-900">{{ $page.props.auth.user.name }}</div>
            <div class="text-sm text-gray-700">
              {{ $page.props.auth.user.email }}
            </div>
          </div>
        </div>
      </div>

      <div class="col-span-6 sm:col-span-4">
        <InputLabel for="name" value="Team Name" />
        <TextInput id="name" v-model="form.name" autofocus class="block w-full mt-1" type="text" />
        <InputError class="mt-2" :message="form.errors.name" />
      </div>
    </template>

    <template #actions>
      <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
        Create
      </PrimaryButton>
    </template>
  </FormSection>
</template>
