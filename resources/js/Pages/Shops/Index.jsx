import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { useForm, Head } from '@inertiajs/react';

export default function Index({ auth }) {
    const { data, setData, post, processing, reset, errors } = useForm({
        name: '',
        key: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('shops.store'), { onSuccess: () => reset() });
    };

    return (
        <AuthenticatedLayout
            navigation={false}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Shops
                </h2>
            }>
            <Head title="Shops" />

            <div className="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
                <form onSubmit={submit}>
                    <input
                        value={data.name}
                        type="text"
                        placeholder="Имя магазина"
                        className="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mb-6"
                        onChange={e => setData('name', e.target.value)}
                    />
                        <textarea
                            value={data.key}
                            placeholder="Ключ API Wildberries"
                            className="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            onChange={e => setData('key', e.target.value)}
                        ></textarea>
                        <InputError message={errors.message} className="mt-2" />
                        <PrimaryButton className="mt-4" disabled={processing}>Add Shop</PrimaryButton>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}