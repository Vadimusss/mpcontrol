import { useState } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';
import Modal from '@/Components/Modal';
import AddCustomerForm from '@/Pages/Shops/Components/Forms/AddCustomerForm';
import ChangeApiKeyForm from '@/Pages/Shops/Components/Forms/ChangeApiKeyForm';
import DeleteShopConfirmModal from '@/Pages/Shops/Components/Modals/DeleteShopConfirmModal';
import Customers from '@/Pages/Shops/Components/Customers';
import { usePage } from '@inertiajs/react';
import { Inertia } from '@inertiajs/inertia';
import moment from 'moment';

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

    const isKeyOk = shop.api_key.is_active;
    const lastKeyCheckTime = moment(shop.api_key.updated_at).format('YYYY-MM-DD HH:mm:ss');

    return (
        <div className="border border-gray-300 rounded-md shadow-sm bg-white mb-2 p-2">
            <p className='font-semibold'>{shop.name}</p>
            <p>Ключ действует до: {shop.api_key.expires_at}</p>
            <p>Последняя проверка ключа: {lastKeyCheckTime} / {isKeyOk ? 'OK' : 'ERROR'}</p>
            <p>Владелец: {shop.owner.name}</p>
            {(shop.customers.length !== 0 && shop.owner.id === auth.user.id) &&
                <Customers shopId={shop.id} customers={shop.customers} />
            }
            <div className="flex flex-col">
                {(shop.owner.id === auth.user.id) &&
                    <>
                        <PrimaryButton
                            className="mt-4 max-w-fit"
                            onClick={(e) => handleAddCustomer(e)}>
                            Добавить пользователя
                        </PrimaryButton>
                        <div className="flex gap-x-2">
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
                        </div>
                        <Modal show={modalState.addCustomerModalIsOpen} onClose={closeAddCustomerModal}>
                            <AddCustomerForm currentShopId={shop.id} closeModal={() => closeAddCustomerModal()} />
                        </Modal><Modal show={modalState.changeApiKeyModalIsOpen} onClose={closeChangeApiKeyModal}>
                            <ChangeApiKeyForm currentShopId={shop.id} closeModal={() => closeChangeApiKeyModal()} />
                        </Modal>
                        <DeleteShopConfirmModal
                            shop={shop}
                            maxWidth={'xl'}
                            IsOpen={modalState.deleteShopConfirmModalIsOpen}
                            closeModal={closeDeleteModal} />
                    </>}
                <PrimaryButton
                    className="mt-4 max-w-fit"
                    onClick={(e) => Inertia.get(route('shops.workspaces.index', shop.id))}>
                    Вход
                </PrimaryButton>
            </div>
        </div >
    );
}
