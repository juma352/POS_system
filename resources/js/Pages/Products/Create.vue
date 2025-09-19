<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    name: '',
    price: '',
    quantity: '',
    barcode: '',
    description: '',
    image: null,
});

const submit = () => {
    form.post(route('products.store'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Create Product" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Product</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div>
                                <InputLabel for="name" value="Name" />
                                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus autocomplete="name" />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="price" value="Price" />
                                <TextInput id="price" type="number" class="mt-1 block w-full" v-model="form.price" required />
                                <InputError class="mt-2" :message="form.errors.price" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="quantity" value="Quantity" />
                                <TextInput id="quantity" type="number" class="mt-1 block w-full" v-model="form.quantity" required />
                                <InputError class="mt-2" :message="form.errors.quantity" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="barcode" value="Barcode" />
                                <TextInput id="barcode" type="text" class="mt-1 block w-full" v-model="form.barcode" />
                                <InputError class="mt-2" :message="form.errors.barcode" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="description" value="Description" />
                                <textarea id="description" class="mt-1 block w-full" v-model="form.description"></textarea>
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="image" value="Image" />
                                <input id="image" type="file" @input="form.image = $event.target.files[0]" />
                                <InputError class="mt-2" :message="form.errors.image" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Create
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>