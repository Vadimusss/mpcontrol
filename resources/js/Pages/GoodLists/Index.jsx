import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import AddGoodListForm from '@/Pages/GoodLists/Components/Forms/AddGoodListForm';
import GoodsTable from '@/Pages/GoodLists/Components/GoodsTable';
import GoodListCard from '@/Pages/GoodLists/Components/GoodListCard';
import Modal from '@/Components/Modal';
import { Head, usePage } from '@inertiajs/react';

export default function GoodLists({ shop, ownGoodLists, goodLists, goods }) {
    const { auth } = usePage().props;
    const [addGoodListModalIsOpen, setAddGoodListModalIsOpen] = useState(false);
    const [selectedGoodsId, setSelectedGoodsId] = useState([]);

    const handleAddGoodListButtonClick = () => {
        setAddGoodListModalIsOpen(true);
    };

    const closeAddGoodListModal = (() => {
        setAddGoodListModalIsOpen(false);
    });
    // console.log(goodLists);
    return (
        <AuthenticatedLayout
            navigation={true}
            shopId={shop.id}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    {shop.name}
                </h2>
            }>
            <Head title={`Магазин ${shop.name}`} />

            <div className="max-w-2xl mx-auto">
                <div className="p-2 sm:p-3 lg:p-6">
                    {goodLists.length !== 0 &&
                        <>
                            <h2 className="text-xl font-bold mb-3">Списки товаров магазина</h2>
                            {goodLists.map((goodList) =>
                                <GoodListCard shopId={shop.id} goodList={goodList} key={goodList.id} selectedGoodsId={selectedGoodsId} />)}
                        </>
                    }
                    {ownGoodLists.length !== 0 &&
                        <>
                            <h2 className="text-xl font-bold mb-3">Мои списки товаров</h2>
                            {ownGoodLists.map((goodList) =>
                                <GoodListCard shopId={shop.id} goodList={goodList} key={goodList.id} selectedGoodsId={selectedGoodsId}/>)}
                        </>
                    }
                    <PrimaryButton
                        disabled={selectedGoodsId.length === 0}
                        className="mt-4 mb-2"
                        onClick={() => handleAddGoodListButtonClick()}>
                        Добавить список товаров
                    </PrimaryButton>

                    <h2 className="text-xl font-bold mb-3">Все товары магазина {shop.name}</h2>
                    <GoodsTable goods={goods} selectedGoodsId={selectedGoodsId} setSelectedGoodsId={setSelectedGoodsId} />
                </div>
            </div>
            <Modal show={addGoodListModalIsOpen} onClose={closeAddGoodListModal}>
                <AddGoodListForm shopId={shop.id} selectedGoodsId={selectedGoodsId} closeModal={() => closeAddGoodListModal()} />
            </Modal>
        </AuthenticatedLayout>
    );
}
