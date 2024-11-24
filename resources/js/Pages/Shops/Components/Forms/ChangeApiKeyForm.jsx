import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import React from 'react';
import { useForm } from '@inertiajs/react';

export default function ChangeApiKeyForm({ currentShopId, closeModal }) {
    const { data, setData, patch, processing, errors } = useForm({
        type: 'changeApiKey',
        key: '',
        shopId: currentShopId,
    });

    const submitChangeApiKey = (e) => {
        e.preventDefault();
        patch(route('shops.update', currentShopId), {
            preserveScroll: true,
            onSuccess: () => closeModal()
        });
    };

    return (
        <div className="p-8">
            <h2 className="text-l font-bold mb-3">Введите новый ключ Api</h2>
            <form onSubmit={submitChangeApiKey}>
                <input
                    value={data.key}
                    type="text"
                    placeholder="Api ключ WB"
                    className="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mb-4"
                    onChange={e => setData('key', e.target.value)}
                />
                <InputError message={errors.key} className="mt-2" />
                <PrimaryButton className="mt-4" disabled={processing}>Изменить</PrimaryButton>
            </form>
        </div>
    );
}