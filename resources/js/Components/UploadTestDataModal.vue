<template>
    <div>
        <dialog-modal :show="modalValue" @close="closeModal">
            <template #title> Upload Data </template>

            <template #content>
                <form>
                    <div class="mt-4">
                        <TextInput id="file_upload" accept=".csv,.xls,.xlsx" autofocus
                            class="block w-full mt-1 border-0 outline-none focus:outline-none file:border-0 file:mr-4 file:py-2 file:px-4 file:rounded-full file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            :error="form.errors.file" label="Upload contact file (.csv,.xls,.xlsx)"
                            placeholder="Choose a file..." required type="file"
                            @input="form.file = $event.target.files[0]" />
                        <progress v-if="form.progress" max="100" :value="form.progress.percentage">
                            {{ form.progress.percentage }}%
                        </progress>
                    </div>
                </form>
            </template>

            <template #footer>
                <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>

                <PrimaryButton class="ml-3" :class="{ 'opacity-25 cursor-progress': form.processing }"
                    :disabled="form.processing" @click="submit">
                    Upload contacts
                </PrimaryButton>
            </template>
        </dialog-modal>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DialogModal from '@/Components/DialogModal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    modalValue: Boolean,
});

const emit = defineEmits(['update:modalValue']);

const form = useForm({
    file: null,
});

const submit = () => {
    return form.post(route('upload.test-data'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: (error) => {
            console.log('failed.......😂', { error });
            form.errors.file = error;
        },
    });
};

const closeModal = () => {
    form.reset();
    emit('update:modalValue', false);
};
</script>

<style lang="scss" scoped></style>
