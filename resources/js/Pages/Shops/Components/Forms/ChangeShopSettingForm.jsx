import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import React from 'react';
import { useForm } from '@inertiajs/react';

export default function ChangeShopSettingForm({ shop, closeModal }) {
    const { data, setData, patch, processing, errors } = useForm({
        type: 'changeSettings',
        name: shop.name,
        key: '',
        settings: {
            commission: shop.settings?.commission || 0,
            logistics: shop.settings?.logistics || 0,
            percentile_coefficient: shop.settings?.percentile_coefficient || 0,
            weight_coefficient: shop.settings?.weight_coefficient || 0,
            gsheet_url: shop.settings?.gsheet_url || ''
        }
    });

    const submitChangeSettings = (e) => {
        e.preventDefault();
        patch(route('shops.update', shop.id), {
            preserveScroll: true,
            onSuccess: () => closeModal()
        });
    };

    return (
        <div className="p-8">
            <h2 className="text-l font-bold mb-3">Настройки магазина</h2>
            <form onSubmit={submitChangeSettings}>
                <div className="mb-4">
                    <input
                        value={data.name}
                        placeholder="Имя магазина"
                        type="text"
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
                        value={data.settings?.gsheet_url || shop.settings?.gsheet_url || ''}
                        placeholder="Ссылка на Google таблицу с НСИ"
                        onChange={e => setData('settings', {
                            ...data.settings,
                            gsheet_url: e.target.value
                        })}
                    />
                    <InputError message={errors.settings?.gsheet_url} className="mt-2" />
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
                        <InputError message={errors.commission} className="mt-2" />
                    </div>
                    <div>
                        <InputLabel value="Логистика" />
                        <input
                            value={data.settings.logistics}
                            type="number"
                            className="block w-24 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            onChange={e => setData('settings', { ...data.settings, logistics: parseInt(e.target.value) })}
                        />
                        <InputError message={errors.logistics} className="mt-2" />
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
                        <InputError message={errors.percentile_coefficient} className="mt-2" />
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
                        <InputError message={errors.weight_coefficient} className="mt-2" />
                    </div>
                </div>
                <PrimaryButton className="mt-4" disabled={processing}>Сохранить</PrimaryButton>
            </form>
        </div>
    );
}
