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
    const addCustomerForms = ownShops.reduce((acc, shop) => {
        acc[shop.id] = useForm({ email: '' });
        return acc;
    }, {});

    const addShopForm = useForm({
        name: '',
        key: '',
    });

    const submitAddShopData = (e) => {
        e.preventDefault();
        addShopForm.post(route('shops.store'), { onSuccess: () => addShopForm.reset() });
    };

    const submitAddCustomerData = (e) => {
        const shopId = e.target.dataset.shopId;
        e.preventDefault();
        addCustomerForms[shopId].post(route('shops.store'), { onSuccess: () => addCustomerForms[shopId].reset() });
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
                            <form onSubmit={submitAddCustomerData} data-shop-id={shop.id}>
                                {/* <input type="hidden" name="shopId" value="shop.id" /> */}
                                <input
                                    value={addCustomerForms[shop.id].data.email}
                                    type="text"
                                    placeholder="E-mail пользователя"
                                    className="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mb-4"
                                    onChange={e => addCustomerForms[shop.id].setData('email', e.target.value)}
                                />
                                <InputError message={addCustomerForms[shop.id].errors.email} className="mt-2" />
                                <PrimaryButton className="mt-4" disabled={addCustomerForms[shop.id].processing}>Add Customer</PrimaryButton>
                            </form>
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