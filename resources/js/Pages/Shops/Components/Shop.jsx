import { useState } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';
import Modal from '@/Components/Modal';
import AddCustomerForm from '@/Pages/Shops/Components/Forms/AddCustomerForm';
import ChangeShopSettingForm from '@/Pages/Shops/Components/Forms/ChangeShopSettingForm';
import DeleteShopConfirmModal from '@/Pages/Shops/Components/Modals/DeleteShopConfirmModal';
import Customers from '@/Pages/Shops/Components/Customers';
import { usePage, router } from '@inertiajs/react';
import moment from 'moment';

export default function Shop({ shop }) {
    const { auth } = usePage().props;
    const [modalState, setModalIState] = useState({
        addCustomerModalIsOpen: false,
        changeSettingsModalIsOpen: false,
        deleteShopConfirmModalIsOpen: false,
    });

    const handleAddCustomer = (e) => {
        setModalIState({ addCustomerModalIsOpen: true });
    };

    const closeAddCustomerModal = (() => {
        setModalIState({ addCustomerModalIsOpen: false });
    });

    const handleChangeSettings = (e) => {
        setModalIState({ changeSettingsModalIsOpen: true });
    };

    const closeChangeSettingsModal = (() => {
        setModalIState({ changeSettingsModalIsOpen: false });
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
            <p>Владелец: {shop.owner.name}</p>
            <p>Ключ действует до: {moment(shop.api_key.expires_at).format('DD.MM.YYYY HH:mm')}</p>
            <p>Последняя проверка ключа: {moment(shop.api_key.updated_at).format('DD.MM.YYYY HH:mm')} / {isKeyOk ? 'OK' : 'ERROR'}</p>
            <p>Последнее обновление НСИ: {shop.last_nsi_update ? moment(shop.last_nsi_update).format('DD.MM.YYYY HH:mm') : 'Никогда'}</p>
            {(shop.customers.length !== 0 && shop.owner.id === auth.user.id) &&
                <Customers shopId={shop.id} customers={shop.customers} />
            }
            <div className="flex flex-col">
                {(shop.owner.id === auth.user.id) &&
                    <>
                        <div className="flex gap-x-2">
                            <PrimaryButton
                                className="mt-4 max-w-fit"
                                onClick={(e) => handleAddCustomer(e)}>
                                Добавить пользователя
                            </PrimaryButton>
                            <PrimaryButton
                                className="mt-4 max-w-fit"
                                onClick={() => router.put(route('shops.update', shop.id), { type: 'update_nsi' })}
                            >
                                Обновить НСИ
                            </PrimaryButton>
                        </div>
                        <div className="flex gap-x-2">
                            <PrimaryButton
                                className="mt-4 max-w-fit"
                                onClick={(e) => handleChangeSettings(e)}>
                                Настройки
                            </PrimaryButton>
                            <PrimaryButton
                                className="mt-4 max-w-fit"
                                onClick={(e) => openDeleteModal()}>
                                Удалить магазин
                            </PrimaryButton>
                            <PrimaryButton
                                className="mt-4 max-w-fit"
                                onClick={(e) => router.get(route('shops.workspaces.index', shop.id))}>
                                Вход
                            </PrimaryButton>
                        </div>
                        <Modal show={modalState.addCustomerModalIsOpen} onClose={closeAddCustomerModal}>
                            <AddCustomerForm currentShopId={shop.id} closeModal={() => closeAddCustomerModal()} />
                        </Modal><Modal show={modalState.changeSettingsModalIsOpen} onClose={closeChangeSettingsModal}>
                            <ChangeShopSettingForm shop={shop} closeModal={() => closeChangeSettingsModal()} />
                        </Modal>
                        <DeleteShopConfirmModal
                            shop={shop}
                            maxWidth={'xl'}
                            IsOpen={modalState.deleteShopConfirmModalIsOpen}
                            closeModal={closeDeleteModal} />
                    </>}
            </div>
        </div >
    );
}
