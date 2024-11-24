import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import React from 'react';
import { useForm } from '@inertiajs/react';

export default function AddShopForm({ closeModal }) {
    const { data, setData, post, reset, processing, errors } = useForm({
        name: '',
        key: '',
    });

    const submitAddShopData = (e) => {
        e.preventDefault();
        post(route('shops.store'), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                closeModal();
            }
        });
    };

    return (
        <div className="p-8">
            <h2 className="text-l font-bold mb-3">Добавить магазин</h2>
            <form onSubmit={submitAddShopData}>
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
                <InputError message={errors.name} className="mt-2" />
                <InputError message={errors.key} className="mt-2" />
                <PrimaryButton className="mt-4" disabled={processing}>Add Shop</PrimaryButton>
            </form>
        </div>
    );
}