import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import AddShopForm from '@/Components/Forms/AddShopForm';
import Shop from '@/Components/Shop'
import PrimaryButton from '@/Components/PrimaryButton';
import Modal from '@/Components/Modal';
import { Head, auth } from '@inertiajs/react';

export default function Index({ auth, ownShops, availableShops }) {
    const [addShopModalIsOpen, setAddShopModalIsOpen] = useState(false);

    const handleAddShopButtonClick = () => {
        setAddShopModalIsOpen(true);
    };

    const closeAddShopModal = (() => {
        setAddShopModalIsOpen(false);
    });

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
                <div className="p-2 sm:p-3 lg:p-6">
                    <h2 className="text-xl font-bold mb-3">Мои магазины</h2>
                    {ownShops && ownShops.map((shop) =>
                        <Shop shop={shop} key={shop.id} />
                    )}
                    <PrimaryButton
                        className="mt-4"
                        onClick={(e) => handleAddShopButtonClick(e)}>
                        Добавить магазин
                    </PrimaryButton>
                </div>
                <div className="p-2 sm:p-3 lg:p-6">
                    <h2 className="text-xl font-bold mb-2">Доступные магазины</h2>
                    {availableShops && availableShops.map((shop) =>
                        <Shop shop={shop} key={shop.id} />
                    )}
                </div>
            </div>
            <Modal show={addShopModalIsOpen} onClose={closeAddShopModal}>
                <AddShopForm closeModal={() => closeAddShopModal()} />
            </Modal>
        </AuthenticatedLayout>
    );
}