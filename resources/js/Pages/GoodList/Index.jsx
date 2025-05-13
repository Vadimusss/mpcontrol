import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import GoodsTable from '../GoodLists/Components/GoodsTable';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, usePage, router } from '@inertiajs/react';

export default function GoodLists({ shop, goodList, goods }) {
    const { auth } = usePage().props;
    const [selectedGoodsId, setSelectedGoodsId] = useState([]);

    const handleDeleteGoodsFromListButtonClick = (e) => {
        e.preventDefault();
        router.patch(route('shops.goodlists.update', { shop: shop.id, goodlist: goodList.id }), {
            selectedGoodsId: selectedGoodsId,
            type: 'delete',
        }, {
            preserveScroll: true,
            onSuccess: () => setSelectedGoodsId([]),
        });
    };

    return (
        <AuthenticatedLayout
            navigation={true}
            shopId={shop.id}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    {shop.name}/{goodList.name}
                </h2>
            }>
            <Head title={`Магазин ${shop.name}`} />
            <div className="max-w-2xl mx-auto">
                <div className="p-2 sm:p-3 lg:p-6">
                    {goodList.creator.id === auth.user.id &&
                        <PrimaryButton
                            disabled={selectedGoodsId.length === 0}
                            className="mt-4 mb-2"
                            onClick={(e) => handleDeleteGoodsFromListButtonClick(e)}>
                            Удалить выбранные товары из списка
                        </PrimaryButton>}
                    <h2 className="text-xl font-bold mb-3">Товары списка {goodList.name}</h2>
                    <GoodsTable
                        goods={goods} selectedGoodsId={selectedGoodsId} setSelectedGoodsId={setSelectedGoodsId} />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
