import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import React from 'react';
import { useForm } from '@inertiajs/react';

export default function AddShopForm({ closeModal }) {
    const { data, setData, post, reset, processing, errors } = useForm({
        name: '',
        key: '',
        settings: {
            commission: 0,
            logistics: 0,
            percentile_coefficient: 0,
            weight_coefficient: 0,
            gsheet_url: ''
        }
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
                <div className="mb-4">
                    <input
                        value={data.name}
                        type="text"
                        placeholder="Имя магазина"
                        className="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        onChange={e => setData('name', e.target.value)}
                    />
                    <InputError message={errors.name} className="mt-2" />
                </div>
                <div className="mb-4">
                    <input
                        value={data.key}
                        placeholder="Ключ API WB"
                        className="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        onChange={e => setData('key', e.target.value)}
                    ></input>
                    <InputError message={errors.key} className="mt-2" />
                </div>
                <div className="mb-4">
                    <input
                        id="gsheet_url"
                        type="url"
                        className="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        value={data.settings?.gsheet_url || ''}
                        placeholder="Ссылка на Google таблицу с НСИ"
                        onChange={e => setData('settings', {
                            ...data.settings,
                            gsheet_url: e.target.value
                        })}
                    />
                    <InputError message={errors['settings.gsheet_url']} className="mt-2" />
                </div>
                <div className="grid grid-cols-2 gap-4 mt-4 w-64">
                    <div>
                        <InputLabel value="Комиссия (%)" />
                        <input
                            value={data.settings.commission}
                            type="number"
                            className="block w-24 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            onChange={e => setData('settings', { ...data.settings, commission: parseInt(e.target.value) })}
                        />
                        <InputError message={errors['settings.commission']} className="mt-2" />
                    </div>
                    <div>
                        <InputLabel value="Логистика" />
                        <input
                            value={data.settings.logistics}
                            type="number"
                            className="block w-24 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            onChange={e => setData('settings', { ...data.settings, logistics: parseInt(e.target.value) })}
                        />
                        <InputError message={errors['settings.logistics']} className="mt-2" />
                    </div>
                    <div>
                        <InputLabel value="Коэф. процентиля" />
                        <input
                            value={data.settings.percentile_coefficient}
                            type="number"
                            step="0.01"
                            className="block w-24 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            onChange={e => setData('settings', { ...data.settings, percentile_coefficient: parseFloat(e.target.value) })}
                        />
                        <InputError message={errors['settings.percentile_coefficient']} className="mt-2" />
                    </div>
                    <div>
                        <InputLabel value="Коэф. веса" />
                        <input
                            value={data.settings.weight_coefficient}
                            type="number"
                            step="0.01"
                            className="block w-24 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            onChange={e => setData('settings', { ...data.settings, weight_coefficient: parseFloat(e.target.value) })}
                        />
                        <InputError message={errors['settings.weight_coefficient']} className="mt-2" />
                    </div>
                </div>
                <PrimaryButton className="mt-4" disabled={processing}>Добавить</PrimaryButton>
            </form>
        </div>
    );
}
