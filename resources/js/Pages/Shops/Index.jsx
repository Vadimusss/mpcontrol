import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { useForm, Head } from '@inertiajs/react';

export default function Index({ auth, ownShops }) {
    /*     const {
            data: addShopData,
            setData: setAddShopData,
            post: postAddShopData,
            processing: processingAddShopData,
            reset: resetAddShopData,
            errors: addShopDataErrors } = useForm({
                name: '',
                key: '',
            }); */

    // console.log(useForm({}));

    const addShopForm = useForm({
        name: '',
        key: '',
    });

    const submitAddShopData = (e) => {
        e.preventDefault();
        addShopForm.post(route('shops.store'), { onSuccess: () => addShopForm.reset() });
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

            <div className="max-w-2xl mx-auto">
                <div className="p-4 sm:p-6 lg:p-8">
                    <h2 className="text-xl font-bold mb-3">Мои магазины</h2>
                    {ownShops && ownShops.map((shop) =>
                        <div key={shop.id} className="border border-gray-300 rounded-md shadow-sm bg-white mb-2 p-2">
                            <p>ID: {shop.id}</p>
                            <p>Name: {shop.name}</p>
                            <p>Owner: {shop.owner.name}</p>
                        </div>
                    )}
                </div>
                <div className="p-4 sm:p-6 lg:p-8">
                    <h2 className="text-xl font-bold mb-2">Доступные магазины</h2>
                </div>
                <div className="p-4 sm:p-6 lg:p-8">
                    <h2 className="text-xl font-bold mb-2">Добавить магазин</h2>
                    <form onSubmit={submitAddShopData}>
                        <input
                            value={addShopForm.data.name}
                            type="text"
                            placeholder="Имя магазина"
                            className="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mb-6"
                            onChange={e => addShopForm.setData('name', e.target.value)}
                        />
                        <textarea
                            value={addShopForm.data.key}
                            placeholder="Ключ API Wildberries"
                            className="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            onChange={e => addShopForm.setData('key', e.target.value)}
                        ></textarea>
                        <InputError message={addShopForm.errors.name} className="mt-2" />
                        <InputError message={addShopForm.errors.key} className="mt-2" />
                        <PrimaryButton className="mt-4" name="addShopForm" disabled={addShopForm.processing}>Add Shop</PrimaryButton>
                    </form>
                </div>

            </div>
        </AuthenticatedLayout>
    );
}