import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import AddShopForm from '@/Pages/Shops/Components/Forms/AddShopForm';
import Shop from '@/Pages/Shops/Components/Shop'
import PrimaryButton from '@/Components/PrimaryButton';
import Modal from '@/Components/Modal';
import { Head } from '@inertiajs/react';

export default function Index({ ownShops, availableShops }) {
    const [addShopModalIsOpen, setAddShopModalIsOpen] = useState(false);
    console.log(ownShops);

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
                    Магазины
                </h2>
            }>
            <Head title="Shops" />

            <div className="max-w-2xl mx-auto">
                <div className="p-2 sm:p-3 lg:p-6">
                    {ownShops.length !== 0 &&
                        <>
                            <h2 className="text-xl font-bold mb-3">Мои магазины</h2>
                            {ownShops.map((shop) =>
                                <Shop shop={shop} key={shop.id} />
                            )}
                        </>
                    }

                    <PrimaryButton
                        className="mt-4"
                        onClick={(e) => handleAddShopButtonClick(e)}>
                        Добавить магазин
                    </PrimaryButton>
                </div>
                <div className="p-2 sm:p-3 lg:p-6">
                    {availableShops.length !== 0 &&
                        <>
                            <h2 className="text-xl font-bold mb-2">Доступные магазины</h2>
                            {availableShops.map((shop) =>
                                <Shop shop={shop} key={shop.id} />
                            )}
                        </>
                    }
                </div>
            </div>
            <Modal show={addShopModalIsOpen} onClose={closeAddShopModal}>
                <AddShopForm closeModal={() => closeAddShopModal()} />
            </Modal>
        </AuthenticatedLayout>
    );
}