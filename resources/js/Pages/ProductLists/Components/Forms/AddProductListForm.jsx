import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import React from 'react';
import { useForm } from '@inertiajs/react';

export default function AddProductListForm({ shopId, closeModal }) {
    const { data, setData, post, processing, errors } = useForm({
        type: 'addProductList',
        name: '',
        shopId: shopId,
    });

    const submitProductList = (e) => {
        e.preventDefault();
        post(route('shops.productlists.store', shopId), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            }
        });
    };

    return (
        <div className="p-8">
            <h2 className="text-l font-bold mb-3">Добавить список товаров</h2>
            <form onSubmit={submitProductList}>
                <input
                    value={data.name}
                    type="text"
                    placeholder="Название списка товаров"
                    className="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mb-4"
                    onChange={e => setData('name', e.target.value)}
                />
                <InputError message={errors.name} className="mt-2" />
                <PrimaryButton className="mt-4" disabled={processing}>Добавить</PrimaryButton>
            </form>
        </div>
    );
}