import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import React from 'react';
import { useForm } from '@inertiajs/react';

export default function AddCustomerForm({ currentShopId, closeModal }) {
    const { data, setData, patch, processing, errors } = useForm({
        type: 'addCustomer',
        email: '',
        shopId: currentShopId,
    });

    const submitAddCustomer = (e) => {
        e.preventDefault();
        patch(route('shops.update', currentShopId), {
            preserveScroll: true,
            onSuccess: () => closeModal()
        });
    };

    return (
        <div className="p-8">
            <h2 className="text-l font-bold mb-3">Добавить пользователя в магазин</h2>
            <form onSubmit={submitAddCustomer}>
                <input
                    value={data.email}
                    type="text"
                    placeholder="E-mail пользователя"
                    className="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mb-4"
                    onChange={e => setData('email', e.target.value)}
                />
                <InputError message={errors.email} className="mt-2" />
                <PrimaryButton className="mt-4" disabled={processing}>Добавить</PrimaryButton>
            </form>
        </div>
    );
}