import { useState } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';
import Modal from '@/Components/Modal';
import AddCustomerForm from '@/Pages/Shops/Components/Forms/AddCustomerForm';
import ChangeApiKeyForm from '@/Pages/Shops/Components/Forms/ChangeApiKeyForm';
import DeleteShopConfirmModal from '@/Pages/Shops/Components/Modals/DeleteShopConfirmModal';
import Customers from '@/Pages/Shops/Components/Customers';
import { usePage } from '@inertiajs/react';

export default function Shop({ shop }) {
    const { auth } = usePage().props;
    const [modalState, setModalIState] = useState({
        addCustomerModalIsOpen: false,
        changeApiKeyModalIsOpen: false,
        deleteShopConfirmModalIsOpen: false,
    });

    const handleAddCustomer = (e) => {
        setModalIState({ addCustomerModalIsOpen: true });
    };

    const closeAddCustomerModal = (() => {
        setModalIState({ addCustomerModalIsOpen: false });
    });

    const handleChangeApiKey = (e) => {
        setModalIState({ changeApiKeyModalIsOpen: true });
    };

    const closeChangeApiKeyModal = (() => {
        setModalIState({ changeApiKeyModalIsOpen: false });
    });

    const closeDeleteModal = (() => {
        setModalIState({ deleteShopConfirmModalIsOpen: false });
    });

    const openDeleteModal = (() => {
        setModalIState({ deleteShopConfirmModalIsOpen: true });
    });

    return (
        <div className="border border-gray-300 rounded-md shadow-sm bg-white mb-2 p-2">
            <p>ID: {shop.id}</p>
            <p>{shop.name}</p>
            <p>Владелец: {shop.owner.name}</p>
            {(shop.customers && shop.owner.id === auth.user.id) &&
                <Customers shopId={shop.id} customers={shop.customers} />
            }
            {(shop.owner.id === auth.user.id) &&
                <div className="flex flex-col">
                    <PrimaryButton
                        className="mt-4 max-w-fit"
                        onClick={(e) => handleAddCustomer(e)}>
                        Добавить пользователя
                    </PrimaryButton>
                    <PrimaryButton
                        className="mt-4 max-w-fit"
                        onClick={(e) => handleChangeApiKey(e)}>
                        Изменить ключ Api
                    </PrimaryButton>
                    <PrimaryButton
                        className="mt-4 max-w-fit"
                        onClick={(e) => openDeleteModal()}>
                        Удалить магазин
                    </PrimaryButton>
                    <Modal show={modalState.addCustomerModalIsOpen} onClose={closeAddCustomerModal}>
                        <AddCustomerForm currentShopId={shop.id} closeModal={() => closeAddCustomerModal()} />
                    </Modal>
                    <Modal show={modalState.changeApiKeyModalIsOpen} onClose={closeChangeApiKeyModal}>
                        <ChangeApiKeyForm currentShopId={shop.id} closeModal={() => closeChangeApiKeyModal()} />
                    </Modal>
                    <DeleteShopConfirmModal
                        shop={shop}
                        maxWidth={'xl'}
                        IsOpen={modalState.deleteShopConfirmModalIsOpen}
                        closeModal={closeDeleteModal}
                    />
                </div>}
        </div >
    );
}
